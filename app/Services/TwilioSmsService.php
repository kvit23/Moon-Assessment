<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioSmsService implements SmsServiceInterface
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
        $this->from = config('services.twilio.phone_number');
    }

    public function send(string $phone, string $message): bool
    {
        try {
            $this->client->messages->create(
                $phone,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendVerificationCode(string $phone, string $code): bool
    {
        $message = "Your verification code is: {$code}. It will expire in 10 minutes.";
        return $this->send($phone, $message);
    }
}