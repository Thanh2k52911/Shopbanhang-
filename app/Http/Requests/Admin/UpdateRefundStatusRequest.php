<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRefundStatusRequest extends FormRequest
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
            'status' => trim((string) $this->input('status')),

            'provider_transaction_id' =>
                $this->filled('provider_transaction_id')
                    ? trim((string) $this->input(
                        'provider_transaction_id'
                    ))
                    : null,

            'failure_reason' =>
                $this->filled('failure_reason')
                    ? trim((string) $this->input(
                        'failure_reason'
                    ))
                    : null,

            'admin_note' =>
                $this->filled('admin_note')
                    ? trim((string) $this->input(
                        'admin_note'
                    ))
                    : null,
        ]);
    }

    /**
     * Quy tắc validation.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'processing',
                    'completed',
                    'failed',
                    'cancelled',
                ]),
            ],

            'provider_transaction_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'failure_reason' => [
                'nullable',
                'required_if:status,failed',
                'string',
                'max:2000',
            ],

            'admin_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Tên trường hiển thị.
     */
    public function attributes(): array
    {
        return [
            'status' => 'trạng thái hoàn tiền',
            'provider_transaction_id' =>
                'mã giao dịch hoàn tiền',

            'failure_reason' =>
                'lý do hoàn tiền thất bại',

            'admin_note' =>
                'ghi chú nội bộ',
        ];
    }

    /**
     * Thông báo validation.
     */
    public function messages(): array
    {
        return [
            'status.required' =>
                'Vui lòng chọn trạng thái hoàn tiền.',

            'status.in' =>
                'Trạng thái hoàn tiền được chọn không hợp lệ.',

            'provider_transaction_id.max' =>
                'Mã giao dịch hoàn tiền không được vượt quá 255 ký tự.',

            'failure_reason.required_if' =>
                'Vui lòng nhập lý do khi đánh dấu hoàn tiền thất bại.',

            'failure_reason.max' =>
                'Lý do thất bại không được vượt quá 2.000 ký tự.',

            'admin_note.max' =>
                'Ghi chú nội bộ không được vượt quá 2.000 ký tự.',
        ];
    }
}
