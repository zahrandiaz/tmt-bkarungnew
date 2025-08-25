<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStockAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // [UBAH] Terapkan keamanan berlapis
        return $this->user()->can('adjustment-stock');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:karung_products,id'],
            'type' => ['required', 'string', 'in:increment,decrement'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'product_name_display' => ['sometimes', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $product = Product::find($this->input('product_id'));
            $quantity = (int) $this->input('quantity');
            $type = $this->input('type');

            // Jika tipe adalah pengurangan, pastikan stok mencukupi
            if ($type === 'decrement' && $product && $product->stock < $quantity) {
                $validator->errors()->add(
                    'quantity',
                    "Stok untuk produk '{$product->name}' tidak mencukupi untuk dikurangi. Stok saat ini: {$product->stock}."
                );
            }
        });
    }
}