<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVariantAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        $attribute = $this->route('variantAttribute');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('variant_attributes', 'name')->ignore($attribute?->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên thuộc tính.',
            'name.max' => 'Tên thuộc tính không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên thuộc tính đã tồn tại.',
        ];
    }
}
