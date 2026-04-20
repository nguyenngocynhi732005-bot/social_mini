<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-1">
    <div class="container-fluid px-4 position-relative">
        <!-- GÓC TRÁI: Logo & Tìm kiếm -->
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
            <form class="position-relative d-none d-md-block" action="{{ route('social.search.index') }}" method="GET" id="searchForm">
                <input id="searchInput" class="form-control rounded-pill bg-light border-0 ps-5" 
                        type="search" name="q" placeholder="Tìm kiếm" autocomplete="off"
                    style="width: min(420px, 40vw); 
                        border: 1px solid #1f1c1c !important;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i> 
                <div id="searchResults" class="dropdown-menu show search-dropdown-menu position-absolute w-100 mt-2 shadow-sm d-none" style="max-height: 320px; overflow-y: auto; overflow-x: hidden; z-index: 3000;"></div>
            </form>
        </div>

        <!-- GÓC GIỮA: Điều hướng chính -->
<div class="d-flex justify-content-center align-items-center position-absolute top-50 start-50 translate-middle" style="z-index: 2;">
    <ul class="navbar-nav d-flex flex-row justify-content-center align-items-center m-0 p-0" style="gap: 10px;">
        <!-- 1. TRANG CHỦ -->
        <li class="nav-item">
            <a class="nav-link nav-link-ajax d-flex justify-content-center {{ Request::is('/') ? 'active-home' : '' }}" 
               href="{{ url('/') }}" data-path="/" title="Trang chủ"
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

        <!-- 3. BẠN BÈ -->
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
    /* Style cơ bản cho icon khi chưa active */
    .nav-link-ajax {
        color: #6c757d !important;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent !important;
        padding-bottom: 12px; /* Khoảng cách từ icon xuống vạch kẻ */
        display: flex;
        align-items: center;
    }

    /* Khi di chuột qua (Hover) */
    .nav-link:hover {
        transform: scale(1.1);
        color: #333 !important;
    }

    /* Trang chủ đang mở */
    .active-home {
        color: #ec3a63 !important;
        border-bottom: 3px solid #1f1c1c !important;
    }

    /* Video đang mở */
    .active-video {
        color: #84fab0 !important;
        border-bottom: 3px solid #1f1c1c !important;
    }

    /* Bạn bè đang mở */
    .active-friends {
        color: #ffd166 !important;
        border-bottom: 3px solid #1f1c1c !important;
    }

    .hover-bg-light {
        transition: background-color 0.2s ease;
    }

    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }

    .search-result-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #searchForm {
        z-index: 3000;
    }

    .search-dropdown-menu {
        border: 1px solid rgba(31, 28, 28, 0.12);
        border-radius: 12px;
        padding: 0.35rem;
        background: #fff;
        z-index: 3000 !important;
    }

    .search-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        padding: 0.45rem 0.55rem;
        border-radius: 10px;
        text-decoration: none;
        color: #1f1c1c;
    }

    .search-item:hover {
        background-color: #f4f7fb;
    }

    .search-item-name {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchForm = document.getElementById('searchForm');
    const autocompleteUrl = @json(route('social.search.autocomplete'));

    if (!searchInput || !searchResults || !searchForm) {
        return;
    }

    let debounceTimer = null;

    function hideResults() {
        searchResults.classList.add('d-none');
        searchResults.innerHTML = '';
    }

    function renderResults(users) {
        searchResults.innerHTML = '';

        if (!users.length) {
            searchResults.innerHTML = '<div class="text-muted px-3 py-2">Không tìm thấy kết quả</div>';
            searchResults.classList.remove('d-none');
            return;
        }

        users.forEach(user => {
            const userHtml = `
                <a href="/friends?target_id=${user.id}" class="search-item">
                    <img src="${user.avatar_url}" alt="${user.name}" width="36" height="36" class="rounded-circle object-fit-cover" style="object-fit: cover;">
                    <span class="search-item-name">${user.name}</span>
                </a>
            `;
            searchResults.insertAdjacentHTML('beforeend', userHtml);
        });

        searchResults.classList.remove('d-none');
    }

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim();

        clearTimeout(debounceTimer);

        if (keyword.length < 1) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`${autocompleteUrl}?q=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(renderResults)
                .catch(() => hideResults());
        }, 200);
    });

    searchInput.addEventListener('focus', function () {
        if (searchResults.children.length > 0) {
            searchResults.classList.remove('d-none');
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchForm.contains(event.target)) {
            hideResults();
        }
    });
});
</script>

        <!-- GÓC PHẢI: Chat & Profile -->
    <div class="d-flex align-items-center justify-content-end" style="flex: 1;">

        <!-- Chuông thông báo -->
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

        <!-- Trang cá nhân -->
        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; cursor: pointer; overflow: hidden; border: 2px solid #ffb7c5;">
            <img src="https://i.pravatar.cc/40?u=nhi" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    </div>
    </div>
</nav>