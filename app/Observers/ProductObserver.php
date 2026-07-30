<?php

namespace App\Observers;

use App\Events\BackInStock;
use App\Events\ProductCreated;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        event(new ProductCreated($product));

        Log::info('Product created', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "updating" event.
     * 
     * CRITICAL: This is where we detect stock changes
     * BEFORE the model is saved to the database.
     */
    public function updating(Product $product): void
    {
        // Get the original stock from the database
        $oldStock = $product->getOriginal('stock_quantity');
        $newStock = $product->stock_quantity;

        // Only trigger if stock changed from 0 to >0
        $this->checkBackInStock($product, $oldStock, $newStock);
    }

    /**
     * Check if product is back in stock and dispatch event if needed.
     */
    protected function checkBackInStock(Product $product, int $oldStock, int $newStock): void
    {
        // CRITICAL: Only trigger when:
        // - Old stock was 0 (out of stock)
        // - New stock is greater than 0 (back in stock)
        if ($oldStock === 0 && $newStock > 0) {
            Log::info('Product back in stock detected', [
                'product_id' => $product->id,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
            ]);

            // Dispatch event for subscribers
            event(new BackInStock($product, $oldStock, $newStock));
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $changes = $product->getChanges();
        
        if (!empty($changes)) {
            Log::info('Product updated', [
                'product_id' => $product->id,
                'changes' => $changes,
            ]);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        Log::info('Product soft-deleted', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        Log::info('Product restored', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "forceDeleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        Log::info('Product permanently deleted', [
            'product_id' => $product->id,
        ]);
    }
}