<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $unitData = [
            ['code' => 'CAI',  'name' => 'Cái'],
            ['code' => 'CHAI', 'name' => 'Chai'],
            ['code' => 'HOP',  'name' => 'Hộp'],
            ['code' => 'KG',   'name' => 'Kg'],
            ['code' => 'LIT',  'name' => 'Lít'],
        ];
        $pick = $this->faker->randomElement($unitData);
        $unit = Unit::firstOrCreate(['code' => $pick['code']], ['name' => $pick['name']]);

        return [
            'sku'           => 'SKU-' . $this->faker->unique()->numerify('####'),
            'name'          => $this->faker->words(3, true),
            'unit_id'       => $unit->id,
            'default_price' => $this->faker->numberBetween(10000, 500000),
            'status'        => 'active',
        ];
    }
}
