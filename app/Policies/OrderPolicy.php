<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all orders
        // Users can only view their own orders (filtered in controller)
        return true;
    }

    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Admins can view any order
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only view their own orders
        return $user->id === $order->user_id;
    }

    /**
     * Determine if the user can create orders.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create orders
        return true;
    }

    /**
     * Determine if the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only admins can update order status
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admins can delete orders
        return $user->isAdmin();
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Users can cancel their own pending orders
        // Admins can cancel any order
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only cancel their own pending orders
        return $user->id === $order->user_id && $order->status === 'pending';
    }

    /**
     * Determine if the user can update order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Only admins can update order status
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view order history.
     */
    public function viewHistory(User $user, Order $order): bool
    {
        // Admins can view any order history
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only view their own order history
        return $user->id === $order->user_id;
    }

    /**
     * Determine if the user can export orders.
     */
    public function export(User $user): bool
    {
        // Only admins can export orders
        return $user->isAdmin();
    }
}