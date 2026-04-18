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
            <form class="position-relative d-none d-md-block">
                <input class="form-control rounded-pill bg-light border-0 ps-5"
                    type="search" placeholder="Tìm kiếm"
                    style="width: 200px; 
                        border: 1px solid #1f1c1c !important;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
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
                padding-bottom: 12px;
                /* Khoảng cách từ icon xuống vạch kẻ */
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
        </style>

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

            <!-- Messenger popup -->
            @include('components.messenger-popup')

            <!-- Trang cá nhân -->
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; cursor: pointer; overflow: hidden; border: 2px solid #ffb7c5;">
                <img src="https://i.pravatar.cc/40?u=nhi" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>
    </div>
</nav>