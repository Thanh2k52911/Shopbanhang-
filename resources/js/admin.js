import './bootstrap';

import Alpine from 'alpinejs';

/*
|--------------------------------------------------------------------------
| Khởi tạo AlpineJS
|--------------------------------------------------------------------------
*/

window.Alpine = Alpine;

/*
|--------------------------------------------------------------------------
| Tiện ích xác nhận hành động
|--------------------------------------------------------------------------
|
| Dùng cho các nút xóa, hủy, duyệt hoặc thay đổi trạng thái quan trọng.
|
| Ví dụ:
|
| <form
|     method="POST"
|     data-confirm="Bạn có chắc muốn xóa sản phẩm này?"
| >
|
*/

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const confirmMessage = form.dataset.confirm;

    if (!confirmMessage) {
        return;
    }

    const confirmed = window.confirm(confirmMessage);

    if (!confirmed) {
        event.preventDefault();
    }
});

/*
|--------------------------------------------------------------------------
| Chống submit form nhiều lần
|--------------------------------------------------------------------------
|
| Khi form đã được submit hợp lệ, nút submit sẽ bị khóa để tránh người dùng
| bấm nhiều lần và tạo dữ liệu trùng.
|
| Muốn bỏ qua chức năng này, thêm:
|
| data-allow-multiple-submit="true"
|
*/

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (event.defaultPrevented) {
        return;
    }

    if (form.dataset.allowMultipleSubmit === 'true') {
        return;
    }

    const submitButtons = form.querySelectorAll(
        'button[type="submit"], input[type="submit"]'
    );

    submitButtons.forEach(function (button) {
        button.disabled = true;

        if (button instanceof HTMLButtonElement) {
            const loadingText = button.dataset.loadingText;

            if (loadingText) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = loadingText;
            }
        }
    });
});

/*
|--------------------------------------------------------------------------
| Preview ảnh trước khi upload
|--------------------------------------------------------------------------
|
| Input:
|
| data-image-preview-input
|
| Ảnh preview:
|
| data-image-preview-target
|
*/

document.addEventListener('change', function (event) {
    const input = event.target;

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    if (!input.matches('[data-image-preview-input]')) {
        return;
    }

    const file = input.files?.[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        window.alert('Vui lòng chọn đúng định dạng hình ảnh.');
        input.value = '';

        return;
    }

    const previewTarget = document.querySelector(
        '[data-image-preview-target]'
    );

    if (!(previewTarget instanceof HTMLImageElement)) {
        return;
    }

    const reader = new FileReader();

    reader.addEventListener('load', function () {
        previewTarget.src = String(reader.result);
        previewTarget.classList.remove('hidden');
    });

    reader.readAsDataURL(file);
});

/*
|--------------------------------------------------------------------------
| Copy nội dung
|--------------------------------------------------------------------------
|
| Ví dụ:
|
| <button
|     type="button"
|     data-copy-text="ORD-000001"
| >
|     Sao chép
| </button>
|
*/

document.addEventListener('click', async function (event) {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const button = target.closest('[data-copy-text]');

    if (!(button instanceof HTMLElement)) {
        return;
    }

    const text = button.dataset.copyText;

    if (!text) {
        return;
    }

    try {
        await navigator.clipboard.writeText(text);

        const originalText = button.innerHTML;

        button.innerHTML = 'Đã sao chép';

        window.setTimeout(function () {
            button.innerHTML = originalText;
        }, 1500);
    } catch (error) {
        console.error('Không thể sao chép nội dung:', error);
    }
});

/*
|--------------------------------------------------------------------------
| Khởi chạy Alpine
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Notification Admin
|--------------------------------------------------------------------------
*/

