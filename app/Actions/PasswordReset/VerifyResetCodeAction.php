<?php

namespace App\Actions\PasswordReset;

use App\Enums\VerificationTypeEnum;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyResetCodeAction
{
    /**
     * Verify the reset code and generate a reset token.
     *
     * @param string $phone
     * @param string $code
     * @return array
     * @throws ValidationException
     */
    public function execute(string $phone, string $code): array
    {
        // Find user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['No account found with this phone number.'],
            ]);
        }

        // Find active verification code for password reset
        $verificationCode = VerificationCode::forUser($user)
            ->ofType(VerificationTypeEnum::PASSWORD_RESET)
            ->active()
            ->latest()
            ->first();

        if (!$verificationCode) {
            throw ValidationException::withMessages([
                'code' => ['No active reset code found. Please request a new code.'],
            ]);
        }

        // Verify the code
        $this->verifyCode($verificationCode, $code);

        // Mark code as used
        $verificationCode->markAsUsed();

        // Generate a password reset token
        $resetToken = $this->createResetToken($user);

        return [
            'message' => 'Code verified successfully.',
            'reset_token' => $resetToken->token,
            'expires_in' => 15, // Token valid for 15 minutes
        ];
    }

    /**
     * Verify the code.
     *
     * @param VerificationCode $verificationCode
     * @param string $code
     * @throws ValidationException
     */
    private function verifyCode(VerificationCode $verificationCode, string $code): void
    {
        // Check if code matches
        if ($verificationCode->code !== $code) {
            // Increment attempts
            $verificationCode->incrementAttempts();

            $message = 'Invalid verification code.';

            if ($verificationCode->isBlocked()) {
                $message = 'Too many failed attempts. Please request a new code.';
            }

            throw ValidationException::withMessages([
                'code' => [$message],
            ]);
        }

        // Additional safety checks
        if (!$verificationCode->isValid()) {
            $message = 'This verification code is no longer valid.';

            if ($verificationCode->isExpired()) {
                $message = 'Verification code has expired. Please request a new code.';
            }

            if ($verificationCode->isUsed()) {
                $message = 'This verification code has already been used.';
            }

            if ($verificationCode->isBlocked()) {
                $message = 'Too many failed attempts. Please request a new code.';
            }

            throw ValidationException::withMessages([
                'code' => [$message],
            ]);
        }
    }

    /**
     * Create a password reset token for the user.
     *
     * @param User $user
     * @return \App\Models\PasswordResetToken
     */
    private function createResetToken(User $user): \App\Models\PasswordResetToken
    {
        // Invalidate any existing reset tokens
        \App\Models\PasswordResetToken::forUser($user)
            ->valid()
            ->update(['used_at' => now()]);

        // Generate a secure token
        $token = Str::random(60);

        // Hash the token for storage
        $hashedToken = hash('sha256', $token);

        // Create new reset token
        $resetToken = \App\Models\PasswordResetToken::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'token' => $hashedToken,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Return the model with the plain text token
        $resetToken->token = $token;

        return $resetToken;
    }
}