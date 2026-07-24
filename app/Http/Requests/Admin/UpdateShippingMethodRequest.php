<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'code' => Str::lower(Str::slug((string) ($this->input('code') ?: $this->input('name')), '_')),
            'provider' => $this->filled('provider') ? trim((string) $this->input('provider')) : null,
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
            'status' => $this->boolean('status'),
        ]);
    }

    public function rules(): array
    {
        $shippingMethod = $this->route('shippingMethod');

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('shipping_methods', 'code')
                    ->ignore($shippingMethod?->id),
            ],
            'provider' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_fee' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'free_shipping_minimum' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'estimated_days_min' => ['nullable', 'integer', 'min:0', 'max:365'],
            'estimated_days_max' => ['nullable', 'integer', 'min:0', 'max:365', 'gte:estimated_days_min'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Mã phương thức vận chuyển đã tồn tại.',
            'estimated_days_max.gte' => 'Số ngày giao tối đa phải lớn hơn hoặc bằng số ngày tối thiểu.',
        ];
    }
}
