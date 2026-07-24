document.addEventListener('DOMContentLoaded', () => {
    const checkoutTotals = window.checkoutTotals;

    if (!checkoutTotals) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const couponInput = document.querySelector(
        '[data-checkout-coupon-input]'
    );

    const couponSaveButton = document.querySelector(
        '[data-checkout-coupon-save]'
    );

    const couponMessage = document.querySelector(
        '[data-checkout-coupon-message]'
    );

    const modal = document.querySelector(
        '[data-saved-coupon-modal]'
    );

    const modalList = document.querySelector(
        '[data-saved-coupon-list]'
    );

    const modalLoading = document.querySelector(
        '[data-saved-coupon-loading]'
    );

    const modalEmpty = document.querySelector(
        '[data-saved-coupon-empty]'
    );

    const modalMessage = document.querySelector(
        '[data-saved-coupon-modal-message]'
    );

    const openButtons = document.querySelectorAll(
    '[data-saved-coupon-open]'
);

    const formatMoney = (value) => {
        return new Intl.NumberFormat('vi-VN').format(
            Math.round(Number(value || 0))
        ) + 'đ';
    };

    const showCouponMessage = (
        message,
        type = 'success'
    ) => {
        if (!couponMessage) {
            return;
        }

        couponMessage.textContent = message;

        couponMessage.classList.toggle(
            'is-error',
            type === 'error'
        );

        couponMessage.classList.toggle(
            'is-success',
            type === 'success'
        );
    };

    const showModalMessage = (
        message,
        type = 'success'
    ) => {
        if (!modalMessage) {
            return;
        }

        modalMessage.textContent = message;

        modalMessage.classList.toggle(
            'is-error',
            type === 'error'
        );

        modalMessage.classList.toggle(
            'is-success',
            type === 'success'
        );
    };

    const getDiscountText = (coupon) => {
        if (
            coupon.discount_type
            === 'free_shipping'
        ) {
            return 'Miễn phí vận chuyển';
        }

        if (
            coupon.discount_type
            === 'percentage'
        ) {
            let text = `Giảm ${Number(
                coupon.discount_value
            )}%`;

            if (coupon.maximum_discount) {
                text += `, tối đa ${formatMoney(
                    coupon.maximum_discount
                )}`;
            }

            return text;
        }

        return `Giảm ${formatMoney(
            coupon.discount_value
        )}`;
    };

    const createCouponCard = (coupon) => {
        const card = document.createElement(
            'article'
        );

        card.className = 'saved-coupon-card';

if (coupon.usable) {
    card.classList.add('is-selectable');

    card.dataset.couponCode =
        coupon.code;

    card.setAttribute(
        'role',
        'button'
    );

    card.setAttribute(
        'tabindex',
        '0'
    );

    card.setAttribute(
        'aria-label',
        `Chọn voucher ${coupon.code}`
    );
}

        if (!coupon.usable) {
            card.classList.add(
                'is-disabled'
            );
        }

        if (coupon.is_best) {
            card.classList.add(
                'is-best'
            );
        }

        const remainingGlobal =
            coupon.remaining_global_uses === null
                ? 'Không giới hạn'
                : String(
                    coupon.remaining_global_uses
                );

        const savingAmount = Number(
            coupon.saving_amount || 0
        );

        const savingText =
            savingAmount > 0
                ? `Tiết kiệm ${formatMoney(
                    savingAmount
                )}`
                : '';

        card.innerHTML = `
            <div class="saved-coupon-card__badge">
                🎟
            </div>

            <div class="saved-coupon-card__body">
                <div class="saved-coupon-card__heading">
                    <div>
                        <div class="saved-coupon-card__code-row">
                            <strong>
                                ${coupon.code}
                            </strong>

                            ${
                                coupon.is_best
                                    ? `
                                        <span class="saved-coupon-card__best">
                                            Đề xuất tốt nhất
                                        </span>
                                    `
                                    : ''
                            }
                        </div>

                        <h3>
                            ${coupon.name ?? ''}
                        </h3>
                    </div>

                    <span>
                        ${getDiscountText(coupon)}
                    </span>
                </div>

                ${
                    savingText
                        ? `
                            <p class="saved-coupon-card__saving">
                                ${savingText}
                            </p>
                        `
                        : ''
                }

                ${
                    coupon.description
                        ? `
                            <p class="saved-coupon-card__description">
                                ${coupon.description}
                            </p>
                        `
                        : ''
                }

                <div class="saved-coupon-card__conditions">
                    <span>
                        Đơn tối thiểu:

                        <strong>
                            ${formatMoney(
                                coupon.minimum_order_amount
                            )}
                        </strong>
                    </span>

                    <span>
                        Hạn dùng:

                        <strong>
                            ${
                                coupon.end_at_display
                                ?? 'Không giới hạn'
                            }
                        </strong>
                    </span>

                    <span>
                        Lượt cá nhân còn:

                        <strong>
                            ${coupon.remaining_user_uses}
                        </strong>
                    </span>

                    <span>
                        Lượt toàn hệ thống:

                        <strong>
                            ${remainingGlobal}
                        </strong>
                    </span>
                </div>

                ${
                    !coupon.usable
                        ? `
                            <p class="saved-coupon-card__unavailable">
                                ${
                                    coupon.unavailable_reason
                                    ?? 'Voucher không khả dụng.'
                                }
                            </p>
                        `
                        : ''
                }

                <button
                    type="button"
                    class="saved-coupon-card__apply"
                    data-apply-saved-coupon
                    data-coupon-code="${coupon.code}"
                    ${
                        coupon.usable
                            ? ''
                            : 'disabled'
                    }
                >
                    ${
                        coupon.usable
                            ? (
                                coupon.is_best
                                    ? 'Dùng mã tốt nhất'
                                    : 'Chọn mã này'
                            )
                            : 'Không khả dụng'
                    }
                </button>
            </div>
        `;

        return card;
    };

    const loadSavedCoupons = async () => {
        if (
            !checkoutTotals.savedCouponsUrl
            || !modalList
        ) {
            return;
        }

        modalList.innerHTML = '';

        if (modalEmpty) {
            modalEmpty.hidden = true;
        }

        if (modalLoading) {
            modalLoading.hidden = false;
        }

        showModalMessage('');

        try {
            const response = await fetch(
                checkoutTotals.savedCouponsUrl,
                {
                    method: 'GET',

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    credentials:
                        'same-origin',
                }
            );

            const data = await response.json();

            if (
                !response.ok
                || !data.success
            ) {
                throw new Error(
                    data.message
                    ?? 'Không thể tải danh sách ưu đãi.'
                );
            }

            if (
                !Array.isArray(data.coupons)
                || data.coupons.length === 0
            ) {
                if (modalEmpty) {
                    modalEmpty.hidden = false;
                }

                return;
            }

            data.coupons.forEach(
                (coupon) => {
                    modalList.appendChild(
                        createCouponCard(
                            coupon
                        )
                    );
                }
            );
        } catch (error) {
            showModalMessage(
                error.message
                ?? 'Có lỗi xảy ra.',
                'error'
            );
        } finally {
            if (modalLoading) {
                modalLoading.hidden = true;
            }
        }
    };

    const openModal = async () => {
        if (!modal) {
            return;
        }

        modal.hidden = false;

        document.body.classList.add(
            'has-saved-coupon-modal'
        );

        await loadSavedCoupons();
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.hidden = true;

        document.body.classList.remove(
            'has-saved-coupon-modal'
        );
    };

    const applyCouponFromModal = async (
        button,
        code
    ) => {
        if (
            !code
            || !csrfToken
            || !checkoutTotals.applyCouponUrl
        ) {
            return;
        }

        const originalText =
            button.textContent;

        button.disabled = true;
        button.textContent =
            'Đang áp dụng...';

        showModalMessage('');

        try {
            const response = await fetch(
                checkoutTotals.applyCouponUrl,
                {
                    method: 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken,

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    credentials:
                        'same-origin',

                    body: JSON.stringify({
                        code,
                    }),
                }
            );

            const data =
                await response.json();

            if (
                !response.ok
                || !data.success
            ) {
                throw new Error(
                    data.message
                    ?? 'Không thể áp dụng voucher.'
                );
            }

            closeModal();

            /*
             * Reload để đồng bộ chính xác:
             * - mã đang áp dụng;
             * - tiền giảm;
             * - phí vận chuyển;
             * - tổng thanh toán.
             */
            window.location.reload();
        } catch (error) {
            button.disabled = false;
            button.textContent =
                originalText;

            showModalMessage(
                error.message
                ?? 'Có lỗi xảy ra.',
                'error'
            );
        }
    };

    openButtons.forEach((button) => {
    button.addEventListener(
        'click',
        openModal
    );
});

    document
        .querySelectorAll(
            '[data-saved-coupon-close]'
        )
        .forEach((button) => {
            button.addEventListener(
                'click',
                closeModal
            );
        });

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && modal
                && !modal.hidden
            ) {
                closeModal();
            }
        }
    );

    couponSaveButton?.addEventListener(
        'click',
        async () => {
            const code =
                couponInput?.value.trim()
                || '';

            if (!code) {
                showCouponMessage(
                    'Vui lòng nhập mã cần lưu.',
                    'error'
                );

                couponInput?.focus();

                return;
            }

            if (
                !csrfToken
                || !checkoutTotals
                    .saveCouponByCodeUrl
            ) {
                return;
            }

            couponSaveButton.disabled = true;
            couponSaveButton.textContent =
                'Đang lưu...';

            try {
                const response = await fetch(
                    checkoutTotals
                        .saveCouponByCodeUrl,
                    {
                        method: 'POST',

                        headers: {
                            Accept:
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials:
                            'same-origin',

                        body: JSON.stringify({
                            code,
                        }),
                    }
                );

                const data =
                    await response.json();

                if (
                    !response.ok
                    || !data.success
                ) {
                    throw new Error(
                        data.message
                        ?? 'Không thể lưu mã.'
                    );
                }

                showCouponMessage(
                    data.message,
                    'success'
                );
            } catch (error) {
                showCouponMessage(
                    error.message
                    ?? 'Có lỗi xảy ra.',
                    'error'
                );
            } finally {
                couponSaveButton.disabled =
                    false;

                couponSaveButton.textContent =
                    'Lưu mã';
            }
        }
    );

    modalList?.addEventListener(
    'click',
    async (event) => {
        const button = event.target.closest(
            '[data-apply-saved-coupon]'
        );

        if (
            button
            && !button.disabled
        ) {
            await applyCouponFromModal(
                button,
                button.dataset.couponCode
            );

            return;
        }

        const card = event.target.closest(
            '.saved-coupon-card.is-selectable'
        );

        if (!card) {
            return;
        }

        const code =
            card.dataset.couponCode;

        if (!code) {
            return;
        }

        const cardButton = card.querySelector(
            '[data-apply-saved-coupon]'
        );

        if (
            !cardButton
            || cardButton.disabled
        ) {
            return;
        }

        await applyCouponFromModal(
            cardButton,
            code
        );
    }
);
modalList?.addEventListener(
    'keydown',
    async (event) => {
        if (
            event.key !== 'Enter'
            && event.key !== ' '
        ) {
            return;
        }

        const card = event.target.closest(
            '.saved-coupon-card.is-selectable'
        );

        if (!card) {
            return;
        }

        event.preventDefault();

        const code =
            card.dataset.couponCode;

        const cardButton = card.querySelector(
            '[data-apply-saved-coupon]'
        );

        if (
            !code
            || !cardButton
            || cardButton.disabled
        ) {
            return;
        }

        await applyCouponFromModal(
            cardButton,
            code
        );
    }
);
});
