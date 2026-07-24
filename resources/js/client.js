import './modules/vietnam-address-selector';

import './bootstrap';

import './modules/checkout-voucher';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector(
        '[data-mobile-menu-button]'
    );

    const menu = document.querySelector(
        '[data-mobile-menu]'
    );

    if (!button || !menu) {
        return;
    }

    button.addEventListener('click', () => {
        menu.hidden = !menu.hidden;
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const sliders = document.querySelectorAll(
        '[data-banner-slider]'
    );

    sliders.forEach((slider) => {
        const slides = Array.from(
            slider.querySelectorAll('[data-banner-slide]')
        );

        const previousButton = slider.querySelector(
            '[data-banner-previous]'
        );

        const nextButton = slider.querySelector(
            '[data-banner-next]'
        );

        const dots = Array.from(
            slider.querySelectorAll('[data-banner-dot]')
        );

        if (slides.length <= 1) {
            return;
        }

        let currentIndex = 0;
        let intervalId = null;

        let isAnimating = false;

const showSlide = (index, direction = 1) => {
    if (isAnimating) {
        return;
    }

    const nextIndex = (
        index + slides.length
    ) % slides.length;

    if (nextIndex === currentIndex) {
        return;
    }

    const currentSlide = slides[currentIndex];
    const nextSlide = slides[nextIndex];

    isAnimating = true;

    nextSlide.hidden = false;

    nextSlide.classList.remove(
        'is-active',
        'is-leaving-left',
        'is-leaving-right',
        'is-entering-left',
        'is-entering-right'
    );

    currentSlide.classList.remove(
        'is-entering-left',
        'is-entering-right'
    );

    if (direction > 0) {
        nextSlide.classList.add('is-entering-right');
        currentSlide.classList.add('is-leaving-left');
    } else {
        nextSlide.classList.add('is-entering-left');
        currentSlide.classList.add('is-leaving-right');
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            nextSlide.classList.add('is-active');

            nextSlide.classList.remove(
                'is-entering-left',
                'is-entering-right'
            );
        });
    });

    window.setTimeout(() => {
        currentSlide.hidden = true;

        currentSlide.classList.remove(
            'is-active',
            'is-leaving-left',
            'is-leaving-right'
        );

        currentIndex = nextIndex;
        isAnimating = false;
    }, 700);

    dots.forEach((dot, dotIndex) => {
        const isActive = dotIndex === nextIndex;

        dot.classList.toggle('is-active', isActive);

        dot.setAttribute(
            'aria-current',
            isActive ? 'true' : 'false'
        );
    });
};


        const stopAutoplay = () => {
            if (intervalId !== null) {
                window.clearInterval(intervalId);
                intervalId = null;
            }
        };

        const startAutoplay = () => {
            stopAutoplay();

            intervalId = window.setInterval(() => {
                showSlide(currentIndex + 1, 1);
            }, 3000);
        };

        previousButton?.addEventListener('click', () => {
            showSlide(currentIndex - 1, -1);
            startAutoplay();
        });

        nextButton?.addEventListener('click', () => {
            showSlide(currentIndex + 1, 1);
            startAutoplay();
        });

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const targetIndex = Number(
                    dot.dataset.bannerDot
                );

                showSlide(
    targetIndex,
    targetIndex > currentIndex ? 1 : -1
);
                startAutoplay();
            });
        });

        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('mouseleave', startAutoplay);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoplay();
            } else {
                startAutoplay();
            }
        });

        slides.forEach((slide, index) => {
    slide.classList.toggle(
        'is-active',
        index === 0
    );
});

