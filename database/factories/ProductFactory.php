<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'sku' => 'SKU-' . $this->faker->unique()->bothify('??##??'),
            'product_category_id' => ProductCategory::factory(),
            'product_type_id' => ProductType::factory(),
            'purchase_price' => $this->faker->numberBetween(1000, 5000),
            'selling_price' => $this->faker->numberBetween(5000, 10000),
            'stock' => $this->faker->numberBetween(10, 100),
            'min_stock_level' => $this->faker->numberBetween(5, 10),
        ];
    }
}