<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counter_id' => 'required|integer|exists:counter_masters,id',
            'cart_name' => 'nullable|string',
            'payment_method' => 'required|string|in:card,upi,split,cash',
            'amount' => 'required|numeric|gt:0',
            'provider' => 'nullable|string|in:mock,paytm,phonepe',
            'items' => 'nullable|array',
        ];
    }
}
