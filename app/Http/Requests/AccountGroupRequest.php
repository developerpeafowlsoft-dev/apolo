<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountGroupRequest extends FormRequest
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
            'accountname' => 'required|string|max:191',
            'accountcode' => 'required|numeric|unique:account_groups,code,' . $this->id,
            'account_type' => 'required|exists:account_types,id',
            'remark' => 'max:191',
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
            'accountname.required' => __('The account name field is required.'),
            'accountname.max' => __('The name may not be greater than 191 characters.'),
            'accountcode.required' => __('The account code field is required.'),
            'accountcode.numeric' => __('The account code must be a number.'),
            'accountcode.unique' => __('The account code has already been taken.'),
            'account_type.required' => __('The account type field is required.'),
            'account_type.exists' => __('The selected account type is invalid.'),
            'remark.max' => __('The remark may not be greater than 191 characters.'),

        ];
    }
}
