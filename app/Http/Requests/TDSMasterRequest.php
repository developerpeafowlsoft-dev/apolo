<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TDSMasterRequest extends FormRequest
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
        $id = $this->route('tdsMaster')?->id;
        return [
            'tds_code'               => [
                'required',
                'string',
                'max:4',
                Rule::unique('tds_masters', 'tds_code')->ignore($id),
            ],
            'tds_description'        => 'required|string',
            'tds_payable_id'         => 'required|exists:account_masters,id',
            'tds_receivable_id'      => 'required|exists:account_masters,id',
            'from_date'              => 'required|date',
            'to_date'                => 'required|date|after_or_equal:from_date',
            'tds_percentage'         => 'required|numeric|min:0|max:100',
            'tds_limit'              => 'required|integer|min:0',
            'tds_single_trans_limit' => 'required|integer|min:0',
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

            'tds_code.required' => __('TDS Code is required.'),
            'tds_code.string'   => __('TDS Code must be a valid text.'),
            'tds_code.max'      => __('TDS Code may not be greater than 4 characters.'),
            'tds_code.unique'   => __('This TDS Code is already taken.'),

            'tds_description.required' => __('TDS Description is required.'),
            'tds_description.string'   => __('TDS Description must be a valid text.'),

            'tds_payable_id.required' => __('TDS Payable A/c is required.'),
            'tds_payable_id.exists'   => __('Selected TDS Payable A/c is invalid.'),

            'tds_receivable_id.required' => __('TDS Receivable A/c is required.'),
            'tds_receivable_id.exists'   => __('Selected TDS Receivable A/c is invalid.'),

            'from_date.required' => __('From Date is required.'),
            'from_date.date'     => __('From Date must be a valid date.'),

            'to_date.required'        => __('To Date is required.'),
            'to_date.date'            => __('To Date must be a valid date.'),
            'to_date.after_or_equal'  => __('To Date must be after or equal to From Date.'),

            'tds_percentage.required' => __('TDS Percentage is required.'),
            'tds_percentage.numeric'  => __('TDS Percentage must be a number.'),
            'tds_percentage.min'      => __('TDS Percentage cannot be less than 0.'),
            'tds_percentage.max'      => __('TDS Percentage cannot be more than 100.'),

            'tds_limit.required' => __('TDS Limit is required.'),
            'tds_limit.integer'  => __('TDS Limit must be an integer.'),
            'tds_limit.min'      => __('TDS Limit cannot be negative.'),

            'tds_single_trans_limit.required' => __('TDS Single Trans Limit is required.'),
            'tds_single_trans_limit.integer'  => __('TDS Single Trans Limit must be an integer.'),
            'tds_single_trans_limit.min'      => __('TDS Single Trans Limit cannot be negative.'),

        ];
    }
}
