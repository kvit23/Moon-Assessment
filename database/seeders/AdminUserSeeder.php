<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'phone' => '+1234567890',
                'password' => 'Password123!',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '+1987654321',
                'password' => 'Password123!',
            ],
            [
                'name' => 'System Admin',
                'email' => 'system@example.com',
                'phone' => '+1122334455',
                'password' => 'Password123!',
            ],
        ];

        foreach ($admins as $adminData) {
            User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'phone' => $adminData['phone'],
                    'password' => Hash::make($adminData['password']),
                    'role' => UserRoleEnum::ADMIN,
                    'is_active' => true,
                    'phone_verified_at' => now(),
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('3 admin users created successfully.');
        $this->command->info('Emails: superadmin@example.com, admin@example.com, system@example.com');
        $this->command->info('Password: Password123!');
    }
}