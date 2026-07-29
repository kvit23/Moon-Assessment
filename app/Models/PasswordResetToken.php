<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetToken extends Model
{
    

    protected $fillable = [
        'user_id',
        'phone',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the password reset token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if the token has been used.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Check if the token is valid.
     */
    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    /**
     * Mark the token as used.
     */
    public function markAsUsed(): static
    {
        $this->update(['used_at' => now()]);
        return $this;
    }

    /**
     * Scope for valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}