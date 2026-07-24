<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return $this->addressRules();
    }

    protected function addressRules(): array
    {
        return [
            'receiver_name' => ['required', 'string', 'max:150'],
            'phone' => [
                'required',
                'string',
                'max:15',
                'regex:/^(0|\+84)[0-9]{9,10}$/',
            ],
            'province' => ['required', 'string', 'max:150'],
            'district' => ['required', 'string', 'max:150'],
            'ward' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_name.required' => 'Vui lòng nhập tên người nhận.',
            'receiver_name.max' => 'Tên người nhận không được vượt quá 150 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'province.required' => 'Vui lòng nhập Tỉnh/Thành phố.',
            'district.required' => 'Vui lòng nhập Quận/Huyện.',
            'ward.required' => 'Vui lòng nhập Phường/Xã.',
            'address.required' => 'Vui lòng nhập địa chỉ cụ thể.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'receiver_name',
            'phone',
            'province',
            'district',
            'ward',
            'address',
        ];

        $prepared = [];

        foreach ($fields as $field) {
            $value = $this->input($field);
            $prepared[$field] = is_string($value) ? trim($value) : $value;
        }

        if (is_string($prepared['phone'] ?? null)) {
            $prepared['phone'] = preg_replace('/\s+/', '', $prepared['phone']);
        }

        $prepared['is_default'] = $this->boolean('is_default');
        $this->merge($prepared);
    }
}
