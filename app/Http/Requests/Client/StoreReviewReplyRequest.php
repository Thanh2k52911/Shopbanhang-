<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'min:1',
                'max:3000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' =>
                'Vui lòng nhập nội dung trả lời.',

            'content.string' =>
                'Nội dung trả lời không hợp lệ.',

            'content.min' =>
                'Nội dung trả lời phải có ít nhất 1 ký tự.',

            'content.max' =>
                'Nội dung trả lời không được vượt quá 3.000 ký tự.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => trim(
                (string) $this->input('content')
            ),
        ]);
    }
}
