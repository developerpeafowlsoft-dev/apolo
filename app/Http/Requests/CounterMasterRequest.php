<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CounterMasterRequest extends FormRequest
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
        $id = $this->route('counterMaster')?->id;
        return [
            'code' => [
                'required',
                'string',
                'max:4',
                Rule::unique('counter_masters', 'code')->ignore($id),
            ],
            'counter_name' => 'required|string|max:170',
            'counter_short_name' => 'required|string|max:21',
            'floor' => 'nullable|string|max:100',
            'voucher_prefix' => 'required|string|max:5',
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
            'code.required' => __('Code field is required.'),
            'code.string' => __('Code must be a valid text.'),
            'code.max' => __('Code may not be greater than 4 characters.'),
            'code.unique' => __('This Code is already taken.'),

            'counter_name.required' => __('Counter Name field is required.'),
            'counter_name.string'   => __('Counter Name must be a valid text.'),
            'counter_name.max'      => __('Counter Name may not be greater than 170 characters.'),

            'counter_short_name.required' => __('Counter Short Name field is required.'),
            'counter_short_name.string'   => __('Counter Short Name must be a valid text.'),
            'counter_short_name.max'      => __('Counter Short Name may not be greater than 21 characters.'),

            'voucher_prefix.required' => __('Voucher Prefix field is required.'),
            'voucher_prefix.string'   => __('Voucher Prefix must be a valid text.'),
            'voucher_prefix.max'      => __('Voucher Prefix may not be greater than 5 characters.'),

        ];
    }
}
