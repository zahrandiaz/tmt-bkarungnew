<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // [UBAH] Menerapkan keamanan berlapis
        return $this->user()->can('transaction-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date_format:Y-m-d\TH:i',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
            'invoice_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'payment_method' => ['required', Rule::in(['tunai', 'transfer', 'lainnya'])],
            'payment_status' => ['required', Rule::in(['lunas', 'belum lunas'])],
            // [TAMBAH] Validasi agar DP tidak melebihi total
            'down_payment' => ['nullable', 'numeric', 'min:0', 'required_if:payment_status,belum lunas', 'lte:total_amount'],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:karung_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ];
    }
}