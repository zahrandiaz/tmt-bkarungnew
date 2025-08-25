<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            // Memastikan nomor telepon unik di tabel 'customers'
            'phone' => 'required|string|max:20|unique:customers,phone',
            'address' => 'nullable|string',
        ];
    }
}