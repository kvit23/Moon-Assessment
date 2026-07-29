<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MockSmsService implements SmsServiceInterface
{
    public function send(string $phone, string $message): bool
    {
        // Log the SMS for development/testing
        Log::info('SMS sent (MOCK)', [
            'phone' => $phone,
            'message' => $message,
        ]);

        // In a real implementation, this would call an SMS gateway
        return true;
    }

    public function sendVerificationCode(string $phone, string $code): bool
    {
        $message = "Your verification code is: {$code}. It will expire in 10 minutes.";
        
        return $this->send($phone, $message);
    }
}