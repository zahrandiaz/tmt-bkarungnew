<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // [UBAH] Menggunakan hak akses yang sama dengan rute master data
        return $this->user()->can('product-view');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Dapatkan ID supplier dari rute
        $supplierId = $this->route('supplier')->id;

        return [
            'name' => 'required|string|max:255',
            // Gunakan Rule::unique untuk mengabaikan ID supplier saat ini
            'phone' => ['required', 'string', 'max:20', Rule::unique('suppliers')->ignore($supplierId)],
            'address' => 'nullable|string',
        ];
    }
}