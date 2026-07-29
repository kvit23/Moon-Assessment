<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public $backoff = 60;

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Only send if user has email
        if ($event->user->email) {
            $event->user->notify(new WelcomeNotification());
        }
    }
}
