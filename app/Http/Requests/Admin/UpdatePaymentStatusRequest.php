<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
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

            'provider_transaction_id' => $this->filled('provider_transaction_id')
                ? trim((string) $this->input('provider_transaction_id'))
                : null,

            'failure_reason' => $this->filled('failure_reason')
                ? trim((string) $this->input('failure_reason'))
                : null,

            'note' => $this->filled('note')
                ? trim((string) $this->input('note'))
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
                    'paid',
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

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Tên trường tiếng Việt.
     */
    public function attributes(): array
    {
        return [
            'status' => 'trạng thái thanh toán',
            'provider_transaction_id' => 'mã giao dịch nhà cung cấp',
            'failure_reason' => 'lý do thanh toán thất bại',
            'note' => 'ghi chú',
        ];
    }

    /**
     * Thông báo validation.
     */
    public function messages(): array
    {
        return [
            'status.required' =>
                'Vui lòng chọn trạng thái thanh toán.',

            'status.in' =>
                'Trạng thái thanh toán được chọn không hợp lệ.',

            'provider_transaction_id.max' =>
                'Mã giao dịch nhà cung cấp không được vượt quá 255 ký tự.',

            'failure_reason.required_if' =>
                'Vui lòng nhập lý do khi đánh dấu thanh toán thất bại.',

            'failure_reason.max' =>
                'Lý do thất bại không được vượt quá 2.000 ký tự.',

            'note.max' =>
                'Ghi chú không được vượt quá 2.000 ký tự.',
        ];
    }
}
