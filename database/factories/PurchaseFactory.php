<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    public function definition(): array
    {
        $totalAmount = $this->faker->numberBetween(100000, 500000);
        return [
            'purchase_code' => 'PUR/' . now()->format('Ym') . '/' . $this->faker->unique()->randomNumber(5),
            'supplier_id' => Supplier::first()->id ?? Supplier::factory(),
            'user_id' => User::first()->id ?? User::factory(),
            'purchase_date' => now(),
            'total_amount' => $totalAmount,
            'payment_method' => 'cash',
            'payment_status' => 'Lunas',
            'total_paid' => $totalAmount,
        ];
    }
}