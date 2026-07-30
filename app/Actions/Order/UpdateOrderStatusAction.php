<?php

namespace App\Actions\Order;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class UpdateOrderStatusAction
{
    public function execute(Order $order, string $newStatus, int $userId, ?string $reason = null): Order
    {
        // ✅ Check if transition is allowed
        if (!$order->canTransitionTo($newStatus)) {
            $currentStatus = $order->status ?? 'pending';
            throw ValidationException::withMessages([
                'status' => [
                    "Cannot change from {$currentStatus} to {$newStatus}."
                ],
            ]);
        }

        // ✅ Update status and record history
        $success = $order->updateStatus($newStatus, $userId, $reason);

        if (!$success) {
            throw new \RuntimeException('Failed to update order status.');
        }

        return $order->fresh(['user', 'items.product', 'history']);
    }
}