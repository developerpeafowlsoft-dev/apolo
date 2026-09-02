<?php

namespace App\Http\Requests;

use App\Models\VerifyManage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;

class AddressRequest extends FormRequest
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
        $verifyManage = Cache::rememberForever('verify_manage', function () {
            return VerifyManage::first();
        });

        $min = $verifyManage?->phone_min_length ?? 9;
        $max = $verifyManage?->phone_max_length ?? 16;

        return [
//            'name' => 'required|string|max:255',
//            'phone' => 'required|numeric|min_digits:'.$min.'|max_digits:'.$max,
//            'area' => 'nullable|string|max:255',
//            'flat_no' => 'nullable|string|max:255',
//            'post_code' => 'nullable|string|max:255',
//            'address_line' => 'required|string|max:255',
//            'address_line2' => 'nullable|string|max:255',
//            'address_type' => 'required|string|max:255',
//            'is_default' => 'nullable|boolean',
//            'longitude' => 'nullable|numeric|max:255',
//            'latitude' => 'nullable|numeric|max:255',
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
//            'country' => ['required', 'string', 'max:100'],
            'country_id' => ['required', 'exists:countries,id'],
            'post_code' => ['required', 'string', 'max:10'],
            'area' => ['required', 'string', 'max:255'],
            'flat_no' => ['nullable', 'string', 'max:255'],
            'address_line' => ['required', 'string', 'max:500'],
            'address_type' => ['required', 'in:home,office'],
            'is_default' => ['nullable', 'boolean'],
            'latitude' => ['nullable'],
            'longitude' => ['nullable'],
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

//        return [
//            'name.required' => __('The name field is required'),
//            'name.max' => __('The name may not be greater than 255 characters'),
//            'name.string' => __('The name must be a string'),
//            'phone.required' => __('The phone field is required.'),
//            'area.required' => __('The area field is required'),
//            'area.max' => __('The area may not be greater than 255 characters'),
//            'address_type.required' => __('The address type field is required'),
//            'address_type.max' => __('The address type may not be greater than 255 characters'),
//            'post_code.required' => __('The post code field is required'),
//            'post_code.max' => __('The post code may not be greater than 255 characters'),
//            'flat_no.max' => __('The flat no may not be greater than 255 characters'),
//            'address_line.required' => __('The address line field is required'),
//            'address_line.max' => __('The address line may not be greater than 255 characters'),
//            'address_line2.max' => __('The address line 2 may not be greater than 255 characters'),
//        ];
        return [

            'name.required' => __('Name is required.'),
            'name.string' => __('Name must be a valid text.'),
            'name.max' => __('Name may not be greater than 255 characters.'),

            'phone.required' => __('Phone number is required.'),
            'phone.numeric' => __('Phone number must contain only numbers.'),
            'phone.min_digits' => __('Phone number must be at least :min digits.'),
            'phone.max_digits' => __('Phone number may not be greater than :max digits.'),

            'country_id.required' => __('Please select a country.'),
            'country_id.exists' => __('Selected country is invalid.'),

            'post_code.required' => __('Postal Code is required.'),
            'post_code.max' => __('Postal Code may not be greater than 10 characters.'),

            'area.required' => __('Locality is required.'),
            'area.max' => __('Locality may not be greater than 255 characters.'),

            'flat_no.max' => __('Landmark may not be greater than 255 characters.'),

            'address_line.required' => __('Address is required.'),
            'address_line.max' => __('Address may not be greater than 500 characters.'),

            'address_type.required' => __('Please select address type.'),
            'address_type.in' => __('Selected address type is invalid.'),

        ];
    }
}
