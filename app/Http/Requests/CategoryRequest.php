<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        $required = $this->isMethod('put') ? 'nullable' : 'required';
        $categoryId = $this->route('category')?->id ?? $this->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($categoryId) {
                    $trimmed = trim($value);
                    if ($trimmed === '') return;

                    $exists = \App\Models\Category::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($trimmed)])
                        ->when($categoryId, function ($q) use ($categoryId) {
                            $q->where('id', '!=', $categoryId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('Category already exists.'));
                    }
                },
            ],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'thumbnail' => [$required, 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
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
            'name.string' => __('The name must be a string.'),
            'name.max' => __('The name may not be greater than 255 characters.'),
            'thumbnail.required' => __('Category image is required.'),
            'thumbnail.image' => __('The thumbnail must be an image.'),
            'thumbnail.mimes' => __('The thumbnail must be a file of type: jpg, jpeg, png, gif.'),
            'thumbnail.max' => __('The thumbnail may not be greater than 2048 kilobytes.'),
        ];
    }
}
