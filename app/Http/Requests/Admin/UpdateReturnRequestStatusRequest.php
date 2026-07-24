<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReturnRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => trim((string) $this->input('status')),

            'approved_amount' =>
                $this->filled('approved_amount')
                    ? $this->input('approved_amount')
                    : null,

            'return_shipping_fee' =>
                $this->filled('return_shipping_fee')
                    ? $this->input('return_shipping_fee')
                    : null,

            'shipping_fee_payer' =>
                $this->filled('shipping_fee_payer')
                    ? trim((string) $this->input(
                        'shipping_fee_payer'
                    ))
                    : null,

            'rejection_reason' =>
                $this->filled('rejection_reason')
                    ? trim((string) $this->input(
                        'rejection_reason'
                    ))
                    : null,

            'note' =>
                $this->filled('note')
                    ? trim((string) $this->input('note'))
                    : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'approved',
                    'waiting_for_return',
                    'received',
                    'processing',
                    'completed',
                    'rejected',
                    'cancelled',
                ]),
            ],

            'approved_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'return_shipping_fee' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'shipping_fee_payer' => [
                'nullable',
                'string',
                Rule::in([
                    'customer',
                    'shop',
                    'shared',
                ]),
            ],

            'rejection_reason' => [
                'nullable',
                'required_if:status,rejected',
                'string',
                'max:2000',
            ],

            'note' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'nullable',
                'array',
            ],

            'items.*.product_condition' => [
                'nullable',
                'string',
                Rule::in([
                    'unopened',
                    'opened',
                    'used',
                    'damaged',
                    'defective',
                    'wrong_item',
                    'missing_parts',
                    'other',
                ]),
            ],

            'items.*.inspection_result' => [
                'nullable',
                'string',
                Rule::in([
                    'pending',
                    'accepted',
                    'partially_accepted',
                    'rejected',
                ]),
            ],

            'items.*.inspection_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items.*.inventory_action' => [
                'nullable',
                'string',
                Rule::in([
                    'none',
                    'restock',
                    'damaged',
                    'dispose',
                    'return_supplier',
                ]),
            ],

            'items.*.approved_refund_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'trạng thái yêu cầu',
            'approved_amount' => 'số tiền được duyệt',
            'return_shipping_fee' => 'phí gửi trả',
            'shipping_fee_payer' => 'bên chịu phí gửi trả',
            'rejection_reason' => 'lý do từ chối',
            'note' => 'ghi chú nội bộ',
            'items.*.product_condition' => 'tình trạng sản phẩm',
            'items.*.inspection_result' => 'kết quả kiểm tra',
            'items.*.inspection_note' => 'ghi chú kiểm tra',
            'items.*.inventory_action' => 'hướng xử lý tồn kho',
            'items.*.approved_refund_amount' =>
                'số tiền hoàn được duyệt',
        ];
    }
}
