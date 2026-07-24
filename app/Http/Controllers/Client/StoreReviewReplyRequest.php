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
                'Vui lòng nhập nội dung phản hồi.',

            'content.max' =>
                'Nội dung phản hồi không được vượt quá 3.000 ký tự.',
        ];
    }
}
