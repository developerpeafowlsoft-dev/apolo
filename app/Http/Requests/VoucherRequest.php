<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherRequest extends FormRequest
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
        return [
            'voucher_type' => 'required|string',
            'shop_id' => 'required|exists:shops,id',
            'financial_year_id' => 'required|exists:financial_years,id',
            'seq_prefix' => 'nullable|string',
            'seq_padding' => 'nullable|integer|min:3|max:12',
        ];
    }

    public function messages(): array
    {
        $request = request();
        if ($request->is('api/*')) {
            $header = strtolower($request->header('accept-language'));
            $lan = (preg_match('/^[a-z]+$/', $header)) ? $header : 'en';
            app()->setLocale($lan);
        }

        return [
            'voucher_type.required'         => __('Voucher type is required.'),
            'voucher_type.string'           => __('Voucher type must be a string.'),

            'shop_id.required'              => __('Shop ID is required.'),
            'shop_id.exists'                => __('The selected shop does not exist.'),

            'financial_year_id.required'    => __('Financial year is required.'),
            'financial_year_id.exists'      => __('The selected financial year does not exist.'),

            'seq_prefix.string'             => __('Sequence prefix must be a string.'),

            'seq_padding.integer'           => __('Sequence padding must be a number.'),
            'seq_padding.min'               => __('Sequence padding must be at least :min digits.'),
            'seq_padding.max'               => __('Sequence padding cannot be more than :max digits.'),
        ];
    }
}
