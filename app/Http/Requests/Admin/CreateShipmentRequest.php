<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateShipmentRequest extends FormRequest
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
        $nullableFields = [
            'shipping_method_id',
            'tracking_code',
            'carrier_name',
            'service_name',
            'weight',
            'length',
            'width',
            'height',
            'estimated_delivery_at',
            'note',
        ];

        $normalizedData = [];

        foreach ($nullableFields as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = trim($value);
            }

            $normalizedData[$field] = blank($value)
                ? null
                : $value;
        }

        $this->merge($normalizedData);
    }

    /**
     * Quy tắc validation.
     */
    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Phương thức và đơn vị vận chuyển
            |--------------------------------------------------------------------------
            */

            'shipping_method_id' => [
                'nullable',
                'integer',
                Rule::exists('shipping_methods', 'id')
                    ->where(function ($query): void {
                        $query
                            ->where('status', true)
                            ->whereNull('deleted_at');
                    }),
            ],

            /*
             * Nếu không chọn shipping_method_id thì bắt buộc phải nhập
             * tên đơn vị vận chuyển thủ công.
             */
            'carrier_name' => [
                'nullable',
                'required_without:shipping_method_id',
                'string',
                'max:150',
            ],

            'service_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Mã vận đơn
            |--------------------------------------------------------------------------
            */

            'tracking_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Chi phí vận chuyển và COD
            |--------------------------------------------------------------------------
            |
            | Không nhận shipping_fee hoặc cod_amount từ request.
            | ShipmentService phải lấy shipping_fee từ order và tự tính cod_amount
            | để tránh dữ liệu shipment lệch với đơn hàng.
            |
            */

            /*
            |--------------------------------------------------------------------------
            | Trọng lượng và kích thước
            |--------------------------------------------------------------------------
            |
            | weight tính theo gram.
            | length, width, height là số nguyên dương.
            |
            */

            'weight' => [
                'nullable',
                'integer',
                'min:1',
                'max:4294967295',
            ],

            'length' => [
                'nullable',
                'integer',
                'min:1',
                'max:4294967295',
            ],

            'width' => [
                'nullable',
                'integer',
                'min:1',
                'max:4294967295',
            ],

            'height' => [
                'nullable',
                'integer',
                'min:1',
                'max:4294967295',
            ],

            /*
            |--------------------------------------------------------------------------
            | Thời gian và ghi chú
            |--------------------------------------------------------------------------
            */

            'estimated_delivery_at' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Tên trường hiển thị bằng tiếng Việt.
     */
    public function attributes(): array
    {
        return [
            'shipping_method_id' => 'phương thức vận chuyển',
            'carrier_name' => 'đơn vị vận chuyển',
            'service_name' => 'dịch vụ vận chuyển',
            'tracking_code' => 'mã vận đơn',
            'weight' => 'trọng lượng',
            'length' => 'chiều dài',
            'width' => 'chiều rộng',
            'height' => 'chiều cao',
            'estimated_delivery_at' => 'thời gian giao dự kiến',
            'note' => 'ghi chú vận chuyển',
        ];
    }

    /**
     * Thông báo validation tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'shipping_method_id.integer' =>
                'Phương thức vận chuyển không hợp lệ.',

            'shipping_method_id.exists' =>
                'Phương thức vận chuyển không tồn tại hoặc đã ngừng hoạt động.',

            'carrier_name.required_without' =>
                'Vui lòng chọn phương thức vận chuyển hoặc nhập tên đơn vị vận chuyển.',

            'carrier_name.max' =>
                'Tên đơn vị vận chuyển không được vượt quá 150 ký tự.',

            'service_name.max' =>
                'Tên dịch vụ vận chuyển không được vượt quá 150 ký tự.',

            'tracking_code.max' =>
                'Mã vận đơn không được vượt quá 100 ký tự.',

            'weight.integer' =>
                'Trọng lượng phải là số nguyên.',

            'weight.min' =>
                'Trọng lượng phải lớn hơn 0 gram.',

            'length.integer' =>
                'Chiều dài phải là số nguyên.',

            'length.min' =>
                'Chiều dài phải lớn hơn 0.',

            'width.integer' =>
                'Chiều rộng phải là số nguyên.',

            'width.min' =>
                'Chiều rộng phải lớn hơn 0.',

            'height.integer' =>
                'Chiều cao phải là số nguyên.',

            'height.min' =>
                'Chiều cao phải lớn hơn 0.',

            'estimated_delivery_at.date' =>
                'Thời gian giao dự kiến không hợp lệ.',

            'estimated_delivery_at.after_or_equal' =>
                'Thời gian giao dự kiến phải từ hôm nay trở đi.',

            'note.max' =>
                'Ghi chú vận chuyển không được vượt quá 2.000 ký tự.',
        ];
    }
}
