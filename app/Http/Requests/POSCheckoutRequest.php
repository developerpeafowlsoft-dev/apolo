<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class POSCheckoutRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'required|string',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'customer_phone' => 'nullable|string|max:30',
            'customer_name' => 'nullable|string|max:150',
            'customer_email' => 'nullable|string|max:150',
            'note' => 'nullable|string|max:500',
            'counter_id' => 'nullable|integer|exists:counter_masters,id',
            'salesman_id' => 'nullable|integer|exists:salesmans,id',
            'cashier_id' => 'nullable|integer|exists:users,id',
            'billing_duration_seconds' => 'nullable|integer|min:0',
            'billing_started_at' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barcode' => 'required|string',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.disc_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.color' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.salesman_id' => 'nullable|integer|exists:salesmans,id',
            'split_payments' => 'nullable|array',
            'split_payments.*.method' => 'required|string|in:cash,online',
            'split_payments.*.amount' => 'required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'exchange_return_order_id' => 'nullable|integer',
            'exchange_return_items' => 'nullable|array',
            'exchange_refund_amount' => 'nullable|numeric',
            'exchange_original_invoice_no' => 'nullable|string',
        ];
    }
}
