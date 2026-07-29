<?php

namespace App\Models;

use App\Enums\VerificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'code',
        'type',
        'attempts',
        'max_attempts',
        'expires_at',
        'used_at',
        'blocked_at',
    ];

    protected $casts = [
        'type' => VerificationTypeEnum::class,
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'blocked_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
    ];

    /**
     * Get the user that owns the verification code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the verification code is expired.
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if the verification code has been used.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Check if the verification code is blocked.
     */
    public function isBlocked(): bool
    {
        return !is_null($this->blocked_at);
    }

    /**
     * Check if the verification code is valid for verification.
     */
    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired() && !$this->isBlocked();
    }

    /**
     * Check if max attempts have been reached.
     */
    public function hasReachedMaxAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Increment attempts count.
     */
    public function incrementAttempts(): static
    {
        $this->increment('attempts');

        // Block if max attempts reached
        if ($this->hasReachedMaxAttempts()) {
            $this->update(['blocked_at' => now()]);
        }

        return $this;
    }

    /**
     * Mark the verification code as used.
     */
    public function markAsUsed(): static
    {
        $this->update(['used_at' => now()]);
        return $this;
    }

    /**
     * Scope for active codes.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('used_at')
            ->whereNull('blocked_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for type.
     */
    public function scopeOfType($query, VerificationTypeEnum $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Scope for user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}