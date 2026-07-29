<?php

namespace App\Services;

interface SmsServiceInterface
{
    /**
     * Send an SMS message.
     */
    public function send(string $phone, string $message): bool;

    /**
     * Send a verification code via SMS.
     */
    public function sendVerificationCode(string $phone, string $code): bool;
}