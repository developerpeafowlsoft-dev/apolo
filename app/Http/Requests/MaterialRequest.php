<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->name),
            ]);
        }
        if ($this->has('code')) {
            $this->merge([
                'code' => trim((string) $this->code),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $materialId = $this->route('material')?->id ?? $this->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($materialId) {
                    $trimmed = trim($value);
                    if ($trimmed === '') return;

                    $exists = \App\Models\Material::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($trimmed)])
                        ->when($materialId, function ($q) use ($materialId) {
                            $q->where('id', '!=', $materialId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('This Name is already taken.'));
                    }
                },
            ],
            'code' => [
                'required',
                'string',
                'max:4',
                Rule::unique('materials', 'code')->ignore($materialId),
            ],
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
            'name.required' => __('The name field is required'),
            'name.string' => __('The name must be a string'),
            'name.max' => __('The name may not be greater than 255 characters'),
            'name.unique'   => __('This Name is already taken.'),

            'code.required' => __('Code is required.'),
            'code.string'   => __('Code must be a valid text.'),
            'code.max'      => __('Code may not be greater than 4 characters.'),
            'code.unique'   => __('This Code is already taken.'),
        ];
    }
}
