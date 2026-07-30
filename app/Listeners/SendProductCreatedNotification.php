<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendProductCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        // Send notifications to admins
        // Update analytics
        // Send to search index
        // Clear cache
        
        // Example:
        // $event->product->notify(new ProductCreatedNotification());
    }
}