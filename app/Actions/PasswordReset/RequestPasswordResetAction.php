<?php

namespace App\Actions\PasswordReset;

use App\Actions\PhoneVerification\SendVerificationCodeAction;
use App\Enums\VerificationTypeEnum;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RequestPasswordResetAction
{
    protected SendVerificationCodeAction $sendVerificationAction;

    public function __construct(SendVerificationCodeAction $sendVerificationAction)
    {
        $this->sendVerificationAction = $sendVerificationAction;
    }

    /**
     * Request a password reset for a user.
     *
     * @param string $phone
     * @return array
     * @throws ValidationException
     */
    public function execute(string $phone): array
    {
        // Find user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['No account found with this phone number.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'phone' => ['This account has been deactivated.'],
            ]);
        }

        // Generate and send verification code for password reset
        $verificationCode = $this->sendVerificationAction->execute(
            $user,
            VerificationTypeEnum::PASSWORD_RESET
        );

        // Return success message (don't reveal if user exists or not for security)
        return [
            'message' => 'Password reset code sent successfully.',
            'expires_in' => VerificationTypeEnum::PASSWORD_RESET->expiresInMinutes(),
        ];
    }
}