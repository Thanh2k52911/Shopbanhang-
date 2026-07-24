<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['value' => trim((string) $this->input('value'))]);
    }

    public function rules(): array
    {
        $attribute = $this->route('variantAttribute');

        return [
            'value' => [
                'required',
                'string',
                'max:100',
                Rule::unique('variant_values', 'value')
                    ->where(fn ($query) => $query->where('attribute_id', $attribute?->id)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Vui lòng nhập giá trị thuộc tính.',
            'value.max' => 'Giá trị không được vượt quá 100 ký tự.',
            'value.unique' => 'Giá trị này đã tồn tại trong thuộc tính.',
        ];
    }
}
