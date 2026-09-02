<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InwardProductRequest extends FormRequest
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
            'invoiceData.inward_vat_tax_id' => 'required|exists:vat_taxes,id',
            'invoiceData.inward_voucher_no' => 'required',
            'invoiceData.inward_date' => 'required|date',
            'invoiceData.inward_party_code' => 'required|exists:account_masters,id',
//            'invoiceData.inward_total' => 'required|numeric|min:0',
            'invoiceData.inward_challan_no' => 'required',
            'invoiceData.inward_challan_date' => 'required|date',

            'rows' => 'required|array|min:1',

            'rows.*.item' => 'required',
            'rows.*.designNo' => 'required',
            'rows.*.qty' => 'required|numeric|min:1',
        ];
    }
}
