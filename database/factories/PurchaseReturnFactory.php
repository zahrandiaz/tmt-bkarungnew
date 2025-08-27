<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'return_code' => 'PR/' . now()->format('Ym') . '/' . $this->faker->unique()->randomNumber(5),
            'purchase_id' => Purchase::factory(),
            'user_id' => User::factory(),
            'return_date' => now(),
            'total_amount' => $this->faker->numberBetween(20000, 100000),
        ];
    }
}