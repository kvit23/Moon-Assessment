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
     * Create a new order.
     */
    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        // Policy check
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
     * Get user's orders.
     */
    public function index(): JsonResponse
    {
        $orders = Order::where('user_id', auth()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return OrderResource::collection($orders)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get a specific order.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.product']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cancel an order.
     */
    public function cancel(CancelOrderRequest $request, CancelOrderAction $action, Order $order): JsonResponse
    {
        // Policy check
        $this->authorize('cancel', $order);

        try {
            $order = $action->execute(
                $order,
                $request->input('reason'),
                $request->user()->id
            );

            return (new OrderResource($order))
                ->additional(['message' => 'Order cancelled successfully.'])
                ->response()
                ->setStatusCode(200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}