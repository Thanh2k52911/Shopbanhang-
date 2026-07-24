@extends('admin.layouts.master')

@section('title', 'Chat với ' . ($supportConversation->user?->name ?? 'khách hàng'))

@section('content')
    <div class="mx-auto max-w-5xl space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <a
                    href="{{ route('admin.support-chats.index') }}"
                    class="text-sm font-semibold text-pink-600"
                >
                    ← Danh sách chat
                </a>

                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $supportConversation->user?->name }}
                </h1>

                <p class="text-sm text-gray-500">
                    {{ $supportConversation->user?->email }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if (
                    ! $supportConversation->assigned_admin_id
                    || (int) $supportConversation->assigned_admin_id
                        !== (int) auth()->id()
                )
                    <form
                        method="POST"
                        action="{{ route(
                            'admin.support-chats.assign',
                            $supportConversation
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="rounded-lg border border-pink-300 px-4 py-2 font-semibold text-pink-700">
                            Nhận xử lý
                        </button>
                    </form>
                @endif

                @if ($supportConversation->status === 'closed')
                    <form
                        method="POST"
                        action="{{ route(
                            'admin.support-chats.reopen',
                            $supportConversation
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white">
                            Mở lại
                        </button>
                    </form>
                @else
                    <form
                        method="POST"
                        action="{{ route(
                            'admin.support-chats.close',
                            $supportConversation
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="rounded-lg bg-gray-800 px-4 py-2 font-semibold text-white">
                            Đóng chat
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div
                class="grid max-h-[620px] min-h-[430px] gap-4 overflow-y-auto bg-gradient-to-b from-white to-pink-50 p-5"
                data-admin-support-messages
                data-messages-url="{{ route(
                    'admin.support-chats.messages',
                    $supportConversation
                ) }}"
            >
                @foreach ($supportConversation->messages as $message)
                    @php
                        $isShop = $message->is_shop_message;
                    @endphp

                    <article
                        class="max-w-[78%] {{ $isShop
                            ? 'justify-self-end'
                            : 'justify-self-start' }}"
                    >
                        <div class="mb-1 flex items-center gap-2 text-xs text-gray-500">
                            @if ($isShop)
                                <span class="rounded-full bg-gradient-to-r from-pink-700 to-pink-500 px-2 py-1 font-bold text-white">
                                    Chủ shop
                                </span>
                            @endif

                            <strong>
                                {{ $message->sender?->name }}
                            </strong>

                            <time>
                                {{ $message->created_at
                                    ->format('d/m/Y H:i') }}
                            </time>
                        </div>

                        <div
                            class="rounded-2xl px-4 py-3 leading-7
                                {{ $isShop
                                    ? 'rounded-br-sm bg-pink-600 text-white'
                                    : 'rounded-bl-sm border bg-white text-gray-900' }}"
                        >
                            {!! nl2br(e($message->message)) !!}
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($supportConversation->status !== 'closed')
                <form
                    method="POST"
                    action="{{ route(
                        'admin.support-chats.reply',
                        $supportConversation
                    ) }}"
                    class="grid gap-3 border-t p-5"
                >
                    @csrf

                    <div>
                        <span class="inline-flex rounded-full bg-gradient-to-r from-pink-700 to-pink-500 px-2.5 py-1 text-xs font-bold text-white">
                            Chủ shop
                        </span>

                        <strong class="ml-2 text-gray-900">
                            {{ auth()->user()->name }}
                        </strong>
                    </div>

                    <textarea
                        name="message"
                        rows="4"
                        maxlength="3000"
                        required
                        placeholder="Nhập nội dung trả lời khách hàng..."
                        class="w-full rounded-xl border-gray-300"
                    >{{ old('message') }}</textarea>

                    @error('message')
                        <p class="text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <button class="justify-self-end rounded-lg bg-pink-600 px-5 py-2.5 font-bold text-white">
                        Gửi phản hồi
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const container = document.querySelector(
                    '[data-admin-support-messages]'
                );

                if (! container) {
                    return;
                }

                const url = container.dataset.messagesUrl;
                let lastPayload = '';

                const renderMessages = function (messages) {
                    const payload = JSON.stringify(messages);

                    if (payload === lastPayload) {
                        return;
                    }

                    lastPayload = payload;
                    container.innerHTML = '';

                    messages.forEach(function (message) {
                        const article =
                            document.createElement('article');

                        article.className =
                            'max-w-[78%] '
                            + (
                                message.is_shop
                                    ? 'justify-self-end'
                                    : 'justify-self-start'
                            );

                        const meta =
                            document.createElement('div');

                        meta.className =
                            'mb-1 flex items-center gap-2 text-xs text-gray-500';

                        if (message.is_shop) {
                            const badge =
                                document.createElement('span');

                            badge.className =
                                'rounded-full bg-gradient-to-r from-pink-700 to-pink-500 px-2 py-1 font-bold text-white';

                            badge.textContent = 'Chủ shop';
                            meta.appendChild(badge);
                        }

                        const name =
                            document.createElement('strong');

                        name.textContent =
                            message.sender_name;

                        const time =
                            document.createElement('time');

                        time.textContent =
                            message.created_at;

                        meta.append(name, time);

                        const bubble =
                            document.createElement('div');

                        bubble.className =
                            'rounded-2xl px-4 py-3 leading-7 '
                            + (
                                message.is_shop
                                    ? 'rounded-br-sm bg-pink-600 text-white'
                                    : 'rounded-bl-sm border bg-white text-gray-900'
                            );

                        bubble.textContent =
                            message.message;

                        article.append(meta, bubble);
                        container.appendChild(article);
                    });

                    container.scrollTop =
                        container.scrollHeight;
                };

                const loadMessages = async function () {
                    try {
                        const response = await fetch(url, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (! response.ok) {
                            return;
                        }

                        const data = await response.json();

                        if (data.success) {
                            renderMessages(data.messages);
                        }
                    } catch (error) {
                        // Giữ giao diện hiện tại nếu mạng tạm lỗi.
                    }
                };

                container.scrollTop =
                    container.scrollHeight;

                window.setInterval(
                    loadMessages,
                    5000
                );
            }
        );
    </script>
@endpush
