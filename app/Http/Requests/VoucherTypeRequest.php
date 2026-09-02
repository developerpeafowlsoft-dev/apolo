<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherTypeRequest extends FormRequest
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
            'name' => 'required|string|max:191|unique:voucher_types,name,' . $this->id,
            'code' => 'required|unique:voucher_types,code,' . $this->id,
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
            'name.required' => __('The voucher type field is required.'),
            'name.max' => __('The voucher type may not be greater than 191 characters.'),
            'name.unique' => __('The voucher type has already been taken.'),
            'code.required' => __('The voucher code field is required.'),
            'code.unique' => __('The voucher code has already been taken.'),
        ];
    }
}
