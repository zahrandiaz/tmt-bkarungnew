<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest
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
        // Dapatkan ID kategori dari parameter rute
        $categoryId = $this->route('product_category')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_product_categories', 'name')->ignore($categoryId),
            ],
        ];
    }
}