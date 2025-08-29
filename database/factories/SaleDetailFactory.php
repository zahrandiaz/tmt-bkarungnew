<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleDetail>
 */
class SaleDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaleDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil produk acak atau buat yang baru jika tidak ada
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();

        return [
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'sale_price' => $product->selling_price,
            'purchase_price' => $product->purchase_price, // Asumsi harga beli saat ini
        ];
    }
}