startAutoplay();
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const countdowns = document.querySelectorAll(
        '[data-countdown]'
    );

    countdowns.forEach((countdown) => {
        const endTime = new Date(
            countdown.dataset.endTime
        ).getTime();

        const daysElement = countdown.querySelector(
            '[data-days]'
        );

        const hoursElement = countdown.querySelector(
            '[data-hours]'
        );

        const minutesElement = countdown.querySelector(
            '[data-minutes]'
        );

        const secondsElement = countdown.querySelector(
            '[data-seconds]'
        );

        const pad = (value) => String(value).padStart(2, '0');

        const update = () => {
            const remaining = Math.max(
                0,
                endTime - Date.now()
            );

            const days = Math.floor(
                remaining / 86400000
            );

            const hours = Math.floor(
                (remaining % 86400000) / 3600000
            );

            const minutes = Math.floor(
                (remaining % 3600000) / 60000
            );

            const seconds = Math.floor(
                (remaining % 60000) / 1000
            );

            daysElement.textContent = pad(days);
            hoursElement.textContent = pad(hours);
            minutesElement.textContent = pad(minutes);
            secondsElement.textContent = pad(seconds);

            if (remaining === 0) {
                window.clearInterval(intervalId);
            }
        };

        update();

        const intervalId = window.setInterval(
            update,
            1000
        );
    });
});
document.addEventListener('DOMContentLoaded', () => {
    /*
     * Gallery chi tiết sản phẩm:
     * - Ảnh sản phẩm
     * - Video tải trực tiếp từ hệ thống
     * - Video URL bên ngoài
     */
    const gallery = document.querySelector(
        '[data-product-gallery]'
    );

    if (gallery) {
        const imagePanel = gallery.querySelector(
            '[data-product-gallery-image-panel]'
        );

        const videoPanel = gallery.querySelector(
            '[data-product-gallery-video-panel]'
        );

        const externalPanel = gallery.querySelector(
            '[data-product-gallery-external-panel]'
        );

        const youtubePanel = gallery.querySelector(
    '[data-product-gallery-youtube-panel]'
);

const youtubeFrame = gallery.querySelector(
    '[data-product-youtube-frame]'
);
        const placeholderPanel = gallery.querySelector(
            '[data-product-gallery-placeholder]'
        );

        const mainImage = gallery.querySelector(
            '[data-product-main-image]'
        );

        const mainVideo = gallery.querySelector(
            '[data-product-main-video]'
        );

        const externalLink = gallery.querySelector(
            '[data-product-external-link]'
        );

        const externalTitle = gallery.querySelector(
            '[data-product-external-title]'
        );

        const thumbnails = Array.from(
            gallery.querySelectorAll(
                '[data-product-media-thumbnail]'
            )
        );

        const hideAllPanels = () => {
    [
        imagePanel,
        videoPanel,
        youtubePanel,
        externalPanel,
        placeholderPanel,
    ]
        .filter(Boolean)
        .forEach((panel) => {
            panel.hidden = true;
        });
};

        const stopCurrentVideo = () => {
    if (mainVideo) {
        mainVideo.pause();
        mainVideo.removeAttribute('src');
        mainVideo.load();
    }

    /*
     * Xóa src iframe để YouTube dừng phát
     * khi chuyển sang ảnh hoặc video khác.
     */
    if (youtubeFrame) {
        youtubeFrame.removeAttribute('src');
    }
};

        const setActiveThumbnail = (selectedThumbnail) => {
            thumbnails.forEach((thumbnail) => {
                thumbnail.classList.remove(
                    'product-gallery__thumbnail--active'
                );

                thumbnail.setAttribute(
                    'aria-current',
                    'false'
                );
            });

            selectedThumbnail.classList.add(
                'product-gallery__thumbnail--active'
            );

            selectedThumbnail.setAttribute(
                'aria-current',
                'true'
            );
        };

        const showMedia = (thumbnail) => {
            const mediaType =
                thumbnail.dataset.mediaType || '';

            const mediaUrl =
                thumbnail.dataset.mediaUrl || '';

            const mediaTitle =
                thumbnail.dataset.mediaTitle
                || 'Video sản phẩm';

            stopCurrentVideo();
            hideAllPanels();

            if (
                mediaType === 'image'
                && imagePanel
                && mainImage
                && mediaUrl
            ) {
                mainImage.src = mediaUrl;
                mainImage.alt = mediaTitle
                    || mainImage.alt
                    || 'Hình ảnh sản phẩm';

                imagePanel.hidden = false;
            } else if (
    mediaType === 'video'
    && videoPanel
    && mainVideo
    && mediaUrl
) {
    mainVideo.src = mediaUrl;

    mainVideo.setAttribute(
        'aria-label',
        mediaTitle
    );

    mainVideo.muted = true;
    mainVideo.playsInline = true;
    mainVideo.autoplay = true;

    videoPanel.hidden = false;
    mainVideo.load();

    } else if (
    mediaType === 'youtube'
    && youtubePanel
    && youtubeFrame
    && mediaUrl
) {
    const separator = mediaUrl.includes('?')
        ? '&'
        : '?';

    youtubeFrame.src =
        `${mediaUrl}${separator}autoplay=1&mute=1&playsinline=1&rel=0`;

    youtubeFrame.title = mediaTitle;
    youtubePanel.hidden = false;
    /*
     * Trình duyệt thường chỉ cho autoplay khi muted.
     */
    mainVideo.play().catch(() => {
        // Người dùng vẫn có thể bấm Play thủ công.
    });
            } else if (
                mediaType === 'external'
                && externalPanel
                && externalLink
                && mediaUrl
            ) {
                externalLink.href = mediaUrl;

                if (externalTitle) {
                    externalTitle.textContent =
                        mediaTitle;
                }

                externalPanel.hidden = false;
            } else if (placeholderPanel) {
                placeholderPanel.hidden = false;
            }

            setActiveThumbnail(thumbnail);
        };

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => {
                showMedia(thumbnail);
            });
        });

        const initialThumbnail =
            gallery.querySelector(
                '.product-gallery__thumbnail--active'
            )
            || thumbnails[0]
            || null;

        if (initialThumbnail) {
            showMedia(initialThumbnail);
        } else {
            hideAllPanels();

            if (placeholderPanel) {
                placeholderPanel.hidden = false;
            }
        }
    }

    const skuButtons = document.querySelectorAll(
        '[data-product-sku]'
    );

    const skuCode = document.querySelector(
        '[data-product-sku-code]'
    );

    const salePrice = document.querySelector(
        '[data-product-sale-price]'
    );

    const originalPrice = document.querySelector(
        '[data-product-original-price]'
    );

    const stockText = document.querySelector(
        '[data-product-stock]'
    );

    const quantityInput = document.querySelector(
        '[data-product-quantity]'
    );

    const cartButton = document.querySelector(
        '[data-detail-add-to-cart]'
    );

    const buyNowDetailButton = document.querySelector(
        '[data-buy-now-detail]'
    );

    const formatPrice = (value) =>
        new Intl.NumberFormat('vi-VN').format(
            Math.max(0, Math.round(value))
        ) + 'đ';

    const calculateSalePrice = (price) => {
        const discount =
            window.productDetailDiscount ?? {};

        if (
            discount.percent !== null
            && discount.percent !== undefined
        ) {
            return price
                * (
                    1
                    - Number(discount.percent) / 100
                );
        }

        if (
            discount.amount !== null
            && discount.amount !== undefined
        ) {
            return price - Number(discount.amount);
        }

        return price;
    };

    skuButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const price = Number(
                button.dataset.price
            );

            const stock = Number(
                button.dataset.stock
            );

            const finalPrice =
                calculateSalePrice(price);

            skuButtons.forEach((item) => {
                item.classList.remove(
                    'product-detail__variant--active'
                );
            });

            button.classList.add(
                'product-detail__variant--active'
            );

            if (skuCode) {
                skuCode.textContent =
                    button.dataset.skuCode;
            }

            if (salePrice) {
                salePrice.textContent =
                    formatPrice(finalPrice);
            }

            if (originalPrice) {
                originalPrice.textContent =
                    formatPrice(price);

                originalPrice.hidden =
                    finalPrice >= price;
            }

            if (stockText) {
                stockText.textContent = stock > 0
                    ? `Còn hàng: ${stock} sản phẩm`
                    : 'Sản phẩm hiện đang hết hàng';

                stockText.classList.toggle(
                    'product-detail__stock--available',
                    stock > 0
                );

                stockText.classList.toggle(
                    'product-detail__stock--empty',
                    stock <= 0
                );
            }

            if (quantityInput) {
                quantityInput.max = String(
                    Math.max(1, stock)
                );

                quantityInput.value = '1';
            }

            if (cartButton) {
                cartButton.dataset.skuId =
                    button.dataset.skuId;

                cartButton.disabled =
                    stock <= 0;
            }


            if (buyNowDetailButton) {
                buyNowDetailButton.dataset.skuId =
                    button.dataset.skuId;

                buyNowDetailButton.disabled =
                    stock <= 0;
            }
        });
    });

    document
        .querySelector('[data-quantity-minus]')
        ?.addEventListener('click', () => {
            if (!quantityInput) {
                return;
            }

            quantityInput.value = String(
                Math.max(
                    Number(
                        quantityInput.min || 1
                    ),
                    Number(
                        quantityInput.value || 1
                    ) - 1
                )
            );
        });

    document
        .querySelector('[data-quantity-plus]')
        ?.addEventListener('click', () => {
            if (!quantityInput) {
                return;
            }

            quantityInput.value = String(
                Math.min(
                    Number(
                        quantityInput.max || 999
                    ),
                    Number(
                        quantityInput.value || 1
                    ) + 1
                )
            );
        });
});

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const buyNowButtons = document.querySelectorAll(
        '[data-buy-now]'
    );

    if (!csrfToken || buyNowButtons.length === 0) {
        return;
    }

    const getErrorMessage = (data) => {
        if (data?.errors) {
            return Object.values(data.errors)[0]?.[0]
                || data.message;
        }

        return data?.message;
    };

    buyNowButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) {
                return;
            }

            const quantityInput = button.hasAttribute(
                'data-buy-now-detail'
            )
                ? document.querySelector(
                    '[data-product-quantity]'
                )
                : null;

            const originalText = button.textContent;

            button.disabled = true;
            button.textContent = 'Đang xử lý...';

            try {
                const response = await fetch('/buy-now', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        product_id: button.dataset.productId
                            ? Number(button.dataset.productId)
                            : null,
                        sku_id: button.dataset.skuId
                            ? Number(button.dataset.skuId)
                            : null,
                        quantity: Math.max(
                            1,
                            Number(quantityInput?.value || 1)
                        ),
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        getErrorMessage(data)
                        || 'Không thể mua ngay sản phẩm.'
                    );
                }

                window.location.href = data.checkout_url;
            } catch (error) {
                window.alert(
                    error.message
                    || 'Không thể mua ngay sản phẩm.'
                );

                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const cartCountElements = document.querySelectorAll(
        '[data-cart-count]'
    );

    const updateCartCount = (count) => {
        cartCountElements.forEach((element) => {
            element.textContent = String(count);
        });
    };

    const showCartMessage = (message, type = 'success') => {
        const oldMessage = document.querySelector(
            '[data-cart-message]'
        );

        oldMessage?.remove();

        const messageElement = document.createElement('div');

        messageElement.dataset.cartMessage = '';
        messageElement.className =
            `cart-toast cart-toast--${type}`;
        messageElement.textContent = message;

        document.body.appendChild(messageElement);

        window.setTimeout(() => {
            messageElement.classList.add('is-visible');
        }, 10);

        window.setTimeout(() => {
            messageElement.classList.remove('is-visible');

            window.setTimeout(() => {
                messageElement.remove();
            }, 250);
        }, 2800);
    };

    const addToCart = async ({
        button,
        productId = null,
        skuId = null,
        quantity = 1,
    }) => {
        if (!csrfToken || button.disabled) {
            return;
        }

        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Đang thêm...';

        try {

                const response = await fetch('/cart/items', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        sku_id: skuId,
                        quantity,
                    }),
                }
            );

            const data = await response.json();

            if (!response.ok) {
                const validationMessage = data.errors
                    ? Object.values(data.errors)[0]?.[0]
                    : null;

                throw new Error(
                    validationMessage
                    || data.message
                    || 'Không thể thêm vào giỏ hàng.'
                );
            }

            updateCartCount(data.cart_count ?? 0);
            showCartMessage(data.message);
        } catch (error) {
            showCartMessage(
                error.message
                || 'Đã xảy ra lỗi, vui lòng thử lại.',
                'error'
            );
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    };

    document
        .querySelectorAll('[data-add-to-cart]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                addToCart({
                    button,
                    productId: Number(
                        button.dataset.productId
                    ),
                    quantity: 1,
                });
            });
        });

    const detailButton = document.querySelector(
        '[data-detail-add-to-cart]'
    );

    detailButton?.addEventListener('click', () => {
        const quantityInput = document.querySelector(
            '[data-product-quantity]'
        );

        addToCart({
            button: detailButton,
            productId: Number(
                detailButton.dataset.productId
            ),
            skuId: Number(
                detailButton.dataset.skuId
            ),
            quantity: Math.max(
                1,
                Number(quantityInput?.value || 1)
            ),
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.content;

    const formatCartMoney = (value) =>
        new Intl.NumberFormat('vi-VN').format(
            Math.round(Number(value))
        ) + 'đ';

    const updateSummary = (data) => {
        document.querySelector('[data-cart-subtotal]')
            ?.replaceChildren(
                formatCartMoney(data.subtotal)
            );

        document.querySelector('[data-cart-discount]')
            ?.replaceChildren(
                '-' + formatCartMoney(data.discount_total)
            );

        document.querySelector('[data-cart-grand-total]')
            ?.replaceChildren(
                formatCartMoney(data.grand_total)
            );

        document
            .querySelectorAll('[data-cart-count]')
            .forEach((element) => {
                element.textContent = String(
                    data.cart_count
                );
            });
    };

    const sendCartRequest = async (
        url,
        method,
        body = null
    ) => {
        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: body
                ? JSON.stringify(body)
                : null,
        });

        const data = await response.json();

        if (!response.ok) {
            const validationMessage = data.errors
                ? Object.values(data.errors)[0]?.[0]
                : null;

            throw new Error(
                validationMessage
                || data.message
                || 'Không thể cập nhật giỏ hàng.'
            );
        }

        return data;
    };

    document
        .querySelectorAll('[data-cart-item]')
        .forEach((item) => {
            const itemId = item.dataset.itemId;
            const input = item.querySelector(
                '[data-cart-quantity]'
            );

            const updateQuantity = async (quantity) => {
    try {
        const data = await sendCartRequest(
            `/cart/items/${itemId}`,
            'PATCH',
            { quantity }
        );

        input.value = String(data.quantity);

        const lineTotalElement = item.querySelector(
            '[data-line-total]'
        );

        if (lineTotalElement) {
            lineTotalElement.textContent = formatCartMoney(
                data.line_total
            );
        }

        updateSummary(data);
    } catch (error) {
        alert(error.message);
    }
};

            item.querySelector('[data-cart-minus]')
                ?.addEventListener('click', () => {
                    const next = Math.max(
                        1,
                        Number(input.value) - 1
                    );

                    updateQuantity(next);
                });

            item.querySelector('[data-cart-plus]')
                ?.addEventListener('click', () => {
                    const next = Math.min(
                        Number(input.max || 99),
                        Number(input.value) + 1
                    );

                    updateQuantity(next);
                });

            input?.addEventListener('change', () => {
                const value = Math.max(
                    1,
                    Math.min(
                        Number(input.max || 99),
                        Number(input.value || 1)
                    )
                );

                updateQuantity(value);
            });

            item.querySelector('[data-cart-remove]')
                ?.addEventListener('click', async () => {
                    if (!confirm(
                        'Bạn có chắc muốn xóa sản phẩm này?'
                    )) {
                        return;
                    }

                    try {
                        const data = await sendCartRequest(
                            `/cart/items/${itemId}`,
                            'DELETE'
                        );

                        item.remove();
                        updateSummary(data);

                        if (data.cart_count === 0) {
                            window.location.reload();
                        }
                    } catch (error) {
                        alert(error.message);
                    }
                });
        });
});
document.addEventListener('DOMContentLoaded', () => {
    const shippingMethods = document.querySelectorAll(
        '[data-shipping-method]'
    );

    const shippingFeeElement = document.querySelector(
        '[data-checkout-shipping-fee]'
    );

    const grandTotalElement = document.querySelector(
        '[data-checkout-grand-total]'
    );

    const couponInput = document.querySelector(
        '[data-checkout-coupon-input]'
    );

    const couponApplyButton = document.querySelector(
        '[data-checkout-coupon-apply]'
    );

    const couponRemoveButton = document.querySelector(
        '[data-checkout-coupon-remove]'
    );

    const couponMessage = document.querySelector(
        '[data-checkout-coupon-message]'
    );

    const couponApplied = document.querySelector(
        '[data-checkout-coupon-applied]'
    );

    const couponCode = document.querySelector(
        '[data-checkout-coupon-code]'
    );

    const couponName = document.querySelector(
        '[data-checkout-coupon-name]'
    );

    const couponRow = document.querySelector(
        '[data-checkout-coupon-row]'
    );

    const couponLabel = document.querySelector(
        '[data-checkout-coupon-label]'
    );

    const couponDiscountElement =
        document.querySelector(
            '[data-checkout-coupon-discount]'
        );

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const checkoutTotals = window.checkoutTotals;

    if (
        !shippingFeeElement
        || !grandTotalElement
        || !checkoutTotals
    ) {
        return;
    }

    const formatMoney = (value) => {
        return new Intl.NumberFormat(
            'vi-VN'
        ).format(
            Math.max(
                0,
                Math.round(Number(value))
            )
        ) + 'đ';
    };

    const subtotal = Number(
        checkoutTotals.subtotal || 0
    );

    const discountTotal = Number(
        checkoutTotals.discountTotal || 0
    );

    const productTotal = Math.max(
        0,
        Number(
            checkoutTotals.productTotal
            ?? subtotal - discountTotal
        )
    );

    let couponDiscount = Number(
        checkoutTotals.couponDiscount || 0
    );

    let hasFreeShippingCoupon = Boolean(
        checkoutTotals.freeShippingCoupon
    );

    const getSelectedShippingMethod = () => {
        return Array.from(shippingMethods)
            .find((input) => input.checked);
    };

    const calculateShippingFee = () => {
        const selected =
            getSelectedShippingMethod();

        if (!selected) {
            return 0;
        }

        if (hasFreeShippingCoupon) {
            return 0;
        }

        let shippingFee = Number(
            selected.dataset.shippingFee || 0
        );

        const freeShippingMinimum = Number(
            selected.dataset
                .freeShippingMinimum || 0
        );

        if (
            freeShippingMinimum > 0
            && productTotal >= freeShippingMinimum
        ) {
            shippingFee = 0;
        }

        return shippingFee;
    };

    const updateCheckoutTotals = () => {
        const shippingFee =
            calculateShippingFee();

        const grandTotal = Math.max(
            0,
            productTotal
            - couponDiscount
            + shippingFee
        );

        shippingFeeElement.textContent =
            formatMoney(shippingFee);

        grandTotalElement.textContent =
            formatMoney(grandTotal);

        shippingFeeElement.dataset
            .currentShippingFee =
                String(shippingFee);
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

    const renderAppliedCoupon = (coupon) => {
        couponDiscount = Number(
            coupon.discount_amount || 0
        );

        hasFreeShippingCoupon = Boolean(
            coupon.free_shipping
        );

        if (couponCode) {
            couponCode.textContent =
                coupon.code || '';
        }

        if (couponName) {
            couponName.textContent =
                coupon.name || '';
        }

        if (couponLabel) {
            couponLabel.textContent =
                `(${coupon.code})`;
        }

        if (couponDiscountElement) {
            couponDiscountElement.textContent =
                '-' + formatMoney(
                    couponDiscount
                );
        }

        if (couponRow) {
            couponRow.hidden =
                couponDiscount <= 0;
        }

        if (couponApplied) {
            couponApplied.hidden = false;
        }

        if (couponInput) {
            couponInput.value =
                coupon.code || '';

            couponInput.disabled = true;
        }

        if (couponApplyButton) {
            couponApplyButton.disabled = true;
        }

        updateCheckoutTotals();
    };

    const renderRemovedCoupon = () => {
        couponDiscount = 0;
        hasFreeShippingCoupon = false;

        if (couponApplied) {
            couponApplied.hidden = true;
        }

        if (couponRow) {
            couponRow.hidden = true;
        }

        if (couponInput) {
            couponInput.value = '';
            couponInput.disabled = false;
            couponInput.focus();
        }

        if (couponApplyButton) {
            couponApplyButton.disabled = false;
        }

        updateCheckoutTotals();
    };

    shippingMethods.forEach((input) => {
        input.addEventListener(
            'change',
            updateCheckoutTotals
        );
    });

    couponApplyButton?.addEventListener(
        'click',
        async () => {
            const code =
                couponInput?.value.trim() || '';

            if (!code) {
                showCouponMessage(
                    'Vui lòng nhập mã giảm giá.',
                    'error'
                );

                couponInput?.focus();

                return;
            }

            if (!csrfToken) {
                showCouponMessage(
                    'Không tìm thấy mã bảo mật.',
                    'error'
                );

                return;
            }

            couponApplyButton.disabled = true;
            couponApplyButton.textContent =
                'Đang áp dụng...';

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
                        ?? 'Không thể áp dụng mã giảm giá.'
                    );
                }

                renderAppliedCoupon(
                    data.coupon
                );

                showCouponMessage(
                    data.message,
                    'success'
                );
            } catch (error) {
                couponApplyButton.disabled =
                    false;

                showCouponMessage(
                    error.message
                    ?? 'Có lỗi xảy ra.',
                    'error'
                );
            } finally {
                couponApplyButton.textContent =
                    'Áp dụng';
            }
        }
    );

    couponInput?.addEventListener(
        'keydown',
        (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();

                couponApplyButton?.click();
            }
        }
    );

    couponRemoveButton?.addEventListener(
        'click',
        async () => {
            if (!csrfToken) {
                return;
            }

            couponRemoveButton.disabled = true;
            couponRemoveButton.textContent =
                'Đang gỡ...';

            try {
                const response = await fetch(
                    checkoutTotals.removeCouponUrl,
                    {
                        method: 'DELETE',

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

                        body: JSON.stringify({}),
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
                        ?? 'Không thể gỡ mã.'
                    );
                }

                renderRemovedCoupon();

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
                couponRemoveButton.disabled =
                    false;

                couponRemoveButton.textContent =
                    'Gỡ mã';
            }
        }
    );

    updateCheckoutTotals();
});
document.addEventListener('DOMContentLoaded', () => {
    const reviewPanels = document.querySelectorAll(
        '[data-review-panel]'
    );

    const closeAllReviewPanels = (exceptId = null) => {
        reviewPanels.forEach((panel) => {
            if (
                exceptId !== null
                && panel.dataset.reviewPanel === String(exceptId)
            ) {
                return;
            }

            panel.hidden = true;

            const toggle = document.querySelector(
                `[data-review-toggle="${panel.dataset.reviewPanel}"]`
            );

            toggle?.setAttribute('aria-expanded', 'false');
        });
    };

    document
        .querySelectorAll('[data-review-toggle]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const itemId = button.dataset.reviewToggle;

                const panel = document.querySelector(
                    `[data-review-panel="${itemId}"]`
                );

                if (!panel) {
                    return;
                }

                const willOpen = panel.hidden;

                closeAllReviewPanels(itemId);

                panel.hidden = !willOpen;

                button.setAttribute(
                    'aria-expanded',
                    willOpen ? 'true' : 'false'
                );

                if (willOpen) {
                    panel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                    });
                }
            });
        });

    document
        .querySelectorAll('[data-review-close]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const itemId = button.dataset.reviewClose;

                const panel = document.querySelector(
                    `[data-review-panel="${itemId}"]`
                );

                const toggle = document.querySelector(
                    `[data-review-toggle="${itemId}"]`
                );

                if (panel) {
                    panel.hidden = true;
                }

                toggle?.setAttribute(
                    'aria-expanded',
                    'false'
                );
            });
        });

    document
        .querySelectorAll('[data-review-form]')
        .forEach((form) => {
            const ratingInputs = form.querySelectorAll(
                'input[name="rating"]'
            );

            const ratingText = form.querySelector(
                '[data-rating-text]'
            );

            ratingInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    if (ratingText) {
                        ratingText.textContent =
                            `Đã chọn ${input.value} sao`;
                    }
                });
            });

            const imageInput = form.querySelector(
                '[data-review-images]'
            );

            const imagePreview = form.querySelector(
                '[data-image-preview]'
            );

            const videoInput = form.querySelector(
                '[data-review-videos]'
            );

            const videoPreview = form.querySelector(
                '[data-video-preview]'
            );

            const clearPreview = (previewElement) => {
                if (!previewElement) {
                    return;
                }

                previewElement
                    .querySelectorAll(
                        '[data-preview-object-url]'
                    )
                    .forEach((element) => {
                        const objectUrl =
                            element.dataset.previewObjectUrl;

                        if (objectUrl) {
                            URL.revokeObjectURL(objectUrl);
                        }
                    });

                previewElement.innerHTML = '';
            };

            const createPreviewItem = (
                file,
                type,
                previewElement
            ) => {
                const objectUrl = URL.createObjectURL(file);

                const item = document.createElement('div');

                item.className =
                    'order-review-preview__item';

                let mediaElement;

                if (type === 'image') {
                    mediaElement =
                        document.createElement('img');

                    mediaElement.alt = file.name;
                } else {
                    mediaElement =
                        document.createElement('video');

                    mediaElement.controls = true;
                    mediaElement.muted = true;
                }

                mediaElement.src = objectUrl;

                mediaElement.dataset.previewObjectUrl =
                    objectUrl;

                const filename =
                    document.createElement('span');

                filename.textContent = file.name;

                item.append(mediaElement, filename);
                previewElement.appendChild(item);
            };

            imageInput?.addEventListener('change', () => {
                clearPreview(imagePreview);

                const files = Array.from(
                    imageInput.files ?? []
                );

                if (files.length > 5) {
                    window.alert(
                        'Mỗi đánh giá chỉ được chọn tối đa 5 hình ảnh.'
                    );

                    imageInput.value = '';

                    return;
                }

                files.forEach((file) => {
                    createPreviewItem(
                        file,
                        'image',
                        imagePreview
                    );
                });
            });

            videoInput?.addEventListener('change', () => {
                clearPreview(videoPreview);

                const files = Array.from(
                    videoInput.files ?? []
                );

                if (files.length > 2) {
                    window.alert(
                        'Mỗi đánh giá chỉ được chọn tối đa 2 video.'
                    );

                    videoInput.value = '';

                    return;
                }

                files.forEach((file) => {
                    createPreviewItem(
                        file,
                        'video',
                        videoPreview
                    );
                });
            });

            form.addEventListener('submit', () => {
                const submitButton = form.querySelector(
                    '[data-review-submit]'
                );

                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent =
                    'Đang gửi đánh giá...';
            });
        });

    const openedPanel = document.querySelector(
        '[data-review-panel]:not([hidden])'
    );

    if (openedPanel) {
        const toggle = document.querySelector(
            `[data-review-toggle="${openedPanel.dataset.reviewPanel}"]`
        );

        toggle?.setAttribute('aria-expanded', 'true');

        openedPanel.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector(
        '[data-review-media-modal]'
    );

    const container = modal?.querySelector(
        '[data-review-media-container]'
    );

    if (!modal || !container) {
        return;
    }

    const closeModal = () => {
        const video = container.querySelector('video');

        video?.pause();

        container.innerHTML = '';
        modal.hidden = true;

        document.body.classList.remove(
            'has-review-media-modal'
        );
    };

    document
        .querySelectorAll('[data-review-media-open]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const type =
                    button.dataset.reviewMediaType;

                const source =
                    button.dataset.reviewMediaSrc;

                if (!source) {
                    return;
                }

                container.innerHTML = '';

                if (type === 'video') {
                    const video =
                        document.createElement('video');

                    video.src = source;
                    video.controls = true;
                    video.autoplay = true;
                    video.playsInline = true;

                    container.appendChild(video);
                } else {
                    const image =
                        document.createElement('img');

                    image.src = source;
                    image.alt =
                        'Hình ảnh đánh giá sản phẩm';

                    container.appendChild(image);
                }

                modal.hidden = false;

                document.body.classList.add(
                    'has-review-media-modal'
                );
            });
        });

    modal
        .querySelectorAll('[data-review-media-close]')
        .forEach((button) => {
            button.addEventListener(
                'click',
                closeModal
            );
        });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape'
            && !modal.hidden
        ) {
            closeModal();
        }
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    document
        .querySelectorAll('[data-review-like]')
        .forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) {
                    return;
                }

                const url = button.dataset.likeUrl;

                const reviewCard = button.closest(
                    '.product-review-card'
                );

                const countElement = button.querySelector(
                    '[data-review-like-count]'
                );

                const messageElement =
                    reviewCard?.querySelector(
                        '[data-review-like-message]'
                    );

                if (!url || !csrfToken) {
                    return;
                }

                button.disabled = true;

                if (messageElement) {
                    messageElement.textContent = '';
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',

                        headers: {
                            Accept: 'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN': csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials: 'same-origin',

                        body: JSON.stringify({}),
                    });

                    if (response.status === 401) {
                        window.location.href = '/login';

                        return;
                    }

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message
                                ?? 'Không thể cập nhật lượt hữu ích.'
                        );
                    }

                    button.classList.toggle(
                        'is-liked',
                        data.liked
                    );

                    button.setAttribute(
                        'aria-pressed',
                        data.liked
                            ? 'true'
                            : 'false'
                    );

                    if (countElement) {
                        countElement.textContent =
                            String(data.likes_count);
                    }

                    if (messageElement) {
                        messageElement.textContent =
                            data.message;

                        window.setTimeout(() => {
                            messageElement.textContent = '';
                        }, 2500);
                    }
                } catch (error) {
                    if (messageElement) {
                        messageElement.textContent =
                            error.message
                            ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
                    }
                } finally {
                    button.disabled = false;
                }
            });
        });
});
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    document
        .querySelectorAll('[data-favorite-toggle]')
        .forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) {
                    return;
                }

                const url = button.dataset.favoriteUrl;

                const wrapper = button.closest(
                    '.product-detail-favorite, .product-card'
                );

                const icon = button.querySelector(
                    '.product-favorite-button__icon'
                );

                const label = button.querySelector(
                    '[data-favorite-label]'
                );

                const message = wrapper?.querySelector(
                    '[data-favorite-message]'
                );

                if (!url || !csrfToken) {
                    return;
                }

                button.disabled = true;

                if (message) {
                    message.textContent = '';
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',

                        headers: {
                            Accept: 'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN': csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials: 'same-origin',

                        body: JSON.stringify({}),
                    });

                    if (response.status === 401) {
                        window.location.href = '/login';

                        return;
                    }

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message
                                ?? 'Không thể cập nhật sản phẩm yêu thích.'
                        );
                    }

                    button.classList.toggle(
                        'is-favorite',
                        data.is_favorite
                    );

                    button.setAttribute(
                        'aria-pressed',
                        data.is_favorite
                            ? 'true'
                            : 'false'
                    );

                    button.setAttribute(
                        'title',
                        data.is_favorite
                            ? 'Bỏ khỏi danh sách yêu thích'
                            : 'Thêm vào danh sách yêu thích'
                    );

                    if (icon) {
                        icon.textContent =
                            data.is_favorite
                                ? '♥'
                                : '♡';
                    }

                    if (label) {
                        label.textContent =
                            data.is_favorite
                                ? 'Đã yêu thích'
                                : 'Yêu thích';
                    }

                    /*
                     * Đồng bộ số lượng yêu thích trên header
                     * nếu sau này có phần tử này.
                     */
                    document
                        .querySelectorAll(
                            '[data-favorites-count]'
                        )
                        .forEach((countElement) => {
                            countElement.textContent =
                                String(
                                    data.favorites_count
                                );

                            countElement.hidden =
                                data.favorites_count < 1;
                        });

                    if (message) {
                        message.textContent =
                            data.message;

                        window.setTimeout(() => {
                            message.textContent = '';
                        }, 2200);
                    }
                } catch (error) {
                    if (message) {
                        message.textContent =
                            error.message
                            ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
                    }
                } finally {
                    button.disabled = false;
                }
            });
        });
});
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const grid = document.querySelector(
        '[data-favorites-grid]'
    );

    if (!grid || !csrfToken) {
        return;
    }

    const totalElement = document.querySelector(
        '[data-favorites-total]'
    );

    const emptyElement = document.querySelector(
        '[data-favorites-empty]'
    );

    const paginationElement = document.querySelector(
        '[data-favorites-pagination]'
    );

    const messageElement = document.querySelector(
        '[data-favorites-message]'
    );

    const updateEmptyState = () => {
        const cards = grid.querySelectorAll(
            '[data-favorite-card]'
        );

        if (cards.length > 0) {
            return;
        }

        grid.hidden = true;

        if (emptyElement) {
            emptyElement.hidden = false;
        }

        if (paginationElement) {
            paginationElement.hidden = true;
        }
    };

    grid
        .querySelectorAll('[data-favorite-remove]')
        .forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) {
                    return;
                }

                const url =
                    button.dataset.favoriteRemoveUrl;

                const card = button.closest(
                    '[data-favorite-card]'
                );

                if (!url || !card) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',

                        headers: {
                            Accept: 'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN': csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials: 'same-origin',

                        body: JSON.stringify({}),
                    });

                    if (response.status === 401) {
                        window.location.href = '/login';

                        return;
                    }

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message
                                ?? 'Không thể xóa sản phẩm yêu thích.'
                        );
                    }

                    card.classList.add(
                        'is-removing'
                    );

                    window.setTimeout(() => {
                        card.remove();

                        if (totalElement) {
                            totalElement.textContent =
                                String(
                                    data.favorites_count
                                );
                        }

                        document
                            .querySelectorAll(
                                '[data-favorites-count]'
                            )
                            .forEach((countElement) => {
                                countElement.textContent =
                                    String(
                                        data.favorites_count
                                    );

                                countElement.hidden =
                                    data.favorites_count < 1;
                            });

                        updateEmptyState();
                    }, 220);

                    if (messageElement) {
                        messageElement.textContent =
                            data.message;

                        window.setTimeout(() => {
                            messageElement.textContent = '';
                        }, 2500);
                    }
                } catch (error) {
                    button.disabled = false;

                    if (messageElement) {
                        messageElement.textContent =
                            error.message
                            ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
                    }
                }
            });
        });
});
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const grid = document.querySelector(
        '[data-recently-viewed-grid]'
    );

    const clearForm = document.querySelector(
        '[data-clear-recently-viewed-form]'
    );

    if (clearForm) {
        clearForm.addEventListener('submit', (event) => {
            const confirmed = window.confirm(
                'Bạn có chắc muốn xóa toàn bộ lịch sử đã xem không?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }

    if (!grid || !csrfToken) {
        return;
    }

    const totalElement = document.querySelector(
        '[data-recently-viewed-total]'
    );

    const emptyElement = document.querySelector(
        '[data-recently-viewed-empty]'
    );

    const paginationElement = document.querySelector(
        '[data-recently-viewed-pagination]'
    );

    const messageElement = document.querySelector(
        '[data-recently-viewed-message]'
    );

    const updateEmptyState = () => {
        const cards = grid.querySelectorAll(
            '[data-recently-viewed-card]'
        );

        if (cards.length > 0) {
            return;
        }

        grid.hidden = true;

        if (emptyElement) {
            emptyElement.hidden = false;
        }

        if (paginationElement) {
            paginationElement.hidden = true;
        }

        if (clearForm) {
            clearForm.hidden = true;
        }
    };

    grid
        .querySelectorAll('[data-recently-viewed-remove]')
        .forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) {
                    return;
                }

                const url = button.dataset.removeUrl;

                const card = button.closest(
                    '[data-recently-viewed-card]'
                );

                if (!url || !card) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',

                        headers: {
                            Accept: 'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN': csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials: 'same-origin',

                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message
                                ?? 'Không thể xóa lịch sử đã xem.'
                        );
                    }

                    card.classList.add('is-removing');

                    window.setTimeout(() => {
                        card.remove();

                        if (totalElement) {
                            totalElement.textContent =
                                String(data.remaining_count);
                        }

                        updateEmptyState();
                    }, 220);

                    if (messageElement) {
                        messageElement.textContent =
                            data.message;

                        window.setTimeout(() => {
                            messageElement.textContent = '';
                        }, 2500);
                    }
                } catch (error) {
                    button.disabled = false;

                    if (messageElement) {
                        messageElement.textContent =
                            error.message
                            ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
                    }
                }
            });
        });
});
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector(
        '[data-saved-coupon-grid]'
    );
});
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector(
        '[data-saved-coupon-grid]'
    );

    if (!grid) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const messageElement = document.querySelector(
        '[data-saved-coupon-message]'
    );

    const totalElement = document.querySelector(
        '[data-saved-coupon-total]'
    );

    const emptyElement = document.querySelector(
        '[data-saved-coupon-empty]'
    );

    const paginationElement = document.querySelector(
        '[data-saved-coupon-pagination]'
    );

    const showMessage = (
        message,
        type = 'success'
    ) => {
        if (!messageElement) {
            return;
        }

        messageElement.textContent = message;

        messageElement.classList.toggle(
            'is-error',
            type === 'error'
        );

        messageElement.classList.toggle(
            'is-success',
            type === 'success'
        );
    };

    const updateEmptyState = () => {
        const cards = grid.querySelectorAll(
            '[data-saved-coupon-card]'
        );

        if (cards.length > 0) {
            return;
        }

        grid.hidden = true;

        if (emptyElement) {
            emptyElement.hidden = false;
        }

        if (paginationElement) {
            paginationElement.hidden = true;
        }
    };

    grid.addEventListener('click', async (event) => {
        const button = event.target.closest(
            '[data-saved-coupon-remove]'
        );

        if (!button || button.disabled) {
            return;
        }

        const url = button.dataset.removeUrl;

        const card = button.closest(
            '[data-saved-coupon-card]'
        );

        if (!url || !card || !csrfToken) {
            return;
        }

        const confirmed = window.confirm(
            'Bạn có chắc muốn xóa mã này khỏi Ưu đãi của tôi không?'
        );

        if (!confirmed) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Đang xóa...';

        try {
            const response = await fetch(url, {
                method: 'DELETE',

                headers: {
                    Accept: 'application/json',

                    'Content-Type':
                        'application/json',

                    'X-CSRF-TOKEN':
                        csrfToken,

                    'X-Requested-With':
                        'XMLHttpRequest',
                },

                credentials: 'same-origin',

                body: JSON.stringify({}),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                        ?? 'Không thể xóa mã.'
                );
            }

            card.classList.add('is-removing');

            window.setTimeout(() => {
                card.remove();

                if (totalElement) {
                    const current = Number(
                        totalElement.textContent || 0
                    );

                    totalElement.textContent =
                        String(
                            Math.max(0, current - 1)
                        );
                }

                updateEmptyState();
            }, 220);

            showMessage(
                data.message,
                'success'
            );
        } catch (error) {
            button.disabled = false;
            button.textContent = 'Xóa';

            showMessage(
                error.message
                    ?? 'Có lỗi xảy ra.',
                'error'
            );
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const menus = document.querySelectorAll(
        '[data-client-notification-menu]'
    );

    menus.forEach((menu) => {
        const toggle = menu.querySelector(
            '[data-client-notification-toggle]'
        );

        const dropdown = menu.querySelector(
            '[data-client-notification-dropdown]'
        );

        if (!toggle || !dropdown) {
            return;
        }

        const close = () => {
            dropdown.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = dropdown.hidden;

            document
                .querySelectorAll('[data-client-notification-dropdown]')
                .forEach((item) => {
                    item.hidden = true;
                });

            dropdown.hidden = !willOpen;
            toggle.setAttribute(
                'aria-expanded',
                willOpen ? 'true' : 'false'
            );
        });

        dropdown.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', close);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
            }
        });
    });
});
