<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialYearRequest extends FormRequest
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'name' => 'required|max:191',
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
            'start_date.required' => __('The start date field is required.'),
            'start_date.date' => __('The start date must be a valid date.'),

            'end_date.required' => __('The end date field is required.'),
            'end_date.date' => __('The end date must be a valid date.'),
            'end_date.after_or_equal' => __('The end date must be after or equal to the start date.'),

            'name.required' => __('The financial name field is required.'),
            'name.max' => __('The financial name may not be greater than 191 characters.'),
        ];
    }
}
