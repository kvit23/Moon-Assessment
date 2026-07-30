<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CancelOrderAction
{
    /**
     * Cancel an order and restore stock.
     */
    public function execute(Order $order, string $reason, int $userId): Order
    {
        // Check if order can be cancelled
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'order' => ['This order has already been cancelled.'],
            ]);
        }

        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'order' => ['Only pending orders can be cancelled.'],
            ]);
        }

        return DB::transaction(function () use ($order, $reason, $userId) {
            // Step 1: Get all order items
            $orderItems = $order->items()->with('product')->get();

            if ($orderItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => ['This order has no items to cancel.'],
                ]);
            }

            // Step 2: Lock products for stock restoration
            $productIds = $orderItems->pluck('product_id')->unique()->toArray();
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Step 3: Restore stock for each item
            foreach ($orderItems as $item) {
                $product = $products->get($item->product_id);

                if (!$product) {
                    continue;
                }

                $beforeStock = $product->stock_quantity;
                $product->stock_quantity += $item->quantity;
                $product->save();

                // Log stock restoration
                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => $item->quantity, // Positive for restoration
                    'type' => 'return',
                    'before_quantity' => $beforeStock,
                    'after_quantity' => $product->stock_quantity,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number} cancelled: {$reason}",
                    'created_by' => $userId,
                ]);
            }

            // Step 4: Update order status
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ]);

            // Step 5: Log the cancellation
            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $userId,
                'reason' => $reason,
                'items_restored' => $orderItems->count(),
            ]);

            // Step 6: Load relationships for response
            $order->load(['items.product']);

            return $order;
        });
    }
}