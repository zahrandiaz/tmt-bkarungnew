<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $productsInCart;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // [UBAH] Menerapkan keamanan berlapis sesuai standar audit kita
        return $this->user()->can('transaction-create');
    }

    /**
     * Mengambil semua produk yang ada di keranjang dalam satu query untuk efisiensi.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getProductsInCart()
    {
        if ($this->productsInCart) {
            return $this->productsInCart;
        }

        $productIds = collect($this->input('items', []))->pluck('product_id')->filter();

        return $this->productsInCart = Product::whereIn('id', $productIds)->get()->keyBy('id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')],
            'sale_date' => 'required|date_format:Y-m-d\TH:i',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'payment_method' => ['required', Rule::in(['tunai', 'transfer', 'lainnya'])],
            'payment_status' => ['required', Rule::in(['lunas', 'belum lunas'])],
            // [TAMBAH] Validasi agar DP tidak melebihi total
            'down_payment' => ['nullable', 'numeric', 'min:0', 'required_if:payment_status,belum lunas', 'lte:total_amount'],

            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('karung_products', 'id')],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                // [OPTIMALISASI] Validasi stok kini menggunakan data yang sudah di-cache (pre-fetched)
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $productId = $this->input('items.' . $index . '.product_id');

                    $product = $this->getProductsInCart()->get($productId);

                    if ($product && $product->stock < $value) {
                        $fail("Stok untuk produk '{$product->name}' tidak mencukupi (sisa: {$product->stock}).");
                    }
                },
            ],
            'items.*.sale_price' => 'required|numeric|min:0',
        ];
    }
}