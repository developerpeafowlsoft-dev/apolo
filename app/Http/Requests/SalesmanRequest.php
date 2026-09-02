<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesmanRequest extends FormRequest
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
        $id = $this->route('salesman')?->id;
        return [
            'name'          => 'required|string|max:255',
            'last_name'          => 'nullable|string|max:255',
            'phone'         => [
                'required',
                'string',
                'min_digits:9',
                'max_digits:15',
                Rule::unique('salesmans', 'phone')->ignore($id),
            ],
            'email'         => 'nullable|email|max:255',
            'src'           => 'nullable|image|mimes:jpg,jpeg,png',
            'gender'        => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'is_active'     => 'boolean',
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
            'name.required' => __('Name is required.'),
            'name.string'   => __('Name must be a valid text.'),
            'name.max'      => __('Name may not be greater than 255 characters.'),

            'last_name.string'   => __('Last Name must be a valid text.'),
            'last_name.max'      => __('Last Name may not be greater than 255 characters.'),

            'phone.required' => __('Phone number is required.'),
            'phone.string'   => __('Phone number must be a valid text.'),
            'phone.min_digits' => __('Phone number must be at least 9 digits.'),
            'phone.max_digits' => __('Phone number may not be greater than 15 digits.'),
            'phone.unique'   => __('This phone number is already taken.'),

            'email.email' => __('Email must be a valid email address.'),
            'email.max'   => __('Email may not be greater than 255 characters.'),

            'src.string' => __('Source must be a valid text.'),

            'gender.required' => __('Gender is required.'),
            'gender.in'       => __('Gender must be Male, Female or Other.'),

            'date_of_birth.date' => __('Date of Birth must be a valid date.'),

            'is_active.boolean' => __('Status must be true or false.'),
        ];
    }
}
