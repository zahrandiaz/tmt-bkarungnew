<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // [PERBAIKAN] Menambahkan 'name' yang wajib diisi
            'name' => $this->faker->words(3, true), // contoh: "Biaya Listrik Kantor"
            'expense_category_id' => ExpenseCategory::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'amount' => $this->faker->numberBetween(10000, 100000),
            'expense_date' => $this->faker->dateTimeThisMonth(),
            'description' => $this->faker->sentence,
        ];
    }
}