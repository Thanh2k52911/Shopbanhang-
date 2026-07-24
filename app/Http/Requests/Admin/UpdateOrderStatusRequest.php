<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Middleware Admin đã kiểm tra quyền truy cập,
     * nên request này cho phép tiếp tục validation.
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
            'order_status' => trim(
                (string) $this->input('order_status')
            ),

            'note' => $this->filled('note')
                ? trim((string) $this->input('note'))
                : null,

            'cancel_reason' => $this->filled('cancel_reason')
                ? trim((string) $this->input('cancel_reason'))
                : null,
        ]);
    }

    /**
     * Quy tắc kiểm tra dữ liệu.
     */
    public function rules(): array
    {
        return [
            'order_status' => [
                'required',
                'string',
                Rule::in([
                    'confirmed',
                    'processing',
                    'packed',
                    'shipping',
                    'completed',
                    'cancelled',
                    'returned',
                ]),
            ],

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'cancel_reason' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('order_status') === 'cancelled'
                ),
                'nullable',
                'string',
                'min:5',
                'max:2000',
            ],
        ];
    }

    /**
     * Tên trường hiển thị trong lỗi validation.
     */
    public function attributes(): array
    {
        return [
            'order_status' => 'trạng thái đơn hàng',
            'note' => 'ghi chú xử lý',
            'cancel_reason' => 'lý do hủy đơn',
        ];
    }

    /**
     * Nội dung lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'order_status.required' =>
                'Vui lòng chọn trạng thái đơn hàng.',

            'order_status.in' =>
                'Trạng thái đơn hàng được chọn không hợp lệ.',

            'note.max' =>
                'Ghi chú xử lý không được vượt quá 2.000 ký tự.',

            'cancel_reason.required' =>
                'Vui lòng nhập lý do hủy đơn hàng.',

            'cancel_reason.min' =>
                'Lý do hủy đơn phải có ít nhất 5 ký tự.',

            'cancel_reason.max' =>
                'Lý do hủy đơn không được vượt quá 2.000 ký tự.',
        ];
    }
}
