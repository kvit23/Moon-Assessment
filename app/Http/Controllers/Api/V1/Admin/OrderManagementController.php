<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Actions\Order\UpdateOrderStatusAction;
use App\Models\Order;
use App\Http\Requests\Api\V1\Order\UpdateOrderStatusRequest;
use App\Http\Resources\Api\V1\OrderResource;
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
     * Update order status (Admin only).
     */
    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        UpdateOrderStatusAction $action
    ): JsonResponse {
        // ✅ Policy check
        $this->authorize('updateStatus', $order);

        $order = $action->execute(
            $order,
            $request->input('status'),
            $request->user()->id,
            $request->input('reason')
        );

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}