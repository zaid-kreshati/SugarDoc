<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseService;


use App\Models\Notification;
use Carbon\Carbon;

class SendWeeklyDiabetesAdvice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FirebaseService $firebase): void
    {
        Log::info('SendWeeklyDiabetesAdvice started for user: ' );

        $advices = config('diabetes_advices');

        // اختيار نصيحة عشوائية
        $advice = $advices[array_rand($advices)];

        // جلب جميع المرضى
        $patients = User::where('role', 'patient')->whereNotNull('firebase_token')
        ->get();

        foreach ($patients as $patient) {
            // داخل foreach
            $firebase->sendNotification(
                        $patient->firebase_token,
                        'نصيحة صحية أسبوعية',
                        $advice
                    );

            Log::info('Weekly Diabetes Advice Sent', [
                'user_id' => $patient->id,
                'advice' => $advice,
            ]);

             Notification::create([
            'user_id' => $patient->id,
            'title' => 'نصيحة صحية أسبوعية',
            'message' => $advice,
            'sent_at' => Carbon::now(),
        ]);


        }
    }
}
