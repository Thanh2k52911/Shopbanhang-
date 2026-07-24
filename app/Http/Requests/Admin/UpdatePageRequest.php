<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('title'))),
            'show_in_header' => $this->boolean('show_in_header'),
            'show_in_footer' => $this->boolean('show_in_footer'),
            'status' => $this->boolean('status'),
            'remove_thumbnail' => $this->boolean('remove_thumbnail'),
        ]);
    }

    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            'content' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_thumbnail' => ['boolean'],
            'page_type' => ['required', Rule::in(['normal', 'policy', 'guide', 'about'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'template' => ['nullable', 'string', 'max:100'],
            'show_in_header' => ['boolean'],
            'show_in_footer' => ['boolean'],
            'status' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Đường dẫn trang đã tồn tại.',
            'slug.regex' => 'Đường dẫn chỉ được chứa chữ thường, số và dấu gạch ngang.',
            'thumbnail.max' => 'Ảnh đại diện không được lớn hơn 5 MB.',
        ];
    }
}
