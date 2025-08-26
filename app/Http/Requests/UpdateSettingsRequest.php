<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Terapkan keamanan berlapis sesuai hak akses rute
        return $this->user()->can('role-edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string|max:500',
            'store_phone' => 'required|string|max:20',
            'invoice_footer_notes' => 'required|string|max:500',
            'enable_automatic_stock' => 'nullable|string',
        ];
    }
}