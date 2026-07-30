<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Policy check - ensures user owns the order or is admin
        $this->authorize('view', $order);

        $order->load(['items.product', 'history']);

        return response()->json([
            'data' => $order,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        // Policy check
        $this->authorize('create', Order::class);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_address.line1' => 'required|string',
            'shipping_address.line2' => 'nullable|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Create order logic here
        // This would include stock reservation, price calculation, etc.

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order ?? null,
        ], 201);
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        // Policy check
        $this->authorize('cancel', $order);

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $order,
        ]);
    }
}