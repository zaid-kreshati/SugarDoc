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

   public function handle(): void
{
    Log::info('SendWeeklyDiabetesAdvice started');

    $advices = config('diabetes_advices');
    $advice = $advices[array_rand($advices)];
    $patients = User::whereNotNull('firebase_token')->get();

    $firebaseService = new FirebaseService();

    foreach ($patients as $patient) {
        try {
            $firebaseService->sendNotification($patient->firebase_token, 'نصيحة صحية أسبوعية', $advice);
            Log::info('Notification sent', ['user_id' => $patient->id]);
        } catch (\Exception $e) {
            Log::error('Failed sending notification', [
                'user_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
}
