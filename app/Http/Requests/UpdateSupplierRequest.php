<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <-- [BARU] Tambahkan ini

class UpdateSupplierRequest extends FormRequest
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
        // [BARU] Dapatkan ID supplier dari rute
        $supplierId = $this->route('supplier')->id;

        return [
            'name' => 'required|string|max:255',
            // [MODIFIKASI] Gunakan Rule::unique untuk mengabaikan ID supplier saat ini
            'phone' => ['required', 'string', 'max:20', Rule::unique('suppliers')->ignore($supplierId)],
            'address' => 'nullable|string',
        ];
    }
}