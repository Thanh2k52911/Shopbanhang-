@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa banner')
@section('page-title', 'Chỉnh sửa banner')
@section('page-description', 'Cập nhật nội dung, hình ảnh, vị trí và lịch hiển thị banner.')

@section('content')
<form
    id="banner-form"
    method="POST"
    action="{{ route('admin.banners.update', $banner->id) }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Chỉnh sửa: {{ $banner->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                ID: {{ $banner->id }}
            </p>
        </div>

        <div class="flex gap-3">
            <a
                href="{{ route('admin.banners.show', $banner->id) }}"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700"
            >
                Quay lại
            </a>

            <button
                type="submit"
                class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700"
            >
                Lưu thay đổi
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('admin.banners.partials.form')
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('banner-form');
    const startAt = document.getElementById('start_at');
    const endAt = document.getElementById('end_at');
    const dateError = document.getElementById('date-error');

    const bindPreview = function (inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        input?.addEventListener('change', function () {
            const file = input.files?.[0];

            if (!file) {
                preview?.classList.add('hidden');
                preview?.removeAttribute('src');
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                window.alert('Ảnh phải là JPG, JPEG, PNG hoặc WEBP.');
                input.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                window.alert('Ảnh không được lớn hơn 10 MB.');
                input.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.remove('hidden');
            };

            reader.readAsDataURL(file);
        });
    };

    const validateDates = function () {
        if (!startAt?.value || !endAt?.value) {
            dateError?.classList.add('hidden');
            return true;
        }

        const valid = new Date(endAt.value) > new Date(startAt.value);
        dateError?.classList.toggle('hidden', valid);

        return valid;
    };

    bindPreview('desktop_image', 'desktop-preview');
    bindPreview('mobile_image', 'mobile-preview');

    startAt?.addEventListener('change', validateDates);
    endAt?.addEventListener('change', validateDates);

    form?.addEventListener('submit', function (event) {
        if (!validateDates()) {
            event.preventDefault();
            return;
        }

        form.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.textContent = 'Đang lưu...';
        });
    });

    validateDates();
});
</script>
@endpush
