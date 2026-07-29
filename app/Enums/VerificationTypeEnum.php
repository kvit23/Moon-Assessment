<?php

namespace App\Enums;

enum VerificationTypeEnum: string
{
    case PHONE_VERIFICATION = 'phone_verification';
    case EMAIL_VERIFICATION = 'email_verification';
    case PASSWORD_RESET = 'password_reset';

    public function label(): string
    {
        return match($this) {
            self::PHONE_VERIFICATION => 'Phone Verification',
            self::EMAIL_VERIFICATION => 'Email Verification',
            self::PASSWORD_RESET => 'Password Reset',
        };
    }

    public function expiresInMinutes(): int
    {
        return match($this) {
            self::PHONE_VERIFICATION => 10,
            self::EMAIL_VERIFICATION => 15,
            self::PASSWORD_RESET => 30,
        };
    }

    public function maxAttempts(): int
    {
        return match($this) {
            self::PHONE_VERIFICATION => 5,
            self::EMAIL_VERIFICATION => 5,
            self::PASSWORD_RESET => 3,
        };
    }

    public function codeLength(): int
    {
        return 6; // All types use 6-digit codes
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}