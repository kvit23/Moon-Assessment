<?php

namespace App\Observers;

use App\Events\ProductCreated;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Dispatch the event
        // This triggers the listener which handles notifications
        event(new ProductCreated($product));

        Log::info('Product created event dispatched', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Log changes
        $changes = $product->getChanges();
        
        if (!empty($changes)) {
            Log::info('Product updated', [
                'product_id' => $product->id,
                'changes' => $changes,
            ]);

            // If stock changed, maybe dispatch StockUpdated event
            if (isset($changes['stock_quantity'])) {
                // event(new ProductStockUpdated($product));
            }

            // If status changed to published, maybe dispatch ProductPublished event
            if (isset($changes['status']) && $changes['status'] === 'published') {
                // event(new ProductPublished($product));
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        Log::info('Product deleted (soft)', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'deleted_by' => Auth::id(),
        ]);

        // Clean up related data
        // - Remove from search index
        // - Clear cache
        // - Remove from wishlists
        // - Disable notifications
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        Log::info('Product restored', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'restored_by' => Auth::id(),
        ]);

        // Re-index for search
        // Restore notifications
    }

    /**
     * Handle the Product "forceDeleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        Log::info('Product permanently deleted', [
            'product_id' => $product->id,
            'sku' => $product->sku,
        ]);

        // Clean up all resources
        // - Delete image files
        // - Remove from search index
        // - Delete notifications
    }
}