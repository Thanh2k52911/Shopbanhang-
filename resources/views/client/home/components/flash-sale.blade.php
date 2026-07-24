@if ($flashCampaign && $flashProducts->isNotEmpty())
    <section class="home-section home-flash">
        <div class="client-container">

            <div class="home-flash__header">
                <div>
                    <p class="home-flash__eyebrow">
                        Ưu đãi giới hạn
                    </p>

                    <h2 class="home-flash__title">
                        ⚡ {{ $flashCampaign->name }}
                    </h2>

                    @if ($flashCampaign->description)
                        <p class="home-flash__description">
                            {{ $flashCampaign->description }}
                        </p>
                    @endif
                </div>

                <div
                    class="home-flash__countdown"
                    data-countdown
                    data-end-time="{{ \Carbon\Carbon::parse(
                        $flashCampaign->end_date
                    )->toIso8601String() }}"
                >
                    <span>
                        <strong data-days>00</strong>
                        <small>Ngày</small>
                    </span>

                    <b>:</b>

                    <span>
                        <strong data-hours>00</strong>
                        <small>Giờ</small>
                    </span>

                    <b>:</b>

                    <span>
                        <strong data-minutes>00</strong>
                        <small>Phút</small>
                    </span>

                    <b>:</b>

                    <span>
                        <strong data-seconds>00</strong>
                        <small>Giây</small>
                    </span>
                </div>
            </div>

            <div class="product-grid">
                @foreach ($flashProducts as $product)
                    @include(
                        'client.components.product-card',
                        [
                            'product' => $product,
                            'badge' => $product->discount_label,
                        ]
                    )
                @endforeach
            </div>

        </div>
    </section>
@endif
