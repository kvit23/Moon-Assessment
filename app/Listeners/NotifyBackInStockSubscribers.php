<?php

namespace App\Listeners;

use App\Events\BackInStock;
use App\Models\BackInStockSubscription;
use App\Notifications\ProductBackInStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyBackInStockSubscribers implements ShouldQueue
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
    public function handle(BackInStock $event): void
    {
        try {
            //CRITICAL: Find ONLY waiting subscriptions for this product
            $subscriptions = BackInStockSubscription::forProduct($event->product)
                ->waiting()
                ->get();

            if ($subscriptions->isEmpty()) {
                Log::info('No waiting subscribers for back-in-stock', [
                    'product_id' => $event->product->id,
                ]);
                return;
            }

            Log::info('Notifying back-in-stock subscribers', [
                'product_id' => $event->product->id,
                'subscriber_count' => $subscriptions->count(),
                'new_stock' => $event->newStock,
            ]);

            // Send notification to each waiting subscriber
            foreach ($subscriptions as $subscription) {
                // Send notification
                $subscription->user->notify(
                    new ProductBackInStock($event->product)
                );

                //CRITICAL: Mark as notified to prevent duplicate notifications
                $subscription->markAsNotified();
            }

            Log::info('Back-in-stock notifications sent', [
                'product_id' => $event->product->id,
                'notified_count' => $subscriptions->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send back-in-stock notifications', [
                'product_id' => $event->product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(BackInStock $event, \Throwable $exception): void
    {
        Log::error('Back-in-stock notification job failed', [
            'product_id' => $event->product->id,
            'error' => $exception->getMessage(),
        ]);
    }
}