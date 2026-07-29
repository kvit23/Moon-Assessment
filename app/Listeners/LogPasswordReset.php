<?php

namespace App\Listeners;

use App\Events\PasswordResetCompleted;
use Illuminate\Support\Facades\Log;

class LogPasswordReset
{
    /**
     * Handle the event.
     */
    public function handle(PasswordResetCompleted $event): void
    {
        Log::info('Password reset completed', [
            'user_id' => $event->user->id,
            'phone' => $event->user->phone,
            'timestamp' => now(),
        ]);
    }
}