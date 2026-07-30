<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\SubscribeBackInStockRequest;
use App\Models\BackInStockSubscription;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BackInStockController extends Controller
{
    /**
     * Subscribe a user to be notified when a product is back in stock.
     */
    public function subscribe(SubscribeBackInStockRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();

        // Check if product is already in stock
        if ($product->stock_quantity > 0) {
            return response()->json([
                'message' => 'Product is already in stock. No need to subscribe.',
            ], 400);
        }

        try {
            //CRITICAL: The UNIQUE constraint will prevent duplicates
            $subscription = BackInStockSubscription::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'notified_at' => null, // Explicitly set to NULL
            ]);

            return response()->json([
                'message' => 'You will be notified when this product is back in stock.',
                'data' => [
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                ],
            ], 201);

        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Handle duplicate subscription
            return response()->json([
                'message' => 'You are already subscribed to this product.',
            ], 409);
        }
    }

    /**
     * Unsubscribe from back-in-stock notifications.
     */
    public function unsubscribe(Product $product): JsonResponse
    {
        $user = request()->user();

        $deleted = BackInStockSubscription::forUser($user)
            ->forProduct($product)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Unsubscribed successfully.',
            ]);
        }

        return response()->json([
            'message' => 'You were not subscribed to this product.',
        ], 404);
    }

    /**
     * Get all subscriptions for the authenticated user.
     */
    public function mySubscriptions(): JsonResponse
    {
        $user = request()->user();

        $subscriptions = BackInStockSubscription::forUser($user)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $subscriptions->map(function ($subscription) {
                return [
                    'product_id' => $subscription->product_id,
                    'product_title' => $subscription->product->title,
                    'product_status' => $subscription->product->stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
                    'notified' => $subscription->isNotified(),
                    'notified_at' => $subscription->notified_at?->toIso8601String(),
                    'created_at' => $subscription->created_at->toIso8601String(),
                ];
            }),
        ]);
    }
}