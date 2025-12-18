<?php

namespace App\Jobs;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;



class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->mailData = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(MailService $mailService): void
    {
        Log::info('Secrets folder: ' . implode(', ', scandir('/etc/secrets')));

        $mailService->sendVerificationEmail($this->mailData);

    }
}
