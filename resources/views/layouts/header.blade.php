<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-1">
    <div class="container-fluid px-4 position-relative">
        <div class="d-flex align-items-center" style="flex: 1; gap: 12px;">
            <a class="navbar-brand me-3 d-flex align-items-center" href="{{ route('newsfeed') }}" style="text-decoration: none;">
                <div style="width: 40px; height: 40px; background: linear-gradient(45deg, #ffb7c5, #ace0f9); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #1f1c1c; font-weight: 900;">S</div>
            </a>

            <form class="position-relative d-none d-md-block" id="global-search-form"
                action="{{ route('social.search.index') }}"
                method="GET"
                autocomplete="off"
                data-search-url="{{ route('social.search.autocomplete') }}"
                data-fallback-profile-url="{{ route('friends') }}">
                <input
                    id="global-search-input"
                    name="q"
                    class="form-control rounded-pill bg-light border-0 ps-5 js-global-search-input"
                    type="search"
                    placeholder="Tìm kiếm"
                    style="width: 240px; border: 1px solid #1f1c1c !important; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                >
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <div class="search-dropdown-menu p-0 d-none js-search-dropdown" id="global-search-dropdown" style="max-height: 360px; overflow-y: auto; position: absolute; top: calc(100% + 8px); z-index: 2000;"></div>
            </form>
        </div>

        <div class="d-flex justify-content-center align-items-center position-absolute top-50 start-50 translate-middle" style="z-index: 2;">
            <ul class="navbar-nav d-flex flex-row justify-content-center align-items-center m-0 p-0" style="gap: 10px;">
                <li class="nav-item">
                    <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('/') || Request::is('newsfeed') ? 'active-home' : '' }}"
                        href="{{ route('newsfeed') }}" data-path="/newsfeed" title="Trang chủ"
                        style="width: 80px; text-decoration: none;">
                        <i class="fas fa-home fs-4"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('videos') ? 'active-video' : '' }}"
                        href="{{ url('/videos') }}" data-path="/videos" title="Video / Reels"
                        style="width: 80px; text-decoration: none;">
                        <i class="fas fa-tv fs-4"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('friends') ? 'active-friends' : '' }}"
                        href="{{ url('/friends') }}" data-path="/friends" title="Bạn bè"
                        style="width: 80px; text-decoration: none;">
                        <i class="fas fa-users fs-4"></i>
                    </a>
                </li>
            </ul>
        </div>

        <style>
            .nav-link-ajax {
                color: #6c757d !important;
                transition: all 0.3s ease;
                border-bottom: 3px solid transparent !important;
                padding-bottom: 12px;
                display: flex;
                align-items: center;
            }

            .nav-link:hover {
                transform: scale(1.1);
                color: #333 !important;
            }

            .active-home {
                color: #ec3a63 !important;
                border-bottom: 3px solid #1f1c1c !important;
            }

            .active-video {
                color: #84fab0 !important;
                border-bottom: 3px solid #1f1c1c !important;
            }

            .active-friends {
                color: #ffd166 !important;
                border-bottom: 3px solid #1f1c1c !important;
            }

            .search-dropdown-menu {
                width: 400px !important;
                border-radius: 12px !important;
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15) !important;
                padding: 8px !important;
                border: 1px solid #ddd !important;
                margin-top: 10px;
                left: 0;
                background: white;
            }

            .search-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 8px 12px;
                border-radius: 8px;
                text-decoration: none;
                color: #050505;
                transition: background 0.2s;
            }

            .search-item:hover {
                background-color: #f2f2f2;
                color: #050505;
            }

            .search-item-name {
                font-weight: 600;
                font-size: 1rem;
                color: #050505;
                flex-grow: 1;
            }

            .notification-trigger {
                width: 40px;
                height: 40px;
                cursor: pointer;
                border: 1px solid #ffe0e6;
                position: relative;
            }

            .notification-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                border-radius: 999px;
                background: #ff3b57;
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.68rem;
                font-weight: 700;
                line-height: 1;
                box-shadow: 0 4px 10px rgba(255, 59, 87, 0.28);
            }

            .notification-dropdown-menu {
                width: 370px;
                max-height: 480px;
                overflow: hidden;
                border-radius: 18px;
                border: 1px solid rgba(211, 219, 232, 0.95);
                box-shadow: 0 20px 40px rgba(17, 24, 39, 0.14);
                padding: 0;
                margin-top: 12px;
                background: #ffffff;
            }

            .notification-dropdown-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 14px 16px 10px;
                border-bottom: 1px solid #edf1f7;
            }

            .notification-dropdown-title {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 800;
                color: #1f2937;
            }

            .notification-mark-read-btn {
                border: 0;
                background: transparent;
                color: #2563eb;
                font-weight: 700;
                font-size: 0.88rem;
            }

            .notification-dropdown-list {
                max-height: 390px;
                overflow-y: auto;
            }

            .notification-item {
                display: flex;
                gap: 12px;
                padding: 12px 16px;
                text-decoration: none;
                color: #111827;
                border-bottom: 1px solid #f3f4f6;
                transition: background-color 0.2s ease;
            }

            .notification-item:hover {
                background: #f8fbff;
                color: #111827;
            }

            .notification-item.unread {
                background: linear-gradient(90deg, rgba(37, 99, 235, 0.08), rgba(255, 255, 255, 0));
            }

            .notification-avatar {
                width: 42px;
                height: 42px;
                border-radius: 999px;
                object-fit: cover;
                flex-shrink: 0;
                border: 1px solid #e5e7eb;
                background: #eef2ff;
            }

            .notification-message {
                font-size: 0.95rem;
                line-height: 1.35;
                color: #111827;
                margin-bottom: 4px;
                word-break: break-word;
            }

            .notification-time {
                font-size: 0.8rem;
                color: #6b7280;
            }

            .notification-empty {
                padding: 28px 16px;
                text-align: center;
                color: #6b7280;
            }

            .notification-unread-dot {
                width: 9px;
                height: 9px;
                border-radius: 999px;
                background: #2563eb;
                flex-shrink: 0;
                margin-top: 6px;
            }
        </style>

        <div class="d-flex align-items-center justify-content-end" style="flex: 1; gap: 10px;">
            <div class="dropdown" id="notificationDropdown" data-notifications-url="{{ route('social.notifications.index') }}" data-mark-all-read-url="{{ route('social.notifications.read-all') }}">
                <button
                    class="btn bg-light rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm notification-trigger border-0"
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Thông báo"
                >
                    <i class="fas fa-bell fs-5"
                        style="background: linear-gradient(180deg, #ff85a2, #ba62ff); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    <span class="notification-badge d-none" data-notification-badge>0</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu" data-notification-menu>
                    <div class="notification-dropdown-header">
                        <h6 class="notification-dropdown-title">Thông báo mới</h6>
                        <button type="button" class="notification-mark-read-btn d-none" data-mark-all-read-btn>Đánh dấu đã đọc</button>
                    </div>
                    <div class="notification-dropdown-list" data-notification-list>
                        <div class="notification-empty text-muted">Đang tải thông báo...</div>
                    </div>
                </div>
            </div>

            @include('components.messenger-popup')

            <div class="dropdown" data-profile-avatar-menu>
                <button class="btn p-0 border-0 bg-transparent rounded-circle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mở menu cá nhân" style="width: 40px; height: 40px; overflow: hidden; border: 2px solid #ffb7c5 !important;">
                    <img
                        src="{{ optional(auth()->user())->avatar_url ?: 'https://i.pravatar.cc/40?u=guest' }}"
                        data-current-user-avatar="1"
                        style="width: 100%; height: 100%; object-fit: cover;"
                        alt="avatar"
                    >
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" style="min-width: 210px; border-radius: 12px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('profile.personalization') }}">
                            <i class="fas fa-user-circle me-2 text-secondary"></i>Xem trang cá nhân
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.personalization.activity-log') }}">
                            <i class="fas fa-history me-2 text-secondary"></i>Nhật ký hoạt động
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('profile.personalization.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
(function () {
    const form = document.getElementById('global-search-form');
    const input = document.getElementById('global-search-input');
    const dropdown = document.getElementById('global-search-dropdown');

    if (!form || !input || !dropdown) {
        return;
    }

    const searchUrl = form.dataset.searchUrl || '';
    const fallbackProfileUrl = form.dataset.fallbackProfileUrl || '';
    let debounceTimer = null;
    let activeController = null;

    function hideDropdown() {
        dropdown.classList.add('d-none');
        dropdown.classList.remove('show');
        dropdown.innerHTML = '';
    }

    function showDropdown() {
        dropdown.classList.remove('d-none');
        dropdown.classList.add('show');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildRow(item) {
        const profileHref = fallbackProfileUrl + '?target_id=' + encodeURIComponent(item.id);
        const avatar = item.avatar_url || 'https://i.pravatar.cc/48?u=' + encodeURIComponent(item.id);
        const name = escapeHtml(item.name || ('User #' + item.id));

        return `
            <a class="search-item dropdown-item" href="${profileHref}">
                <img src="${avatar}" alt="${name}" width="36" height="36" class="rounded-circle" style="object-fit: cover; flex-shrink: 0;">
                <div class="search-item-name">${name}</div>
            </a>
        `;
    }

    async function fetchSuggestions(keyword) {
        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        const url = new URL(searchUrl, window.location.origin);
        url.searchParams.set('q', keyword);

        const response = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            signal: activeController.signal,
        });

        if (!response.ok) {
            throw new Error('Search autocomplete failed');
        }

        return response.json();
    }

    input.addEventListener('input', function () {
        const keyword = input.value.trim();

        window.clearTimeout(debounceTimer);

        if (keyword.length < 1) {
            hideDropdown();
            return;
        }

        debounceTimer = window.setTimeout(async () => {
            try {
                const items = await fetchSuggestions(keyword);

                if (!Array.isArray(items) || items.length === 0) {
                    dropdown.innerHTML = '<div class="px-3 py-2 text-muted small">Không có gợi ý phù hợp.</div>';
                    showDropdown();
                    return;
                }

                dropdown.innerHTML = items.map(buildRow).join('');
                showDropdown();
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                dropdown.innerHTML = '<div class="px-3 py-2 text-muted small">Không tải được gợi ý tìm kiếm.</div>';
                showDropdown();
            }
        }, 250);
    });

    input.addEventListener('focus', function () {
        if (dropdown.innerHTML.trim() !== '') {
            showDropdown();
        }
    });

    document.addEventListener('click', function (event) {
        if (!form.contains(event.target)) {
            hideDropdown();
        }
    });

    form.addEventListener('submit', function () {
        hideDropdown();
    });
})();

