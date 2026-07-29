<?php

namespace App\Events;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public VerificationCode $verificationCode
    ) {}
}