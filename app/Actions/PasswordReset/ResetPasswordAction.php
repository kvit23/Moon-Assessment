<?php

namespace App\Actions\PasswordReset;

use App\Events\PasswordResetCompleted;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    /**
     * Reset the user's password using a valid reset token.
     *
     * @param string $phone
     * @param string $token
     * @param string $newPassword
     * @return User
     * @throws ValidationException
     */
    public function execute(string $phone, string $token, string $newPassword): User
    {
        // Find user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['No account found with this phone number.'],
            ]);
        }

        // Find the reset token
        $hashedToken = hash('sha256', $token);
        $resetToken = PasswordResetToken::forUser($user)
            ->where('token', $hashedToken)
            ->valid()
            ->first();

        if (!$resetToken) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired reset token.'],
            ]);
        }

        // Use a transaction for atomic operation
        DB::transaction(function () use ($user, $resetToken, $newPassword) {
            // Update password
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            // Mark reset token as used
            $resetToken->markAsUsed();

            // Revoke all Sanctum tokens for security
            $user->tokens()->delete();

            // Dispatch event
            event(new PasswordResetCompleted($user));
        });

        return $user;
    }
}