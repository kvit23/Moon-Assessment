<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Models\User;
use App\Notifications\NewProductAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyUsersAboutNewProduct implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        try {
            // Get all active users who want notifications
            // For simplicity, we'll notify all active users
            // In production, you'd check user preferences
            $users = User::active()->get();

            // If no users, just log and return
            if ($users->isEmpty()) {
                Log::info('No active users to notify about new product', [
                    'product_id' => $event->product->id,
                ]);
                return;
            }

            // Send notification to each user
            // Each notification is queued individually
            foreach ($users as $user) {
                $user->notify(new NewProductAvailable($event->product));
            }

            Log::info('New product notifications sent', [
                'product_id' => $event->product->id,
                'user_count' => $users->count(),
            ]);

        } catch (\Exception $e) {
            // Log error but don't re-throw
            // This prevents the job from failing
            Log::error('Failed to send product notifications', [
                'product_id' => $event->product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductCreated $event, \Throwable $exception): void
    {
        Log::error('Product notification job failed', [
            'product_id' => $event->product->id,
            'error' => $exception->getMessage(),
        ]);
    }
}