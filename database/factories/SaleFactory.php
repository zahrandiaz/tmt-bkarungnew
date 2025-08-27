<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        $totalAmount = $this->faker->numberBetween(10000, 100000);
        return [
            'invoice_number' => 'INV/' . now()->format('Ym') . '/' . $this->faker->unique()->randomNumber(5),
            'customer_id' => Customer::first()->id ?? Customer::factory(),
            'user_id' => User::first()->id ?? User::factory(),
            'sale_date' => now(),
            'total_amount' => $totalAmount,
            'payment_method' => 'cash',
            'payment_status' => 'Lunas',
            'total_paid' => $totalAmount,
        ];
    }
}