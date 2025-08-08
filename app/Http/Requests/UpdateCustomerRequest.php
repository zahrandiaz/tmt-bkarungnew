<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <-- [BARU] Tambahkan ini

class UpdateCustomerRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // [BARU] Dapatkan ID pelanggan dari rute
        $customerId = $this->route('customer')->id;

        return [
            'name' => 'required|string|max:255',
            // [MODIFIKASI] Gunakan Rule::unique untuk mengabaikan ID pelanggan saat ini
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers')->ignore($customerId)],
            'address' => 'nullable|string',
        ];
    }
}