function initializeAdminNotifications() {
    const config = document.getElementById(
        'admin-notification-config'
    );

    if (!(config instanceof HTMLElement)) {
        return;
    }

    const latestUrl = config.dataset.latestUrl;
    const readAllUrl = config.dataset.readAllUrl;

    if (!latestUrl || !readAllUrl) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const badge = document.querySelector(
        '[data-admin-notification-badge]'
    );

    const summary = document.querySelector(
        '[data-admin-notification-summary]'
    );

    const loading = document.querySelector(
        '[data-admin-notification-loading]'
    );

    const list = document.querySelector(
        '[data-admin-notification-list]'
    );

    const empty = document.querySelector(
        '[data-admin-notification-empty]'
    );

    const readAllButton = document.querySelector(
        '[data-admin-notification-read-all]'
    );

    let loadedOnce = false;
    let loadingRequest = false;

    const escapeHtml = function (value) {
        const element = document.createElement('div');

        element.textContent = value ?? '';

        return element.innerHTML;
    };

    const iconForCategory = function (category) {
        const icons = {
            order: '🛍️',
            payment: '💳',
            shipping: '🚚',
            promotion: '🎁',
            review: '⭐',
            question: '❓',
            inventory: '📦',
            return: '↩️',
            contact: '📩',
            system: '🔔'
        };

        return icons[category] ?? '🔔';
    };

    const updateBadge = function (count) {
        if (!(badge instanceof HTMLElement)) {
            return;
        }

        const number = Number(count || 0);

        badge.textContent = number > 99
            ? '99+'
            : String(number);

        badge.classList.toggle(
            'hidden',
            number < 1
        );

        if (summary instanceof HTMLElement) {
            summary.textContent = number > 0
                ? number + ' thông báo chưa đọc'
                : 'Không có thông báo chưa đọc';
        }

        if (readAllButton instanceof HTMLElement) {
            readAllButton.classList.toggle(
                'hidden',
                number < 1
            );
        }
    };

    const renderNotifications = function (notifications) {
        if (
            !(list instanceof HTMLElement)
            || !(empty instanceof HTMLElement)
            || !(loading instanceof HTMLElement)
        ) {
            return;
        }

        loading.classList.add('hidden');

        if (
            !Array.isArray(notifications)
            || notifications.length === 0
        ) {
            list.classList.add('hidden');
            empty.classList.remove('hidden');

            return;
        }

        empty.classList.add('hidden');
        list.classList.remove('hidden');

        list.innerHTML = notifications.map(function (notification) {
            const unreadClass = notification.is_unread
                ? 'bg-pink-50'
                : 'bg-white';

            const dot = notification.is_unread
                ? '<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500"></span>'
                : '';

            return `
                <a
                    href="${escapeHtml(notification.open_url)}"
                    class="flex gap-3 px-4 py-3 transition hover:bg-gray-50 ${unreadClass}"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-lg">
                        ${iconForCategory(notification.category)}
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="flex items-start gap-2">
                            <strong class="line-clamp-2 flex-1 text-sm text-gray-900">
                                ${escapeHtml(
                                    notification.title || 'Thông báo'
                                )}
                            </strong>

                            ${dot}
                        </span>

                        <span class="mt-1 line-clamp-2 block text-xs leading-5 text-gray-500">
                            ${escapeHtml(notification.message || '')}
                        </span>

                        <span class="mt-1.5 block text-[11px] text-gray-400">
                            ${escapeHtml(
                                notification.created_at_human || ''
                            )}
                        </span>
                    </span>
                </a>
            `;
        }).join('');
    };

    const loadNotifications = async function () {
        if (loadingRequest) {
            return;
        }

        loadingRequest = true;

        try {
            const response = await fetch(
                latestUrl + '?limit=10',
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Không thể tải thông báo.'
                );
            }

            const payload = await response.json();

            updateBadge(payload.unread_count);
            renderNotifications(payload.notifications);

            loadedOnce = true;
        } catch (error) {
            console.error(error);

            if (loading instanceof HTMLElement) {
                loading.textContent =
                    'Không thể tải thông báo.';
            }
        } finally {
            loadingRequest = false;
        }
    };

    window.addEventListener(
        'admin-notifications-opened',
        function () {
            if (!loadedOnce) {
                loadNotifications();
            }
        }
    );

    readAllButton?.addEventListener(
        'click',
        async function () {
            try {
                const response = await fetch(
                    readAllUrl,
                    {
                        method: 'PATCH',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({})
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'Không thể đánh dấu tất cả đã đọc.'
                    );
                }

                updateBadge(0);
                loadedOnce = false;

                await loadNotifications();
            } catch (error) {
                console.error(error);
            }
        }
    );

    loadNotifications();

    window.setInterval(
        loadNotifications,
        30000
    );
}

/*
|--------------------------------------------------------------------------
| AJAX trạng thái Notification
|--------------------------------------------------------------------------
*/

function updateNotificationBadge(count) {
    const badge = document.querySelector(
        '[data-admin-notification-badge]'
    );

    if (!(badge instanceof HTMLElement)) {
        return;
    }

    const unreadCount = Number(count || 0);

    badge.textContent = unreadCount > 99
        ? '99+'
        : String(unreadCount);

    badge.classList.toggle(
        'hidden',
        unreadCount < 1
    );

    const summary = document.querySelector(
        '[data-admin-notification-summary]'
    );

    if (summary instanceof HTMLElement) {
        summary.textContent = unreadCount > 0
            ? unreadCount + ' thông báo chưa đọc'
            : 'Không có thông báo chưa đọc';
    }
}

