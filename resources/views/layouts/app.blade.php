<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Social Mini - @yield('title')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js"></script>

    @stack('styles')

    <style>
        body {
            background: linear-gradient(to top, #fff1eb 0%, #ace0f9 100%);
            min-height: 100vh;
            font-family: 'Quicksand', sans-serif;
            background-attachment: fixed;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(15px);
            border-bottom: 2px solid rgba(255, 192, 203, 0.3);
            z-index: 1030;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .nav-link-ajax {
            color: #6c757d !important;
            border-bottom: 3px solid transparent !important;
            padding-bottom: 10px;
            transition: all 0.3s ease;
            text-decoration: none !important;
            position: relative;
        }

        .nav-active {
            border-bottom: 3px solid #1f1c1c !important;
        }

        .nav-active .fa-home {
            color: #ec3a63 !important;
        }

        .nav-active .fa-tv {
            color: #84fab0 !important;
        }

        .nav-active .fa-users {
            color: #ffd166 !important;
        }

        .modal {
            z-index: 9999 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .active-home,
        .active-video,
        .active-friends,
        .active-groups {
            border-bottom: 3px solid #1f1c1c !important;
        }

        .active-home {
            color: #ec3a63 !important;
        }

        .active-video {
            color: #84fab0 !important;
        }

        .active-friends {
            color: #ffd166 !important;
        }

        .active-groups {
            color: #6ea8fe !important;
        }
    </style>

    @yield('styles')
</head>

<body>
    @include('layouts.header')

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-7" id="ajax-content">
                @yield('content')
            </div>

            @if(View::hasSection('right-sidebar'))
                <div class="col-lg-3 d-none d-lg-block">
                    @yield('right-sidebar')
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateActiveLinks(url) {
            const currentPath = new URL(url, window.location.origin).pathname;
            const homePaths = new Set(['/', '/newsfeed']);

            document.querySelectorAll('.nav-link-ajax').forEach(link => {
                const path = link.getAttribute('data-path');
                link.classList.remove('active-home', 'active-video', 'active-friends', 'active-groups');

                if (homePaths.has(currentPath) && homePaths.has(path)) {
                    link.classList.add('active-home');
                    return;
                }

                if (path === '/videos' && currentPath === '/videos') {
                    link.classList.add('active-video');
                    return;
                }

                if (path === '/friends' && currentPath === '/friends') {
                    link.classList.add('active-friends');
                    return;
                }

                if (path === '/social/groups' && currentPath === '/social/groups') {
                    link.classList.add('active-groups');
                }
            });
        }

        document.addEventListener('click', function (e) {
            const link = e.target.closest('.nav-link-ajax');
            if (!link) {
                return;
            }

            e.preventDefault();
            const url = link.getAttribute('href');
            const targetPath = new URL(url, window.location.origin).pathname;
            const homePaths = new Set(['/', '/newsfeed']);

            if (homePaths.has(targetPath)) {
                window.location.href = url;
                return;
            }

            const container = document.getElementById('ajax-content');
            if (!container) {
                window.location.href = url;
                return;
            }

            container.style.opacity = '0.4';

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('ajax-content');

                    if (newContent) {
                        container.innerHTML = newContent.innerHTML;
                        container.style.opacity = '1';
                        window.history.pushState({ path: url }, '', url);
                        updateActiveLinks(url);
                        window.scrollTo(0, 0);
                    } else {
                        window.location.href = url;
                    }
                })
                .catch(() => {
                    window.location.href = url;
                });
        });

        window.addEventListener('popstate', function () {
            location.reload();
        });

        document.addEventListener('DOMContentLoaded', function () {
            updateActiveLinks(window.location.href);
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>
