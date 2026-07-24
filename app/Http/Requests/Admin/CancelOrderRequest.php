<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    /**
     * Middleware Admin đã kiểm tra quyền truy cập.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cancel_reason' => $this->filled('cancel_reason')
                ? trim((string) $this->input('cancel_reason'))
                : null,

            'note' => $this->filled('note')
                ? trim((string) $this->input('note'))
                : null,
        ]);
    }

    /**
     * Quy tắc kiểm tra dữ liệu.
     */
    public function rules(): array
    {
        return [
            'cancel_reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Tên trường hiển thị khi validation lỗi.
     */
    public function attributes(): array
    {
        return [
            'cancel_reason' => 'lý do hủy đơn',
            'note' => 'ghi chú nội bộ',
        ];
    }

    /**
     * Nội dung lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'cancel_reason.required' =>
                'Vui lòng nhập lý do hủy đơn hàng.',

            'cancel_reason.min' =>
                'Lý do hủy đơn phải có ít nhất 5 ký tự.',

            'cancel_reason.max' =>
                'Lý do hủy đơn không được vượt quá 2.000 ký tự.',

            'note.max' =>
                'Ghi chú nội bộ không được vượt quá 2.000 ký tự.',
        ];
    }
}
