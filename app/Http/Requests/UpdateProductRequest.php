<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // [UBAH] Izinkan request hanya jika pengguna memiliki hak akses 'product-edit'.
        return $this->user()->can('product-edit');
    }

    public function rules(): array
    {
        // Dapatkan produk yang sedang diedit dari rute
        $product = $this->route('product');

        return [
            'name' => 'required|string|max:255',
            // [BARU] Tambahkan validasi untuk SKU, abaikan produk saat ini
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('karung_products', 'sku')->ignore($product->id),
            ],
            'product_category_id' => 'required|exists:karung_product_categories,id',
            'product_type_id' => 'required|exists:karung_product_types,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:purchase_price',
            'stock' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }
}