function notificationRequestHeaders() {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken ?? '',
        'X-Requested-With': 'XMLHttpRequest'
    };
}

document.addEventListener('click', async function (event) {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const toggleButton = target.closest(
        '[data-notification-toggle]'
    );

    if (toggleButton instanceof HTMLButtonElement) {
        const notificationItem = toggleButton.closest(
            '[data-notification-item]'
        );

        if (!(notificationItem instanceof HTMLElement)) {
            return;
        }

        const nextStatus =
            toggleButton.dataset.nextStatus;

        const requestUrl = nextStatus === 'read'
            ? toggleButton.dataset.readUrl
            : toggleButton.dataset.unreadUrl;

        if (!requestUrl) {
            return;
        }

        toggleButton.disabled = true;

        const originalText = toggleButton.textContent;

        toggleButton.textContent = 'Đang xử lý...';

        try {
            const response = await fetch(
                requestUrl,
                {
                    method: 'PATCH',
                    headers: notificationRequestHeaders(),
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Không thể cập nhật trạng thái thông báo.'
                );
            }

            const payload = await response.json();

            const isNowRead =
                payload.notification?.is_read === true;

            notificationItem.dataset.notificationStatus =
                isNowRead
                    ? 'read'
                    : 'unread';

            notificationItem.classList.toggle(
                'bg-pink-50/60',
                !isNowRead
            );

            notificationItem.classList.toggle(
                'bg-white',
                isNowRead
            );

            const unreadDot =
                notificationItem.querySelector(
                    '[data-notification-unread-dot]'
                );

            if (unreadDot instanceof HTMLElement) {
                unreadDot.classList.toggle(
                    'hidden',
                    isNowRead
                );
            }

            if (isNowRead) {
                toggleButton.textContent = 'Chưa đọc';
                toggleButton.dataset.nextStatus = 'unread';

                toggleButton.className =
                    'rounded-lg border border-blue-200 ' +
                    'bg-blue-50 px-3 py-2 text-xs ' +
                    'font-semibold text-blue-700 transition';
            } else {
                toggleButton.textContent = 'Đã đọc';
                toggleButton.dataset.nextStatus = 'read';

                toggleButton.className =
                    'rounded-lg border border-green-200 ' +
                    'bg-green-50 px-3 py-2 text-xs ' +
                    'font-semibold text-green-700 transition';
            }

            updateNotificationBadge(
                payload.unread_count
            );

            const currentStatus = new URLSearchParams(
                window.location.search
            ).get('status');

            if (
                currentStatus === 'unread'
                && isNowRead
            ) {
                notificationItem.remove();
            }

            if (
                currentStatus === 'read'
                && !isNowRead
            ) {
                notificationItem.remove();
            }
        } catch (error) {
            console.error(error);

            window.alert(
                'Không thể cập nhật trạng thái thông báo.'
            );

            toggleButton.textContent = originalText;
        } finally {
            toggleButton.disabled = false;
        }

        return;
    }

    const deleteButton = target.closest(
        '[data-notification-delete]'
    );

    if (!(deleteButton instanceof HTMLButtonElement)) {
        return;
    }

    const deleteUrl =
        deleteButton.dataset.deleteUrl;

    if (!deleteUrl) {
        return;
    }

    const confirmed = window.confirm(
        'Xóa thông báo này?'
    );

    if (!confirmed) {
        return;
    }

    const notificationItem = deleteButton.closest(
        '[data-notification-item]'
    );

    deleteButton.disabled = true;
    deleteButton.textContent = 'Đang xóa...';

    try {
        const response = await fetch(
            deleteUrl,
            {
                method: 'DELETE',
                headers: notificationRequestHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({})
            }
        );

        if (!response.ok) {
            throw new Error(
                'Không thể xóa thông báo.'
            );
        }

        const payload = await response.json();

        if (notificationItem instanceof HTMLElement) {
            notificationItem.remove();
        }

        updateNotificationBadge(
            payload.unread_count
        );
    } catch (error) {
        console.error(error);

        window.alert(
            'Không thể xóa thông báo.'
        );

        deleteButton.disabled = false;
        deleteButton.textContent = 'Xóa';
    }
});
Alpine.start();

document.addEventListener(
    'DOMContentLoaded',
    initializeAdminNotifications
);
