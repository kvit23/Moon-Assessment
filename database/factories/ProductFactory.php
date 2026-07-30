<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = 'App\Models\Product';

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'published', 'archived']);

        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->optional(0.8)->paragraphs(3, true),
            'sku' => strtoupper($this->faker->bothify('???-#####')),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost' => $this->faker->optional(0.7)->randomFloat(2, 5, 800),
            'stock_quantity' => $this->faker->numberBetween(0, 500),
            'reorder_level' => $this->faker->numberBetween(5, 50),
            'status' => $status,
            'image' => $this->faker->optional(0.5)->imageUrl(640, 480, 'products', true),
            'created_by' => null,
            'updated_by' => null,
            'published_at' => $status === 'published' ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the product is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the product is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the product is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the product is in stock.
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(10, 500),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Indicate that the product has low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(1, 5),
            'reorder_level' => 5,
        ]);
    }

    /**
     * Indicate that the product has an image.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => $this->faker->imageUrl(640, 480, 'products', true),
        ]);
    }

    /**
     * Indicate that the product has no image.
     */
    public function withoutImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => null,
        ]);
    }

    /**
     * Set a specific price range.
     */
    public function priceBetween(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, $min, $max),
        ]);
    }

    /**
     * Indicate a cheap product.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 5, 50),
        ]);
    }

    /**
     * Indicate an expensive product.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 500, 1000),
        ]);
    }
}