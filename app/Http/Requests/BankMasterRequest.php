<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankMasterRequest extends FormRequest
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
            'bank_name'     => 'required|string|max:255',
            'short_name' => 'required|string|max:10',
            'account_group_id' => 'required|exists:account_groups,id',
            'bank_ac_no' => 'required|digits_between:8,20',
            'bank_branch' => 'required|string|max:170',
            'bank_swift_code' => 'required|regex:/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            'bank_ifsc_code' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_address' => 'required|string',
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
            'bank_name.required' => __('Bank Name is required.'),
            'bank_name.string' => __('Bank Name must be a valid text.'),
            'bank_name.max' => __('Bank Name may not be greater than 255 characters.'),

            'short_name.required' => __('Short Name field is required.'),
            'short_name.string'   => __('Short Name must be a valid text.'),
            'short_name.max'      => __('Short Name may not be greater than 10 characters.'),

            'account_group_id.required' => __('Please select an Account Group'),
            'account_group_id.exists' => __('Selected Account Group is invalid'),

            'bank_ac_no.required' => __('Account No. is required.'),
            'bank_ac_no.digits_between' => __('Account No. must be between 8 and 20 digits.'),

            'bank_branch.required' => __('Branch is required.'),
            'bank_branch.string'   => __('Branch must be a valid text.'),
            'bank_branch.max'      => __('Branch may not be greater than 170 characters.'),

            'bank_swift_code.required' => __('SWIFT Code is required.'),
            'bank_swift_code.regex' => __('Please enter a valid SWIFT Code (8 or 11 characters, e.g., SBININBBXXX).'),

            'bank_ifsc_code.required' => __('IFSC Code is required.'),
            'bank_ifsc_code.regex' => __('Please enter a valid IFSC Code (e.g., SBIN0001234).'),

            'bank_address.required' => __('Bank Address is required.'),
            'bank_address.string'   => __('Bank Address must be a valid text.'),

        ];
    }
}
