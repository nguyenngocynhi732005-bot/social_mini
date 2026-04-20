<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-1">
    <div class="container-fluid px-4 position-relative">
        <!-- GÃ“C TRÃI: Logo & TÃ¬m kiáº¿m -->
        <div class="d-flex align-items-center" style="flex: 1;">
            <a class="navbar-brand me-3 d-flex align-items-center" href="{{ route('newsfeed') }}" style="text-decoration: none;">
    <div style="width: 40px; 
                height: 40px; 
                background: linear-gradient(45deg, #ffb7c5, #ace0f9); 
                
                border-radius: 12px; 
                display: flex; 
                align-items: center; 
                justify-content: center;
                color: #1f1c1c;
                font-weight: 900;"> S
    </div>
</a>
            <form class="position-relative d-none d-md-block" id="global-search-form" action="{{ route('social.search.index') }}" method="GET" autocomplete="off">
                <input
                    id="global-search-input"
                    name="q"
                    class="form-control rounded-pill bg-light border-0 ps-5 js-global-search-input"
                    type="search"
                    placeholder="TÃ¬m kiáº¿m"
                    style="width: 240px; border: 1px solid #1f1c1c !important; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                >
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>

                <div class="search-dropdown-menu p-0 d-none js-search-dropdown" id="global-search-dropdown" style="max-height: 360px; overflow-y: auto; position: absolute; top: calc(100% + 8px); z-index: 2000;"></div>
            </form>
        </div>

        <!-- GÃ“C GIá»®A: Äiá»u hÆ°á»›ng chÃ­nh -->
<div class="d-flex justify-content-center align-items-center position-absolute top-50 start-50 translate-middle" style="z-index: 2;">
    <ul class="navbar-nav d-flex flex-row justify-content-center align-items-center m-0 p-0" style="gap: 10px;">
        <!-- 1. TRANG CHá»¦ -->
        <li class="nav-item">
            <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('/') ? 'active-home' : '' }}" 
               href="{{ url('/') }}" data-path="/" title="Trang chá»§"
               style="width: 80px; text-decoration: none;">
                <i class="fas fa-home fs-4"></i>
            </a>
        </li>

        <!-- 2. VIDEO -->
        <li class="nav-item">
            <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('videos') ? 'active-video' : '' }}" 
               href="{{ url('/videos') }}" data-path="/videos" title="Video / Reels"
               style="width: 80px; text-decoration: none;">
                <i class="fas fa-tv fs-4"></i>
            </a>
        </li>

        <!-- 3. Báº N BÃˆ -->
        <li class="nav-item">
            <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('friends') ? 'active-friends' : '' }}" 
               href="{{ url('/friends') }}" data-path="/friends" title="Báº¡n bÃ¨"
               style="width: 80px; text-decoration: none;">
                <i class="fas fa-users fs-4"></i>
            </a>
        </li>
    </ul>
</div>

<style>
    /* Style cÆ¡ báº£n cho icon khi chÆ°a active */
    .nav-link-ajax {
        color: #6c757d !important;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent !important;
        padding-bottom: 12px; /* Khoáº£ng cÃ¡ch tá»« icon xuá»‘ng váº¡ch káº» */
        display: flex;
        align-items: center;
    }

    /* Khi di chuá»™t qua (Hover) */
    .nav-link:hover {
        transform: scale(1.1);
        color: #333 !important;
    }

    /* Trang chá»§ Ä‘ang má»Ÿ */
    .active-home {
        color: #ec3a63 !important;
        border-bottom: 3px solid #1f1c1c !important;
    }

    /* Video Ä‘ang má»Ÿ */
    .active-video {
        color: #84fab0 !important;
        border-bottom: 3px solid #1f1c1c !important;
    }

    /* Báº¡n bÃ¨ Ä‘ang má»Ÿ */
    .active-friends {
        color: #ffd166 !important;
        border-bottom: 3px solid #1f1c1c !important;
    }
</style>

<script>
(function () {
    const form = document.getElementById('global-search-form');
    const input = document.getElementById('global-search-input');
    const dropdown = document.getElementById('global-search-dropdown');

    if (!form || !input || !dropdown) {
        return;
    }

    const searchUrl = @json(route('social.search.autocomplete'));
    const fallbackProfileUrl = @json(route('friends'));
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
</script>

        
    <div class="d-flex align-items-center justify-content-end" style="flex: 1;">

       
        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" 
            style="width: 40px; height: 40px; cursor: pointer; border: 1px solid #ffe0e6;">
            <i class="fas fa-bell fs-5" 
            style="background: linear-gradient(180deg, #ff85a2, #ba62ff); 
                    background-clip: text;
                    -webkit-background-clip: text; 
                    -webkit-text-fill-color: transparent;"></i>
        </div>

        <!-- Messenger  -->
        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" 
            style="width: 40px; height: 40px; cursor: pointer; border: 1px solid #ffe0e6;">
            <i class="fab fa-facebook-messenger fs-5" 
            style="background: linear-gradient(45deg, #00d2ff, #9d50bb, #ff85a2); 
                    background-clip: text;
                    -webkit-background-clip: text; 
                    -webkit-text-fill-color: transparent;"></i>
        </div>

        <!-- Trang cÃ¡ nhÃ¢n -->
        <div class="dropdown" data-profile-avatar-menu>
            <button class="btn p-0 border-0 bg-transparent rounded-circle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mo menu ca nhan" style="width: 40px; height: 40px; overflow: hidden; border: 2px solid #ffb7c5 !important;">
                <img src="https://i.pravatar.cc/40?u=nhi" style="width: 100%; height: 100%; object-fit: cover;" alt="avatar">
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
