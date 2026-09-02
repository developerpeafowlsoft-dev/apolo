<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bridge_token' => 'required|string',
            'status' => 'required|string',
            'approved_amount' => 'required|numeric|gte:0',
            'transaction_id' => 'nullable|string',
            'rrn' => 'nullable|string',
            'approval_code' => 'nullable|string',
            'raw_response' => 'nullable|array',
        ];
    }
}
