<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRefundRequest extends FormRequest
{
    /**
     * Middleware Admin đã kiểm tra quyền truy cập.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $nullableFields = [
            'bank_name',
            'bank_account_number',
            'bank_account_name',
            'reason',
            'admin_note',
        ];

        $normalized = [];

        foreach ($nullableFields as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = trim($value);
            }

            $normalized[$field] = blank($value)
                ? null
                : $value;
        }

        $this->merge($normalized);
    }

    /**
     * Quy tắc validation.
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999999.99',
            ],

            'method' => [
                'required',
                'string',
                Rule::in([
                    'original_payment',
                    'bank_transfer',
                    'cash',
                    'store_credit',
                    'coupon',
                ]),
            ],

            'bank_name' => [
                'nullable',
                'required_if:method,bank_transfer',
                'string',
                'max:150',
            ],

            'bank_account_number' => [
                'nullable',
                'required_if:method,bank_transfer',
                'string',
                'max:100',
            ],

            'bank_account_name' => [
                'nullable',
                'required_if:method,bank_transfer',
                'string',
                'max:150',
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
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
     * Tên trường tiếng Việt.
     */
    public function attributes(): array
    {
        return [
            'amount' => 'số tiền hoàn',
            'method' => 'phương thức hoàn tiền',
            'bank_name' => 'tên ngân hàng',
            'bank_account_number' => 'số tài khoản',
            'bank_account_name' => 'tên chủ tài khoản',
            'reason' => 'lý do hoàn tiền',
            'admin_note' => 'ghi chú nội bộ',
        ];
    }

    /**
     * Thông báo validation.
     */
    public function messages(): array
    {
        return [
            'amount.required' =>
                'Vui lòng nhập số tiền cần hoàn.',

            'amount.numeric' =>
                'Số tiền hoàn phải là một số hợp lệ.',

            'amount.gt' =>
                'Số tiền hoàn phải lớn hơn 0.',

            'method.required' =>
                'Vui lòng chọn phương thức hoàn tiền.',

            'method.in' =>
                'Phương thức hoàn tiền không hợp lệ.',

            'bank_name.required_if' =>
                'Vui lòng nhập tên ngân hàng khi hoàn bằng chuyển khoản.',

            'bank_account_number.required_if' =>
                'Vui lòng nhập số tài khoản nhận tiền.',

            'bank_account_name.required_if' =>
                'Vui lòng nhập tên chủ tài khoản nhận tiền.',

            'reason.required' =>
                'Vui lòng nhập lý do hoàn tiền.',

            'reason.min' =>
                'Lý do hoàn tiền phải có ít nhất 5 ký tự.',

            'reason.max' =>
                'Lý do hoàn tiền không được vượt quá 2.000 ký tự.',

            'admin_note.max' =>
                'Ghi chú nội bộ không được vượt quá 2.000 ký tự.',
        ];
    }
}
