<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Terapkan keamanan berlapis
        return $this->user()->can('finance-manage-payment');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Ambil objek purchase dari rute untuk validasi
        $purchase = $this->route('purchase');
        $remainingAmount = $purchase->total_amount - $purchase->total_paid;

        return [
            // Batasi jumlah pembayaran maksimal sebesar sisa tagihan
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $remainingAmount],
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}