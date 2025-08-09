<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Validasi untuk data utama (header)
            'customer_id' => ['required', Rule::exists('customers', 'id')],
            // [MODIFIKASI] Ubah aturan validasi tanggal
            'sale_date' => 'required|date_format:Y-m-d\TH:i',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',

            // Validasi untuk detail item (array)
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('karung_products', 'id')],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $productId = $this->input('items.' . $index . '.product_id');

                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->stock < $value) {
                            $fail("Stok untuk produk '{$product->name}' tidak mencukupi (sisa: {$product->stock}).");
                        }
                    }
                },
            ],
            'items.*.sale_price' => 'required|numeric|min:0',
        ];
    }
}