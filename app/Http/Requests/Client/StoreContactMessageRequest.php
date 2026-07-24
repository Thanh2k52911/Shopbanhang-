<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
            'subject' => trim((string) $this->input('subject')),
            'message' => trim((string) $this->input('message')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+().\-\s]+$/'],
            'type' => [
                'required',
                Rule::in([
                    'general',
                    'order',
                    'product',
                    'payment',
                    'shipping',
                    'return',
                    'complaint',
                ]),
            ],
            'subject' => ['required', 'string', 'min:5', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'order_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'type.required' => 'Vui lòng chọn loại hỗ trợ.',
            'type.in' => 'Loại hỗ trợ không hợp lệ.',
            'subject.required' => 'Vui lòng nhập tiêu đề.',
            'subject.min' => 'Tiêu đề phải có ít nhất 5 ký tự.',
            'message.required' => 'Vui lòng nhập nội dung liên hệ.',
            'message.min' => 'Nội dung phải có ít nhất 10 ký tự.',
            'message.max' => 'Nội dung không được vượt quá 5.000 ký tự.',
        ];
    }
}
