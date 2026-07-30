<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\CancelOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Requests\Api\V1\Order\CancelOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;



class OrderController extends Controller
{
    /**
     * List orders (users see own, admins see all).
     */
    public function index(): JsonResponse
    {
        // Policy check - ensures user is authorized to view orders
        $this->authorize('viewAny', Order::class);

        $user = auth()->user();

        // Build the query
        $orders = Order::query()
            ->with(['items.product']) // Eager load relationships
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                // Non-admin users only see their own orders
                return $query->where('user_id', $user->id);
            })
            // Admin users see all orders (no filter applied)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return OrderResource::collection($orders)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $this->authorize('create', Order::class);

        try {
            $order = $action->execute(
                $request->validated(),
                $request->user()->id
            );

            return (new OrderResource($order))
                ->additional(['message' => 'Order created successfully.'])
                ->response()
                ->setStatusCode(201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific order.
     */
    public function show(Order $order): JsonResponse
    {
        // Policy check - ensures user owns order or is admin
        $this->authorize('view', $order);

        $order->load(['items.product']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cancel a specific order.
     */
    public function cancel(Order $order): JsonResponse
    {
        // Policy check
        $this->authorize('cancel', $order);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => 'Cancelled by user',
        ]);

        // Release stock back
        // This would be handled by a separate action

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}