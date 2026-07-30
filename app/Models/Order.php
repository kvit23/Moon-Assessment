<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'discount',
        'total_price',
        'shipping_address',
        'notes',
        'cancelled_at',
        'cancelled_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'shipping_address' => 'array',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Generate unique order number
    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
    /**
     * Get the status history.
     */
    public function history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }


    /**
     * Check if order can transition to a new status.
     * Simple validation: check if status is in allowed list.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        // Get current status
        $currentStatus = $this->status ?? 'pending';

        //Prevent duplicate status updates
        if ($currentStatus === $newStatus) {
            return false;
        }

        // Check if transition is allowed
        $currentEnum = OrderStatusEnum::from($currentStatus);
        return $currentEnum->canTransitionTo($newStatus);
    }

    /**
     * Update order status and record history.
     */
    public function updateStatus(string $newStatus, int $changedBy, ?string $reason = null): bool
    {
        //Validate transition
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;

        // Update status
        $this->status = $newStatus;
        $this->save();

        //Record history
        $this->history()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'reason' => $reason,
        ]);

        //Dispatch event for notifications (QUEUED)
        event(new OrderStatusChanged($this, $oldStatus, $newStatus, $changedBy));

        return true;
    }
}
    
