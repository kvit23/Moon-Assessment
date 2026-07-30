<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackInStockSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that is subscribed to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the subscription has been notified.
     */
    public function isNotified(): bool
    {
        return !is_null($this->notified_at);
    }

    /**
     * Mark the subscription as notified.
     */
    public function markAsNotified(): static
    {
        $this->update(['notified_at' => now()]);
        return $this;
    }

    /**
     * Scope for subscriptions waiting to be notified.
     */
    public function scopeWaiting($query)
    {
        return $query->whereNull('notified_at');
    }

    /**
     * Scope for subscriptions that have been notified.
     */
    public function scopeNotified($query)
    {
        return $query->whereNotNull('notified_at');
    }

    /**
     * Scope for a specific product.
     */
    public function scopeForProduct($query, Product $product)
    {
        return $query->where('product_id', $product->id);
    }

    /**
     * Scope for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}