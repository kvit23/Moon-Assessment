<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    /**
     * Display a listing of all orders.
     */
    public function index(): JsonResponse
    {
        // Admin middleware ensures only admins can access
        $orders = Order::with(['user', 'items.product'])->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        // Policy check
        $this->authorize('view', $order);

        $order->load(['user', 'items.product', 'history']);

        return response()->json([
            'data' => $order,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        // Policy check
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
        ]);

        // Log status change
        // OrderStatusHistory::create([...]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Cancel an order.
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

        // Release stock
        // StockService::release($order);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Get order statistics.
     */
    public function statistics(): JsonResponse
    {
        // Only admins can view statistics
        $statistics = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::sum('total_price'),
            'average_order_value' => Order::avg('total_price'),
        ];

        return response()->json([
            'data' => $statistics,
        ]);
    }
}