(function () {
    const dropdownRoot = document.getElementById('notificationDropdown');
    if (!dropdownRoot) {
        return;
    }

    const toggleButton = dropdownRoot.querySelector('[data-bs-toggle="dropdown"]');
    const menu = dropdownRoot.querySelector('[data-notification-menu]');
    const list = dropdownRoot.querySelector('[data-notification-list]');
    const badge = dropdownRoot.querySelector('[data-notification-badge]');
    const markAllReadBtn = dropdownRoot.querySelector('[data-mark-all-read-btn]');
    const notificationsUrl = dropdownRoot.dataset.notificationsUrl || '';
    const markAllReadUrl = dropdownRoot.dataset.markAllReadUrl || '';
    const markReadUrlTemplate = @json(route('social.notifications.read', ['id' => '__ID__']));
    let latestUnreadCount = 0;
    let refreshTimer = null;
    let refreshInFlight = false;

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeAvatarUrl(url, senderName, senderId) {
        if (url) {
            return url;
        }

        const seed = encodeURIComponent(senderName || ('User #' + senderId));
        return 'https://i.pravatar.cc/64?u=' + seed;
    }

    function updateBadge(unreadCount) {
        const safeCount = Math.max(0, parseInt(unreadCount || 0, 10));
        latestUnreadCount = safeCount;

        if (!badge) {
            return;
        }

        if (safeCount <= 0) {
            badge.textContent = '0';
            badge.classList.add('d-none');
            return;
        }

        badge.classList.remove('d-none');
        badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
    }

    function renderEmpty(message) {
        if (!list) {
            return;
        }

        list.innerHTML = '<div class="notification-empty text-muted">' + escapeHtml(message) + '</div>';
    }

    function renderNotifications(notifications) {
        if (!list) {
            return;
        }

        if (!Array.isArray(notifications) || notifications.length === 0) {
            renderEmpty('Chưa có thông báo mới.');
            return;
        }

        list.innerHTML = notifications.map(function (notification) {
            const unreadClass = notification.is_read ? '' : ' unread';
            const dot = notification.is_read ? '' : '<span class="notification-unread-dot"></span>';
            const avatar = normalizeAvatarUrl(notification.sender_avatar_url, notification.sender_name, notification.sender_id);
            const message = escapeHtml(notification.message || 'Bạn có thông báo mới.');
            const time = escapeHtml(notification.time || 'Vừa xong');
            const href = notification.link && notification.link !== '#' ? notification.link : '#';

            return [
                '<a class="notification-item' + unreadClass + '" href="' + href + '" data-notification-id="' + notification.id + '" data-notification-link="' + href + '">',
                '  <img class="notification-avatar" src="' + avatar + '" alt="notify-avatar">',
                '  <div class="flex-grow-1 min-w-0">',
                '    <div class="notification-message">' + message + '</div>',
                '    <div class="notification-time">' + time + '</div>',
                '  </div>',
                dot,
                '</a>'
            ].join('');
        }).join('');
    }

    async function fetchNotifications() {
        if (refreshInFlight || !notificationsUrl) {
            return;
        }

        refreshInFlight = true;

        try {
            const url = new URL(notificationsUrl, window.location.origin);
            url.searchParams.set('limit', '8');

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Không tải được thông báo.');
            }

            const payload = await response.json();
            updateBadge(payload.unread_count || 0);
            renderNotifications(payload.notifications || []);

            if (markAllReadBtn) {
                markAllReadBtn.classList.toggle('d-none', latestUnreadCount <= 0);
            }
        } catch (error) {
            if (!list || list.innerHTML.trim() === '') {
                renderEmpty('Không tải được thông báo.');
            }
        } finally {
            refreshInFlight = false;
        }
    }

    async function markNotificationRead(notificationId) {
        if (!notificationId) {
            return;
        }

        try {
            const url = markReadUrlTemplate.replace('__ID__', notificationId);
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
        } catch (error) {
            // Ignore marking failures and continue navigation.
        }
    }

    if (menu) {
        menu.addEventListener('click', async function (event) {
            const notificationItem = event.target.closest('[data-notification-id]');
            if (!notificationItem) {
                return;
            }

            const notificationId = notificationItem.dataset.notificationId;
            const notificationLink = notificationItem.dataset.notificationLink || '#';

            event.preventDefault();
            await markNotificationRead(notificationId);
            await fetchNotifications();

            if (notificationLink && notificationLink !== '#') {
                window.location.href = notificationLink;
            }
        });
    }

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', async function () {
            if (!markAllReadUrl) {
                return;
            }

            try {
                await fetch(markAllReadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                await fetchNotifications();
            } catch (error) {
                // No-op; keep the dropdown usable even if this fails.
            }
        });
    }

    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            fetchNotifications();
        });
    }

    const startPolling = function () {
        fetchNotifications();

        if (refreshTimer) {
            window.clearInterval(refreshTimer);
        }

        refreshTimer = window.setInterval(fetchNotifications, 10000);
    };

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            fetchNotifications();
        }
    });

    startPolling();
})();
</script>
