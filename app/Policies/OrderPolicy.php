<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any orders (index).
     */
    public function viewAny(User $user): bool
    {
        // Everyone who is authenticated can access the order index
        // The controller will filter based on role
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
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Admins can cancel any order
        if ($user->isAdmin()) {
            return true;
        }

        // Users can cancel their own pending orders
        return $user->id === $order->user_id && $order->status === 'pending';
    }
}