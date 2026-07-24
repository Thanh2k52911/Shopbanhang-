@extends('admin.layouts.master')

@section('title', 'Quản lý liên hệ')
@section('page-title', 'Quản lý liên hệ')
@section('page-description', 'Theo dõi, phân công và xử lý yêu cầu từ khách hàng.')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Liên hệ khách hàng</h2>
        <p class="mt-1 text-sm text-gray-500">Quản lý yêu cầu, khiếu nại và trao đổi hỗ trợ.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
        @foreach ([
            ['Tổng liên hệ', $statistics->total ?? 0, 'text-gray-900'],
            ['Mới', $statistics->new_count ?? 0, 'text-blue-600'],
            ['Đang xử lý', $statistics->processing_count ?? 0, 'text-orange-600'],
            ['Đã phản hồi', $statistics->replied_count ?? 0, 'text-green-600'],
            ['Đã đóng', $statistics->closed_count ?? 0, 'text-gray-600'],
            ['Thư rác', $statistics->spam_count ?? 0, 'text-red-600'],
            ['Ưu tiên cao', $statistics->high_priority_count ?? 0, 'text-pink-600'],
            ['Chưa phân công', $statistics->unassigned_count ?? 0, 'text-purple-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format((int) $value) }}</strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Mã, tên, email, điện thoại, tiêu đề, mã đơn..."
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Loại liên hệ</label>
                <select name="type" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Ưu tiên</label>
                <select name="priority" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Phân công</label>
                <select name="assignment" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="assigned" @selected(request('assignment') === 'assigned')>Đã phân công</option>
                    <option value="unassigned" @selected(request('assignment') === 'unassigned')>Chưa phân công</option>
                    <option value="mine" @selected(request('assignment') === 'mine')>Của tôi</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Khách hàng</label>
                <select name="member_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="member" @selected(request('member_type') === 'member')>Thành viên</option>
                    <option value="guest" @selected(request('member_type') === 'guest')>Khách vãng lai</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="priority_desc" @selected(request('sort') === 'priority_desc')>Ưu tiên cao trước</option>
                    <option value="status" @selected(request('sort') === 'status')>Theo trạng thái</option>
                    <option value="updated_desc" @selected(request('sort') === 'updated_desc')>Mới cập nhật</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-7">
                <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.contact-messages.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Danh sách liên hệ</h3>
            <p class="mt-1 text-sm text-gray-500">Hiển thị {{ $messages->count() }} / {{ $messages->total() }} kết quả.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Khách hàng</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Liên hệ</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nội dung</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Ưu tiên</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Người xử lý</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($messages as $message)
                        @php
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

                            $displayName = $message->user_id
                                ? ($message->user_name ?: $message->name)
                                : $message->name;

                            $displayEmail = $message->user_id
                                ? ($message->user_email ?: $message->email)
                                : $message->email;
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[220px]">
                                    @if ($message->user_id)
                                        <a href="{{ route('admin.customers.show', $message->user_id) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $displayName }}</a>
                                    @else
                                        <strong class="text-gray-900">{{ $displayName }}</strong>
                                    @endif

                                    <p class="mt-1 text-xs text-gray-500">{{ $displayEmail ?: 'Không có email' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $message->phone ?: 'Không có điện thoại' }}</p>

                                    <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $message->user_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $message->user_id ? 'Thành viên' : 'Khách vãng lai' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[180px]">
                                    <a href="{{ route('admin.contact-messages.show', $message->id) }}" class="font-semibold text-blue-600 hover:underline">{{ $message->contact_code }}</a>
                                    <p class="mt-2 text-sm text-gray-700">{{ $types[$message->type] ?? $message->type }}</p>

                                    @if ($message->order_id && $message->order_code)
                                        <p class="mt-1 text-xs text-gray-500">
                                            Đơn:
                                            <a href="{{ route('admin.orders.show', $message->order_id) }}" class="font-semibold text-blue-600 hover:underline">{{ $message->order_code }}</a>
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[300px]">
                                    <p class="font-semibold text-gray-900">{{ $message->subject }}</p>
                                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-gray-600">{{ $message->message }}</p>
                                    <p class="mt-2 text-xs text-gray-400">{{ \Carbon\Carbon::parse($message->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">{{ $priorities[$message->priority] ?? $message->priority }}</span>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                @if ($message->assignee_name)
                                    <strong class="text-gray-900">{{ $message->assignee_name }}</strong>
                                    <p class="mt-1 text-xs text-gray-500">{{ $message->assignee_email }}</p>
                                @else
                                    <span class="text-orange-600">Chưa phân công</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statuses[$message->status] ?? $message->status }}</span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.contact-messages.show', $message->id) }}" class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">CHI TIẾT</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500">Không tìm thấy liên hệ.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $messages->links() }}</div>
        @endif
    </section>
</div>
@endsection
