<?php

namespace App\Providers;

use App\Services\MockSmsService;
use App\Services\SmsServiceInterface;
use App\Services\TwilioSmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsServiceInterface::class, function ($app) {
            // Use environment variable to switch between providers
            $driver = config('services.sms.driver', 'mock');

            return match($driver) {
                'twilio' => new TwilioSmsService(),
                default => new MockSmsService(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}