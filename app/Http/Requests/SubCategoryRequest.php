<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubCategoryRequest extends FormRequest
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
        $required = ($this->isMethod('put')) ? 'nullable' : 'required';
        $subCategoryId = $this->route('subCategory')?->id ?? $this->route('subcategory')?->id ?? $this->id;

        return [
            'category' => ['required', 'array', 'exists:categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($subCategoryId) {
                    $trimmed = trim($value);
                    if ($trimmed === '') return;

                    $exists = \App\Models\SubCategory::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($trimmed)])
                        ->when($subCategoryId, function ($q) use ($subCategoryId) {
                            $q->where('id', '!=', $subCategoryId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('Subcategory already exists.'));
                    }
                },
            ],
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
            'category.required' => __('The category field is required.'),
            'category.exists' => __('The selected category is invalid.'),
            'name.required' => __('The name field is required.'),
            'thumbnail.required' => __('Sub category image is required.'),
            'thumbnail.image' => __('The thumbnail must be an image.'),
            'thumbnail.mimes' => __('The thumbnail must be a file of type: jpg, jpeg, png, gif.'),
            'thumbnail.max' => __('The thumbnail must not be greater than 2048 kilobytes.'),
        ];
    }
}
