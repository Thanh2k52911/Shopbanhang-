@extends('admin.layouts.master')

@section('title', 'Chat hỗ trợ khách hàng')

@section('content')
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-pink-600">
                    Chăm sóc khách hàng
                </p>

                <h1 class="mt-1 text-2xl font-bold text-gray-900">
                    Chat hỗ trợ
                </h1>
            </div>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-3">
            <input
                type="text"
                name="keyword"
                value="{{ $keyword }}"
                placeholder="Tên hoặc email khách hàng"
                class="rounded-lg border-gray-300"
            >

            <select
                name="status"
                class="rounded-lg border-gray-300"
            >
                <option value="">Tất cả trạng thái</option>
                <option value="open" @selected($status === 'open')>Mở</option>
                <option value="waiting_shop" @selected($status === 'waiting_shop')>Chờ shop</option>
                <option value="waiting_customer" @selected($status === 'waiting_customer')>Chờ khách</option>
                <option value="closed" @selected($status === 'closed')>Đã đóng</option>
            </select>

            <button class="rounded-lg bg-pink-600 px-4 py-2 font-semibold text-white">
                Lọc
            </button>
        </form>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Khách hàng</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Người xử lý</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tin chưa đọc</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($conversations as $conversation)
                            <tr>
                                <td class="px-5 py-4">
                                    <strong class="block text-gray-900">
                                        {{ $conversation->user?->name }}
                                    </strong>
                                    <span class="text-sm text-gray-500">
                                        {{ $conversation->user?->email }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $conversation->status }}
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $conversation->assignedAdmin?->name
                                        ?? 'Chưa nhận' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($conversation->unread_messages_count > 0)
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                            {{ $conversation->unread_messages_count }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">0</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a
                                        href="{{ route(
                                            'admin.support-chats.show',
                                            $conversation
                                        ) }}"
                                        class="font-semibold text-pink-600 hover:text-pink-700"
                                    >
                                        Mở chat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-gray-500">
                                    Chưa có cuộc trò chuyện nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t px-5 py-4">
                {{ $conversations->links() }}
            </div>
        </div>
    </div>
@endsection
