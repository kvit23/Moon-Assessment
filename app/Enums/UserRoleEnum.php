<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'User',
        };
    }

    public static function fromValue(string $value): ?self
    {
        return match($value) {
            'admin' => self::ADMIN,
            'user' => self::USER,
            default => null,
        };
    }
}