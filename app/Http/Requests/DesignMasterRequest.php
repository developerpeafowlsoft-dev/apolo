<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignMasterRequest extends FormRequest
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
        $id = $this->route('designMaster')?->id;
        return [
            'design_number' => [
                'required',
                Rule::unique('design_masters', 'design_number')->ignore($id),
            ],
            'itemname' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'buy_price' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
            'discount_percentage' => 'nullable|numeric|min:0',
            'mrp' => 'required|numeric|min:1',
            'mark_up' => 'required|numeric|min:0',
            'mark_down' => 'required|numeric|min:0',
            'account_master' => 'required|exists:account_masters,id',
            'account_master_name' => 'required|string|max:255',

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
            'design_number.required' => __('Design number is required.'),
            'design_number.unique' => __('The design number has already been taken.'),

            'itemname.required' => __('Selected item name is required.'),
            'itemname.exists' => __('Selected item name is invalid.'),

            'quantity.required' => __('Purchase stock quantity is required.'),
            'quantity.integer' => __('Purchase stock quantity must be an integer.'),
            'quantity.min' => __('Purchase stock quantity cannot be negative.'),

            'buy_price.required' => __('Purchase price is required.'),
            'buy_price.numeric' => __('Purchase price must be a number.'),
            'buy_price.min' => __('Purchase price must be at least 1.'),

            'price.required' => __('Selling price is required.'),
            'price.numeric' => __('Selling price must be a number.'),
            'price.min' => __('Selling price must be at least 1.'),

            'discount_percentage.numeric' => __('Discount must be a number.'),
            'discount_percentage.min' => __('Discount cannot be negative.'),

            'mrp.required' => __('MRP is required.'),
            'mrp.numeric' => __('MRP must be a number.'),
            'mrp.min' => __('MRP must be at least 1.'),

            'mark_up.required' => __('Markup is required.'),
            'mark_up.numeric' => __('Markup must be a number.'),
            'mark_up.min' => __('Markup cannot be negative.'),

            'mark_down.required' => __('Markdown is required.'),
            'mark_down.numeric' => __('Markdown must be a number.'),
            'mark_down.min' => __('Markdown cannot be negative.'),

            'account_master.required' => __('Selected account master is required.'),
            'account_master.exists' => __('Selected account master is invalid.'),

            'account_master_name.required' => __('Account master name is required.'),
            'account_master_name.string' => __('Account master name must be a valid string.'),
            'account_master_name.max' => __('Account master name cannot exceed 255 characters.'),
        ];
    }
}
