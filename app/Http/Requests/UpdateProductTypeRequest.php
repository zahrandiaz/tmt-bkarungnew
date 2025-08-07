<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductTypeRequest extends FormRequest
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
        // Dapatkan ID jenis produk dari parameter rute
        $productTypeId = $this->route('product_type')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_product_types', 'name')->ignore($productTypeId),
            ],
        ];
    }
}