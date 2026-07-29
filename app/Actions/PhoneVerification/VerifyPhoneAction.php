<?php

namespace App\Actions\PhoneVerification;

use App\Enums\VerificationTypeEnum;
use App\Events\PhoneVerified;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class VerifyPhoneAction
{
    /**
     * Verify a phone using a verification code.
     *
     * @param User $user
     * @param string $code
     * @return User
     * @throws ValidationException
     */
    public function execute(User $user, string $code): User
    {
        // Find the latest active verification code for this user
        $verificationCode = $this->findActiveVerificationCode($user);

        // Verify the code
        $this->verifyCode($verificationCode, $code);

        // Mark code as used
        $verificationCode->markAsUsed();

        // Mark phone as verified
        $user->markPhoneAsVerified();

        // Dispatch event
        event(new PhoneVerified($user));

        return $user;
    }

    /**
     * Find the active verification code.
     *
     * @param User $user
     * @return VerificationCode
     * @throws ValidationException
     */
    private function findActiveVerificationCode(User $user): VerificationCode
    {
        $verificationCode = VerificationCode::forUser($user)
            ->ofType(VerificationTypeEnum::PHONE_VERIFICATION)
            ->active()
            ->latest()
            ->first();

        if (!$verificationCode) {
            throw ValidationException::withMessages([
                'code' => ['No active verification code found. Please request a new code.'],
            ]);
        }

        return $verificationCode;
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

        // Additional safety checks (redundant but explicit)
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
}