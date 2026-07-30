<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ─── Get or Create Admin User ───
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '+1234567890',
                'password' => bcrypt('Password123!'),
                'role' => 'admin',
            ]);
        }

        $this->command->info('📦 Seeding products...');

        // ─── Published Products ───
        $this->command->info('  📌 Creating published products...');

        // 20 standard published products
        Product::factory()
            ->count(20)
            ->published()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // 10 published products with images
        Product::factory()
            ->count(10)
            ->published()
            ->withImage()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // 10 published products out of stock
        Product::factory()
            ->count(10)
            ->published()
            ->outOfStock()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // 5 cheap published products
        Product::factory()
            ->count(5)
            ->published()
            ->cheap()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // 5 expensive published products
        Product::factory()
            ->count(5)
            ->published()
            ->expensive()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // ─── Draft Products ───
        $this->command->info('  📝 Creating draft products...');

        // 10 standard draft products
        Product::factory()
            ->count(10)
            ->draft()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // 5 draft products with images
        Product::factory()
            ->count(5)
            ->draft()
            ->withImage()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // ─── Archived Products ───
        $this->command->info('  📦 Creating archived products...');

        // 10 standard archived products
        Product::factory()
            ->count(10)
            ->archived()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // 5 archived products with images
        Product::factory()
            ->count(5)
            ->archived()
            ->withImage()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // ─── Low Stock Products ───
        $this->command->info('  ⚠️ Creating low stock products...');

        Product::factory()
            ->count(5)
            ->published()
            ->lowStock()
            ->state(['created_by' => $admin->id, 'updated_by' => $admin->id])
            ->create();

        // ─── Summary ───
        $total = Product::count();
        $this->command->info('✅ ' . $total . ' products seeded successfully!');
        $this->command->info('  📊 Breakdown:');
        $this->command->info('  - Published: ' . Product::where('status', 'published')->count());
        $this->command->info('  - Draft: ' . Product::where('status', 'draft')->count());
        $this->command->info('  - Archived: ' . Product::where('status', 'archived')->count());
        $this->command->info('  - With images: ' . Product::whereNotNull('image')->count());
        $this->command->info('  - Out of stock: ' . Product::where('stock_quantity', 0)->count());
        $this->command->info('  - Low stock: ' . Product::whereBetween('stock_quantity', [1, 5])->count());
    }
}