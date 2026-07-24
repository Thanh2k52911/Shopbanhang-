@extends('client.layouts.master')

@section('title', 'Trò chuyện với cửa hàng - Cosmetic Shop')

@section('content')
    <section class="support-chat-page">
        <div class="client-container">
            <nav class="support-chat-page__breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('account.index') }}">Tài khoản</a>
                <span>/</span>
                <span>Trò chuyện với cửa hàng</span>
            </nav>

            <header class="support-chat-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Hỗ trợ trực tiếp
                    </p>

                    <h1>Trò chuyện với cửa hàng</h1>

                    <p>
                        Admin hoặc chủ shop sẽ phản hồi trực tiếp
                        trong cuộc trò chuyện này.
                    </p>
                </div>

                @if ($conversation)
                    <span class="support-chat-status">
                        {{ $conversation->status === 'closed'
                            ? 'Đã đóng'
                            : 'Đang hỗ trợ' }}
                    </span>
                @endif
            </header>

            @if (session('success'))
                <div class="order-message order-message--success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="support-chat-card">
                <div
                    class="support-chat-messages"
                    data-support-chat-messages
                    data-messages-url="{{ route(
                        'account.support-chat.messages'
                    ) }}"
                >
                    @forelse ($conversation?->messages ?? [] as $message)
                        @php
                            $isMine = (int) $message->sender_id
                                === (int) auth()->id();
                        @endphp

                        <article
                            class="support-message
                                {{ $isMine
                                    ? 'support-message--customer'
                                    : 'support-message--shop' }}"
                        >
                            <div class="support-message__meta">
                                @if ($message->is_shop_message)
                                    <span class="support-shop-badge">
                                        Chủ shop
                                    </span>
                                @endif

                                <strong>
                                    {{ $message->sender?->name
                                        ?? 'Người dùng' }}
                                </strong>

                                <time>
                                    {{ $message->created_at
                                        ->format('d/m/Y H:i') }}
                                </time>
                            </div>

                            <div class="support-message__bubble">
                                {!! nl2br(e($message->message)) !!}
                            </div>
                        </article>
                    @empty
                        <div class="support-chat-empty">
                            <span>💬</span>
                            <h2>Bắt đầu cuộc trò chuyện</h2>
                            <p>
                                Hãy gửi câu hỏi hoặc vấn đề cần hỗ trợ.
                            </p>
                        </div>
                    @endforelse
                </div>

                @if (! $conversation || $conversation->status !== 'closed')
                    <form
                        action="{{ route(
                            'account.support-chat.store'
                        ) }}"
                        method="POST"
                        class="support-chat-form"
                    >
                        @csrf

                        <label for="support-message">
                            Nội dung tin nhắn
                        </label>

                        <textarea
                            id="support-message"
                            name="message"
                            rows="4"
                            maxlength="3000"
                            placeholder="Nhập nội dung cần cửa hàng hỗ trợ..."
                            required
                        >{{ old('message') }}</textarea>

                        @error('message')
                            <small class="order-review-form__error">
                                {{ $message }}
                            </small>
                        @enderror

                        <button type="submit">
                            Gửi tin nhắn
                        </button>
                    </form>
                @else
                    <div class="support-chat-closed">
                        Cuộc trò chuyện đã được cửa hàng đóng.
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .support-chat-page {
            padding-block: 2rem 5rem;
            background: #f9fafb;
        }

        .support-chat-page__breadcrumb {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-bottom: 1.5rem;
            color: #6b7280;
        }

        .support-chat-page__breadcrumb a {
            color: #db2777;
            text-decoration: none;
        }

        .support-chat-page__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .support-chat-page__header h1 {
            margin: 0;
        }

        .support-chat-page__header p:last-child {
            color: #6b7280;
        }

        .support-chat-status,
        .support-shop-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-weight: 800;
        }

        .support-chat-status {
            padding: .55rem .8rem;
            background: #ecfdf5;
            color: #047857;
        }

        .support-shop-badge {
            padding: .2rem .5rem;
            background: linear-gradient(135deg, #be185d, #ec4899);
            color: #fff;
            font-size: .7rem;
        }

        .support-chat-card {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 1.25rem;
            background: #fff;
            box-shadow: 0 18px 42px rgb(17 24 39 / 7%);
        }

        .support-chat-messages {
            display: grid;
            gap: 1rem;
            min-height: 420px;
            max-height: 620px;
            overflow-y: auto;
            padding: 1.5rem;
            background: linear-gradient(180deg, #fff, #fff7fb);
        }

        .support-message {
            display: grid;
            max-width: min(78%, 760px);
        }

        .support-message--customer {
            justify-self: end;
        }

        .support-message--shop {
            justify-self: start;
        }

        .support-message__meta {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .4rem;
            color: #6b7280;
            font-size: .78rem;
        }

        .support-message__meta time {
            color: #9ca3af;
        }

        .support-message__bubble {
            padding: .9rem 1rem;
            border-radius: 1rem;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        .support-message--customer .support-message__bubble {
            border-bottom-right-radius: .25rem;
            background: #ec4899;
            color: #fff;
        }

        .support-message--shop .support-message__bubble {
            border: 1px solid #f3e8ee;
            border-bottom-left-radius: .25rem;
            background: #fff;
            color: #111827;
        }

        .support-chat-empty {
            align-self: center;
            justify-self: center;
            text-align: center;
            color: #6b7280;
        }

        .support-chat-empty span {
            font-size: 3rem;
        }

        .support-chat-form {
            display: grid;
            gap: .75rem;
            padding: 1.25rem;
            border-top: 1px solid #f3f4f6;
        }

        .support-chat-form label {
            font-weight: 800;
        }

        .support-chat-form textarea {
            width: 100%;
            box-sizing: border-box;
            padding: .9rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: .8rem;
            resize: vertical;
        }

        .support-chat-form textarea:focus {
            outline: 0;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgb(236 72 153 / 12%);
        }

        .support-chat-form button {
            justify-self: end;
            min-height: 46px;
            padding: .75rem 1.25rem;
            border: 0;
            border-radius: .75rem;
            background: #ec4899;
            color: #fff;
            font-weight: 800;
            cursor: pointer;
        }

        .support-chat-closed {
            padding: 1rem;
            background: #f3f4f6;
            color: #6b7280;
            text-align: center;
        }

        @media (max-width: 640px) {
            .support-chat-page__header {
                align-items: flex-start;
                flex-direction: column;
            }

            .support-message {
                max-width: 92%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const container = document.querySelector(
                    '[data-support-chat-messages]'
                );

                if (! container) {
                    return;
                }

                const url = container.dataset.messagesUrl;
                let lastPayload = '';

                const renderMessages = function (messages) {
                    if (! Array.isArray(messages)) {
                        return;
                    }

                    const payload = JSON.stringify(messages);

                    if (payload === lastPayload) {
                        return;
                    }

                    lastPayload = payload;
                    container.innerHTML = '';

                    if (messages.length === 0) {
                        container.innerHTML = `
                            <div class="support-chat-empty">
                                <span>💬</span>
                                <h2>Bắt đầu cuộc trò chuyện</h2>
                                <p>Hãy gửi câu hỏi hoặc vấn đề cần hỗ trợ.</p>
                            </div>
                        `;
                        return;
                    }

                    messages.forEach(function (message) {
                        const article =
                            document.createElement('article');

                        article.className =
                            'support-message '
                            + (
                                message.is_shop
                                    ? 'support-message--shop'
                                    : 'support-message--customer'
                            );

                        const meta =
                            document.createElement('div');

                        meta.className =
                            'support-message__meta';

                        if (message.is_shop) {
                            const badge =
                                document.createElement('span');

                            badge.className =
                                'support-shop-badge';

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
                            'support-message__bubble';

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
                        // Giữ giao diện hiện tại khi mạng tạm lỗi.
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
