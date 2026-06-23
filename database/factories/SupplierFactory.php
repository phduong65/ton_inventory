<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'code' => 'NCC-' . $this->faker->unique()->numerify('###'),
            'name' => $this->faker->company(),
        ];
    }
}
