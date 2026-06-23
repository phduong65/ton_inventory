<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'code'       => 'TX-' . $this->faker->unique()->numerify('#####'),
            'type'       => 'IN',
            'status'     => 'draft',
            'date'       => now()->toDateString(),
            'created_by' => User::factory(),
        ];
    }

    public function in(): static
    {
        return $this->state(['type' => 'IN']);
    }

    public function out(): static
    {
        return $this->state(['type' => 'OUT']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }
}
