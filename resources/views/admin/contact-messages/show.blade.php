@extends('admin.layouts.master')

@section('title', 'Chi tiết liên hệ')
@section('page-title', 'Chi tiết liên hệ')
@section('page-description', 'Xem nội dung, phân công và xử lý yêu cầu khách hàng.')

@section('content')
@php
    $displayName = $message->user_id
        ? ($message->user_name ?: $message->name)
        : $message->name;

    $displayEmail = $message->user_id
        ? ($message->user_email ?: $message->email)
        : $message->email;

    $statusClass = match ($message->status) {
        'new' => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-orange-100 text-orange-700',
        'replied' => 'bg-green-100 text-green-700',
        'closed' => 'bg-gray-100 text-gray-700',
        'spam' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };

    $priorityClass = match ($message->priority) {
        'urgent' => 'bg-red-100 text-red-700',
        'high' => 'bg-pink-100 text-pink-700',
        'normal' => 'bg-blue-100 text-blue-700',
        'low' => 'bg-gray-100 text-gray-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $message->contact_code }}</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statuses[$message->status] ?? $message->status }}</span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">{{ $priorities[$message->priority] ?? $message->priority }}</span>
            </div>

            <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($message->created_at)->format('d/m/Y H:i') }}</p>
        </div>

        <a href="{{ route('admin.contact-messages.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
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

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_380px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4">
                        @if ($message->user_avatar)
                            <img src="{{ asset('storage/' . ltrim($message->user_avatar, '/')) }}" alt="{{ $displayName }}" class="h-14 w-14 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-xl font-bold text-pink-600">
                                {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            @if ($message->user_id)
                                <a href="{{ route('admin.customers.show', $message->user_id) }}" class="font-bold text-gray-900 hover:text-pink-600">{{ $displayName }}</a>
                            @else
                                <strong class="text-gray-900">{{ $displayName }}</strong>
                            @endif

                            <p class="mt-1 text-sm text-gray-500">{{ $displayEmail ?: 'Không có email' }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ $message->phone ?: 'Không có điện thoại' }}</p>

                            <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $message->user_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $message->user_id ? 'Thành viên' : 'Khách vãng lai' }}
                            </span>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-gray-500">Loại liên hệ</p>
                        <strong class="mt-1 block text-gray-900">{{ $types[$message->type] ?? $message->type }}</strong>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">{{ $message->subject }}</h3>
                <p class="mt-5 whitespace-pre-line text-base leading-7 text-gray-700">{{ $message->message }}</p>
            </section>

            @if ($message->order_id && $message->order_code)
                <section class="rounded-xl border border-gray-200 bg-white p-6">
                    <h3 class="text-lg font-bold text-gray-900">Đơn hàng liên quan</h3>

                    <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">Mã đơn</dt>
                            <dd class="mt-1"><a href="{{ route('admin.orders.show', $message->order_id) }}" class="font-semibold text-blue-600 hover:underline">{{ $message->order_code }}</a></dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">Tổng tiền</dt>
                            <dd class="mt-1 font-semibold text-orange-600">{{ number_format((float) $message->total_amount, 0, ',', '.') }}đ</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">Trạng thái đơn</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $message->order_status }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">Thanh toán / vận chuyển</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $message->payment_status }} / {{ $message->shipping_status }}</dd>
                        </div>
                    </dl>
                </section>
            @endif

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Ghi chú nội bộ</h3>

                <form method="POST" action="{{ route('admin.contact-messages.update-note', $message->id) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <textarea name="admin_note" rows="8" maxlength="10000" class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500">{{ old('admin_note', $message->admin_note) }}</textarea>

                    <button type="submit" class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">Lưu ghi chú</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Liên hệ khác cùng email</h3>

                <div class="mt-5 space-y-3">
                    @forelse ($customerMessages as $item)
                        <a href="{{ route('admin.contact-messages.show', $item->id) }}" class="block rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between gap-4">
                                <strong class="text-blue-600">{{ $item->contact_code }}</strong>
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $statuses[$item->status] ?? $item->status }}</span>
                            </div>
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $item->subject }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Không có liên hệ khác.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Cập nhật xử lý</h3>

                <form method="POST" action="{{ route('admin.contact-messages.update-status', $message->id) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($message->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Mức ưu tiên</label>
                        <select name="priority" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected($message->priority === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Người xử lý</label>
                        <select name="assigned_to" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                            <option value="">Chưa phân công</option>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->id }}" @selected((string) $message->assigned_to === (string) $assignee->id)>{{ $assignee->name }} — {{ $assignee->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Lưu cập nhật</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thao tác nhanh</h3>

                <div class="mt-4 space-y-3">
                    <form method="POST" action="{{ route('admin.contact-messages.assign-to-me', $message->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700">Nhận xử lý</button>
                    </form>

                    <form method="POST" action="{{ route('admin.contact-messages.mark-replied', $message->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-700">Đánh dấu đã phản hồi</button>
                    </form>

                    <form method="POST" action="{{ route('admin.contact-messages.close', $message->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700">Đóng liên hệ</button>
                    </form>

                    <form method="POST" action="{{ route('admin.contact-messages.mark-spam', $message->id) }}" onsubmit="return confirm('Đánh dấu liên hệ này là thư rác?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700">Đánh dấu thư rác</button>
                    </form>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thông tin xử lý</h3>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Người xử lý</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $message->assignee_name ?: 'Chưa phân công' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Đã phản hồi lúc</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $message->replied_at ? \Carbon\Carbon::parse($message->replied_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Đã đóng lúc</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $message->closed_at ? \Carbon\Carbon::parse($message->closed_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Cập nhật gần nhất</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($message->updated_at)->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>
@endsection
