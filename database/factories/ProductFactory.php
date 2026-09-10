<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => 'SKU-'.strtoupper(fake()->bothify('??###')),
            'price_per_unit' => fake()->randomFloat(2, 5, 250),
            'tax_percentage' => fake()->randomElement([0.00, 5.00, 10.00, 15.00, 18.00]),
            'stock_on_hand' => fake()->numberBetween(1, 100),
        ];
    }

    public function lowStock(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => fake()->numberBetween(1, $count),
        ]);
    }
}
