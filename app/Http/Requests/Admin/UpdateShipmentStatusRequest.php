<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentStatusRequest extends FormRequest
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

            'location' => $this->filled('location')
                ? trim((string) $this->input('location'))
                : null,

            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
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
                    'picked_up',
                    'in_transit',
                    'out_for_delivery',
                    'delivered',
                    'delivery_failed',
                    'returned',
                    'cancelled',
                ]),
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
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
            'status' => 'trạng thái vận chuyển',
            'location' => 'vị trí',
            'description' => 'mô tả',
        ];
    }

    /**
     * Thông báo validation.
     */
    public function messages(): array
    {
        return [
            'status.required' =>
                'Vui lòng chọn trạng thái vận chuyển.',

            'status.in' =>
                'Trạng thái vận chuyển được chọn không hợp lệ.',

            'location.max' =>
                'Vị trí không được vượt quá 255 ký tự.',

            'description.max' =>
                'Mô tả không được vượt quá 2.000 ký tự.',
        ];
    }
}
