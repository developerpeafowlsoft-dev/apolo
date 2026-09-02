<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $unitId = $this->route('unit')?->id ?? $this->id;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($unitId) {
                    $trimmed = trim($value);
                    if ($trimmed === '') return;

                    $exists = \App\Models\Unit::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($trimmed)])
                        ->when($unitId, function ($q) use ($unitId) {
                            $q->where('id', '!=', $unitId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('Unit already exists.'));
                    }
                },
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
            'name.required' => __('The name field is required.'),
        ];
    }
}
