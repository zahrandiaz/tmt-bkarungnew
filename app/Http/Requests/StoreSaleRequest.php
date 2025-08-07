<?php

namespace App\Http\Requests;

use App\Models\Product; // <-- [BARU] Tambahkan ini
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <-- [BARU] Tambahkan ini

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
            'sale_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',

            // Validasi untuk detail item (array)
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('karung_products', 'id')],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                // [MODIFIKASI] Tambahkan validasi stok kustom
                function ($attribute, $value, $fail) {
                    // $attribute akan menjadi 'items.0.quantity', 'items.1.quantity', dst.
                    // Kita perlu mengambil product_id dari item yang sama.
                    $index = explode('.', $attribute)[1]; // Ambil index array (0, 1, 2, ...)
                    $productId = $this->input('items.' . $index . '.product_id');

                    // Jika product_id tidak ada atau tidak valid, aturan 'exists' lain akan menangkapnya.
                    // Jadi kita hanya perlu melanjutkan jika product_id ada.
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->stock < $value) {
                            // Jika stok tidak mencukupi, gagalkan validasi.
                            $fail("Stok untuk produk '{$product->name}' tidak mencukupi (sisa: {$product->stock}).");
                        }
                    }
                },
            ],
            'items.*.sale_price' => 'required|numeric|min:0',
        ];
    }
}