<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    /**
     * Get the label for display.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get all allowed transitions from this status.
     * This is the heart of the validation.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::PENDING => ['confirmed', 'processing', 'cancelled'],
            self::CONFIRMED => ['processing', 'cancelled'],
            self::PROCESSING => ['shipped', 'cancelled'],
            self::SHIPPED => ['delivered', 'cancelled'],
            self::DELIVERED => [],
            self::CANCELLED => [],
        };
    }

    /**
     * Check if a transition is allowed.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }

    /**
     * Get all values for database.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}