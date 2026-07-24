<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rating' => $this->filled('rating')
                ? (int) $this->input('rating')
                : null,

            'order_item_id' => $this->filled('order_item_id')
                ? (int) $this->input('order_item_id')
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'order_item_id' => [
                'required',
                'integer',
                'exists:order_items,id',
            ],

            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'content' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'images' => [
                'nullable',
                'array',
                'max:5',
            ],

            'images.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'videos' => [
                'nullable',
                'array',
                'max:2',
            ],

            'videos.*' => [
                'required',
                'file',
                'mimes:mp4,mov,avi,webm',
                'max:51200',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'order_item_id.required' =>
                'Không xác định được sản phẩm cần đánh giá.',

            'order_item_id.integer' =>
                'Sản phẩm cần đánh giá không hợp lệ.',

            'order_item_id.exists' =>
                'Sản phẩm trong đơn hàng không tồn tại.',

            'rating.required' =>
                'Vui lòng chọn số sao đánh giá.',

            'rating.integer' =>
                'Số sao đánh giá không hợp lệ.',

            'rating.between' =>
                'Số sao đánh giá phải từ 1 đến 5.',

            'content.string' =>
                'Nội dung đánh giá không hợp lệ.',

            'content.max' =>
                'Nội dung đánh giá không được vượt quá 3000 ký tự.',

            'images.array' =>
                'Danh sách hình ảnh không hợp lệ.',

            'images.max' =>
                'Mỗi đánh giá chỉ được tải tối đa 5 hình ảnh.',

            'images.*.image' =>
                'Tệp tải lên phải là hình ảnh.',

            'images.*.mimes' =>
                'Ảnh chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.',

            'images.*.max' =>
                'Mỗi hình ảnh không được lớn hơn 5MB.',

            'videos.array' =>
                'Danh sách video không hợp lệ.',

            'videos.max' =>
                'Mỗi đánh giá chỉ được tải tối đa 2 video.',

            'videos.*.mimes' =>
                'Video chỉ hỗ trợ MP4, MOV, AVI hoặc WEBM.',

            'videos.*.max' =>
                'Mỗi video không được lớn hơn 50MB.',
        ];
    }
}
