<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    /**
     * Execute the action to create a new user.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? UserRoleEnum::USER->value,
                'is_active' => true,
            ]);

            // Generate Sanctum token
            $token = $user->createToken(
                $data['device_name'] ?? 'api',
                ['*']
            );

            // Store token in user object for response
            $user->token = $token->plainTextToken;

            return $user;
        });
    }
}