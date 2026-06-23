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
            'sku'           => 'SKU-' . $this->faker->unique()->numerify('####'),
            'name'          => $this->faker->words(3, true),
            'unit'          => $this->faker->randomElement(['cái', 'chai', 'hộp', 'kg', 'lít']),
            'default_price' => $this->faker->numberBetween(10000, 500000),
            'status'        => 'active',
        ];
    }
}
