<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // [UBAH] Izinkan request hanya jika pengguna memiliki hak akses 'product-create'.
        return $this->user()->can('product-create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // [BARU] Tambahkan validasi untuk SKU
            'sku' => 'nullable|string|max:255|unique:karung_products,sku',
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