<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HSNMasterRequest extends FormRequest
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
        $id = $this->route('hsnMaster')?->id;

        return [
            'hsn_code' => [
                'required',
                'string',
                'max:15',
                Rule::unique('hsn_masters', 'hsn_code')->ignore($id),
            ],

            'hsn_description' => 'required|string',

            // 🔥 IMPORTANT (array validation)
            'vat_tax_id' => 'required|array|min:1',
            'vat_tax_id.*' => 'required|exists:vat_taxes,id',

            'from_sales_rate' => 'required|array|min:1',
            'from_sales_rate.*' => 'required|numeric|min:0',

            'to_sales_rate' => 'required|array|min:1',
            'to_sales_rate.*' => 'required|numeric|min:0',

            'from_purchase_rate' => 'required|array|min:1',
            'from_purchase_rate.*' => 'required|numeric|min:0',

            'to_purchase_rate' => 'required|array|min:1',
            'to_purchase_rate.*' => 'required|numeric|min:0',

            'from_date' => 'nullable|array',
            'from_date.*' => 'nullable|date',

            'to_date' => 'nullable|array',
            'to_date.*' => 'nullable|date|after_or_equal:from_date.*',
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
            'hsn_code.required' => 'HSN Code is required',
            'hsn_code.unique' => 'HSN Code already exists',

            'vat_tax_id.required' => 'At least one Tax required',
            'vat_tax_id.*.required' => 'Tax field is required',
            'vat_tax_id.*.exists' => 'Invalid tax selected',

            'from_sales_rate.*.required' => 'From Sales Rate required',
            'from_sales_rate.*.numeric' => 'Must be number',

            'to_sales_rate.*.required' => 'To Sales Rate required',
            'to_sales_rate.*.numeric' => 'Must be number',

            'from_purchase_rate.*.required' => 'From Purchase Rate required',
            'from_purchase_rate.*.numeric' => 'Must be number',

            'to_purchase_rate.*.required' => 'To Purchase Rate required',
            'to_purchase_rate.*.numeric' => 'Must be number',

            'to_date.*.after_or_equal' => 'To Date must be after From Date',
        ];
    }
}
