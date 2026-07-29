<?php

namespace App\Actions\PhoneVerification;

use App\Enums\VerificationTypeEnum;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Str;

class GenerateVerificationCodeAction
{
    /**
     * Generate a new verification code for a user.
     *
     * @param User $user
     * @param VerificationTypeEnum $type
     * @return VerificationCode
     */
    public function execute(User $user, VerificationTypeEnum $type = VerificationTypeEnum::PHONE_VERIFICATION): VerificationCode
    {
        // Generate a 6-digit numeric code
        $code = $this->generateNumericCode($type->codeLength());

        // Calculate expiration time
        $expiresAt = now()->addMinutes($type->expiresInMinutes());

        // Invalidate any existing active codes for this user and type
        $this->invalidateExistingCodes($user, $type);

        // Create new verification code
        return VerificationCode::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'code' => $code,
            'type' => $type->value,
            'max_attempts' => $type->maxAttempts(),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Generate a numeric code of specified length.
     */
    private function generateNumericCode(int $length): string
    {
        return Str::padLeft((string) rand(0, pow(10, $length) - 1), $length, '0');
    }

    /**
     * Invalidate any existing active codes for this user and type.
     */
    private function invalidateExistingCodes(User $user, VerificationTypeEnum $type): void
    {
        VerificationCode::where('user_id', $user->id)
            ->where('type', $type->value)
            ->whereNull('used_at')
            ->whereNull('blocked_at')
            ->update(['blocked_at' => now()]);
    }
}