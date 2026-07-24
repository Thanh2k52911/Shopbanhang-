<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cho phép cả khách chưa đăng nhập đặt hàng.
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('user_addresses', 'id')
                    ->where(fn ($query) => $query->where(
                        'user_id',
                        (int) auth()->id()
                    )),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^(0|\+84)[0-9]{9,10}$/',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'province' => [
                'required',
                'string',
                'max:150',
            ],

            'district' => [
                'required',
                'string',
                'max:150',
            ],

            'ward' => [
                'required',
                'string',
                'max:150',
            ],

            'address' => [
                'required',
                'string',
                'max:500',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'shipping_method_id' => [
                'required',
                'integer',
                'exists:shipping_methods,id',
            ],

            'payment_method' => [
                'required',
                'in:cod',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.exists' =>
                'Địa chỉ đã chọn không tồn tại hoặc không thuộc tài khoản của bạn.',

            'name.required' =>
                'Vui lòng nhập họ và tên người nhận.',

            'name.max' =>
                'Họ và tên không được vượt quá 150 ký tự.',

            'phone.required' =>
                'Vui lòng nhập số điện thoại.',

            'phone.regex' =>
                'Số điện thoại không đúng định dạng Việt Nam.',

            'email.email' =>
                'Địa chỉ email không đúng định dạng.',

            'province.required' =>
                'Vui lòng nhập Tỉnh/Thành phố.',

            'district.required' =>
                'Vui lòng nhập Quận/Huyện.',

            'ward.required' =>
                'Vui lòng nhập Phường/Xã.',

            'address.required' =>
                'Vui lòng nhập địa chỉ nhận hàng.',

            'shipping_method_id.required' =>
                'Vui lòng chọn phương thức vận chuyển.',

            'shipping_method_id.exists' =>
                'Phương thức vận chuyển không tồn tại.',

            'payment_method.required' =>
                'Vui lòng chọn phương thức thanh toán.',

            'payment_method.in' =>
                'Phương thức thanh toán chưa được hỗ trợ.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name)
                ? trim($this->name)
                : $this->name,

            'phone' => is_string($this->phone)
                ? preg_replace('/\s+/', '', trim($this->phone))
                : $this->phone,

            'email' => is_string($this->email)
                ? trim($this->email)
                : $this->email,

            'province' => is_string($this->province)
                ? trim($this->province)
                : $this->province,

            'district' => is_string($this->district)
                ? trim($this->district)
                : $this->district,

            'ward' => is_string($this->ward)
                ? trim($this->ward)
                : $this->ward,

            'address' => is_string($this->address)
                ? trim($this->address)
                : $this->address,

            'note' => is_string($this->note)
                ? trim($this->note)
                : $this->note,
        ]);
    }
}
