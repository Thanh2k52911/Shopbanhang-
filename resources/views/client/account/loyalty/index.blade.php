@extends('client.layouts.master')

@section('title', 'Điểm tích lũy - Cosmetic Shop')

@section(
    'meta_description',
    'Theo dõi điểm tích lũy, hạng thành viên và lịch sử giao dịch điểm tại Cosmetic Shop.'
)

@section('content')
    @php
        $currentTier = $account->tier;
        $highestTier = $account->highestTier;
        $isInactiveDowngraded = $account->inactive_downgraded_at !== null;

        $tierName = $currentTier?->name
            ?? 'Thành viên';

        $tierColor = $currentTier?->color
            ?? '#9ca3af';

        $availablePoints = (int)
            $account->available_points;

        $pendingPoints = (int)
            $account->pending_points;

        $lifetimeEarnedPoints = (int)
            $account->lifetime_earned_points;

        $lifetimeRedeemedPoints = (int)
            $account->lifetime_redeemed_points;

        $lifetimeSpending = (float)
            $account->lifetime_spending;

        $overallPercentage = (float) (
            $tierProgress['overall_percentage']
            ?? 0
        );
    @endphp

    <section class="account-loyalty-page">
        <div class="client-container">
            <nav
                class="account-loyalty-page__breadcrumb"
                aria-label="Breadcrumb"
            >
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span aria-hidden="true">/</span>

                <a href="{{ route('account.index') }}">
                    Tài khoản
                </a>

                <span aria-hidden="true">/</span>

                <span>Điểm tích lũy</span>
            </nav>

            <header class="account-loyalty-page__header">
                <div>
                    <p class="home-section__eyebrow">
                        Thành viên Cosmetic Shop
                    </p>

                    <h1>Điểm tích lũy của tôi</h1>

                    <p>
                        Theo dõi điểm thưởng, hạng thành viên
                        và các giao dịch điểm của bạn.
                    </p>
                </div>

                <a
                    href="{{ route('products.index') }}"
                    class="account-loyalty-page__shop-link"
                >
                    Tiếp tục mua sắm
                </a>
            </header>

            @if ($isInactiveDowngraded)
                <section class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                    <h2 class="text-lg font-bold">
                        Hạng hiện tại đang bị giảm tạm thời
                    </h2>

                    <p class="mt-2 leading-7">
                        Do lâu không có đơn hoàn thành, tài khoản đang ở hạng
                        <strong>{{ $currentTier?->name ?? 'Thành viên' }}</strong>.
                        Chỉ cần hoàn thành một đơn hàng bất kỳ, hệ thống sẽ tự
                        khôi phục hạng cao nhất bạn từng đạt là
                        <strong>{{ $highestTier?->name ?? $currentTier?->name ?? 'Thành viên' }}</strong>.
                    </p>
                </section>
            @endif

            <section
                class="loyalty-overview"
                style="--loyalty-tier-color: {{ $tierColor }}"
            >
                <div class="loyalty-member-card">
                    <div class="loyalty-member-card__top">
                        <div>
                            <span class="loyalty-member-card__label">
                                Hạng hiện tại
                            </span>

                            <h2>
                                {{ $tierName }}
                            </h2>
                        </div>

                        <span
                            class="loyalty-member-card__icon"
                            aria-hidden="true"
                        >
                            ★
                        </span>
                    </div>

                    @if ($currentTier?->description)
                        <p class="loyalty-member-card__description">
                            {{ $currentTier->description }}
                        </p>
                    @endif

                    <div class="loyalty-member-card__points">
                        <span>Điểm khả dụng</span>

                        <strong>
                            {{ number_format(
                                $availablePoints,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>

                        <small>điểm</small>
                    </div>

                    <div class="loyalty-member-card__meta">
                        <span>
                            Hệ số tích điểm:

                            <strong>
                                x{{ number_format(
                                    (float) (
                                        $currentTier
                                            ?->point_multiplier
                                        ?? 1
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </span>

                        <span>
                            Ưu đãi theo hạng:

                            <strong>
                                {{ number_format(
                                    (float) (
                                        $currentTier
                                            ?->discount_percent
                                        ?? 0
                                    ),
                                    0,
                                    ',',
                                    '.'
                                ) }}%
                            </strong>
                        </span>
                    </div>
                </div>

                <div class="loyalty-stat-grid">
                    <article class="loyalty-stat-card">
                        <span aria-hidden="true">⏳</span>

                        <div>
                            <p>Điểm đang chờ</p>

                            <strong>
                                {{ number_format(
                                    $pendingPoints,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </div>
                    </article>

                    <article class="loyalty-stat-card">
                        <span aria-hidden="true">➕</span>

                        <div>
                            <p>Tổng điểm đã nhận</p>

                            <strong>
                                {{ number_format(
                                    $lifetimeEarnedPoints,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </div>
                    </article>

                    <article class="loyalty-stat-card">
                        <span aria-hidden="true">➖</span>

                        <div>
                            <p>Tổng điểm đã dùng</p>

                            <strong>
                                {{ number_format(
                                    $lifetimeRedeemedPoints,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </div>
                    </article>

                    <article class="loyalty-stat-card">
                        <span aria-hidden="true">💳</span>

                        <div>
                            <p>Tổng chi tiêu</p>

                            <strong>
                                {{ number_format(
                                    $lifetimeSpending,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </strong>
                        </div>
                    </article>
                </div>
            </section>

            <section class="mb-8 grid gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
                    <span class="text-sm font-semibold uppercase tracking-wide text-pink-600">
                        Tích điểm
                    </span>
                    <h2 class="mt-2 text-xl font-bold text-gray-900">
                        x{{ number_format((float) ($currentTier?->point_multiplier ?? 1), 2, ',', '.') }} điểm
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Hệ số được áp dụng khi đơn hàng chuyển sang hoàn thành.
                    </p>
                </article>

                <article class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
                    <span class="text-sm font-semibold uppercase tracking-wide text-pink-600">
                        Ưu đãi theo hạng
                    </span>
                    <h2 class="mt-2 text-xl font-bold text-gray-900">
                        {{ number_format((float) ($currentTier?->discount_percent ?? 0), 0, ',', '.') }}%
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Mức quyền lợi tham chiếu của hạng hiện tại.
                    </p>
                </article>

                <article class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
                    <span class="text-sm font-semibold uppercase tracking-wide text-pink-600">
                        Quà thăng hạng
                    </span>
                    <h2 class="mt-2 text-xl font-bold text-gray-900">
                        {{ $account->tierRewards->count() }} voucher
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Voucher được tự động thêm vào Ví voucher khi lần đầu đạt hạng mới.
                    </p>
                </article>
            </section>

            <section class="loyalty-tier-progress">
                <div class="loyalty-tier-progress__heading">
                    <div>
                        <p class="home-section__eyebrow">
                            Tiến trình thành viên
                        </p>

                        @if ($nextTier)
                            <h2>
                                Tiến tới hạng
                                {{ $nextTier->name }}
                            </h2>

                            <p>
                                Hoàn thành đồng thời điều kiện
                                chi tiêu và điểm tích lũy.
                            </p>
                        @else
                            <h2>
                                Bạn đang ở hạng cao nhất
                            </h2>

                            <p>
                                Tiếp tục mua sắm để nhận thêm
                                nhiều điểm thưởng.
                            </p>
                        @endif
                    </div>

                    <strong>
                        {{ number_format(
                            $overallPercentage,
                            1,
                            ',',
                            '.'
                        ) }}%
                    </strong>
                </div>

                <div class="loyalty-tier-progress__bar">
                    <span
                        style="width: {{ min(
                            100,
                            max(0, $overallPercentage)
                        ) }}%"
                    ></span>
                </div>

                @if ($nextTier)
                    <div class="loyalty-tier-progress__conditions">
                        <article>
                            <div>
                                <span>Điều kiện chi tiêu</span>

                                <strong>
                                    {{ number_format(
                                        $lifetimeSpending,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                    /
                                    {{ number_format(
                                        (float) $tierProgress[
                                            'spending_required'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </strong>
                            </div>

                            <div class="loyalty-condition-bar">
                                <span
                                    style="width: {{ min(
                                        100,
                                        max(
                                            0,
                                            (float) $tierProgress[
                                                'spending_percentage'
                                            ]
                                        )
                                    ) }}%"
                                ></span>
                            </div>

                            <small>
                                Còn thiếu
                                {{ number_format(
                                    (float) $tierProgress[
                                        'spending_remaining'
                                    ],
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </small>
                        </article>

                        <article>
                            <div>
                                <span>Điều kiện điểm</span>

                                <strong>
                                    {{ number_format(
                                        $lifetimeEarnedPoints,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                    /
                                    {{ number_format(
                                        (int) $tierProgress[
                                            'points_required'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                    điểm
                                </strong>
                            </div>

                            <div class="loyalty-condition-bar">
                                <span
                                    style="width: {{ min(
                                        100,
                                        max(
                                            0,
                                            (float) $tierProgress[
                                                'points_percentage'
                                            ]
                                        )
                                    ) }}%"
                                ></span>
                            </div>

                            <small>
                                Còn thiếu
                                {{ number_format(
                                    (int) $tierProgress[
                                        'points_remaining'
                                    ],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                                điểm
                            </small>
                        </article>
                    </div>
                @endif
            </section>

            <section class="loyalty-history">
                <div class="loyalty-history__heading">
                    <div>
                        <p class="home-section__eyebrow">
                            Hoạt động điểm
                        </p>

                        <h2>Lịch sử giao dịch</h2>
                    </div>
                </div>

                @if ($transactions->isNotEmpty())
                    <div class="loyalty-transaction-list">
                        @foreach ($transactions as $transaction)
                            @php
                                $points = (int)
                                    $transaction->points;

                                $isPositive = $points > 0;

                                $transactionLabel = match (
                                    $transaction->type
                                ) {
                                    'earn' =>
                                        'Cộng điểm mua hàng',

                                    'redeem' =>
                                        'Dùng điểm thanh toán',

                                    'refund' =>
                                        'Hoàn điểm',

                                    'cancel' =>
                                        'Hủy giao dịch điểm',

                                    'adjustment' =>
                                        'Điều chỉnh điểm',

                                    'expire' =>
                                        'Điểm hết hạn',

                                    default =>
                                        'Giao dịch điểm',
                                };

                                $statusLabel = match (
                                    $transaction->status
                                ) {
                                    'pending' =>
                                        'Đang chờ',

                                    'completed' =>
                                        'Hoàn thành',

                                    'cancelled' =>
                                        'Đã hủy',

                                    'expired' =>
                                        'Đã hết hạn',

                                    default =>
                                        $transaction->status,
                                };
                            @endphp

                            <article class="loyalty-transaction">
                                <div
                                    class="loyalty-transaction__icon {{ $isPositive
                                        ? 'is-positive'
                                        : 'is-negative' }}"
                                >
                                    {{ $isPositive ? '+' : '−' }}
                                </div>

                                <div class="loyalty-transaction__content">
                                    <div class="loyalty-transaction__heading">
                                        <div>
                                            <h3>
                                                {{ $transactionLabel }}
                                            </h3>

                                            @if ($transaction->description)
                                                <p>
                                                    {{ $transaction
                                                        ->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <strong
                                            class="{{ $isPositive
                                                ? 'is-positive'
                                                : 'is-negative' }}"
                                        >
                                            {{ $isPositive ? '+' : '' }}
                                            {{ number_format(
                                                $points,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                            điểm
                                        </strong>
                                    </div>

                                    <div class="loyalty-transaction__meta">
                                        <span>
                                            Trạng thái:
                                            <strong>
                                                {{ $statusLabel }}
                                            </strong>
                                        </span>

                                        @if ($transaction->balance_after !== null)
                                            <span>
                                                Số dư sau giao dịch:
                                                <strong>
                                                    {{ number_format(
                                                        (int) $transaction
                                                            ->balance_after,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </strong>
                                            </span>
                                        @endif

                                        <time
                                            datetime="{{ $transaction
                                                ->created_at
                                                ?->toIso8601String() }}"
                                        >
                                            {{ $transaction
                                                ->created_at
                                                ?->format('d/m/Y H:i') }}
                                        </time>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($transactions->hasPages())
                        <div class="loyalty-pagination">
                            {{ $transactions
                                ->withQueryString()
                                ->links() }}
                        </div>
                    @endif
                @else
                    <div class="loyalty-history-empty">
                        <span aria-hidden="true">⭐</span>

                        <h3>Chưa có giao dịch điểm</h3>

                        <p>
                            Điểm thưởng sẽ được hiển thị tại đây
                            sau khi bạn hoàn thành đơn hàng.
                        </p>

                        <a href="{{ route('products.index') }}">
                            Bắt đầu mua sắm
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </section>
@endsection
