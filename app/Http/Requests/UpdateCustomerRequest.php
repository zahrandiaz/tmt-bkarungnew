<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        // Dapatkan ID pelanggan dari rute
        $customerId = $this->route('customer')->id;

        return [
            'name' => 'required|string|max:255',
            // Gunakan Rule::unique untuk mengabaikan ID pelanggan saat ini
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers')->ignore($customerId)],
            'address' => 'nullable|string',
        ];
    }
}