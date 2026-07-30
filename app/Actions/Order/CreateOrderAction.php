<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    /**
     * Create a new order with stock validation and locking.
     */
    public function execute(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            // Step 1: Extract product IDs and validate items
            $items = $data['items'];
            $productIds = collect($items)->pluck('product_id')->unique()->values()->toArray();

            // Step 2: Lock products for update (prevents race conditions)
            // 🔒 CRITICAL: This locks the rows so no one else can modify them
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Step 3: Validate stock and calculate totals
            $orderItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items.*.product_id' => "Product ID {$item['product_id']} not found.",
                    ]);
                }

                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items.*.quantity' => "Insufficient stock for {$product->title}. Available: {$product->stock_quantity}",
                    ]);
                }

                $unitPrice = (float) $product->price;
                $itemSubtotal = $unitPrice * $item['quantity'];

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];

                $subtotal += $itemSubtotal;
            }

            // Step 4: Calculate final totals
            $tax = $subtotal * 0.10; // 10% tax (simple for assessment)
            $shippingCost = 10.00; // Flat shipping (simple for assessment)
            $total = $subtotal + $tax + $shippingCost;

            // Step 5: Create the order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $userId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'discount' => 0,
                'total_price' => $total,
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Step 6: Create order items and update stock
            foreach ($orderItems as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Decrease stock
                $beforeStock = $product->stock_quantity;
                $product->stock_quantity -= $quantity;
                $product->save();

                // Log stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => -$quantity, // Negative for decrease
                    'type' => 'sale',
                    'before_quantity' => $beforeStock,
                    'after_quantity' => $product->stock_quantity,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number}",
                    'created_by' => $userId,
                ]);
            }

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $userId,
                'total' => $total,
            ]);

            // Step 7: Load relationships for response
            $order->load(['items.product']);

            return $order;
        });
    }
}