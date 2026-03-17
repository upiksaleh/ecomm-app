<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'description' => fake()->optional(0.7)->sentence(),
            'price' => fake()->randomFloat(2, 1, 500),
            'quantity' => fake()->numberBetween(0, 100),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(['quantity' => 0]);
    }

    public function priced(float $price): static
    {
        return $this->state(['price' => $price]);
    }
}
