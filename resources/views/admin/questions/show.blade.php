@extends('admin.layouts.master')

@section('title', 'Chi tiết câu hỏi')
@section('page-title', 'Chi tiết câu hỏi')
@section('page-description', 'Xem nội dung, trạng thái và câu trả lời sản phẩm.')

@section('content')
@php
    $authorName = $question->user_id
        ? ($question->user_name ?: 'Thành viên')
        : ($question->guest_name ?: 'Khách vãng lai');

    $authorEmail = $question->user_id
        ? $question->user_email
        : $question->guest_email;

    $statusClass = match ($question->status) {
        'pending' => 'bg-orange-100 text-orange-700',
        'published' => 'bg-blue-100 text-blue-700',
        'answered' => 'bg-green-100 text-green-700',
        'hidden' => 'bg-gray-100 text-gray-700',
        'rejected' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Câu hỏi #{{ $question->id }}</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $questionStatuses[$question->status] ?? $question->status }}</span>
            </div>

            <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($question->created_at)->format('d/m/Y H:i') }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.questions.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
            <a href="{{ route('admin.products.show', $question->product_id) }}" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Xem sản phẩm</a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_370px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4">
                        @if ($question->user_avatar)
                            <img src="{{ asset('storage/' . ltrim($question->user_avatar, '/')) }}" alt="{{ $authorName }}" class="h-14 w-14 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-xl font-bold text-pink-600">
                                {{ mb_strtoupper(mb_substr($authorName, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            @if ($question->user_id)
                                <a href="{{ route('admin.customers.show', $question->user_id) }}" class="font-bold text-gray-900 hover:text-pink-600">{{ $authorName }}</a>
                            @else
                                <strong class="text-gray-900">{{ $authorName }}</strong>
                            @endif

                            <p class="mt-1 text-sm text-gray-500">{{ $authorEmail ?: 'Không có email' }}</p>

                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $question->user_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $question->user_id ? 'Thành viên' : 'Khách vãng lai' }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $question->is_public ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $question->is_public ? 'Công khai' : 'Riêng tư' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right text-sm text-gray-500">
                        <p>Trả lời lúc:</p>
                        <strong class="mt-1 block text-gray-900">
                            {{ $question->answered_at
                                ? \Carbon\Carbon::parse($question->answered_at)->format('d/m/Y H:i')
                                : 'Chưa trả lời' }}
                        </strong>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Nội dung câu hỏi</h3>
                    <p class="mt-3 whitespace-pre-line text-base leading-7 text-gray-700">{{ $question->question }}</p>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Thông tin sản phẩm</h3>

                <div class="mt-5 flex items-start gap-4">
                    @if ($question->product_image)
                        <img src="{{ asset('storage/' . ltrim($question->product_image, '/')) }}" alt="{{ $question->product_name }}" class="h-20 w-20 rounded-xl border border-gray-200 object-cover">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-gray-100 text-xs text-gray-400">No image</div>
                    @endif

                    <div>
                        <a href="{{ route('admin.products.show', $question->product_id) }}" class="text-lg font-semibold text-blue-600 hover:underline">{{ $question->product_name }}</a>
                        <p class="mt-2 text-sm text-gray-500">Trạng thái: <strong class="text-gray-900">{{ $question->product_status }}</strong></p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Danh sách câu trả lời</h3>

                <div class="mt-5 space-y-4">
                    @forelse ($answers as $answer)
                        @php
                            $isAdminAnswer = $answer->roles->intersect([
                                'super_admin',
                                'admin',
                                'staff',
                                'customer_support',
                            ])->isNotEmpty();
                        @endphp

                        <article class="rounded-xl border {{ $answer->is_official ? 'border-pink-200 bg-pink-50' : 'border-gray-200 bg-white' }} p-4">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <strong class="text-gray-900">{{ $answer->user_name ?: 'Người dùng' }}</strong>

                                        @if ($answer->is_official)
                                            <span class="rounded-full bg-pink-100 px-2.5 py-1 text-xs font-semibold text-pink-700">Câu trả lời chính thức</span>
                                        @elseif ($isAdminAnswer)
                                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Quản trị</span>
                                        @endif

                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $answer->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $answer->status ? 'Đang hiển thị' : 'Đang ẩn' }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-xs text-gray-500">{{ $answer->user_email ?: 'Không có email' }} · {{ \Carbon\Carbon::parse($answer->created_at)->format('d/m/Y H:i') }}</p>
                                </div>

                                @if ($answer->is_official)
                                    <form method="POST" action="{{ route('admin.questions.answers.destroy', [$question->id, $answer->id]) }}" onsubmit="return confirm('Xóa câu trả lời chính thức này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Xóa</button>
                                    </form>
                                @endif
                            </div>

                            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $answer->answer }}</p>

                            @if ($answer->is_official)
                                <details class="mt-4 border-t border-pink-200 pt-4">
                                    <summary class="cursor-pointer text-sm font-semibold text-pink-700">Chỉnh sửa câu trả lời</summary>

                                    <form method="POST" action="{{ route('admin.questions.answers.update', [$question->id, $answer->id]) }}" class="mt-4 space-y-4">
                                        @csrf
                                        @method('PATCH')

                                        <textarea name="answer" rows="5" maxlength="5000" required class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('answer', $answer->answer) }}</textarea>

                                        <label class="flex items-start gap-3 rounded-lg border border-pink-200 bg-white p-3">
                                            <input type="hidden" name="status" value="0">
                                            <input type="checkbox" name="status" value="1" @checked((bool) $answer->status) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                            <span>
                                                <strong class="block text-sm text-gray-900">Hiển thị câu trả lời</strong>
                                                <span class="mt-1 block text-xs text-gray-500">Bỏ chọn để ẩn câu trả lời.</span>
                                            </span>
                                        </label>

                                        <button type="submit" class="rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white">Lưu câu trả lời</button>
                                    </form>
                                </details>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">Chưa có câu trả lời.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Câu hỏi khác của sản phẩm</h3>

                <div class="mt-5 space-y-3">
                    @forelse ($relatedQuestions as $related)
                        @php
                            $relatedAuthor = $related->user_name ?: ($related->guest_name ?: 'Khách vãng lai');
                        @endphp

                        <a href="{{ route('admin.questions.show', $related->id) }}" class="block rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between gap-4">
                                <strong class="text-gray-900">{{ $relatedAuthor }}</strong>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $related->answered_at ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $related->answered_at ? 'Đã trả lời' : 'Chưa trả lời' }}
                                </span>
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm text-gray-600">{{ $related->question }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Không có câu hỏi liên quan.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Trạng thái câu hỏi</h3>

                <form method="POST" action="{{ route('admin.questions.update-status', $question->id) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                            @foreach ($questionStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($question->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
                        <input type="hidden" name="is_public" value="0">
                        <input type="checkbox" name="is_public" value="1" @checked((bool) $question->is_public) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        <span>
                            <strong class="block text-sm text-gray-900">Công khai câu hỏi</strong>
                            <span class="mt-1 block text-xs text-gray-500">Hiển thị trên trang sản phẩm.</span>
                        </span>
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Cập nhật trạng thái</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Trả lời chính thức</h3>

                <form method="POST" action="{{ route('admin.questions.answer', $question->id) }}" class="mt-4 space-y-4">
                    @csrf

                    <textarea name="answer" rows="7" maxlength="5000" required placeholder="Nhập câu trả lời chính thức..." class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('answer') }}</textarea>

                    <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
                        <input type="hidden" name="publish_question" value="0">
                        <input type="checkbox" name="publish_question" value="1" checked class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        <span>
                            <strong class="block text-sm text-gray-900">Công khai sau khi trả lời</strong>
                            <span class="mt-1 block text-xs text-gray-500">Câu hỏi sẽ chuyển sang trạng thái đã trả lời.</span>
                        </span>
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">Gửi câu trả lời</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thông tin nhanh</h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Tổng câu trả lời</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($answers->count()) }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Câu trả lời chính thức</dt>
                        <dd class="font-semibold text-pink-600">{{ number_format($answers->where('is_official', true)->count()) }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Đang hiển thị</dt>
                        <dd class="font-semibold text-green-600">{{ number_format($answers->where('status', true)->count()) }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
