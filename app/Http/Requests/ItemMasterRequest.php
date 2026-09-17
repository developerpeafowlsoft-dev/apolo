<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemMasterRequest extends FormRequest
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
        $id = $this->route('itemMaster')?->id;
        return [
            'name' => 'required|string|max:191',
            'category' => 'required|exists:categories,id',
            'sub_category' => 'nullable|array|exists:sub_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'code' => [
                'required',
                'numeric',
                'digits_between:1,25',
                Rule::unique('products', 'code')->ignore($id),
            ],
            'color' => 'nullable|array',
            'size' => 'nullable|array',
            'size.*.id' => 'nullable|exists:sizes,id',
            'size.*.price' => 'nullable|numeric|min:0',
            'hsn_master_id' => 'required|exists:hsn_masters,id',
            'vat_tax_id' => 'required|exists:vat_taxes,id',
            'unit_id' => 'required|exists:units,id',
            'material_id' => 'nullable|exists:materials,id',
//            'buy_price' => 'required|numeric|min:0',
//            'price' => 'required|numeric|min:0',
//            'discount_percentage' => 'nullable|numeric|min:0',
//            'mrp' => 'required|numeric|min:0',
//            'mark_up' => 'required|numeric|min:0',
//            'mark_down' => 'required|numeric|min:0',
//            'quantity' => 'required|integer|min:0',
            'salesman_id' => 'nullable|exists:salesmans,id',
//            'commission_type'     => 'nullable|in:1,2',
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('commission_type', ['required', 'in:1,2'], function ($input) {
            return !empty($input->salesman_id);
        });

        $validator->sometimes('salesman_comm', ['required', 'numeric', 'min:1'], function ($input) {
            return $input->commission_type == 1;
        });

        $validator->sometimes('salesman_comm_amt', ['required', 'numeric', 'min:1'], function ($input) {
            return $input->commission_type == 2;
        });
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
            'name.required' => __('Item name is required.'),
            'name.string' => __('Item name must be a valid string.'),
            'name.max' => __('Item name cannot exceed 191 characters.'),

            'category.required' => __('Selected category is required.'),
            'category.exists' => __('Selected category is invalid.'),

            'sub_category.array' => __('Sub-categories must be in array format.'),
            'sub_category.exists' => __('One or more selected sub-categories are invalid.'),

            'brand_id.required' => __('Selected brand is required.'),
            'brand_id.exists' => __('Selected brand is invalid.'),

            'code.required' => __('Item No / ID is required.'),
            'code.numeric' => __('Item No / ID must be a number.'),
            'code.digits_between' => __('Item No / ID must be between 1 and 25 digits.'),

            'color.array' => __('Color must be in array format.'),

            'size.array' => __('Size must be in array format.'),
            'size.*.id.exists' => __('One or more selected sizes are invalid.'),
            'size.*.price.numeric' => __('Each size price must be a number.'),
            'size.*.price.min' => __('Size price must be at least 0.'),

            'hsn_master_id.required' => __('Selected hsn code is required.'),
            'hsn_master_id.exists' => __('Selected hsn code is invalid.'),

            'vat_tax_id.required' => __('Selected tax type is required.'),
            'vat_tax_id.exists' => __('Selected tax type is invalid.'),

            'unit_id.required' => __('Selected unit is required.'),
            'unit_id.exists' => __('Selected unit is invalid.'),

            'material_id.exists' => __('Selected material is invalid.'),

            'buy_price.required' => __('Purchase price is required.'),
            'buy_price.numeric' => __('Purchase price must be a number.'),
            'buy_price.min' => __('Purchase price must be at least 0.'),

            'price.required' => __('Selling price is required.'),
            'price.numeric' => __('Selling price must be a number.'),
            'price.min' => __('Selling price must be at least 0.'),

            'discount_percentage.numeric' => __('Discount must be a number.'),
            'discount_percentage.min' => __('Discount cannot be negative.'),
            'discount_percentage.max' => __('Discount cannot exceed the product price.'),

            'mrp.required' => __('MRP is required.'),
            'mrp.numeric' => __('MRP must be a number.'),
            'mrp.min' => __('MRP must be at least 0.'),

            'mark_up.required' => __('Markup is required.'),
            'mark_up.numeric' => __('Markup must be a number.'),
            'mark_up.min' => __('Markup cannot be negative.'),

            'mark_down.required' => __('Markdown is required.'),
            'mark_down.numeric' => __('Markdown must be a number.'),
            'mark_down.min' => __('Markdown cannot be negative.'),

            'quantity.required' => __('Purchase stock quantity is required.'),
            'quantity.integer' => __('Purchase stock quantity must be an integer.'),
            'quantity.min' => __('Purchase stock quantity cannot be negative.'),

            'salesman_id.exists' => __('Selected salesman is invalid.'),

            'commission_type.required' => __('Commission type is required.'),
            'commission_type.in' => __('Commission type must be either percentage or amount.'),

            'salesman_comm.required_if' => __('Commission percentage is required.'),
            'salesman_comm.numeric' => __('Commission percentage must be a number.'),
            'salesman_comm.min' => __('Commission percentage cannot be negative.'),

            'salesman_comm_amt.required_if' => __('Commission amount is required.'),
            'salesman_comm_amt.numeric' => __('Commission amount must be a number.'),
            'salesman_comm_amt.min' => __('Commission amount cannot be negative.'),
        ];
    }
}
