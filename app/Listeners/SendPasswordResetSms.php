<?php

namespace App\Listeners;

use App\Events\PasswordResetRequested;
use App\Services\SmsServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPasswordResetSms implements ShouldQueue
{
    use InteractsWithQueue;

    protected SmsServiceInterface $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordResetRequested $event): void
    {
        $this->smsService->sendVerificationCode(
            $event->user->phone,
            $event->verificationCode->code
        );
    }
}