<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountBalanceRequest extends FormRequest
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
            'acOpenBalance' => 'required|numeric',
            'acCloseBalance' => 'required|numeric',
            'account_id' => 'required|exists:accounts,id|unique:account_balances,account_id',
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
            'account_id.required' => __('Account is required.'),
            'account_id.exists' => __('selected account is invalid.'),
            'account_id.unique'     => __('The account has already been taken.'),

            'acOpenBalance.required' => __('Opening balance is required.'),
            'acOpenBalance.numeric' => __('Opening balance must be numeric.'),

            'acCloseBalance.required' => __('Closing balance is required.'),
            'acCloseBalance.numeric' => __('Closing balance must be numeric.'),
        ];
    }
}
