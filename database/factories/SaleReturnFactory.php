<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleReturnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'return_code' => 'SR/' . now()->format('Ym') . '/' . $this->faker->unique()->randomNumber(5),
            'sale_id' => Sale::factory(),
            'user_id' => User::factory(),
            'return_date' => now(),
            'total_amount' => $this->faker->numberBetween(10000, 50000),
        ];
    }
}