<?php

namespace App\Listeners;

use App\Events\PhoneVerificationRequested;
use App\Services\SmsServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVerificationCodeSms implements ShouldQueue
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
    public function handle(PhoneVerificationRequested $event): void
    {
        $this->smsService->sendVerificationCode(
            $event->user->phone,
            $event->verificationCode->code
        );
    }
}