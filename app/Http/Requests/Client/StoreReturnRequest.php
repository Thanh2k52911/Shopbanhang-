<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReturnRequest extends FormRequest
{
    /**
     * Route đã nằm trong middleware auth.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'request_type' => trim(
                (string) $this->input('request_type')
            ),

            'reason' => trim(
                (string) $this->input('reason')
            ),

            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,

            'customer_note' => $this->filled('customer_note')
                ? trim((string) $this->input('customer_note'))
                : null,
        ]);
    }

    /**
     * Quy tắc validation.
     */
    public function rules(): array
    {
        return [
            'request_type' => [
                'required',
                'string',
                Rule::in([
                    'return',
                    'exchange',
                    'refund',
                ]),
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'customer_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sản phẩm khách chọn
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.selected' => [
                'nullable',
                'boolean',
            ],

            'items.*.quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'items.*.reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items.*.product_condition' => [
                'nullable',
                'string',
                Rule::in([
                    'unopened',
                    'opened',
                    'damaged',
                    'defective',
                    'wrong_item',
                    'expired',
                    'allergic',
                    'other',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Ảnh bằng chứng
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
                'max:6',
            ],

            'images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    /**
     * Tên trường tiếng Việt.
     */
    public function attributes(): array
    {
        return [
            'request_type' => 'loại yêu cầu',
            'reason' => 'lý do',
            'description' => 'mô tả chi tiết',
            'customer_note' => 'ghi chú',

            'items' => 'danh sách sản phẩm',
            'items.*.selected' => 'sản phẩm được chọn',
            'items.*.quantity' => 'số lượng yêu cầu',
            'items.*.reason' => 'lý do của sản phẩm',
            'items.*.description' => 'mô tả sản phẩm',
            'items.*.product_condition' => 'tình trạng sản phẩm',

            'images' => 'ảnh bằng chứng',
            'images.*' => 'ảnh bằng chứng',
        ];
    }

    /**
     * Thông báo validation.
     */
    public function messages(): array
    {
        return [
            'request_type.required' =>
                'Vui lòng chọn loại yêu cầu.',

            'request_type.in' =>
                'Loại yêu cầu không hợp lệ.',

            'reason.required' =>
                'Vui lòng nhập lý do gửi yêu cầu.',

            'reason.min' =>
                'Lý do phải có ít nhất 5 ký tự.',

            'reason.max' =>
                'Lý do không được vượt quá 255 ký tự.',

            'description.max' =>
                'Mô tả không được vượt quá 3.000 ký tự.',

            'customer_note.max' =>
                'Ghi chú không được vượt quá 2.000 ký tự.',

            'items.required' =>
                'Vui lòng chọn ít nhất một sản phẩm.',

            'items.array' =>
                'Danh sách sản phẩm không hợp lệ.',

            'items.min' =>
                'Vui lòng chọn ít nhất một sản phẩm.',

            'items.*.quantity.integer' =>
                'Số lượng sản phẩm phải là số nguyên.',

            'items.*.quantity.min' =>
                'Số lượng sản phẩm phải từ 1 trở lên.',

            'items.*.product_condition.in' =>
                'Tình trạng sản phẩm không hợp lệ.',

            'images.max' =>
                'Bạn chỉ được tải tối đa 6 ảnh.',

            'images.*.image' =>
                'Tệp tải lên phải là hình ảnh.',

            'images.*.mimes' =>
                'Ảnh phải có định dạng JPG, JPEG, PNG hoặc WEBP.',

            'images.*.max' =>
                'Mỗi ảnh không được vượt quá 5MB.',
        ];
    }
}
