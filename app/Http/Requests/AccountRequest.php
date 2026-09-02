<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
            'name' => 'required|string|max:191',
            'account_group_id' => 'required|exists:account_groups,id',
            'code' => 'required|unique:accounts,code,' . $this->id,
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
            'name.required' => __('The account name field is required.'),
            'name.max' => __('The name may not be greater than 191 characters.'),
            'account_group_id.required' => __('The account group field is required.'),
            'account_group_id.exists' => __('The selected account group is invalid.'),
            'code.required' => __('The code field is required.'),
            'code.unique' => __('The code has already been taken.'),
            'remark.max' => __('The remark may not be greater than 191 characters.'),

        ];
    }
}
