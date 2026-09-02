<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountMasterRequest extends FormRequest
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
        $id = $this->route('accountMaster')?->id;
        return [
            // Account Information
            'accountName'     => 'required|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'cities_id' => 'required|exists:cities,id',
//            'accountshortcode' => 'required|digits_between:1,6|unique:account_masters,accountshortcode,' . $this->route('accountMaster')->id,
            'accountshortcode' => [
                'required',
                'digits_between:1,6',
                Rule::unique('account_masters', 'accountshortcode')->ignore($id),
            ],
            'agentcomm' => 'required|numeric|min:0|max:100',
            'agentcommremark' => 'nullable|string|max:255',
            'referenceby' => 'nullable|string|max:255',

            // Contact Address
            'contperson' => 'required|string|max:255',
            'contpincode' => 'required|digits:6',
            'contaddress' => 'required|string|max:1000',
            'country_id' => 'required|exists:countries,id',
            'state_id'   => 'required|exists:states,id',
            'city_id'    => 'required|exists:cities,id',

            // Contact Information
            'cont_info_mobile1' => 'required|digits:10',
            'cont_info_mobile2' => 'nullable|digits:10',
            'cont_info_phone'   => 'nullable|digits_between:5,13',
            'cont_info_email'        => 'nullable|email|max:255',
            'cont_info_website_url'  => 'nullable|url|max:255',
            'cont_info_birth_date'   => 'nullable|date',

            // Tax Information
            'tax_info_gst_no' => 'required|regex:/^([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})$/',
            'tax_info_gst_reg_date' => 'nullable|date',
            'tax_info_gst_cancel_date' => 'nullable|date',
            'vat_tax_id' => 'nullable|exists:vat_taxes,id',
            'tax_info_tan_no' => 'nullable|regex:/^[A-Z]{4}[0-9]{5}[A-Z]{1}$/',
            'tax_info_pan_no' => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',

            // Bank Information
            'bank_info_bank_name' => 'required|string|max:255',
            'bank_info_ac_no' => 'required|digits_between:8,20',
            'bank_info_swift_code' => 'nullable|regex:/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            'bank_info_ifsc_code' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_info_branch' => 'required|string|max:255',
            'bank_info_upi_id' => 'nullable|regex:/^[a-zA-Z0-9._]{2,256}@[a-zA-Z]{2,64}$/|max:255',
            'bank_info_payment_name' => 'required|string|max:255',
            'bank_info_address' => 'nullable|string',

            // Other Information
            'other_info_discount'          => 'nullable|numeric|min:0|max:100',
            'other_info_discount_limit'    => 'nullable|numeric|min:0|max:100',
            'other_info_cash_disc'         => 'nullable|numeric|min:0|max:100',
            'other_info_special_disc'      => 'nullable|numeric|min:0|max:100',
            'other_info_bank_cs_disc'      => 'nullable|numeric|min:0|max:100',

            'other_info_credit_day'        => 'nullable|integer|min:0|max:3650', // 10 years max

            'other_info_act_limit'         => 'nullable|numeric|min:0',

//            'other_info_adjustment_type'   => 'nullable|string|max:255',
//            'other_info_delivery_type'     => 'nullable|string|max:255',
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
            'accountName.required' => __('Account Name is required.'),
            'account_id.required' => __('The account field is required.'),
            'account_id.exists'   => __('The selected account is invalid.'),
            'cities_id.required' => __('The region/area field is required.'),
            'cities_id.exists'   => __('The selected region/area is invalid.'),
            'accountshortcode.required' => __('Short Code is required.'),
            'accountshortcode.digits_between' => __('Short Code must be between 1 to 10 digits.'),
            'accountshortcode.unique'   => __('This Short Code is already taken.'),
            'agentcomm.required' => __('Agent Commission is required.'),
            'agentcomm.numeric'  => __('Agent Commission must be a number.'),
            'agentcomm.min'      => __('Agent Commission cannot be less than 0%.'),
            'agentcomm.max'      => __('Agent Commission cannot be more than 100%.'),
            'agentcommremark.string'  => __('Remark must be a valid text.'),
            'agentcommremark.max'     => __('Remark cannot exceed 255 characters.'),
            'referenceby.string'  => __('Reference by must be a valid text.'),
            'referenceby.max'     => __('Reference by cannot exceed 255 characters.'),

            'contperson.required' => __('Contact Person is required.'),
            'contperson.string'   => __('Contact Person must be a valid text.'),
            'contperson.max'      => __('Contact Person cannot exceed 255 characters.'),
            'contpincode.required' => __('Pincode is required.'),
            'contpincode.digits'   => __('Pincode must be exactly 6 digits.'),
            'contaddress.required' => __('Contact Address is required.'),
            'contaddress.string'   => __('Contact Address must be valid text.'),
            'contaddress.max'      => __('Contact Address cannot exceed 1000 characters.'),
            'country_id.required' => __('Country is required.'),
            'country_id.exists'   => __('The selected country is invalid.'),
            'state_id.required'   => __('State is required.'),
            'state_id.exists'     => __('The selected state is invalid.'),
            'city_id.required'    => __('City is required.'),
            'city_id.exists'      => __('The selected city is invalid.'),

            'cont_info_mobile1.required' => __('Mobile No. (1) is required.'),
            'cont_info_mobile1.digits'   => __('Mobile No. (1) must be exactly 10 digits.'),
            'cont_info_mobile2.digits'   => __('Mobile No. (2) must be exactly 10 digits.'),
            'cont_info_phone.digits_between'   => __('Phone No. must be between 5 to 13 digits.'),
            'cont_info_email.email'       => __('Email must be a valid email address.'),
            'cont_info_email.max'         => __('Email cannot exceed 255 characters.'),
            'cont_info_website_url.url'   => __('Website URL must be a valid URL.'),
            'cont_info_website_url.max'   => __('Website URL cannot exceed 255 characters.'),
            'cont_info_birth_date.date'   => __('Birth Date must be a valid date.'),

            'tax_info_gst_no.required' => 'GST No. field is required.',
            'tax_info_gst_no.regex' => 'Please enter a valid GST No. (e.g., 27ABCDE1234F1Z5)',
//            'tax_info_gst_reg_date.required' => __('GST Registration Date is required.'),
//            'tax_info_gst_reg_date.date'     => __('GST Registration Date must be a valid date.'),
//            'tax_info_gst_reg_date.before_or_equal' => __('GST Registration Date cannot be a future date.'),
            'tax_info_gst_cancel_date.date' => __('GST Cancel Date must be a valid date.'),
            'vat_tax_id.required' => __('Tax Type is required.'),
            'vat_tax_id.exists'   => __('Selected Tax Type is invalid.'),
            'tax_info_tan_no.regex' => __('Please enter a valid TAN No. (e.g., DELT12345A)'),
            'tax_info_pan_no.required' => __('PAN No. field is required.'),
            'tax_info_pan_no.regex'    => __('Please enter a valid PAN No. (e.g., ABCDE1234F)'),

            'bank_info_bank_name.required' => __('Bank Name is required.'),
            'bank_info_bank_name.string'   => __('Bank Name must be a valid text.'),
            'bank_info_bank_name.max'      => __('Bank Name may not be greater than 255 characters.'),
            'bank_info_ac_no.required' => __('Bank Account No. is required.'),
            'bank_info_ac_no.digits_between' => __('Bank Account No. must be between 8 and 20 digits.'),
            'bank_info_swift_code.required' => __('SWIFT Code is required.'),
            'bank_info_swift_code.regex' => __('Please enter a valid SWIFT Code (8 or 11 characters, e.g., SBININBBXXX).'),

            'bank_info_ifsc_code.required' => __('IFSC Code is required.'),
            'bank_info_ifsc_code.regex' => __('Please enter a valid IFSC Code (e.g., SBIN0001234).'),

            'bank_info_branch.required' => __('Branch is required.'),
            'bank_info_branch.string'   => __('Branch must be a valid text.'),
            'bank_info_branch.max'      => __('Branch may not be greater than 255 characters.'),

            'bank_info_upi_id.required' => __('UPI ID is required.'),
            'bank_info_upi_id.regex' => __('Please enter a valid UPI ID (e.g., username@bank).'),
            'bank_info_upi_id.max'      => __('UPI ID may not be greater than 255 characters.'),

            'bank_info_payment_name.required' => __('Payment Name is required.'),
            'bank_info_payment_name.string'   => __('Payment Name must be a valid text.'),
            'bank_info_payment_name.max'      => __('Payment Name may not be greater than 255 characters.'),

            'bank_info_address.required' => __('Bank Address is required.'),
            'bank_info_address.string'   => __('Bank Address must be a valid text.'),

            'other_info_discount.numeric'       => 'The discount must be a number.',
            'other_info_discount.min'           => 'The discount cannot be less than 0.',
            'other_info_discount.max'           => 'The discount cannot be more than 100.',

            'other_info_discount_limit.numeric' => 'The discount limit must be a number.',
            'other_info_discount_limit.min'     => 'The discount limit cannot be less than 0.',
            'other_info_discount_limit.max'     => 'The discount limit cannot be more than 100.',

            'other_info_cash_disc.numeric'      => 'The cash discount must be a number.',
            'other_info_cash_disc.min'          => 'The cash discount cannot be less than 0.',
            'other_info_cash_disc.max'          => 'The cash discount cannot be more than 100.',

            'other_info_special_disc.numeric'   => 'The special discount must be a number.',
            'other_info_special_disc.min'       => 'The special discount cannot be less than 0.',
            'other_info_special_disc.max'       => 'The special discount cannot be more than 100.',

            'other_info_bank_cs_disc.numeric'   => 'The bank cash discount must be a number.',
            'other_info_bank_cs_disc.min'       => 'The bank cash discount cannot be less than 0.',
            'other_info_bank_cs_disc.max'       => 'The bank cash discount cannot be more than 100.',

            'other_info_credit_day.integer'     => 'The credit days must be an integer.',
            'other_info_credit_day.min'         => 'The credit days cannot be less than 0.',
            'other_info_credit_day.max'         => 'The credit days cannot be more than 3650.',

            'other_info_act_limit.numeric'      => 'The account limit must be a number.',
            'other_info_act_limit.min'          => 'The account limit cannot be less than 0.',
        ];
    }
}
