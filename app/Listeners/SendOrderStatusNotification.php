<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusChanged as OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Number of times to retry the job.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between retries.
     */
    public array $backoff = [10, 30, 60];

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        try {
            $user = $event->order->user;

            // Send notification
            $user->notify(new OrderStatusChangedNotification(
                $event->order,
                $event->oldStatus,
                $event->newStatus
            ));

            Log::info('Order status notification sent', [
                'order_id' => $event->order->id,
                'user_id' => $user->id,
                'new_status' => $event->newStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Order status notification failed', [
                'order_id' => $event->order->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderStatusChanged $event, \Throwable $exception): void
    {
        Log::error('Order status notification permanently failed', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}