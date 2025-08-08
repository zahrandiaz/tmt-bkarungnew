<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi sudah ditangani oleh middleware 'role:Admin' pada rute,
        // jadi kita bisa langsung mengizinkannya di sini.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // Memastikan nomor telepon unik di tabel 'suppliers'
            'phone' => 'required|string|max:20|unique:suppliers,phone',
            'address' => 'nullable|string',
        ];
    }
}