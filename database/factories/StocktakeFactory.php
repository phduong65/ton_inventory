<?php

namespace Database\Factories;

use App\Models\Stocktake;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StocktakeFactory extends Factory
{
    protected $model = Stocktake::class;

    public function definition(): array
    {
        return [
            'code'       => 'KK-' . $this->faker->unique()->numerify('#####'),
            'status'     => 'draft',
            'created_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }
}
