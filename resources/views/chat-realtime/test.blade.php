@php
$manifestPath = public_path('mix-manifest.json');
$mixManifest = is_file($manifestPath) ? (json_decode(file_get_contents($manifestPath), true) ?: []) : [];
$requestedPeerId = (int) request()->query('peer_id', 0);
$testConversationId = (int) request()->query('conversation_id', 0);
if ($testConversationId <= 0) {
    $testConversationId=(int) \App\Models\Conversation::query()->orderBy('id')->value('id');
    }

    $authUser = auth()->user();
    $currentUserId = (int) (optional($authUser)->ID ?? auth()->id() ?? 0);

    $defaultSenderId = \App\Models\User::query()->orderBy('ID')->value('ID') ?? 1;
    $requestedSenderId = (int) request()->query('sender_id', 0);
    $isTestMode = app()->environment('local') && $requestedSenderId > 0;
    $candidateSenderId = $isTestMode ? $requestedSenderId : ($currentUserId ?: $defaultSenderId);

    $activeUser = \App\Models\User::query()
    ->where('ID', $candidateSenderId)
    ->first();

    if (!$activeUser) {
    $activeUser = \App\Models\User::query()->where('ID', $defaultSenderId)->first();
    }

    $activeSenderId = (int) (optional($activeUser)->ID ?? ($defaultSenderId ?: 1));
    $displayUsername = trim((string) (optional($activeUser)->name ?? ''));
    $zegoAppId = (int) env('ZEGO_APP_ID', 1404858540);
    $zegoServerSecret = (string) env('ZEGO_SERVER_SECRET', '43f4f3877e081c999c7baaea7011ff94');

    if ($displayUsername === '') {
    $first = trim((string) (optional($activeUser)->First_name ?? ''));
    $last = trim((string) (optional($activeUser)->Last_name ?? ''));
    $displayUsername = trim($first . ' ' . $last);
    }

    if ($displayUsername === '') {
    $displayUsername = (string) (optional($activeUser)->Email ?: ('User #' . $activeSenderId));
    }

    $currentUserAvatarUrl = $activeUser && !empty($activeUser->avatar_url)
        ? $activeUser->avatar_url
        : 'https://ui-avatars.com/api/?name=' . urlencode($displayUsername) . '&background=eceef2&color=111827&size=96';
    @endphp

    <script>
        document.title = 'VibeTalk';
    </script>

    <div id="ig-chat-app"
        data-current-user-id="{{ $isTestMode ? $activeSenderId : $currentUserId }}"
        data-sender-id="{{ $activeSenderId }}"
        data-sender-name="{{ $displayUsername }}"
        data-profile-url-template="{{ url('/profile/__ID__') }}"
        data-test-mode="{{ $isTestMode ? 1 : 0 }}"
        data-default-conversation-id="{{ $testConversationId ?? '' }}"
        data-default-peer-id="{{ $requestedPeerId > 0 ? $requestedPeerId : '' }}"
        data-csrf-token="{{ csrf_token() }}"
        data-conversations-url="{{ route('chat.conversations.index') }}"
        data-messages-url-template="{{ url('/chat/conversations/__ID__/messages') }}"
        data-background-url-template="{{ url('/chat/conversations/__ID__/background') }}"
        data-call-signal-url-template="{{ url('/chat/conversations/__ID__/call-signal') }}"
        data-call-signal-latest-url-template="{{ url('/chat/conversations/__ID__/call-signal/latest') }}"
        data-typing-url-template="{{ url('/chat/conversations/__ID__/typing') }}"
        data-typing-latest-url-template="{{ url('/chat/conversations/__ID__/typing/latest') }}"
        data-zego-app-id="{{ $zegoAppId }}"
        data-zego-server-secret="{{ $zegoServerSecret }}">
        <section class="ig-sidebar">
            <div class="ig-sidebar-head">
                <div class="ig-user-row">
                    <a class="ig-home-link" href="{{ route('newsfeed') }}" aria-label="Trang chủ">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                    <img
                        id="ig-current-user-avatar"
                        src="{{ $currentUserAvatarUrl }}"
                        data-profile-user-id="{{ $activeSenderId }}"
                        data-current-user-avatar="1"
                        title="Xem trang cá nhân"
                        alt="Current user avatar"
                    >
                    <h1>{{ $displayUsername }}</h1>
                </div>
                <div class="ig-sidebar-head-actions">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ig-logout-btn" aria-label="Đăng xuất">Đăng xuất</button>
                    </form>
                </div>
            </div>
            <label class="ig-search-wrap" for="ig-search-input">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8" />
                    <path d="M20 20l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
                <input id="ig-search-input" type="search" placeholder="Tìm kiếm" autocomplete="off">
            </label>
            <div class="ig-inbox-head">
                <strong>Tin nhắn</strong>
            </div>
            <div id="conversation-list" class="ig-conversation-list" role="list"></div>
        </section>

        <section class="ig-thread">
            <header class="ig-thread-head" id="thread-head">
                <div class="ig-thread-user">
                    <img id="thread-avatar" src="https://ui-avatars.com/api/?name=VibeTalk&background=edeef0&color=1f2937" alt="Avatar" title="Xem trang cá nhân">
                    <div>
                        <h2 id="thread-name">Chọn cuộc trò chuyện</h2>
                        <p id="thread-status">Bắt đầu nhắn tin trong khung bên dưới</p>
                    </div>
                </div>
                <div class="ig-thread-actions">
                    <button type="button" id="voice-call-btn" aria-label="Call"><svg viewBox="0 0 24 24" fill="none">
                            <path d="M5 4h4l2 5-2.5 1.5a13.5 13.5 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2.2 2A16 16 0 0 1 3 6.2 2 2 0 0 1 5 4z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg></button>
                    <button type="button" id="video-call-btn" aria-label="Video call"><svg viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="7" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.7" />
                            <path d="M15 10l6-3v10l-6-3v-4z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        </svg></button>
                    <button type="button" id="bg-image-btn" aria-label="Chọn ảnh nền"><svg viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7" />
                            <circle cx="9" cy="10" r="1.4" fill="currentColor" />
                            <path d="M6 16l4-4 3 3 3-2 2 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg></button>
                    <button type="button" id="bg-image-clear-btn" aria-label="Xóa ảnh nền"><svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 6l12 12M18 6l-12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg></button>
                </div>
                <input type="file" id="bg-image-input" accept="image/*" hidden>
            </header>

            <main id="chat-box" class="ig-messages" aria-live="polite">
                <section id="ig-thread-intro" class="ig-thread-intro">
                    <img id="intro-avatar" src="https://ui-avatars.com/api/?name=VibeTalk&background=dfe3e8&color=111827" alt="Avatar" title="Xem trang cá nhân">
                    <h3 id="intro-name">VibeTalk</h3>
                    <p id="intro-handle">vibetalk</p>
                    <button type="button">Xem trang cá nhân</button>
                </section>
                <div id="ig-typing-indicator" class="ig-typing-indicator" hidden>
                    <span class="ig-typing-indicator-bubble" aria-label="Đang soạn tin">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </div>
            </main>

            <form id="composer-form" class="ig-composer" autocomplete="off">
                <button class="ig-composer-icon" id="voice-message-btn" type="button" aria-label="Voice"><svg viewBox="0 0 24 24" fill="none">
                        <rect x="9" y="3" width="6" height="11" rx="3" stroke="currentColor" stroke-width="1.8" />
                        <path d="M6 11a6 6 0 1 0 12 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        <path d="M12 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg></button>
                <input type="text" id="message-input" placeholder="Nhắn tin..." maxlength="2000">
                <div class="ig-messenger-tools" aria-label="Messenger tools">
                    <button class="ig-tool-btn" type="button" id="emoji-picker-toggle" aria-label="Emoji"><svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" />
                            <path d="M8.5 14.5c.8 1 2 1.5 3.5 1.5s2.7-.5 3.5-1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M9 10h.01M15 10h.01" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" />
                        </svg></button>
                    <button class="ig-tool-btn" type="button" id="sticker-picker-toggle" aria-label="Sticker"><svg viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="1.8" />
                            <path d="M9 12h6M9 15h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg></button>
                    <button class="ig-tool-btn" type="button" id="chat-image-picker-toggle" aria-label="Chụp hoặc gửi ảnh"><svg viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="7" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.8" />
                            <path d="M9 7l1.5-2h3L15 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <circle cx="12" cy="13.5" r="3.2" stroke="currentColor" stroke-width="1.8" />
                        </svg></button>
                </div>
                <button class="ig-send-btn" type="submit" id="send-button" aria-label="Gửi nhanh">
                    <span id="send-button-label">Gửi</span>
                    <span id="send-button-reaction" class="ig-reaction-icon">❤️</span>
                </button>
                <input type="file" id="chat-image-input" accept="image/*" capture="environment" hidden>
                <div class="ig-attachment-picker" id="chat-attachment-picker" hidden>
                    <button type="button" class="ig-attachment-action" id="chat-attachment-file-btn">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 3.5h7l5 5V20a1.5 1.5 0 0 1-1.5 1.5h-10A1.5 1.5 0 0 1 5 20V5A1.5 1.5 0 0 1 6.5 3.5z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <path d="M13 3.5V8h4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Chọn tệp</span>
                    </button>
                    <button type="button" class="ig-attachment-action is-camera" id="chat-attachment-camera-btn">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 7.5h2.2l1.2-2h3.4l1.2 2H17a2 2 0 0 1 2 2v7.5a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9.5a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <circle cx="12" cy="13" r="2.9" stroke="currentColor" stroke-width="1.7" />
                        </svg>
                        <span>Chụp ảnh</span>
                    </button>
                </div>
                <div class="ig-camera-modal" id="chat-camera-modal" hidden>
                    <div class="ig-camera-panel" role="dialog" aria-label="Chụp ảnh bằng camera">
                        <video id="chat-camera-video" class="ig-camera-video" autoplay playsinline muted webkit-playsinline="true"></video>
                        <p id="chat-camera-status" class="ig-camera-status" hidden></p>
                        <canvas id="chat-camera-canvas" hidden></canvas>
                        <div class="ig-camera-actions">
                            <button type="button" class="ig-camera-action is-cancel" id="chat-camera-cancel">Hủy</button>
                            <button type="button" class="ig-camera-action" id="chat-camera-capture">Chụp & gửi</button>
                        </div>
                    </div>
                </div>
                <div class="ig-call-modal" id="zego-call-modal" hidden>
                    <div class="ig-call-shell" role="dialog" aria-label="Cuộc gọi thoại hoặc video">
                        <div class="ig-call-head">
                            <div>
                                <strong id="zego-call-title">Đang gọi...</strong>
                                <p id="zego-room-label"></p>
                            </div>
                            <button type="button" id="zego-call-close" aria-label="Kết thúc cuộc gọi">Kết thúc</button>
                        </div>
                        <div class="ig-call-container" id="zego-call-container"></div>
                    </div>
                </div>
                <div class="ig-incoming-call-modal" id="incoming-call-modal" hidden>
                    <div class="ig-incoming-call-card" role="dialog" aria-label="Cuộc gọi đến">
                        <p class="ig-incoming-call-label">Cuộc gọi đến</p>
                        <strong id="incoming-call-caller">Ai đó đang gọi...</strong>
                        <p id="incoming-call-type">Cuộc gọi video</p>
                        <div class="ig-incoming-call-actions">
                            <button type="button" id="incoming-call-reject" class="is-reject">Từ chối</button>
                            <button type="button" id="incoming-call-accept" class="is-accept">Nghe máy</button>
                        </div>
                    </div>
                </div>
                <div class="ig-reaction-picker" id="reaction-picker" hidden>
                    <div class="ig-reaction-search">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8" />
                            <path d="M20 20l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                        <input id="reaction-search-input" type="search" placeholder="Tìm kiếm biểu tượng cảm xúc" autocomplete="off">
                    </div>
                    <div class="ig-reaction-grid" id="reaction-grid">
                        <button type="button" class="ig-reaction-option" data-reaction="😀" data-category="smile" aria-label="Smile">😀</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😄" data-category="smile" aria-label="Happy">😄</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😂" data-category="smile" aria-label="Haha">😂</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😍" data-category="smile" aria-label="Love eyes">😍</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🥰" data-category="smile" aria-label="Cute">🥰</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😎" data-category="smile" aria-label="Cool">😎</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😮" data-category="smile" aria-label="Wow">😮</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😭" data-category="smile" aria-label="Cry">😭</button>
                        <button type="button" class="ig-reaction-option" data-reaction="😡" data-category="smile" aria-label="Angry">😡</button>
                        <button type="button" class="ig-reaction-option" data-reaction="👍" data-category="smile" aria-label="Like">👍</button>
                        <button type="button" class="ig-reaction-option" data-reaction="👏" data-category="smile" aria-label="Clap">👏</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🙏" data-category="smile" aria-label="Pray">🙏</button>
                        <button type="button" class="ig-reaction-option" data-reaction="❤️" data-category="smile" aria-label="Tim">❤️</button>
                        <button type="button" class="ig-reaction-option" data-reaction="💜" data-category="smile" aria-label="Purple heart">💜</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🔥" data-category="smile" aria-label="Fire">🔥</button>
                        <button type="button" class="ig-reaction-option" data-reaction="✨" data-category="smile" aria-label="Sparkle">✨</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🎉" data-category="smile" aria-label="Party">🎉</button>
                        <button type="button" class="ig-reaction-option" data-reaction="💯" data-category="smile" aria-label="Hundred">💯</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🤝" data-category="smile" aria-label="Handshake">🤝</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🤗" data-category="smile" aria-label="Hug">🤗</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🐶" data-category="paw" aria-label="Dog">🐶</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🐱" data-category="paw" aria-label="Cat">🐱</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🐾" data-category="paw" aria-label="Paw">🐾</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🦊" data-category="paw" aria-label="Fox">🦊</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🐰" data-category="paw" aria-label="Rabbit">🐰</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🍜" data-category="food" aria-label="Noodle">🍜</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🍣" data-category="food" aria-label="Sushi">🍣</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🍕" data-category="food" aria-label="Pizza">🍕</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🍔" data-category="food" aria-label="Burger">🍔</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🍴" data-category="food" aria-label="Fork">🍴</button>
                        <button type="button" class="ig-reaction-option" data-reaction="⚽" data-category="sport" aria-label="Football">⚽</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🏀" data-category="sport" aria-label="Basketball">🏀</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🏸" data-category="sport" aria-label="Badminton">🏸</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🏓" data-category="sport" aria-label="Ping pong">🏓</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🎾" data-category="sport" aria-label="Tennis">🎾</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🚗" data-category="vehicle" aria-label="Car">🚗</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🏍️" data-category="vehicle" aria-label="Motorbike">🏍️</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🚕" data-category="vehicle" aria-label="Taxi">🚕</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🚲" data-category="vehicle" aria-label="Bicycle">🚲</button>
                        <button type="button" class="ig-reaction-option" data-reaction="✈️" data-category="vehicle" aria-label="Plane">✈️</button>
                        <button type="button" class="ig-reaction-option" data-reaction="💡" data-category="idea" aria-label="Idea">💡</button>
                        <button type="button" class="ig-reaction-option" data-reaction="🧠" data-category="idea" aria-label="Brain">🧠</button>
                        <button type="button" class="ig-reaction-option" data-reaction="📌" data-category="idea" aria-label="Pin">📌</button>
                        <button type="button" class="ig-reaction-option" data-reaction="✨" data-category="idea" aria-label="Spark">✨</button>
                        <button type="button" class="ig-reaction-option" data-reaction="📝" data-category="idea" aria-label="Note">📝</button>
                    </div>
                    <div class="ig-reaction-tabs" id="reaction-tabs" role="tablist" aria-label="Nhóm biểu tượng">
                        <button type="button" class="ig-reaction-tab is-active" data-category="smile" aria-label="Mặt cười">😀</button>
                        <button type="button" class="ig-reaction-tab" data-category="paw" aria-label="Động vật">🐾</button>
                        <button type="button" class="ig-reaction-tab" data-category="food" aria-label="Đồ ăn">🍴</button>
                        <button type="button" class="ig-reaction-tab" data-category="sport" aria-label="Thể thao">⚽</button>
                        <button type="button" class="ig-reaction-tab" data-category="vehicle" aria-label="Xe cộ">🚗</button>
                        <button type="button" class="ig-reaction-tab" data-category="idea" aria-label="Ý tưởng">💡</button>
                    </div>
                </div>
                <div class="ig-media-picker" id="sticker-picker" hidden>
                    <div class="ig-media-tabs" id="sticker-tabs" role="tablist">
                        <button type="button" class="ig-media-tab is-active" data-category="popular" aria-label="Phổ biến">⭐</button>
                        <button type="button" class="ig-media-tab" data-category="smile" aria-label="Mặt cười">😊</button>
                        <button type="button" class="ig-media-tab" data-category="animal" aria-label="Động vật">🐶</button>
                        <button type="button" class="ig-media-tab" data-category="nature" aria-label="Tự nhiên">🌸</button>
                        <button type="button" class="ig-media-tab" data-category="celebration" aria-label="Celebration">🎉</button>
                    </div>
                    <div class="ig-media-search">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8" />
                            <path d="M20 20l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                        <input id="sticker-search-input" type="search" placeholder="Tìm kiếm nhân dân" autocomplete="off">
                    </div>
                    <div class="ig-media-grid" id="sticker-grid">
                        <button type="button" class="ig-media-item" data-sticker-id="good-morning" data-keyword="morning good chao buoi sang" data-category="popular,nature" aria-label="Good morning"><span class="ig-sticker-art">☀️</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="thursday" data-keyword="thursday thu nam" data-category="popular,celebration" aria-label="Thursday"><span class="ig-sticker-art">📅</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="birthday" data-keyword="birthday sinh nhat" data-category="popular,celebration" aria-label="Happy birthday"><span class="ig-sticker-art">🎂</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="love-note" data-keyword="love yeu tim" data-category="popular,smile" aria-label="Love note"><span class="ig-sticker-art">💌</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="thanks" data-keyword="cam on thanks" data-category="smile,popular" aria-label="Cam on"><span class="ig-sticker-art">🙏</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="haha" data-keyword="haha vui" data-category="smile" aria-label="Haha"><span class="ig-sticker-art">😂</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="sleepy-dog" data-keyword="dog cho sleepy" data-category="animal" aria-label="Sleepy dog"><span class="ig-sticker-art">🐶</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="cool-cat" data-keyword="cat meo cool" data-category="animal,smile" aria-label="Cool cat"><span class="ig-sticker-art">🐱</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="balloons" data-keyword="balloon bong bong" data-category="celebration" aria-label="Balloons"><span class="ig-sticker-art">🎈</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="flower-smile" data-keyword="flower hoa smile" data-category="nature,smile" aria-label="Flower smile"><span class="ig-sticker-art">🌸</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="party-time" data-keyword="party vui" data-category="celebration" aria-label="Party time"><span class="ig-sticker-art">🎉</span></button>
                        <button type="button" class="ig-media-item" data-sticker-id="sunny-day" data-keyword="sunny ngay moi" data-category="nature" aria-label="Sunny day"><span class="ig-sticker-art">🌞</span></button>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <style>
        :root {
            --ig-bg: #fff7fb;
            --ig-panel: #ffffff;
            --ig-border: #e5d1df;
            --ig-muted: #f6dff0;
            --ig-text: #2a2230;
            --ig-sidebar-bg: linear-gradient(135deg, #12d4d8 0%, #9f71f1 100%);
            --ig-sidebar-item-hover: rgba(255, 255, 255, 0.18);
            --ig-sidebar-item-active: rgba(255, 255, 255, 0.30);
            --ig-bubble-me: #ffffff;
            --ig-bubble-them: #ffffff;
            --ig-thread-head-height: 74px;
            --ig-composer-height: 72px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--ig-bg);
            color: var(--ig-text);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
        }

        #ig-chat-app {
            height: calc(100vh - 20px);
            margin: 10px;
            background: var(--ig-panel);
            border: 1px solid var(--ig-border);
            border-radius: 16px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 360px minmax(480px, 1fr);
            grid-template-rows: minmax(0, 1fr);
            align-items: stretch;
        }

        .ig-sidebar {
            border-right: 1px solid var(--ig-border);
            display: flex;
            flex-direction: column;
            background: var(--ig-sidebar-bg);
        }

        .ig-sidebar-head {
            padding: 20px 18px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ig-sidebar-head h1 {
            margin: 0;
            font-size: 22px;
            line-height: 1;
            color: #ffffff;
        }

        .ig-sidebar-head-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .ig-user-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ig-user-row img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.7);
            flex: 0 0 auto;
        }

        .ig-home-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            color: #ffffff;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .ig-home-link svg {
            width: 20px;
            height: 20px;
        }

        .ig-home-link:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .ig-logout-btn {
            border: 1px solid rgba(255, 255, 255, 0.45);
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .ig-logout-btn:hover {
            background: rgba(255, 255, 255, 0.24);
        }

        .ig-search-wrap {
            margin: 0 14px 12px;
            border-radius: 11px;
            background: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4c3d68;
        }

        .ig-search-wrap svg {
            width: 17px;
            height: 17px;
        }

        .ig-search-wrap input {
            flex: 1;
            border: 0;
            outline: none;
            background: transparent;
            font-size: 14px;
            color: #2f2542;
        }

        .ig-search-wrap input::placeholder {
            color: #8b78a8;
        }

        .ig-inbox-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 16px 10px;
        }

        .ig-inbox-head strong {
            font-size: 18px;
            color: #ffffff;
        }

        .ig-inbox-head span {
            color: rgba(255, 255, 255, 0.88);
            font-size: 14px;
        }

        .ig-conversation-list {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.55) rgba(255, 255, 255, 0.12);
            padding-bottom: 12px;
        }

        .ig-conversation-list::-webkit-scrollbar {
            width: 8px;
        }

        .ig-conversation-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 999px;
        }

        .ig-conversation-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.55);
            border-radius: 999px;
        }

        .ig-conversation-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.75);
        }

        .ig-conversation-section {
            padding: 10px 16px 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.78);
        }

        .ig-conversation-item {
            border: 0;
            width: 100%;
            background: transparent;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            cursor: pointer;
        }

        .ig-conversation-item.is-active {
            background: var(--ig-sidebar-item-active);
        }

        .ig-conversation-item:hover {
            background: var(--ig-sidebar-item-hover);
        }

        .ig-conversation-item img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ececec;
        }

        .ig-conversation-avatar-wrap {
            position: relative;
            width: 56px;
            height: 56px;
            flex: 0 0 auto;
        }

        .ig-conversation-avatar-wrap .ig-conversation-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ececec;
            display: block;
        }

        .ig-conversation-active-dot {
            position: absolute;
            right: 1px;
            bottom: 1px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #22c55e;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .ig-conversation-main {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        .ig-conversation-main strong {
            font-size: 16px;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ig-conversation-main span {
            color: rgba(255, 255, 255, 0.88);
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ig-thread {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            min-height: 0;
            height: 100%;
            overflow: hidden;
        }

        .ig-thread-head {
            border-bottom: 1px solid #e7e2ea;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 0 0 auto;
            min-height: var(--ig-thread-head-height);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
        }

        .ig-thread-user {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .ig-thread-user img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid #ececec;
        }

        .ig-thread-user h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #111827;
        }

        .ig-thread-user p {
            margin: 1px 0 0;
            font-size: 12px;
            color: #d8b8cf;
        }

        .ig-thread-actions {
            display: flex;
            gap: 8px;
        }

        .ig-thread-actions button {
            border: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            color: #111827;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .ig-thread-actions svg {
            width: 22px;
            height: 22px;
        }

        .ig-messages {
            overflow: auto;
            flex: 1 1 auto;
            min-height: 0;
            height: auto;
            padding: 16px 22px 0;
            scroll-padding-bottom: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background-color: #ffffff;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }

        .ig-thread-intro {
            margin: auto;
            text-align: center;
            color: #4b5563;
        }

        .ig-thread-intro img {
            width: 94px;
            height: 94px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
        }

        .ig-thread-intro h3 {
            margin: 12px 0 4px;
            color: #111827;
            font-size: 30px;
        }

        .ig-thread-intro p {
            margin: 0 0 14px;
            color: var(--ig-muted);
        }

        .ig-thread-intro button {
            border: 0;
            border-radius: 9px;
            padding: 10px 16px;
            background: #efefef;
            font-weight: 600;
            cursor: pointer;
        }

        .ig-typing-indicator {
            align-self: flex-start;
            margin: 4px 0 10px 40px;
        }

        .ig-typing-indicator[hidden] {
            display: none !important;
        }

        .ig-typing-indicator-bubble {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 1px rgba(15, 23, 42, 0.05);
        }

        .ig-typing-indicator-bubble span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #9ca3af;
            animation: igTypingPulse 1.1s infinite ease-in-out;
        }

        .ig-typing-indicator-bubble span:nth-child(2) {
            animation-delay: 0.18s;
        }

        .ig-typing-indicator-bubble span:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes igTypingPulse {

            0%,
            80%,
            100% {
                opacity: 0.35;
                transform: translateY(0);
            }

            40% {
                opacity: 1;
                transform: translateY(-1px);
            }
        }

        .ig-message-row {
            display: flex;
            max-width: min(70%, 560px);
            align-items: flex-end;
            gap: 10px;
            animation: bubbleIn 0.16s ease;
        }

        .ig-message-row.them {
            align-self: flex-start;
        }

        .ig-message-row.me {
            align-self: flex-end;
            justify-content: flex-end;
            flex-direction: row-reverse;
        }

        .ig-message-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #e5e7eb;
            flex: 0 0 auto;
            align-self: flex-end;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        img[data-profile-user-id] {
            cursor: pointer;
        }

        .ig-message-content {
            border-radius: 18px;
            padding: 10px 14px;
            font-size: 15px;
            line-height: 1.45;
            word-break: break-word;
            border: 1px solid #e6e7eb;
            box-shadow: 0 1px 1px rgba(15, 23, 42, 0.04);
        }

        .ig-message-content.is-recalled {
            color: #6b7280;
            font-style: italic;
            background: transparent;
            border: 0;
            padding: 2px 0;
            box-shadow: none;
        }

        .ig-message-content:empty {
            display: none;
        }

        .ig-message-media {
            display: block;
            width: min(100%, 520px);
            max-width: min(520px, 92vw);
            height: auto;
            aspect-ratio: auto;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            object-fit: contain;
            background: #f3f4f6;
        }

        .ig-message-row.is-image-only {
            max-width: min(520px, 88%);
        }

        .ig-message-wrapper {
            display: grid;
            gap: 6px;
            position: relative;
        }

        .ig-message-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            pointer-events: none;
            min-height: 18px;
        }

        .ig-message-send-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 700;
            flex: 0 0 auto;
        }

        .ig-message-send-status.is-pending {
            color: #9ca3af;
            background: rgba(156, 163, 175, 0.12);
        }

        .ig-message-send-status.is-error {
            color: #ffffff;
            background: #dc2626;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.12);
        }

        .ig-message-row.is-sending {
            opacity: 0.78;
        }

        .ig-message-row.is-send-error .ig-message-wrapper {
            box-shadow: 0 0 0 1px rgba(220, 38, 38, 0.18);
        }

        .ig-message-row.me .ig-message-meta {
            justify-content: flex-end;
        }

        .ig-message-row.is-recalled {
            max-width: max-content;
        }

        .ig-message-row.is-recalled .ig-message-wrapper {
            gap: 2px;
        }

        .ig-message-row.is-recalled .ig-message-content.is-recalled {
            padding: 0;
            border: 0;
            background: transparent;
            color: #9ca3af;
            font-size: 12px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .ig-message-recall-btn {
            border: 0;
            background: transparent;
            color: #dc2626;
            font-size: 12px;
            font-weight: 700;
            padding: 0;
            cursor: pointer;
            opacity: 0;
            pointer-events: auto;
            transition: opacity 0.15s ease;
        }

        .ig-message-row.me:hover .ig-message-recall-btn,
        .ig-message-row.me:focus-within .ig-message-recall-btn {
            opacity: 1;
            pointer-events: auto;
        }

        .ig-message-recall-btn:hover {
            text-decoration: underline;
        }

        .ig-message-reaction {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            font-size: 14px;
            line-height: 1;
        }

        .ig-message-content+.ig-message-media,
        .ig-message-media+.ig-message-meta {
            margin-top: 8px;
        }

        .ig-message-stamp {
            align-self: center;
            color: #8f95a3;
            font-size: 11px;
            line-height: 1.1;
            margin: 1px 0;
            letter-spacing: 0.1px;
            opacity: 0.85;
        }

        .ig-camera-modal {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 0;
            padding: 12px;
        }

        .ig-camera-modal[hidden] {
            display: none !important;
        }

        .ig-camera-panel {
            width: min(440px, calc(100vw - 24px));
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 20px 50px rgba(17, 24, 39, 0.2);
        }

        .ig-camera-video {
            width: 100%;
            border-radius: 12px;
            background: #111827;
            aspect-ratio: 4 / 3;
            object-fit: cover;
        }

        .ig-camera-status {
            margin: 8px 4px 0;
            font-size: 13px;
            color: #374151;
        }

        .ig-camera-status.is-error {
            color: #b91c1c;
        }

        .ig-camera-actions {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .ig-camera-action {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #f9fafb;
            color: #111827;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
            cursor: pointer;
        }

        .ig-camera-action.is-cancel {
            background: #ffffff;
        }

        .ig-call-modal {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.64);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1100;
            padding: 12px;
        }

        .ig-call-modal[hidden] {
            display: none !important;
        }

        .ig-call-shell {
            width: min(980px, calc(100vw - 24px));
            height: min(700px, calc(100vh - 24px));
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #d6d7dd;
            background: #0f172a;
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.45);
            display: grid;
            grid-template-rows: auto minmax(0, 1fr);
        }

        .ig-call-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #111827;
            color: #f9fafb;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .ig-call-head strong {
            display: block;
            font-size: 15px;
        }

        .ig-call-head p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #c9d1e0;
        }

        #zego-call-close {
            border: 0;
            border-radius: 999px;
            background: #dc2626;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 14px;
            cursor: pointer;
        }

        .ig-call-container {
            width: 100%;
            height: 100%;
            min-height: 0;
        }

        .ig-incoming-call-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            display: grid;
            place-items: center;
            z-index: 1200;
            padding: 16px;
        }

        .ig-incoming-call-modal[hidden] {
            display: none !important;
        }

        .ig-incoming-call-card {
            width: min(360px, calc(100vw - 32px));
            border-radius: 20px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #e5e7eb;
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.48);
            padding: 22px;
            text-align: center;
            display: grid;
            gap: 8px;
        }

        .ig-incoming-call-label {
            margin: 0;
            font-size: 13px;
            color: #93c5fd;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        #incoming-call-caller {
            font-size: 24px;
            line-height: 1.2;
        }

        #incoming-call-type {
            margin: 0;
            color: #cbd5e1;
            font-size: 14px;
        }

        .ig-incoming-call-actions {
            margin-top: 8px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .ig-incoming-call-actions button {
            border: 0;
            border-radius: 999px;
            min-width: 116px;
            padding: 10px 14px;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
        }

        .ig-incoming-call-actions .is-reject {
            background: #ef4444;
        }

        .ig-incoming-call-actions .is-accept {
            background: #10b981;
        }

        .ig-message-row.them .ig-message-content {
            background: #f4f5f7;
            color: #1f2937;
            border-top-left-radius: 8px;
        }

        .ig-message-row.me .ig-message-content {
            background: linear-gradient(180deg, #5f6cff 0%, #4d5cfb 100%);
            color: #ffffff;
            border-color: transparent;
            border-top-right-radius: 8px;
        }

        .ig-message-time {
            display: none;
        }

        .ig-message-wrapper {
            display: grid;
        }

        .ig-message-row.me .ig-message-wrapper {
            justify-items: end;
        }

        .ig-composer {
            border-top: 1px solid #ebe5ef;
            padding: 12px 14px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            position: relative;
            z-index: 2;
            min-height: var(--ig-composer-height);
            height: var(--ig-composer-height);
            flex-shrink: 0;
        }

        .ig-composer input {
            flex: 1;
            border: 0;
            outline: none;
            background: #ffffff;
            font-size: 15px;
            color: #111827;
            border-radius: 999px;
            padding: 10px 12px;
        }

        .ig-composer-icon {
            border: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            color: #0f172a;
        }

        .ig-composer-icon svg {
            width: 21px;
            height: 21px;
        }

        .ig-composer-icon.is-recording {
            color: #dc2626;
            background: #fee2e2;
        }

        .ig-audio-bubble {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 180px;
            max-width: min(260px, 80vw);
            padding: 12px 14px;
            border-radius: 20px;
            background: linear-gradient(180deg, #7f93e9 0%, #5f6cff 100%);
            color: #ffffff;
            box-shadow: 0 1px 1px rgba(15, 23, 42, 0.08);
        }

        .ig-message-row.them .ig-audio-bubble {
            background: #eef2ff;
            color: #1f2937;
        }

        .ig-audio-toggle {
            border: 0;
            background: transparent;
            color: inherit;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex: 0 0 auto;
            padding: 0;
        }

        .ig-audio-play-icon {
            font-size: 16px;
            line-height: 1;
            margin-left: 1px;
        }

        .ig-audio-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex: 1 1 auto;
            min-width: 0;
        }

        .ig-audio-duration {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .ig-message-audio {
            display: none;
        }

        .ig-messenger-tools {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .ig-tool-btn {
            border: 0;
            min-width: 34px;
            height: 34px;
            border-radius: 50%;
            background: transparent;
            color: #0f172a;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.2px;
        }

        .ig-tool-btn svg {
            width: 18px;
            height: 18px;
        }

        .ig-tool-btn:hover {
            background: #eef2ff;
        }

        .ig-send-btn {
            border: 0;
            background: linear-gradient(180deg, #5f6cff 0%, #4d5cfb 100%);
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
            padding: 10px 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 84px;
            gap: 4px;
            border-radius: 999px;
            box-shadow: 0 8px 18px rgba(77, 92, 251, 0.24);
        }

        .ig-send-btn .ig-reaction-icon {
            display: none;
            font-size: 21px;
            line-height: 1;
        }

        .ig-composer.is-empty .ig-send-btn {
            background: transparent;
            color: #0f172a;
            box-shadow: none;
        }

        .ig-composer.is-empty #send-button-label {
            display: none;
        }

        .ig-composer.is-empty .ig-send-btn .ig-reaction-icon {
            display: inline;
        }

        .ig-reaction-picker {
            position: absolute;
            left: 0;
            bottom: 62px;
            width: 300px;
            display: grid;
            gap: 10px;
            padding: 12px;
            border: 1px solid #e6e7eb;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18);
            z-index: 20;
        }

        .ig-reaction-picker[hidden] {
            display: none;
        }

        .ig-reaction-search {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d9dde3;
            border-radius: 999px;
            padding: 8px 12px;
            color: #64748b;
        }

        .ig-reaction-search svg {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
        }

        .ig-reaction-search input {
            border: 0;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #334155;
        }

        .ig-reaction-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 4px;
            max-height: 176px;
            overflow: auto;
            padding-right: 4px;
        }

        .ig-reaction-option {
            border: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            font-size: 21px;
            line-height: 1;
        }

        .ig-reaction-option:hover,
        .ig-reaction-option.is-active {
            background: #eef2ff;
        }

        .ig-reaction-tabs {
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 4px;
        }

        .ig-reaction-tab {
            border: 0;
            background: transparent;
            color: #6b7280;
            text-align: center;
            font-size: 16px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
        }

        .ig-reaction-tab:hover,
        .ig-reaction-tab.is-active {
            background: #eef2ff;
            color: #111827;
        }

        .ig-media-picker {
            position: absolute;
            left: 0;
            bottom: 62px;
            width: 360px;
            display: grid;
            grid-template-rows: auto auto 1fr;
            gap: 0;
            border: 1px solid #e6e7eb;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18);
            z-index: 20;
            max-height: 400px;
        }

        .ig-media-picker[hidden] {
            display: none;
        }

        .ig-media-tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 2px;
            overflow-x: auto;
        }

        .ig-media-tab {
            border: 0;
            background: transparent;
            color: #6b7280;
            font-size: 18px;
            height: 44px;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            transition: all 0.2s;
        }

        .ig-media-tab:hover,
        .ig-media-tab.is-active {
            color: #111827;
            border-bottom-color: #0095f6;
        }

        .ig-media-search {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 12px;
            color: #64748b;
        }

        .ig-media-search svg {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
        }

        .ig-media-search input {
            border: 0;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #334155;
            background: transparent;
        }

        .ig-media-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            overflow: auto;
            padding: 12px;
        }

        .ig-media-item {
            border: 0;
            border-radius: 12px;
            padding: 0;
            background: var(--sticker-bg, #f3f4f6) no-repeat center/cover;
            cursor: pointer;
            font-size: 14px;
            color: #334155;
            text-align: center;
            line-height: 1;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }

        .ig-sticker-art {
            font-size: 34px;
            filter: drop-shadow(0 2px 2px rgba(15, 23, 42, 0.12));
            line-height: 1;
        }

        .ig-sticker-thumb {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
            padding: 4px;
        }

        .ig-message-sticker {
            width: min(220px, 58vw);
            max-width: min(220px, 58vw);
            aspect-ratio: auto;
            border: 0;
            background: transparent;
            border-radius: 0;
            object-fit: contain;
        }

        .ig-media-item:hover,
        .ig-media-item.is-active {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .ig-send-btn:disabled {
            opacity: 0.45;
            cursor: default;
        }

        .ig-attachment-picker {
            position: absolute;
            right: 0;
            bottom: 62px;
            width: 260px;
            display: grid;
            gap: 10px;
            padding: 12px;
            border: 1px solid #e6e7eb;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18);
            z-index: 20;
        }

        .ig-attachment-picker[hidden] {
            display: none;
        }

        .ig-attachment-action {
            width: 100%;
            border: 0;
            border-radius: 12px;
            background: #f8fafc;
            color: #111827;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: 700;
            text-align: left;
        }

        .ig-attachment-action svg {
            width: 19px;
            height: 19px;
            flex: 0 0 auto;
        }

        .ig-attachment-action:hover {
            background: #eef2ff;
        }

        .ig-attachment-action.is-camera {
            background: #eefaf2;
        }

        .ig-attachment-action.is-camera:hover {
            background: #dcfce7;
        }

        .ig-empty-note {
            margin: 12px auto;
            color: var(--ig-muted);
            font-size: 14px;
        }

        @keyframes bubbleIn {
            from {
                transform: translateY(8px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 1100px) {
            #ig-chat-app {
                grid-template-columns: 280px minmax(300px, 1fr);
            }

            .ig-message-row {
                max-width: 86%;
            }
        }

        @media (max-width: 860px) {
            :root {
                --ig-thread-head-height: 64px;
                --ig-composer-height: 68px;
            }

            #ig-chat-app {
                margin: 0;
                height: 100vh;
                border-radius: 0;
                grid-template-columns: 1fr;
            }

            .ig-sidebar {
                display: none;
            }

            .ig-thread {
                min-height: 0;
                height: 100%;
            }

            .ig-thread-head {
                padding: 10px 12px;
            }

            .ig-messages {
                padding: 12px 12px 2px;
                scroll-padding-bottom: 68px;
            }

            .ig-message-row {
                max-width: 84%;
            }

            .ig-composer {
                padding: 10px 10px 12px;
            }

            .ig-messenger-tools {
                gap: 2px;
            }

            .ig-reaction-picker {
                width: calc(100vw - 20px);
                max-width: 340px;
            }

            .ig-media-picker {
                width: calc(100vw - 20px);
                max-width: 360px;
            }

            .ig-media-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>

    @if (isset($mixManifest['/js/app.js']))
    <script src="{{ mix('js/app.js') }}"></script>
    @elseif (is_file(public_path('js/app.js')))
    <script src="{{ asset('js/app.js') }}"></script>
    @endif
    <script src="https://unpkg.com/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>

    <script>
        (function() {
            const app = document.getElementById('ig-chat-app');
            if (!app) return;

            const authUserId = Number(app.dataset.currentUserId) || 0;
            const currentUserAvatarElements = () => Array.from(document.querySelectorAll('img[data-current-user-avatar="1"]'));

            function updateCurrentUserAvatar(avatarUrl) {
                if (!avatarUrl) {
                    return;
                }

                currentUserAvatarElements().forEach(function (img) {
                    img.src = avatarUrl;
                });
            }

            function readAvatarPayload(rawValue) {
                if (!rawValue) {
                    return '';
                }

                try {
                    var parsed = JSON.parse(rawValue);
                    return parsed && parsed.avatarUrl ? parsed.avatarUrl : '';
                } catch (error) {
                    return '';
                }
            }

            window.addEventListener('social:avatar-updated', function (event) {
                updateCurrentUserAvatar(event && event.detail ? event.detail.avatarUrl : '');
            });

            window.addEventListener('storage', function (event) {
                if (!event || event.key !== 'social:avatar-updated') {
                    return;
                }

                updateCurrentUserAvatar(readAvatarPayload(event.newValue));
            });

            if ('BroadcastChannel' in window) {
                var avatarChannel = new BroadcastChannel('social-avatar');
                avatarChannel.addEventListener('message', function (event) {
                    if (event && event.data && event.data.type === 'avatar-updated') {
                        updateCurrentUserAvatar(event.data.avatarUrl || '');
                    }
                });

                window.addEventListener('beforeunload', function () {
                    avatarChannel.close();
                });
            }

            const dom = {
                searchInput: document.getElementById('ig-search-input'),
                conversationList: document.getElementById('conversation-list'),
                chatBox: document.getElementById('chat-box'),
                typingIndicator: document.getElementById('ig-typing-indicator'),
                bgImageBtn: document.getElementById('bg-image-btn'),
                bgImageClearBtn: document.getElementById('bg-image-clear-btn'),
                bgImageInput: document.getElementById('bg-image-input'),
                chatImagePickerToggle: document.getElementById('chat-image-picker-toggle'),
                chatImageInput: document.getElementById('chat-image-input'),
                chatAttachmentPicker: document.getElementById('chat-attachment-picker'),
                chatAttachmentFileBtn: document.getElementById('chat-attachment-file-btn'),
                chatAttachmentCameraBtn: document.getElementById('chat-attachment-camera-btn'),
                chatCameraModal: document.getElementById('chat-camera-modal'),
                chatCameraVideo: document.getElementById('chat-camera-video'),
                chatCameraStatus: document.getElementById('chat-camera-status'),
                chatCameraCanvas: document.getElementById('chat-camera-canvas'),
                chatCameraCaptureBtn: document.getElementById('chat-camera-capture'),
                chatCameraCancelBtn: document.getElementById('chat-camera-cancel'),
                voiceMessageBtn: document.getElementById('voice-message-btn'),
                voiceCallBtn: document.getElementById('voice-call-btn'),
                videoCallBtn: document.getElementById('video-call-btn'),
                zegoCallModal: document.getElementById('zego-call-modal'),
                zegoCallContainer: document.getElementById('zego-call-container'),
                zegoCallCloseBtn: document.getElementById('zego-call-close'),
                zegoCallTitle: document.getElementById('zego-call-title'),
                zegoRoomLabel: document.getElementById('zego-room-label'),
                incomingCallModal: document.getElementById('incoming-call-modal'),
                incomingCallCaller: document.getElementById('incoming-call-caller'),
                incomingCallType: document.getElementById('incoming-call-type'),
                incomingCallAcceptBtn: document.getElementById('incoming-call-accept'),
                incomingCallRejectBtn: document.getElementById('incoming-call-reject'),
                intro: document.getElementById('ig-thread-intro'),
                threadName: document.getElementById('thread-name'),
                threadStatus: document.getElementById('thread-status'),
                threadAvatar: document.getElementById('thread-avatar'),
                introAvatar: document.getElementById('intro-avatar'),
                introName: document.getElementById('intro-name'),
                introHandle: document.getElementById('intro-handle'),
                form: document.getElementById('composer-form'),
                input: document.getElementById('message-input'),
                sendButton: document.getElementById('send-button'),
                sendButtonLabel: document.getElementById('send-button-label'),
                sendButtonReaction: document.getElementById('send-button-reaction'),
                reactionPickerToggle: document.getElementById('emoji-picker-toggle'),
                reactionPicker: document.getElementById('reaction-picker'),
                reactionSearchInput: document.getElementById('reaction-search-input'),
                reactionOptions: Array.from(document.querySelectorAll('.ig-reaction-option')),
                reactionTabs: Array.from(document.querySelectorAll('.ig-reaction-tab')),
                toolButtons: Array.from(document.querySelectorAll('.ig-tool-btn[data-insert]')),
                stickerPickerToggle: document.getElementById('sticker-picker-toggle'),
                stickerPicker: document.getElementById('sticker-picker'),
                stickerSearchInput: document.getElementById('sticker-search-input'),
                stickerItems: Array.from(document.querySelectorAll('#sticker-grid .ig-media-item')),
                stickerTabs: Array.from(document.querySelectorAll('#sticker-tabs .ig-media-tab')),
            };

            const state = {
                currentUserId: Number(app.dataset.currentUserId) || 0,
                senderId: Number(app.dataset.senderId) || 0,
                profileUrlTemplate: app.dataset.profileUrlTemplate || '/profile/__ID__',
                testMode: String(app.dataset.testMode || '0') === '1',
                defaultConversationId: Number(app.dataset.defaultConversationId) || null,
                defaultPeerId: Number(app.dataset.defaultPeerId) || null,
                csrfToken: app.dataset.csrfToken || '',
                conversationsUrl: app.dataset.conversationsUrl,
                messagesUrlTemplate: app.dataset.messagesUrlTemplate,
                backgroundUrlTemplate: app.dataset.backgroundUrlTemplate,
                callSignalUrlTemplate: app.dataset.callSignalUrlTemplate,
                callSignalLatestUrlTemplate: app.dataset.callSignalLatestUrlTemplate,
                typingUrlTemplate: app.dataset.typingUrlTemplate,
                typingLatestUrlTemplate: app.dataset.typingLatestUrlTemplate,
                activeConversationId: null,
                activePeerId: null,
                activeConversationName: 'VibeTalk',
                activeConversationAvatar: avatarUrl('VibeTalk'),
                conversations: [],
                messageCache: {},
                seenMessageIds: new Set(),
                messagesMeta: {
                    has_more: false,
                    next_before_id: null,
                    limit: 20,
                },
                loadingOlderMessages: false,
                searchKeyword: '',
                lastTimelineMessageAt: null,
                activeReactionCategory: 'smile',
                activeStickerCategory: 'popular',
                cameraStream: null,
                voiceRecorder: null,
                voiceChunks: [],
                voiceStream: null,
                isRecordingVoice: false,
                voiceShouldSend: true,
                zegoCallInstance: null,
                zegoCallType: null,
                activeCallRoomId: null,
                incomingCall: null,
                lastCallSignalAt: null,
                lastTypingSignalAt: null,
                unsubscribeRealtime: null,
                pollingTimer: null,
                callPollingTimer: null,
            };

            let localMessageSequence = 0;
            let typingStopTimer = null;
            let typingStatusResetTimer = null;
            let typingLastSentState = false;
            let typingLastSentAt = 0;
            const typingPreviewByConversation = new Map();
            const typingPreviewResetTimers = new Map();

            const MESSAGE_PAGE_SIZE = 20;
            const TYPING_IDLE_MS = 1400;
            const TYPING_STATUS_RESET_MS = 2600;
            const TYPING_THROTTLE_MS = 900;

            const usePublicRealtimeChannel = window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                state.testMode;

            const zegoConfig = {
                appId: Number(app.dataset.zegoAppId) || 0,
                serverSecret: String(app.dataset.zegoServerSecret || '').trim(),
            };

            const CHAT_QUICK_REACTION_STORAGE_KEY = 'ig_chat_quick_reaction';
            const CHAT_SYNC_STORAGE_KEY = 'ig_chat_sync_event';
            const CHAT_CALL_SYNC_STORAGE_KEY = 'ig_chat_call_sync_event';
            const STICKER_TOKEN_PREFIX = '[STICKER:';
            const STICKER_TOKEN_SUFFIX = ']';

            async function ensureZegoSdkLoaded() {
                if (window.ZegoUIKitPrebuilt) {
                    return window.ZegoUIKitPrebuilt;
                }

                const candidates = [
                    'https://unpkg.com/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js',
                    'https://cdn.jsdelivr.net/npm/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js',
                    'https://unpkg.com/zego-uikit-prebuilt/zego-uikit-prebuilt.js',
                ];

                for (const src of candidates) {
                    try {
                        await new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = src;
                            script.async = true;
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });

                        if (window.ZegoUIKitPrebuilt) {
                            return window.ZegoUIKitPrebuilt;
                        }
                    } catch (error) {
                        // Try the next CDN candidate.
                    }
                }

                throw new Error('ZEGO_SDK_LOAD_FAILED');
            }

            function buildZegoRoomId() {
                const conversationId = Number(state.activeConversationId) || 0;
                return `room_${conversationId}`;
            }

            function buildZegoUserId() {
                const source = Number(state.senderId) || Number(state.currentUserId) || Date.now();
                return `user_${source}`;
            }

            function buildZegoUserName() {
                const provided = String(app.dataset.senderName || '').trim();
                return provided || `User ${Number(state.senderId) || Number(state.currentUserId) || 0}`;
            }

            function setCallButtonsDisabled(disabled) {
                if (dom.voiceCallBtn) {
                    dom.voiceCallBtn.disabled = disabled;
                }
                if (dom.videoCallBtn) {
                    dom.videoCallBtn.disabled = disabled;
                }
            }

            function closeIncomingCallModal() {
                state.incomingCall = null;
                if (dom.incomingCallModal) {
                    dom.incomingCallModal.hidden = true;
                }
            }

            function openIncomingCallModal(payload) {
                state.incomingCall = payload || null;
                if (!dom.incomingCallModal || !payload) {
                    return;
                }

                if (dom.incomingCallCaller) {
                    dom.incomingCallCaller.textContent = payload.caller_name || 'Ai đó';
                }
                if (dom.incomingCallType) {
                    dom.incomingCallType.textContent = payload.call_type === 'voice' ? 'Cuộc gọi thoại' : 'Cuộc gọi video';
                }

                dom.incomingCallModal.hidden = false;
            }

            async function sendCallSignal(action, payload = {}) {
                if (!state.activeConversationId || !state.callSignalUrlTemplate) {
                    return;
                }

                const url = state.callSignalUrlTemplate.replace('__ID__', String(state.activeConversationId));
                const body = {
                    action,
                    sender_id: state.senderId,
                    target_user_id: payload.targetUserId || state.activePeerId || null,
                    call_type: payload.callType || state.zegoCallType || 'video',
                    room_id: payload.roomId || state.activeCallRoomId || buildZegoRoomId(),
                    caller_name: payload.callerName || buildZegoUserName(),
                    test_mode: usePublicRealtimeChannel ? 1 : 0,
                };

                try {
                    localStorage.setItem(CHAT_CALL_SYNC_STORAGE_KEY, JSON.stringify({
                        ...body,
                        conversation_id: Number(state.activeConversationId),
                        timestamp: Date.now(),
                    }));

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': state.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(body),
                    });

                    if (!response.ok) {
                        throw new Error('Không thể gửi tín hiệu cuộc gọi.');
                    }

                    const payloadData = await response.json().catch(() => ({}));
                    const signal = payloadData.signal || null;
                    if (signal && String(signal.action || '').toLowerCase() === 'incoming') {
                        state.lastCallSignalAt = signal.created_at || state.lastCallSignalAt;
                    }
                } catch (error) {
                    console.error(error);
                }
            }

            function handleCallSignalEvent(event) {
                const payload = event || {};
                const senderId = Number(payload.sender_id) || 0;
                const targetUserId = Number(payload.target_user_id) || 0;
                const currentUserId = Number(state.senderId) || Number(state.currentUserId) || 0;

                if (!currentUserId || senderId === currentUserId) {
                    return;
                }

                if (targetUserId > 0 && targetUserId !== currentUserId) {
                    return;
                }

                const action = String(payload.action || '').toLowerCase();
                if (!action) return;

                if (action === 'incoming') {
                    if (state.zegoCallInstance) {
                        sendCallSignal('rejected', {
                            targetUserId: senderId,
                            roomId: payload.room_id,
                            callType: payload.call_type,
                        });
                        return;
                    }

                    openIncomingCallModal(payload);
                    return;
                }

                if (action === 'rejected' && state.zegoCallInstance) {
                    alert('Người nhận đã từ chối cuộc gọi.');
                    endZegoCall(false);
                    return;
                }

                if (action === 'ended' && state.zegoCallInstance) {
                    endZegoCall(false);
                }
            }

            window.addEventListener('storage', function(event) {
                if (event.key !== CHAT_CALL_SYNC_STORAGE_KEY || !event.newValue) {
                    return;
                }

                try {
                    const payload = JSON.parse(event.newValue);
                    if (!payload || Number(payload.conversation_id) !== Number(state.activeConversationId)) {
                        return;
                    }

                    handleCallSignalEvent({
                        conversation_id: payload.conversation_id,
                        sender_id: payload.sender_id,
                        target_user_id: payload.target_user_id,
                        action: payload.action,
                        call_type: payload.call_type,
                        room_id: payload.room_id,
                        caller_name: payload.caller_name,
                        test_mode: payload.test_mode,
                    });
                } catch (error) {
                    console.error(error);
                }
            });

            async function pollLatestCallSignal() {
                if (!state.activeConversationId || !state.callSignalLatestUrlTemplate) {
                    return;
                }

                try {
                    const url = `${state.callSignalLatestUrlTemplate.replace('__ID__', String(state.activeConversationId))}?sender_id=${encodeURIComponent(String(state.senderId))}`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const signal = payload?.signal || null;
                    if (!signal) return;

                    const signalAt = String(signal.created_at || '');
                    if (signalAt && signalAt === state.lastCallSignalAt) {
                        return;
                    }

                    state.lastCallSignalAt = signalAt || state.lastCallSignalAt;
                    handleCallSignalEvent(signal);
                } catch (error) {
                    console.error(error);
                }
            }

            async function pollLatestTypingStatus() {
                if (!state.activeConversationId || !state.typingLatestUrlTemplate) {
                    return;
                }

                try {
                    const url = new URL(state.typingLatestUrlTemplate.replace('__ID__', String(state.activeConversationId)), window.location.origin);
                    url.searchParams.set('sender_id', String(state.senderId));
                    if (usePublicRealtimeChannel) {
                        url.searchParams.set('test_mode', '1');
                    }

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const typing = payload?.typing || null;
                    if (!typing) {
                        resetRemoteTypingIndicator(state.activeConversationId);
                        return;
                    }

                    const typingAt = String(typing.created_at || '');
                    if (typingAt && typingAt === state.lastTypingSignalAt) {
                        return;
                    }

                    state.lastTypingSignalAt = typingAt || state.lastTypingSignalAt;

                    if (Boolean(typing.is_typing)) {
                        showRemoteTypingIndicator(String(typing.sender_name || ''), Number(typing.conversation_id) || state.activeConversationId);
                        return;
                    }

                    resetRemoteTypingIndicator(Number(typing.conversation_id) || state.activeConversationId);
                } catch (error) {
                    console.error(error);
                }
            }

            function endZegoCall(notifyRemote = false) {
                if (notifyRemote && state.activeConversationId && state.activePeerId) {
                    sendCallSignal('ended', {
                        targetUserId: state.activePeerId,
                    });
                }

                if (state.zegoCallInstance && typeof state.zegoCallInstance.destroy === 'function') {
                    state.zegoCallInstance.destroy();
                }

                state.zegoCallInstance = null;
                state.zegoCallType = null;
                state.activeCallRoomId = null;

                if (dom.zegoCallContainer) {
                    dom.zegoCallContainer.innerHTML = '';
                }
                if (dom.zegoCallModal) {
                    dom.zegoCallModal.hidden = true;
                }

                closeIncomingCallModal();

                setCallButtonsDisabled(false);
            }

            async function startZegoCall(callType = 'video', options = {}) {
                if (!state.activeConversationId) {
                    alert('Hãy chọn một cuộc trò chuyện trước khi gọi.');
                    return;
                }

                let zegoUIKit = window.ZegoUIKitPrebuilt;

                if (!zegoUIKit) {
                    try {
                        zegoUIKit = await ensureZegoSdkLoaded();
                    } catch (error) {
                        alert('Không tải được SDK ZegoCloud. Kiểm tra mạng hoặc chặn CDN rồi thử lại.');
                        return;
                    }
                }

                if (!zegoConfig.appId || !zegoConfig.serverSecret) {
                    alert('Thiếu cấu hình ZEGO_APP_ID hoặc ZEGO_SERVER_SECRET.');
                    return;
                }

                const roomId = String(options.roomId || buildZegoRoomId());
                const userId = buildZegoUserId();
                const userName = buildZegoUserName();

                if (!roomId || roomId === 'room_0') {
                    alert('Không tìm thấy conversation hợp lệ để tạo phòng gọi.');
                    return;
                }

                endZegoCall(false);
                setCallButtonsDisabled(true);

                try {
                    const kitToken = window.ZegoUIKitPrebuilt.generateKitTokenForTest(
                        zegoConfig.appId,
                        zegoConfig.serverSecret,
                        roomId,
                        userId,
                        userName
                    );

                    state.zegoCallInstance = zegoUIKit.create(kitToken);
                    state.zegoCallType = callType;
                    state.activeCallRoomId = roomId;

                    if (!options.skipSignal && state.activePeerId) {
                        sendCallSignal('incoming', {
                            targetUserId: state.activePeerId,
                            roomId,
                            callType,
                        });
                    }

                    if (dom.zegoCallTitle) {
                        dom.zegoCallTitle.textContent = callType === 'video' ? 'Cuộc gọi video' : 'Cuộc gọi thoại';
                    }
                    if (dom.zegoRoomLabel) {
                        dom.zegoRoomLabel.textContent = `Phong: ${roomId}`;
                    }
                    if (dom.zegoCallModal) {
                        dom.zegoCallModal.hidden = false;
                    }

                    state.zegoCallInstance.joinRoom({
                        container: dom.zegoCallContainer,
                        scenario: {
                            mode: zegoUIKit.OneONoneCall,
                        },
                        turnOnMicrophoneWhenJoining: true,
                        turnOnCameraWhenJoining: callType === 'video',
                        showPreJoinView: false,
                        showScreenSharingButton: false,
                        showRoomDetailsButton: false,
                        maxUsers: 2,
                        onLeaveRoom: () => {
                            endZegoCall(false);
                        },
                    });
                } catch (error) {
                    console.error(error);
                    endZegoCall(false);
                    alert('Không thể bắt đầu cuộc gọi ZegoCloud. Vui lòng thử lại.');
                }
            }

            function stopVoiceStreamTracks() {
                if (state.voiceStream) {
                    state.voiceStream.getTracks().forEach((track) => track.stop());
                    state.voiceStream = null;
                }
            }

            function resetVoiceRecordingState() {
                state.voiceRecorder = null;
                state.voiceChunks = [];
                state.isRecordingVoice = false;
                state.voiceShouldSend = true;
                stopVoiceStreamTracks();

                if (dom.voiceMessageBtn) {
                    dom.voiceMessageBtn.classList.remove('is-recording');
                    dom.voiceMessageBtn.setAttribute('aria-label', 'Voice');
                }
            }

            async function sendAudioMessage(file) {
                if (!state.activeConversationId || !file) return;

                const formData = new FormData();
                formData.append('sender_id', String(state.senderId));
                formData.append('test_mode', '1');
                formData.append('type', 'audio');
                formData.append('body', '');
                formData.append('audio', file);

                dom.sendButton.disabled = true;
                if (dom.voiceMessageBtn) dom.voiceMessageBtn.disabled = true;

                try {
                    const response = await fetch(state.messagesUrlTemplate.replace('__ID__', String(state.activeConversationId)), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': state.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        const payload = await response.json().catch(() => ({}));
                        const firstValidationError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : null;
                        throw new Error(firstValidationError || payload.message || 'Không gửi được tin nhắn thoại');
                    }

                    const message = await response.json();
                    renderMessage(message, true);
                    broadcastChatSync('message-created', message);
                    updateSendButton();

                    const currentConversation = state.conversations.find((c) => Number(c.id) === Number(state.activeConversationId));
                    if (currentConversation) {
                        currentConversation.last_message = getMessagePreview(message);
                        currentConversation.last_message_at = new Date().toISOString();
                        state.conversations.sort((a, b) => new Date(b.last_message_at || 0) - new Date(a.last_message_at || 0));
                        renderConversationList();
                    }
                } catch (error) {
                    console.error(error);
                    alert(error.message || 'Không gửi được tin nhắn thoại.');
                } finally {
                    dom.sendButton.disabled = false;
                    if (dom.voiceMessageBtn) dom.voiceMessageBtn.disabled = false;
                }
            }

            async function startVoiceRecording() {
                if (!state.activeConversationId) {
                    alert('Hãy chọn một cuộc trò chuyện trước khi gửi tin nhắn thoại.');
                    return;
                }

                if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function' || typeof window.MediaRecorder === 'undefined') {
                    alert('Trình duyệt không hỗ trợ ghi âm tin nhắn thoại.');
                    return;
                }

                const isSecureOrigin = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                if (!isSecureOrigin) {
                    alert('Ghi âm chỉ hoạt động trên HTTPS hoặc localhost.');
                    return;
                }

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });

                    const preferredMimeTypes = [
                        'audio/webm;codecs=opus',
                        'audio/webm',
                        'audio/ogg;codecs=opus',
                        'audio/mp4',
                    ];

                    let recorder = null;
                    for (const mimeType of preferredMimeTypes) {
                        if (window.MediaRecorder.isTypeSupported(mimeType)) {
                            recorder = new window.MediaRecorder(stream, {
                                mimeType
                            });
                            break;
                        }
                    }

                    if (!recorder) {
                        recorder = new window.MediaRecorder(stream);
                    }

                    state.voiceStream = stream;
                    state.voiceRecorder = recorder;
                    state.voiceChunks = [];
                    state.voiceShouldSend = true;

                    recorder.ondataavailable = function(event) {
                        if (event.data && event.data.size > 0) {
                            state.voiceChunks.push(event.data);
                        }
                    };

                    recorder.onstop = async function() {
                        const chunks = state.voiceChunks.slice();
                        const shouldSend = state.voiceShouldSend;
                        resetVoiceRecordingState();

                        if (!shouldSend || !chunks.length) {
                            return;
                        }

                        const mimeType = chunks[0].type || 'audio/webm';
                        const blob = new Blob(chunks, {
                            type: mimeType
                        });
                        const extension = mimeType.includes('ogg') ? 'ogg' : (mimeType.includes('mp4') ? 'm4a' : 'webm');
                        const file = new File([blob], `voice-${Date.now()}.${extension}`, {
                            type: mimeType
                        });

                        await sendAudioMessage(file);
                    };

                    recorder.start(250);
                    state.isRecordingVoice = true;

                    if (dom.voiceMessageBtn) {
                        dom.voiceMessageBtn.classList.add('is-recording');
                        dom.voiceMessageBtn.setAttribute('aria-label', 'Dừng ghi âm và gửi');
                    }
                } catch (error) {
                    console.error(error);
                    resetVoiceRecordingState();
                    alert('Không thể truy cập micro. Vui lòng cấp quyền micro và thử lại.');
                }
            }

            function stopVoiceRecording(send = true) {
                state.voiceShouldSend = send;

                if (state.voiceRecorder && state.voiceRecorder.state === 'recording') {
                    state.voiceRecorder.stop();
                    return;
                }

                resetVoiceRecordingState();
            }

            function broadcastChatSync(action, message = null) {
                if (!state.testMode || !state.activeConversationId) {
                    return;
                }

                localStorage.setItem(CHAT_SYNC_STORAGE_KEY, JSON.stringify({
                    action,
                    conversationId: Number(state.activeConversationId),
                    senderId: Number(state.senderId),
                    messageId: message && message.id ? Number(message.id) : null,
                    timestamp: Date.now(),
                }));
            }

            window.addEventListener('storage', function(event) {
                if (event.key !== CHAT_SYNC_STORAGE_KEY || !event.newValue) {
                    return;
                }

                if (!state.testMode) {
                    return;
                }

                try {
                    const payload = JSON.parse(event.newValue);
                    if (!payload || Number(payload.conversationId) !== Number(state.activeConversationId)) {
                        return;
                    }

                    if (Number(payload.senderId) === Number(state.senderId)) {
                        return;
                    }

                    reloadActiveConversation();
                } catch (error) {
                    console.error(error);
                }
            });

            const STICKER_CATALOG = {
                'good-morning': {
                    label: 'Good morning',
                    emoji: '☀️',
                    caption: 'GOOD MORNING',
                    bgStart: '#fff5d9',
                    bgEnd: '#ffd48f',
                },
                'thursday': {
                    label: 'Thursday',
                    emoji: '📅',
                    caption: 'THURSDAY',
                    bgStart: '#eef3ff',
                    bgEnd: '#c9d8ff',
                },
                'birthday': {
                    label: 'Happy birthday',
                    emoji: '🎂',
                    caption: 'HAPPY BIRTHDAY',
                    bgStart: '#ffeef9',
                    bgEnd: '#ffd0ea',
                },
                'love-note': {
                    label: 'Love note',
                    emoji: '💌',
                    caption: 'LOVE YOU',
                    bgStart: '#fff1f0',
                    bgEnd: '#ffd8d4',
                },
                'thanks': {
                    label: 'Cam on',
                    emoji: '🙏',
                    caption: 'CAM ON',
                    bgStart: '#ecfbff',
                    bgEnd: '#cdeeff',
                },
                'haha': {
                    label: 'Haha',
                    emoji: '😂',
                    caption: 'HAHA',
                    bgStart: '#fff8df',
                    bgEnd: '#ffe59f',
                },
                'sleepy-dog': {
                    label: 'Sleepy dog',
                    emoji: '🐶',
                    caption: 'ZZZ',
                    bgStart: '#f3f5f8',
                    bgEnd: '#d9dde5',
                },
                'cool-cat': {
                    label: 'Cool cat',
                    emoji: '🐱',
                    caption: 'COOL',
                    bgStart: '#f5f0ff',
                    bgEnd: '#e0d4ff',
                },
                'balloons': {
                    label: 'Balloons',
                    emoji: '🎈',
                    caption: 'YAY',
                    bgStart: '#f0f9ff',
                    bgEnd: '#d2edff',
                },
                'flower-smile': {
                    label: 'Flower smile',
                    emoji: '🌸',
                    caption: 'SMILE',
                    bgStart: '#fff1fa',
                    bgEnd: '#ffd9ef',
                },
                'party-time': {
                    label: 'Party time',
                    emoji: '🎉',
                    caption: 'PARTY',
                    bgStart: '#e9fff5',
                    bgEnd: '#caf7e4',
                },
                'sunny-day': {
                    label: 'Sunny day',
                    emoji: '🌞',
                    caption: 'SUNNY DAY',
                    bgStart: '#fff9db',
                    bgEnd: '#ffe49b',
                },
            };

            const LEGACY_STICKER_BODY_MAP = {
                '[Sticker - Heart]': 'love-note',
                '[Sticker - Smile]': 'haha',
                '[Sticker - Fun]': 'party-time',
                '[Sticker - Flower]': 'flower-smile',
                '[Sticker - Dog]': 'sleepy-dog',
                '[Sticker - Cat]': 'cool-cat',
            };

            function buildStickerDataUrl(sticker) {
                const svg = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320">
                    <defs>
                        <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="${sticker.bgStart}"/>
                            <stop offset="100%" stop-color="${sticker.bgEnd}"/>
                        </linearGradient>
                    </defs>
                    <rect x="6" y="6" width="308" height="308" rx="42" fill="url(#g)"/>
                    <text x="160" y="168" text-anchor="middle" font-size="112">${sticker.emoji}</text>
                    <text x="160" y="285" text-anchor="middle" font-size="28" font-weight="700" font-family="Segoe UI, Arial, sans-serif" fill="#24324a">${sticker.caption}</text>
                </svg>
            `;

                return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
            }

            function getStickerToken(stickerId) {
                return `${STICKER_TOKEN_PREFIX}${stickerId}${STICKER_TOKEN_SUFFIX}`;
            }

            function parseStickerIdFromBody(body) {
                const normalizedBody = String(body || '').trim();
                if (!normalizedBody) return null;

                const legacy = LEGACY_STICKER_BODY_MAP[normalizedBody];
                if (legacy) {
                    return legacy;
                }

                const match = normalizedBody.match(/^\[STICKER:([a-z0-9_-]+)\]$/i);
                if (!match) return null;

                const stickerId = String(match[1] || '').toLowerCase();
                return STICKER_CATALOG[stickerId] ? stickerId : null;
            }

            function initStickerGallery() {
                Object.values(STICKER_CATALOG).forEach((sticker) => {
                    sticker.url = buildStickerDataUrl(sticker);
                });

                dom.stickerItems.forEach((item) => {
                    const stickerId = String(item.dataset.stickerId || '').trim();
                    const sticker = STICKER_CATALOG[stickerId];
                    if (!sticker) return;

                    const fallbackKeyword = item.dataset.keyword || '';
                    item.dataset.keyword = `${fallbackKeyword} ${sticker.label.toLowerCase()} ${sticker.caption.toLowerCase()}`.trim();
                    item.setAttribute('aria-label', sticker.label);
                    item.style.setProperty('--sticker-bg', `linear-gradient(135deg, ${sticker.bgStart} 0%, ${sticker.bgEnd} 100%)`);
                    item.innerHTML = `<img class="ig-sticker-thumb" src="${sticker.url}" alt="${escapeHtml(sticker.label)}">`;
                });
            }

            function resolveConversationBackgroundUrl(rawUrl) {
                const value = String(rawUrl || '').trim();
                if (!value) {
                    return '';
                }

                if (/^(https?:)?\/\//i.test(value) || value.startsWith('data:') || value.startsWith('blob:')) {
                    return value;
                }

                const normalizedPath = value.replace(/\\/g, '/').replace(/^\/+/, '');
                if (!normalizedPath) {
                    return '';
                }

                if (normalizedPath.startsWith('storage/')) {
                    return `/${normalizedPath}`;
                }

                if (normalizedPath.startsWith('chat-backgrounds/')) {
                    return `/storage/${normalizedPath}`;
                }

                return `/${normalizedPath}`;
            }

            function setChatBackgroundImage(imageDataUrl) {
                const resolvedUrl = resolveConversationBackgroundUrl(imageDataUrl);
                if (!resolvedUrl) {
                    dom.chatBox.style.backgroundImage = 'none';
                    dom.chatBox.style.backgroundColor = '#ffffff';
                    dom.chatBox.style.backgroundBlendMode = 'normal';
                    return;
                }

                const safeUrl = resolvedUrl.replace(/"/g, '\\"');
                dom.chatBox.style.backgroundImage = `url("${safeUrl}")`;
                dom.chatBox.style.backgroundColor = '#f4f5f7';
                dom.chatBox.style.backgroundBlendMode = 'normal';
            }

            function setConversationBackground(conversationId, backgroundUrl) {
                const conversation = state.conversations.find((item) => Number(item.id) === Number(conversationId));
                if (!conversation) return;
                conversation.chat_background_url = resolveConversationBackgroundUrl(backgroundUrl) || null;
            }

            function applyActiveConversationBackground() {
                const conversation = state.conversations.find((item) => Number(item.id) === Number(state.activeConversationId));
                setChatBackgroundImage(conversation?.chat_background_url || '');
            }

            async function updateConversationBackground(file = null, shouldClear = false) {
                if (!state.activeConversationId) return;

                const formData = new FormData();
                formData.append('sender_id', String(state.senderId));
                formData.append('test_mode', '1');

                if (shouldClear) {
                    formData.append('clear', '1');
                }

                if (file) {
                    formData.append('image', file);
                }

                const response = await fetch(state.backgroundUrlTemplate.replace('__ID__', String(state.activeConversationId)), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': state.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const payload = await response.json().catch(() => ({}));
                    throw new Error(payload.message || 'Không thể cập nhật ảnh nền cuộc trò chuyện.');
                }

                const payload = await response.json();
                const backgroundUrl = payload?.chat_background_url || null;
                setConversationBackground(state.activeConversationId, backgroundUrl);
                applyActiveConversationBackground();
            }

            function avatarUrl(name) {
                const encoded = encodeURIComponent(name || 'User');
                return `https://ui-avatars.com/api/?name=${encoded}&background=eceef2&color=111827&size=96`;
            }

            function resolveMessageSenderAvatar(message, mine, senderName) {
                if (mine) {
                    const currentUserAvatar = document.getElementById('ig-current-user-avatar');
                    const ownAvatar = String(currentUserAvatar?.getAttribute('src') || '').trim();
                    return ownAvatar || avatarUrl(senderName || app.dataset.senderName || 'You');
                }

                const senderAvatar = String(
                    message?.sender?.avatar_url ||
                    message?.sender?.avatar ||
                    message?.sender_avatar_url ||
                    message?.sender_avatar ||
                    ''
                ).trim();

                if (senderAvatar) {
                    return senderAvatar;
                }

                const activeAvatar = String(state.activeConversationAvatar || '').trim();
                if (activeAvatar) {
                    return activeAvatar;
                }

                return avatarUrl(senderName || state.activeConversationName);
            }

            function escapeHtml(text) {
                return String(text || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function resolveAttachmentUrl(attachment) {
                const directUrl = String(attachment?.file_url || '').trim();
                if (directUrl) {
                    return directUrl;
                }

                const rawPath = String(attachment?.file_path || '').trim();
                if (!rawPath) {
                    return '';
                }

                if (/^(https?:)?\/\//i.test(rawPath) || rawPath.startsWith('data:') || rawPath.startsWith('blob:')) {
                    return rawPath;
                }

                const normalizedPath = rawPath.replace(/\\/g, '/').replace(/^\/+/, '');
                if (!normalizedPath) {
                    return '';
                }

                if (normalizedPath.startsWith('storage/')) {
                    return `/${normalizedPath}`;
                }

                return `/storage/${normalizedPath}`;
            }

            function formatShortTime(isoTime) {
                if (!isoTime) return '';
                const value = new Date(isoTime);
                if (Number.isNaN(value.getTime())) return '';
                return value.toLocaleTimeString('vi-VN', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            const TIMELINE_STAMP_GAP_MINUTES = 5;

            function parseTimelineTime(isoTime) {
                if (!isoTime) return null;
                const value = new Date(isoTime).getTime();
                return Number.isNaN(value) ? null : value;
            }

            function shouldShowTimelineStamp(currentIsoTime, previousIsoTime) {
                const currentTime = parseTimelineTime(currentIsoTime);
                if (currentTime === null) return false;

                const previousTime = parseTimelineTime(previousIsoTime);
                if (previousTime === null) return true;

                return (currentTime - previousTime) >= (TIMELINE_STAMP_GAP_MINUTES * 60 * 1000);
            }

            function formatTimelineStamp(isoTime) {
                if (!isoTime) return '';
                const value = new Date(isoTime);
                if (Number.isNaN(value.getTime())) return '';

                const hour = String(value.getHours()).padStart(2, '0');
                const minute = String(value.getMinutes()).padStart(2, '0');
                const day = String(value.getDate()).padStart(2, '0');
                const month = String(value.getMonth() + 1).padStart(2, '0');
                const year = value.getFullYear();

                return `${hour}:${minute} ${day}/${month}/${year}`;
            }

            function formatSidebarTime(isoTime) {
                if (!isoTime) return '';
                const value = new Date(isoTime);
                if (Number.isNaN(value.getTime())) return '';
                const diffMinutes = Math.floor((Date.now() - value.getTime()) / 60000);
                if (diffMinutes < 1) return 'Vừa xong';
                if (diffMinutes < 60) return `${diffMinutes} phút`;
                if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)} giờ`;
                return `${Math.floor(diffMinutes / 1440)} ngày`;
            }

            function positionMediaPicker(picker, anchorButton = null) {
                if (!picker || !dom.form) return;

                const formRect = dom.form.getBoundingClientRect();
                const pickerWidth = picker.getBoundingClientRect().width || 360;
                let left = (formRect.width - pickerWidth) / 2;

                if (anchorButton) {
                    const anchorRect = anchorButton.getBoundingClientRect();
                    left = (anchorRect.left - formRect.left) + (anchorRect.width / 2) - (pickerWidth / 2);
                }

                const minLeft = 8;
                const maxLeft = Math.max(minLeft, formRect.width - pickerWidth - 8);
                left = Math.max(minLeft, Math.min(left, maxLeft));
                picker.style.left = `${left}px`;
            }

            function toggleMediaPicker(picker, searchInput, anchorButton = null) {
                if (!picker) return;
                const opening = picker.hidden;
                picker.hidden = !picker.hidden;
                if (opening) {
                    positionMediaPicker(picker, anchorButton);
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                    }
                }
            }

            function closeAttachmentPicker() {
                if (dom.chatAttachmentPicker) {
                    dom.chatAttachmentPicker.hidden = true;
                }
            }

            function toggleAttachmentPicker() {
                if (!dom.chatAttachmentPicker) return;
                const opening = dom.chatAttachmentPicker.hidden;
                closeReactionPicker();
                if (dom.stickerPicker) dom.stickerPicker.hidden = true;
                dom.chatAttachmentPicker.hidden = !dom.chatAttachmentPicker.hidden;
                if (opening) {
                    dom.chatAttachmentFileBtn?.focus();
                }
            }

            function setMediaCategory(category, tabs, items) {
                state.activeStickerCategory = tabs[0]?.dataset.category || 'popular';

                tabs.forEach((tab) => {
                    const tabCategory = tab.dataset.category;
                    tab.classList.toggle('is-active', false);
                });
                if (tabs[0]) tabs[0].classList.add('is-active');

                filterMediaItems(items, '', tabs[0]?.dataset.category || 'popular');
            }

            function filterMediaItems(items, keyword, category = null) {
                const value = (keyword || '').trim().toLowerCase();
                items.forEach((item) => {
                    const text = item.textContent || '';
                    const kw = item.dataset.keyword || '';
                    const label = item.getAttribute('aria-label') || '';
                    const itemCategories = (item.dataset.category || '').split(',');
                    const keywordMatch = !value || text.toLowerCase().includes(value) || kw.includes(value) || label.toLowerCase().includes(value);
                    const categoryMatch = !category || itemCategories.includes(category);
                    const visible = keywordMatch && categoryMatch;
                    item.hidden = !visible;
                });
            }

            function getMessagesUrl(conversationId) {
                const base = state.messagesUrlTemplate.replace('__ID__', conversationId);
                const query = new URLSearchParams({
                    sender_id: String(state.senderId),
                    test_mode: '1'
                });
                return `${base}?${query.toString()}`;
            }

            function isMyMessage(message) {
                const senderId = Number(message.sender_id) || 0;
                if (senderId === 0) return false;
                return state.testMode ? senderId === state.senderId : senderId === authUserId;
            }

            function conversationPreview(item) {
                const conversationId = Number(item?.id) || 0;
                const typingState = typingPreviewByConversation.get(conversationId) || null;
                if (typingState && Boolean(typingState.is_typing)) {
                    const typingName = String(typingState.sender_name || item?.name || 'Người dùng').trim();
                    return `${typingName} đang soạn tin...`;
                }

                const baseText = item.last_message || 'Bắt đầu cuộc trò chuyện';
                const senderId = Number(item.last_message_sender_id) || 0;
                const text = senderId > 0 && senderId === Number(state.senderId) ? `Bạn: ${baseText}` : baseText;
                const time = formatSidebarTime(item.last_message_at);
                return time ? `${text} · ${time}` : text;
            }

            function setConversationTypingPreview(conversationId, isTyping, senderDisplayName = '') {
                const targetId = Number(conversationId) || 0;
                if (!targetId) {
                    return;
                }

                const existingTimer = typingPreviewResetTimers.get(targetId);
                if (existingTimer) {
                    clearTimeout(existingTimer);
                    typingPreviewResetTimers.delete(targetId);
                }

                if (Boolean(isTyping)) {
                    typingPreviewByConversation.set(targetId, {
                        is_typing: true,
                        sender_name: String(senderDisplayName || '').trim(),
                        updated_at: Date.now(),
                    });

                    const resetTimer = setTimeout(function() {
                        typingPreviewByConversation.delete(targetId);
                        typingPreviewResetTimers.delete(targetId);
                        renderConversationList();
                    }, TYPING_STATUS_RESET_MS);
                    typingPreviewResetTimers.set(targetId, resetTimer);
                } else {
                    typingPreviewByConversation.delete(targetId);
                }

                const isActiveThread = Number(state.activeConversationId) === targetId;
                if (isActiveThread && dom.typingIndicator) {
                    const showTyping = Boolean(isTyping);
                    dom.typingIndicator.hidden = !showTyping;
                    if (showTyping) {
                        // Keep typing bubble at the visual bottom even after new messages append.
                        if (dom.typingIndicator.parentElement === dom.chatBox) {
                            dom.chatBox.appendChild(dom.typingIndicator);
                        }
                        scrollToLatestMessage();
                    }
                }

                renderConversationList();
            }

            function updateSendButton() {
                const hasText = dom.input.value.trim().length > 0;
                dom.form.classList.toggle('is-empty', !hasText);
                dom.sendButtonLabel.textContent = hasText ? 'Gửi' : '';
                dom.sendButtonReaction.textContent = state.quickReaction;
                dom.sendButton.setAttribute('aria-label', hasText ? 'Gửi tin nhắn' : `Gửi nhanh ${state.quickReaction}`);
            }

            function setQuickReaction(reaction) {
                state.quickReaction = reaction || '❤️';
                localStorage.setItem(CHAT_QUICK_REACTION_STORAGE_KEY, state.quickReaction);

                dom.reactionOptions.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.reaction === state.quickReaction);
                });

                updateSendButton();
            }

            function closeReactionPicker() {
                if (dom.reactionPicker) {
                    dom.reactionPicker.hidden = true;
                }
            }

            function positionReactionPicker() {
                if (!dom.reactionPicker || !dom.reactionPickerToggle || !dom.form) return;

                const formRect = dom.form.getBoundingClientRect();
                const toggleRect = dom.reactionPickerToggle.getBoundingClientRect();
                const pickerWidth = dom.reactionPicker.getBoundingClientRect().width || 300;

                let left = (toggleRect.left - formRect.left) + (toggleRect.width / 2) - (pickerWidth / 2);
                const minLeft = 8;
                const maxLeft = Math.max(minLeft, formRect.width - pickerWidth - 8);
                left = Math.min(maxLeft, Math.max(minLeft, left));

                dom.reactionPicker.style.left = `${left}px`;
            }

            function toggleReactionPicker() {
                if (!dom.reactionPicker) return;
                const opening = dom.reactionPicker.hidden;
                dom.reactionPicker.hidden = !dom.reactionPicker.hidden;
                if (opening) {
                    positionReactionPicker();
                }

                if (!dom.reactionPicker.hidden && dom.reactionSearchInput) {
                    dom.reactionSearchInput.value = '';
                    filterReactionOptions('', state.activeReactionCategory);
                    dom.reactionSearchInput.focus();
                }
            }

            function setReactionCategory(category) {
                state.activeReactionCategory = category || 'smile';
                dom.reactionTabs.forEach((tab) => {
                    tab.classList.toggle('is-active', tab.dataset.category === state.activeReactionCategory);
                });
                filterReactionOptions(dom.reactionSearchInput?.value || '', state.activeReactionCategory);
            }

            function filterReactionOptions(keyword, category = state.activeReactionCategory) {
                const value = (keyword || '').trim();
                dom.reactionOptions.forEach((button) => {
                    const reaction = button.dataset.reaction || '';
                    const label = button.getAttribute('aria-label') || '';
                    const itemCategory = button.dataset.category || 'smile';
                    const categoryMatch = category === 'all' || itemCategory === category;
                    const keywordMatch = !value || reaction.includes(value) || label.toLowerCase().includes(value.toLowerCase());
                    const visible = categoryMatch && keywordMatch;
                    button.hidden = !visible;
                });
            }

            function renderConversationList() {
                const filtered = state.conversations.filter((conversation) => {
                    if (!state.searchKeyword) return true;
                    const haystack = `${conversation.name || ''} ${conversation.email || ''}`.toLowerCase();
                    return haystack.includes(state.searchKeyword);
                });

                if (!filtered.length) {
                    dom.conversationList.innerHTML = '<p class="ig-empty-note">Không tìm thấy cuộc trò chuyện phù hợp.</p>';
                    return;
                }

                const recentUsers = filtered.filter((conversation) => Boolean(conversation.is_recently_active));
                const otherUsers = filtered.filter((conversation) => !conversation.is_recently_active);

                const renderItem = (conversation) => {
                    const isActive = Number(conversation.id) === Number(state.activeConversationId);
                    const activeDot = conversation.is_recently_active ? '<span class="ig-conversation-active-dot" aria-label="Đang hoạt động"></span>' : '';
                    const peerId = Number(conversation.peer_id) || 0;
                    return `
                    <button class="ig-conversation-item ${isActive ? 'is-active' : ''}" type="button" data-conversation-id="${conversation.id}">
                        <span class="ig-conversation-avatar-wrap">
                            <img class="ig-conversation-avatar" src="${escapeHtml(conversation.avatar)}" alt="${escapeHtml(conversation.name)}" ${peerId ? `data-profile-user-id="${peerId}" title="Xem trang cá nhân"` : ''}>
                            ${activeDot}
                        </span>
                        <div class="ig-conversation-main">
                            <strong>${escapeHtml(conversation.name)}</strong>
                            <span>${escapeHtml(conversationPreview(conversation))}</span>
                        </div>
                    </button>
                `;
                };

                let html = '';
                if (recentUsers.length) {
                    html += '<p class="ig-conversation-section">Hoạt động gần đây</p>';
                    html += recentUsers.map(renderItem).join('');
                }

                if (otherUsers.length) {
                    html += '<p class="ig-conversation-section">Tất cả người dùng</p>';
                    html += otherUsers.map(renderItem).join('');
                }

                dom.conversationList.innerHTML = html;
            }

            function updateThreadIdentity(name, avatar) {
                const handle = String(name || 'vibetalk').toLowerCase().replace(/\s+/g, '');
                state.activeConversationName = name || 'VibeTalk';
                state.activeConversationAvatar = avatar || avatarUrl(state.activeConversationName);
                dom.threadName.textContent = state.activeConversationName;
                dom.threadStatus.textContent = 'Đang hoạt động';
                dom.threadAvatar.src = state.activeConversationAvatar;
                if (state.activePeerId) {
                    dom.threadAvatar.setAttribute('data-profile-user-id', String(state.activePeerId));
                    dom.introAvatar.setAttribute('data-profile-user-id', String(state.activePeerId));
                } else {
                    dom.threadAvatar.removeAttribute('data-profile-user-id');
                    dom.introAvatar.removeAttribute('data-profile-user-id');
                }
                dom.introAvatar.src = state.activeConversationAvatar;
                dom.introName.textContent = state.activeConversationName;
                dom.introHandle.textContent = handle;
            }

            function openProfileByUserId(rawUserId) {
                const userId = Number(rawUserId) || 0;
                if (!userId) {
                    return;
                }

                const profileUrl = String(state.profileUrlTemplate || '/profile/__ID__').replace('__ID__', encodeURIComponent(String(userId)));
                window.location.href = profileUrl;
            }

            function resetRemoteTypingIndicator(conversationId = state.activeConversationId) {
                if (typingStatusResetTimer) {
                    clearTimeout(typingStatusResetTimer);
                    typingStatusResetTimer = null;
                }

                setConversationTypingPreview(conversationId, false);
            }

            function showRemoteTypingIndicator(senderName = '', conversationId = state.activeConversationId) {
                const displayName = String(senderName || state.activeConversationName || 'Người dùng').trim();
                setConversationTypingPreview(conversationId, true, displayName);

                if (typingStatusResetTimer) {
                    clearTimeout(typingStatusResetTimer);
                }

                typingStatusResetTimer = setTimeout(function() {
                    resetRemoteTypingIndicator(conversationId);
                }, TYPING_STATUS_RESET_MS);
            }

            async function sendTypingStatus(isTyping) {
                if (!state.activeConversationId || !state.typingUrlTemplate) {
                    return;
                }

                const now = Date.now();
                if (
                    isTyping &&
                    typingLastSentState === true &&
                    (now - typingLastSentAt) < TYPING_THROTTLE_MS
                ) {
                    return;
                }

                typingLastSentState = Boolean(isTyping);
                typingLastSentAt = now;

                try {
                    await fetch(state.typingUrlTemplate.replace('__ID__', String(state.activeConversationId)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': state.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            sender_id: state.senderId,
                            is_typing: Boolean(isTyping),
                            test_mode: usePublicRealtimeChannel ? 1 : 0,
                        })
                    });
                } catch (error) {
                    console.error(error);
                }
            }

            function scheduleTypingStatusFromInput() {
                if (!state.activeConversationId) {
                    return;
                }

                const hasText = String(dom.input.value || '').trim().length > 0;

                if (hasText) {
                    sendTypingStatus(true);

                    if (typingStopTimer) {
                        clearTimeout(typingStopTimer);
                    }

                    typingStopTimer = setTimeout(function() {
                        sendTypingStatus(false);
                        typingStopTimer = null;
                    }, TYPING_IDLE_MS);
                    return;
                }

                if (typingStopTimer) {
                    clearTimeout(typingStopTimer);
                    typingStopTimer = null;
                }

                sendTypingStatus(false);
            }

            function clearMessages() {
                const rows = dom.chatBox.querySelectorAll('.ig-message-row');
                rows.forEach((row) => row.remove());
                const stamps = dom.chatBox.querySelectorAll('.ig-message-stamp');
                stamps.forEach((stamp) => stamp.remove());
                state.seenMessageIds.clear();
                state.lastTimelineMessageAt = null;
                state.messagesMeta = {
                    has_more: false,
                    next_before_id: null,
                    limit: MESSAGE_PAGE_SIZE,
                };
                state.loadingOlderMessages = false;
                if (dom.typingIndicator) {
                    dom.typingIndicator.hidden = true;
                }
                dom.intro.style.display = 'block';
            }

            function getCachedConversation(conversationId) {
                const key = String(Number(conversationId) || 0);
                return key !== '0' ? (state.messageCache[key] || null) : null;
            }

            function setCachedConversation(conversationId, payload) {
                const key = String(Number(conversationId) || 0);
                if (key === '0') {
                    return;
                }

                state.messageCache[key] = {
                    messages: Array.isArray(payload?.messages) ? payload.messages.slice() : [],
                    meta: payload?.meta && typeof payload.meta === 'object' ? {
                        has_more: Boolean(payload.meta.has_more),
                        next_before_id: Number(payload.meta.next_before_id) || null,
                        limit: Number(payload.meta.limit) || MESSAGE_PAGE_SIZE,
                    } : {
                        has_more: false,
                        next_before_id: null,
                        limit: MESSAGE_PAGE_SIZE,
                    },
                    updated_at: new Date().toISOString(),
                };
            }

            function appendMessageToCache(conversationId, message) {
                const cached = getCachedConversation(conversationId);
                if (!cached || !message) {
                    return;
                }

                const messageId = Number(message.id) || 0;
                if (messageId && cached.messages.some((item) => Number(item?.id) === messageId)) {
                    return;
                }

                cached.messages = cached.messages.concat([message]);
                cached.meta = cached.meta || {
                    has_more: false,
                    next_before_id: null,
                    limit: MESSAGE_PAGE_SIZE,
                };
                cached.updated_at = new Date().toISOString();
            }

            function prependMessagesToCache(conversationId, messages, meta = null) {
                const cached = getCachedConversation(conversationId) || {
                    messages: [],
                    meta: {
                        has_more: false,
                        next_before_id: null,
                        limit: MESSAGE_PAGE_SIZE,
                    },
                };

                const existingIds = new Set(cached.messages.map((item) => Number(item?.id) || 0));
                const nextMessages = Array.isArray(messages) ? messages.filter((item) => {
                    const id = Number(item?.id) || 0;
                    return id > 0 && !existingIds.has(id);
                }) : [];

                cached.messages = nextMessages.concat(cached.messages);
                if (meta && typeof meta === 'object') {
                    cached.meta = {
                        has_more: Boolean(meta.has_more),
                        next_before_id: Number(meta.next_before_id) || null,
                        limit: Number(meta.limit) || MESSAGE_PAGE_SIZE,
                    };
                }
                cached.updated_at = new Date().toISOString();
                setCachedConversation(conversationId, cached);
            }

            function renderConversationFromCache(conversationId) {
                const cached = getCachedConversation(conversationId);
                if (!cached) {
                    return false;
                }

                clearMessages();
                state.messagesMeta = cached.meta || {
                    has_more: false,
                    next_before_id: null,
                    limit: MESSAGE_PAGE_SIZE,
                };
                (cached.messages || []).forEach((message) => renderMessage(message, false));
                scrollToLatestMessage();
                return true;
            }

            function scrollToLatestMessage() {
                requestAnimationFrame(function() {
                    dom.chatBox.scrollTop = dom.chatBox.scrollHeight;
                    requestAnimationFrame(function() {
                        dom.chatBox.scrollTop = dom.chatBox.scrollHeight;
                    });
                });
            }

            function getMessagePreview(message) {
                if (message && (message.type === 'recalled' || message.is_recalled)) {
                    return 'Tin nhắn đã được thu hồi';
                }

                const body = String(message.body || '').trim();
                if (parseStickerIdFromBody(body)) {
                    return 'Đã gửi sticker';
                }
                if (body) return body;

                const attachments = Array.isArray(message.attachments) ? message.attachments : [];
                if (attachments.some(isAudioAttachment)) {
                    return 'Đã gửi tin nhắn thoại';
                }

                if (attachments.some((attachment) => String(attachment.mime_type || '').startsWith('image/'))) {
                    return 'Đã gửi 1 ảnh';
                }

                return 'Bắt đầu cuộc trò chuyện';
            }

            function isAudioAttachment(attachment) {
                const mimeType = String(attachment?.mime_type || '').toLowerCase();
                if (mimeType.startsWith('audio/')) {
                    return true;
                }

                const name = String(attachment?.file_name || attachment?.file_path || '').toLowerCase();
                return /\.(webm|ogg|mp3|wav|m4a|aac|mp4)$/i.test(name);
            }

            function formatAudioDuration(seconds) {
                const totalSeconds = Number.isFinite(Number(seconds)) ? Math.max(0, Math.round(Number(seconds))) : 0;
                const minutes = Math.floor(totalSeconds / 60);
                const remainingSeconds = String(totalSeconds % 60).padStart(2, '0');
                return `${minutes}:${remainingSeconds}`;
            }

            function setupAudioBubbles(scope = dom.chatBox) {
                if (!scope) return;

                scope.querySelectorAll('[data-audio-bubble]').forEach((bubble) => {
                    if (bubble.dataset.audioBound === '1') return;
                    bubble.dataset.audioBound = '1';

                    const audio = bubble.querySelector('audio');
                    const toggle = bubble.querySelector('[data-audio-toggle]');
                    const durationLabel = bubble.querySelector('[data-audio-duration]');

                    if (!audio || !toggle || !durationLabel) return;

                    const syncDuration = () => {
                        if (Number.isFinite(audio.duration) && audio.duration > 0) {
                            durationLabel.textContent = formatAudioDuration(audio.duration);
                        }
                    };

                    const syncToggleState = () => {
                        toggle.setAttribute('aria-label', audio.paused ? 'Phát tin nhắn thoại' : 'Tạm dừng tin nhắn thoại');
                        toggle.classList.toggle('is-playing', !audio.paused);
                        toggle.innerHTML = audio.paused ? '<span class="ig-audio-play-icon">▶</span>' : '<span class="ig-audio-play-icon">❚❚</span>';
                    };

                    audio.addEventListener('loadedmetadata', syncDuration);
                    audio.addEventListener('durationchange', syncDuration);
                    audio.addEventListener('play', syncToggleState);
                    audio.addEventListener('pause', syncToggleState);
                    audio.addEventListener('ended', syncToggleState);

                    toggle.addEventListener('click', async function() {
                        if (audio.paused) {
                            scope.querySelectorAll('audio').forEach((otherAudio) => {
                                if (otherAudio !== audio) {
                                    otherAudio.pause();
                                }
                            });

                            try {
                                await audio.play();
                            } catch (error) {
                                console.error(error);
                            }
                        } else {
                            audio.pause();
                        }
                    });

                    syncDuration();
                    syncToggleState();
                });
            }

            function renderMessageAttachments(message) {
                if (message && (message.type === 'recalled' || message.is_recalled)) {
                    return '';
                }

                const attachments = Array.isArray(message.attachments) ? message.attachments : [];
                if (!attachments.length) return '';

                return attachments.map((attachment) => {
                    const mimeType = String(attachment.mime_type || '');
                    const fileUrl = escapeHtml(resolveAttachmentUrl(attachment));
                    const fileName = escapeHtml(attachment.file_name || 'attachment');

                    if (!fileUrl) {
                        return '';
                    }

                    if (mimeType.startsWith('image/')) {
                        return `<img class="ig-message-media" src="${fileUrl}" alt="${fileName}">`;
                    }

                    if (isAudioAttachment(attachment)) {
                        return `
                        <div class="ig-audio-bubble" data-audio-bubble>
                            <button type="button" class="ig-audio-toggle" data-audio-toggle aria-label="Phát tin nhắn thoại">
                                <span class="ig-audio-play-icon">▶</span>
                            </button>
                            <div class="ig-audio-meta">
                                <span class="ig-audio-duration" data-audio-duration>0:00</span>
                            </div>
                            <audio class="ig-message-audio" preload="metadata" src="${fileUrl}"></audio>
                        </div>
                    `;
                    }

                    return `<a class="ig-message-media" href="${fileUrl}" target="_blank" rel="noopener noreferrer">${fileName}</a>`;
                }).join('');
            }

            function renderMessage(message, shouldScroll = true, options = {}) {
                const prepend = Boolean(options?.prepend);
                const tempId = Number(options?.tempId || 0);
                const sendStatus = String(options?.sendStatus || '');
                const isPending = sendStatus === 'pending';
                const isSendError = sendStatus === 'error';
                const messageId = Number(message.id) || 0;
                if (messageId && state.seenMessageIds.has(messageId)) return;
                if (messageId) state.seenMessageIds.add(messageId);

                dom.intro.style.display = 'none';

                const mine = isMyMessage(message);
                const senderName = message.sender?.name || state.activeConversationName;
                const senderAvatar = resolveMessageSenderAvatar(message, mine, senderName);
                const senderId = Number(message?.sender_id || message?.sender?.id || 0) || (mine ? Number(state.senderId) || 0 : Number(state.activePeerId) || 0);
                const isRecalled = Boolean(message && (message.type === 'recalled' || message.is_recalled));
                const stickerId = isRecalled ? null : parseStickerIdFromBody(message.body);
                const sticker = stickerId ? STICKER_CATALOG[stickerId] : null;
                const body = isRecalled ? 'Tin nhắn đã được thu hồi' : (sticker ? '' : escapeHtml(message.body || ''));
                const attachments = renderMessageAttachments(message);
                const imageAttachments = Array.isArray(message.attachments) ?
                    message.attachments.filter((attachment) => String(attachment.mime_type || '').startsWith('image/')) : [];
                const isImageOnly = !body && (imageAttachments.length > 0 || Boolean(sticker));
                const recallButton = mine && messageId && !isRecalled ? `<button type="button" class="ig-message-recall-btn" data-message-recall="${messageId}">Thu hồi</button>` : '';
                const stickerMarkup = sticker ? `<img class="ig-message-media ig-message-sticker" src="${sticker.url}" alt="${escapeHtml(sticker.label)}">` : '';
                const reactionMarkup = message.reaction ? `<span class="ig-message-reaction">${escapeHtml(message.reaction)}</span>` : '';
                const avatarMarkup = !mine ? `<img class="ig-message-avatar" src="${senderAvatar}" alt="${escapeHtml(senderName)}" ${senderId ? `data-profile-user-id="${senderId}" title="Xem trang cá nhân"` : ''}>` : '';
                const sendStatusText = isSendError ? (String(options?.errorMessage || 'Gửi thất bại')) : 'Đang gửi';
                const sendStatusMarkup = (isPending || isSendError) ?
                    `<span class="ig-message-send-status ${isSendError ? 'is-error' : 'is-pending'}" title="${escapeHtml(sendStatusText)}" aria-label="${escapeHtml(sendStatusText)}">${isSendError ? '!' : '...'}</span>` :
                    '';

                const shouldShowStamp = !prepend && shouldShowTimelineStamp(message.created_at, state.lastTimelineMessageAt);
                if (shouldShowStamp) {
                    const stampLabel = formatTimelineStamp(message.created_at);
                    dom.chatBox.insertAdjacentHTML('beforeend', `<div class="ig-message-stamp">${escapeHtml(stampLabel)}</div>`);
                }

                if (!prepend) {
                    state.lastTimelineMessageAt = message.created_at || state.lastTimelineMessageAt;
                }

                const html = `
                <article class="ig-message-row ${mine ? 'me' : 'them'} ${isImageOnly ? 'is-image-only' : ''} ${isRecalled ? 'is-recalled' : ''} ${isPending ? 'is-sending' : ''} ${isSendError ? 'is-send-error' : ''}" data-message-id="${messageId || tempId || ''}" ${tempId ? `data-message-temp-id="${tempId}"` : ''}>
                    ${avatarMarkup}
                    <div class="ig-message-wrapper">
                        ${body ? `<div class="ig-message-content ${isRecalled ? 'is-recalled' : ''}">${body}</div>` : ''}
                        ${stickerMarkup}
                        ${attachments}
                        <div class="ig-message-meta">
                            ${reactionMarkup}
                            ${sendStatusMarkup}
                            ${recallButton}
                        </div>
                    </div>
                </article>
            `;

                dom.chatBox.insertAdjacentHTML(prepend ? 'afterbegin' : 'beforeend', html);
                setupAudioBubbles(dom.chatBox);

                if (shouldScroll && !prepend) {
                    scrollToLatestMessage();
                }
            }

            window.appendMessageToUI = function(message) {
                if (Number(message.conversation_id) !== Number(state.activeConversationId)) {
                    return;
                }
                renderMessage(message, true);
                resetRemoteTypingIndicator();
            };

            function createOptimisticMessagePayload(body, tempId) {
                return {
                    id: tempId,
                    conversation_id: Number(state.activeConversationId),
                    sender_id: Number(state.senderId),
                    body: body,
                    type: 'text',
                    created_at: new Date().toISOString(),
                    sender: {
                        id: Number(state.senderId),
                        name: state.activeConversationName,
                    },
                    attachments: [],
                };
            }

            function findOptimisticMessageRow(tempId) {
                const normalizedTempId = Number(tempId) || 0;
                if (!normalizedTempId) {
                    return null;
                }

                return dom.chatBox.querySelector(`[data-message-temp-id="${normalizedTempId}"]`) ||
                    dom.chatBox.querySelector(`[data-message-id="${normalizedTempId}"]`);
            }

            function finalizeOptimisticMessage(tempId, message) {
                const row = findOptimisticMessageRow(tempId);
                if (!row) {
                    return;
                }

                const realMessageId = Number(message?.id) || 0;
                if (realMessageId > 0) {
                    state.seenMessageIds.add(realMessageId);
                }

                if (realMessageId > 0) {
                    row.dataset.messageId = String(realMessageId);
                }

                row.removeAttribute('data-message-temp-id');
                row.classList.remove('is-sending', 'is-send-error');

                const statusNode = row.querySelector('.ig-message-send-status');
                if (statusNode) {
                    statusNode.remove();
                }
            }

            function markOptimisticMessageError(tempId, errorMessage = 'Gửi thất bại') {
                const row = findOptimisticMessageRow(tempId);
                if (!row) {
                    return;
                }

                row.classList.remove('is-sending');
                row.classList.add('is-send-error');

                let statusNode = row.querySelector('.ig-message-send-status');
                if (!statusNode) {
                    const meta = row.querySelector('.ig-message-meta');
                    if (!meta) {
                        return;
                    }

                    statusNode = document.createElement('span');
                    statusNode.className = 'ig-message-send-status is-error';
                    meta.insertBefore(statusNode, meta.firstChild);
                }

                statusNode.className = 'ig-message-send-status is-error';
                statusNode.textContent = '!';
                statusNode.title = errorMessage;
                statusNode.setAttribute('aria-label', errorMessage);
            }

            window.removeMessageFromUI = function(messageId) {
                const normalizedMessageId = Number(messageId) || 0;
                if (!normalizedMessageId) return;

                const row = dom.chatBox.querySelector(`[data-message-id="${normalizedMessageId}"]`);
                if (!row) return;

                const body = row.querySelector('.ig-message-content');
                if (body) {
                    body.textContent = 'Tin nhắn đã được thu hồi';
                    body.classList.add('is-recalled');
                } else {
                    const wrapper = row.querySelector('.ig-message-wrapper');
                    if (wrapper) {
                        wrapper.insertAdjacentHTML('afterbegin', '<div class="ig-message-content is-recalled">Tin nhắn đã được thu hồi</div>');
                    }
                }

                row.classList.add('is-recalled');

                row.querySelectorAll('.ig-message-media, .ig-message-audio, .ig-audio-bubble').forEach((node) => node.remove());

                const recallButton = row.querySelector('[data-message-recall]');
                if (recallButton) {
                    recallButton.remove();
                }

                row.classList.remove('is-image-only');
            };

            async function fetchConversations() {
                const query = new URLSearchParams({
                    sender_id: String(state.senderId),
                    test_mode: '1'
                });
                const response = await fetch(`${state.conversationsUrl}?${query.toString()}`);
                if (!response.ok) {
                    throw new Error('Không tải được danh sách hội thoại.');
                }
                const items = await response.json();
                const normalized = (Array.isArray(items) ? items : []).map((item) => ({
                    id: Number(item.id),
                    peer_id: Number(item.peer_id) || null,
                    name: item.name || `Conversation ${item.id}`,
                    email: item.email || '',
                    avatar: item.avatar || avatarUrl(item.name || `Conversation ${item.id}`),
                    is_recently_active: Boolean(item.is_recently_active),
                    recent_activity_at: item.recent_activity_at || null,
                    last_message: item.last_message || '',
                    last_message_sender_id: Number(item.last_message_sender_id) || null,
                    last_message_at: item.last_message_at || null,
                    chat_background_url: item.chat_background_url || null,
                }));

                if (!normalized.length && state.defaultConversationId) {
                    normalized.push({
                        id: state.defaultConversationId,
                        peer_id: null,
                        name: 'Cuộc trò chuyện mẫu',
                        email: '',
                        avatar: avatarUrl('Sample Chat'),
                        is_recently_active: false,
                        recent_activity_at: null,
                        last_message: '',
                        last_message_sender_id: null,
                        last_message_at: null,
                        chat_background_url: null,
                    });
                }

                state.conversations = normalized;
                renderConversationList();

                const conversationByPeerId = state.defaultPeerId ?
                    normalized.find((c) => Number(c.peer_id) === Number(state.defaultPeerId)) :
                    null;

                const targetConversationId = state.defaultConversationId && normalized.some((c) => c.id === state.defaultConversationId) ?
                    state.defaultConversationId :
                    (conversationByPeerId?.id || normalized[0]?.id);

                if (targetConversationId) {
                    await selectConversation(targetConversationId);
                }
            }

            async function fetchMessages(conversationId, options = {}) {
                const beforeId = Number(options.beforeId) || 0;
                const url = new URL(getMessagesUrl(conversationId), window.location.origin);
                url.searchParams.set('limit', String(MESSAGE_PAGE_SIZE));
                if (beforeId > 0) {
                    url.searchParams.set('before_id', String(beforeId));
                }

                const response = await fetch(url.toString());
                if (!response.ok) {
                    throw new Error('Không tải được tin nhắn.');
                }
                const payload = await response.json();
                if (Array.isArray(payload)) {
                    return {
                        data: payload,
                        meta: {
                            has_more: false,
                            next_before_id: null,
                            limit: MESSAGE_PAGE_SIZE,
                        },
                    };
                }

                return {
                    data: Array.isArray(payload?.data) ? payload.data : [],
                    meta: {
                        has_more: Boolean(payload?.meta?.has_more),
                        next_before_id: Number(payload?.meta?.next_before_id) || null,
                        limit: Number(payload?.meta?.limit) || MESSAGE_PAGE_SIZE,
                    },
                };
            }

            function refreshConversationPreviewFromMessages(conversationId, messages) {
                const currentConversation = state.conversations.find((item) => Number(item.id) === Number(conversationId));
                if (!currentConversation) return;

                const latestMessage = messages[messages.length - 1] || null;
                currentConversation.last_message = latestMessage ? getMessagePreview(latestMessage) : '';
                currentConversation.last_message_sender_id = latestMessage ? (Number(latestMessage.sender_id) || null) : null;
                currentConversation.last_message_at = latestMessage?.created_at || null;
                renderConversationList();
            }

            function refreshConversationPreview(conversationId, body, messageSenderId = null) {
                const currentConversation = state.conversations.find((item) => Number(item.id) === Number(conversationId));
                if (!currentConversation) {
                    return;
                }

                currentConversation.last_message = String(body || '').trim() || '❤️';
                setConversationTypingPreview(conversationId, false);
                if (messageSenderId !== null) {
                    currentConversation.last_message_sender_id = Number(messageSenderId) || null;
                }
                currentConversation.last_message_at = new Date().toISOString();

                state.conversations.sort((a, b) => new Date(b.last_message_at || 0) - new Date(a.last_message_at || 0));
                renderConversationList();
            }

            async function reloadActiveConversation() {
                if (!state.activeConversationId) return;

                const payload = await fetchMessages(state.activeConversationId);
                const messages = payload.data;
                state.messagesMeta = payload.meta;
                clearMessages();
                messages.forEach((message) => renderMessage(message, false));
                refreshConversationPreviewFromMessages(state.activeConversationId, messages);
                scrollToLatestMessage();
            }

            async function loadOlderMessages() {
                if (!state.activeConversationId || state.loadingOlderMessages) {
                    return;
                }

                if (!state.messagesMeta?.has_more) {
                    return;
                }

                const beforeId = Number(state.messagesMeta?.next_before_id) || 0;
                if (!beforeId) {
                    return;
                }

                state.loadingOlderMessages = true;
                const previousScrollHeight = dom.chatBox.scrollHeight;
                const previousScrollTop = dom.chatBox.scrollTop;

                try {
                    const payload = await fetchMessages(state.activeConversationId, {
                        beforeId,
                    });

                    const olderMessages = Array.isArray(payload.data) ? payload.data : [];
                    state.messagesMeta = payload.meta;

                    if (!olderMessages.length) {
                        return;
                    }

                    for (let index = olderMessages.length - 1; index >= 0; index -= 1) {
                        renderMessage(olderMessages[index], false, {
                            prepend: true,
                        });
                    }

                    requestAnimationFrame(function() {
                        const currentScrollHeight = dom.chatBox.scrollHeight;
                        const deltaHeight = currentScrollHeight - previousScrollHeight;
                        dom.chatBox.scrollTop = previousScrollTop + deltaHeight;
                    });
                } catch (error) {
                    console.error(error);
                } finally {
                    state.loadingOlderMessages = false;
                }
            }

            function handleChatBoxScroll() {
                if (dom.chatBox.scrollTop > 8) {
                    return;
                }

                loadOlderMessages();
            }

            function stopLiveListeners() {
                if (typeof state.unsubscribeRealtime === 'function') {
                    state.unsubscribeRealtime();
                    state.unsubscribeRealtime = null;
                }

                if (typingStopTimer) {
                    clearTimeout(typingStopTimer);
                    typingStopTimer = null;
                }

                if (typingStatusResetTimer) {
                    clearTimeout(typingStatusResetTimer);
                    typingStatusResetTimer = null;
                }

                if (state.pollingTimer) {
                    clearInterval(state.pollingTimer);
                    state.pollingTimer = null;
                }

                if (state.callPollingTimer) {
                    clearInterval(state.callPollingTimer);
                    state.callPollingTimer = null;
                }
            }

            function startLiveListeners(conversationId) {
                stopLiveListeners();

                if (typeof window.subscribeToConversation === 'function') {
                    state.unsubscribeRealtime = window.subscribeToConversation(conversationId, function(message) {
                        window.appendMessageToUI(message);
                    }, usePublicRealtimeChannel);
                }

                if (window.Echo) {
                    const channelName = `chat.${conversationId}`;
                    const channels = [];
                    const primaryChannel = usePublicRealtimeChannel ? window.Echo.channel(channelName) : window.Echo.private(channelName);
                    channels.push(primaryChannel);

                    // Defensive fallback: listen on both channel types to avoid missing events when mode drifts.
                    channels.push(usePublicRealtimeChannel ? window.Echo.private(channelName) : window.Echo.channel(channelName));

                    channels.forEach(function(channel) {
                        channel.listen('.ChatBackgroundChanged', function(event) {
                            const updatedConversationId = Number(event?.conversationId) || 0;
                            if (!updatedConversationId) return;

                            setConversationBackground(updatedConversationId, event?.backgroundUrl || null);

                            if (updatedConversationId === Number(state.activeConversationId)) {
                                applyActiveConversationBackground();
                            }
                        });

                        channel.listen('.CallSignal', function(event) {
                            handleCallSignalEvent(event);
                        });

                        channel.listen('.TypingStatusChanged', function(event) {
                            const eventConversationId = Number(event?.conversation_id) || 0;
                            if (!eventConversationId) {
                                return;
                            }

                            const eventSenderId = Number(event?.sender_id) || 0;
                            if (eventSenderId === Number(state.senderId)) {
                                return;
                            }

                            if (Boolean(event?.is_typing)) {
                                showRemoteTypingIndicator(String(event?.sender_name || ''), eventConversationId);
                                return;
                            }

                            resetRemoteTypingIndicator(eventConversationId);
                        });
                    });
                }

                state.pollingTimer = setInterval(async function() {
                    try {
                        const latestPayload = await fetchMessages(conversationId);
                        const latest = latestPayload.data;
                        latest.forEach((message) => renderMessage(message, true));
                    } catch (error) {
                        console.error(error);
                    }
                }, 3000);

                state.callPollingTimer = setInterval(function() {
                    pollLatestCallSignal();
                    pollLatestTypingStatus();
                }, 1500);

                pollLatestCallSignal();
                pollLatestTypingStatus();
            }

            async function selectConversation(conversationId) {
                state.activeConversationId = Number(conversationId);
                renderConversationList();

                const active = state.conversations.find((item) => Number(item.id) === Number(conversationId));
                state.activePeerId = Number(active?.peer_id) || null;
                updateThreadIdentity(active?.name || 'VibeTalk', active?.avatar || avatarUrl(active?.name || 'VibeTalk'));
                resetRemoteTypingIndicator();
                applyActiveConversationBackground();

                clearMessages();

                try {
                    const payload = await fetchMessages(conversationId);
                    const messages = payload.data;
                    state.messagesMeta = payload.meta;
                    messages.forEach((message) => renderMessage(message, false));
                    scrollToLatestMessage();

                    const typingState = typingPreviewByConversation.get(Number(conversationId)) || null;
                    if (typingState?.is_typing) {
                        setConversationTypingPreview(Number(conversationId), true, String(typingState.sender_name || active?.name || ''));
                    }

                    startLiveListeners(conversationId);
                } catch (error) {
                    dom.chatBox.insertAdjacentHTML('beforeend', '<p class="ig-empty-note">Không thể tải tin nhắn. Vui lòng thử lại.</p>');
                    console.error(error);
                }
            }

            async function sendMessage(event) {
                event.preventDefault();

                if (!state.activeConversationId) return;

                if (state.isRecordingVoice || (state.voiceRecorder && state.voiceRecorder.state === 'recording')) {
                    stopVoiceRecording(true);
                    return;
                }

                const text = dom.input.value.trim();
                const payloadBody = text || state.quickReaction || '❤️';
                const tempId = -((Date.now() * 1000) + (localMessageSequence++ % 1000));
                const optimisticMessage = createOptimisticMessagePayload(payloadBody, tempId);
                let optimisticRendered = false;

                dom.sendButton.disabled = true;

                try {
                    renderMessage(optimisticMessage, true, {
                        tempId: tempId,
                        sendStatus: 'pending',
                    });
                    optimisticRendered = true;

                    dom.input.value = '';
                    updateSendButton();
                    refreshConversationPreview(state.activeConversationId, payloadBody, Number(state.senderId));
                    sendTypingStatus(false);

                    const response = await fetch(state.messagesUrlTemplate.replace('__ID__', String(state.activeConversationId)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': state.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            body: payloadBody,
                            type: 'text',
                            sender_id: state.senderId,
                            test_mode: true,
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Không gửi được tin nhắn');
                    }

                    const message = await response.json();
                    finalizeOptimisticMessage(tempId, message);
                    broadcastChatSync('message-created', message);

                    const currentConversation = state.conversations.find((c) => Number(c.id) === Number(state.activeConversationId));
                    if (currentConversation) {
                        currentConversation.last_message = getMessagePreview(message);
                        currentConversation.last_message_sender_id = Number(state.senderId) || null;
                        currentConversation.last_message_at = message.created_at || new Date().toISOString();
                        state.conversations.sort((a, b) => new Date(b.last_message_at || 0) - new Date(a.last_message_at || 0));
                        renderConversationList();
                    }
                } catch (error) {
                    console.error(error);
                    if (optimisticRendered) {
                        markOptimisticMessageError(tempId, error.message || 'Không thể gửi tin nhắn.');
                    }
                } finally {
                    dom.sendButton.disabled = false;
                }
            }

            async function sendImageMessage(file) {
                if (!state.activeConversationId || !file) return;

                const formData = new FormData();
                const caption = dom.input.value.trim();

                formData.append('sender_id', String(state.senderId));
                formData.append('test_mode', '1');
                formData.append('type', 'image');
                formData.append('body', caption);
                formData.append('image', file);

                dom.sendButton.disabled = true;
                if (dom.chatImagePickerToggle) dom.chatImagePickerToggle.disabled = true;

                try {
                    const response = await fetch(state.messagesUrlTemplate.replace('__ID__', String(state.activeConversationId)), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': state.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('Không gửi được ảnh');
                    }

                    const message = await response.json();
                    renderMessage(message, true);
                    broadcastChatSync('message-created', message);
                    dom.input.value = '';
                    updateSendButton();

                    const currentConversation = state.conversations.find((c) => Number(c.id) === Number(state.activeConversationId));
                    if (currentConversation) {
                        currentConversation.last_message = getMessagePreview(message);
                        currentConversation.last_message_sender_id = Number(state.senderId) || null;
                        currentConversation.last_message_at = new Date().toISOString();
                        state.conversations.sort((a, b) => new Date(b.last_message_at || 0) - new Date(a.last_message_at || 0));
                        renderConversationList();
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    dom.sendButton.disabled = false;
                    if (dom.chatImagePickerToggle) dom.chatImagePickerToggle.disabled = false;
                }
            }

            async function sendStickerMessage(stickerId) {
                if (!state.activeConversationId || !stickerId) return;
                if (!STICKER_CATALOG[stickerId]) return;

                try {
                    const response = await fetch(state.messagesUrlTemplate.replace('__ID__', String(state.activeConversationId)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': state.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            body: getStickerToken(stickerId),
                            type: 'text',
                            sender_id: state.senderId,
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Không gửi được sticker');
                    }

                    const message = await response.json();
                    renderMessage(message, true);
                    broadcastChatSync('message-created', message);

                    const currentConversation = state.conversations.find((c) => Number(c.id) === Number(state.activeConversationId));
                    if (currentConversation) {
                        currentConversation.last_message = getMessagePreview(message);
                        currentConversation.last_message_sender_id = Number(state.senderId) || null;
                        currentConversation.last_message_at = new Date().toISOString();
                        state.conversations.sort((a, b) => new Date(b.last_message_at || 0) - new Date(a.last_message_at || 0));
                        renderConversationList();
                    }
                } catch (error) {
                    console.error(error);
                }
            }

            async function recallMessage(messageId) {
                if (!state.activeConversationId || !messageId) return;

                const confirmed = window.confirm('Thu hồi tin nhắn này?');
                if (!confirmed) return;

                const url = `${state.messagesUrlTemplate.replace('__ID__', String(state.activeConversationId))}/${messageId}?sender_id=${encodeURIComponent(String(state.senderId))}&test_mode=1`;

                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': state.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const payload = await response.json().catch(() => ({}));
                    throw new Error(payload.message || 'Không thể thu hồi tin nhắn.');
                }

                const recalledMessage = await response.json();
                broadcastChatSync('message-deleted', {
                    id: Number(messageId)
                });

                window.removeMessageFromUI(messageId);

                const currentConversation = state.conversations.find((item) => Number(item.id) === Number(state.activeConversationId));
                if (currentConversation) {
                    currentConversation.last_message = getMessagePreview(recalledMessage);
                    currentConversation.last_message_sender_id = Number(state.senderId) || null;
                    renderConversationList();
                }

                scrollToLatestMessage();
            }

            function stopCameraCapture() {
                if (state.cameraStream) {
                    state.cameraStream.getTracks().forEach((track) => track.stop());
                    state.cameraStream = null;
                }

                if (dom.chatCameraVideo) {
                    dom.chatCameraVideo.srcObject = null;
                }

                if (dom.chatCameraModal) {
                    dom.chatCameraModal.hidden = true;
                }

                if (dom.chatCameraStatus) {
                    dom.chatCameraStatus.hidden = true;
                    dom.chatCameraStatus.classList.remove('is-error');
                    dom.chatCameraStatus.textContent = '';
                }

                if (dom.chatCameraCaptureBtn) {
                    dom.chatCameraCaptureBtn.disabled = false;
                }
            }

            function setCameraStatus(text, isError = false) {
                if (!dom.chatCameraStatus) return;
                dom.chatCameraStatus.textContent = text || '';
                dom.chatCameraStatus.hidden = !text;
                dom.chatCameraStatus.classList.toggle('is-error', Boolean(text) && isError);
            }

            function setCameraCaptureEnabled(enabled) {
                if (dom.chatCameraCaptureBtn) {
                    dom.chatCameraCaptureBtn.disabled = !enabled;
                }
            }

            function waitForVideoReady(video, timeoutMs = 5000) {
                if (video.readyState >= 2 && video.videoWidth > 0 && video.videoHeight > 0) {
                    return Promise.resolve();
                }

                return new Promise((resolve, reject) => {
                    let settled = false;

                    const cleanup = () => {
                        video.removeEventListener('loadeddata', onReady);
                        video.removeEventListener('error', onError);
                        clearTimeout(timer);
                    };

                    const onReady = () => {
                        if (settled) return;
                        if (video.readyState < 2 || video.videoWidth <= 0 || video.videoHeight <= 0) {
                            return;
                        }
                        settled = true;
                        cleanup();
                        resolve();
                    };

                    const onError = () => {
                        if (settled) return;
                        settled = true;
                        cleanup();
                        reject(new Error('Không nhận được dữ liệu camera.'));
                    };

                    const timer = setTimeout(() => {
                        if (settled) return;
                        settled = true;
                        cleanup();
                        reject(new Error('Camera phản hồi quá chậm.'));
                    }, timeoutMs);

                    video.addEventListener('loadedmetadata', onReady, {
                        once: true
                    });
                    video.addEventListener('loadeddata', onReady, {
                        once: true
                    });
                    video.addEventListener('canplay', onReady, {
                        once: true
                    });
                    video.addEventListener('error', onError, {
                        once: true
                    });
                });
            }

            async function startCameraCapture() {
                if (!dom.chatCameraModal || !dom.chatCameraVideo) {
                    return;
                }

                if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                    alert('Trình duyệt không hỗ trợ camera trực tiếp, vui lòng chọn ảnh từ máy.');
                    dom.chatImageInput?.click();
                    return;
                }

                const isSecureOrigin = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                if (!isSecureOrigin) {
                    alert('Camera chỉ hoạt động trên HTTPS hoặc localhost. Vui lòng mở bằng localhost/https.');
                    dom.chatImageInput?.click();
                    return;
                }

                try {
                    dom.chatCameraModal.hidden = false;
                    setCameraStatus('Đang kết nối camera...');
                    setCameraCaptureEnabled(false);

                    if (state.cameraStream) {
                        state.cameraStream.getTracks().forEach((track) => track.stop());
                        state.cameraStream = null;
                    }

                    const cameraConstraints = [{
                            video: {
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 720
                                },
                                facingMode: {
                                    ideal: 'user'
                                }
                            },
                            audio: false,
                        },
                        {
                            video: true,
                            audio: false,
                        }
                    ];

                    let stream = null;
                    let lastError = null;

                    for (const constraints of cameraConstraints) {
                        try {
                            stream = await navigator.mediaDevices.getUserMedia(constraints);
                            break;
                        } catch (error) {
                            lastError = error;
                        }
                    }

                    if (!stream) {
                        throw lastError || new Error('Không lấy được stream camera.');
                    }

                    state.cameraStream = stream;
                    dom.chatCameraVideo.srcObject = stream;
                    await dom.chatCameraVideo.play().catch(() => {});
                    await waitForVideoReady(dom.chatCameraVideo);
                    setCameraCaptureEnabled(true);
                    setCameraStatus('');
                } catch (error) {
                    console.error(error);
                    setCameraStatus('Không thể mở camera. Kiểm tra quyền camera trong trình duyệt hoặc đóng ứng dụng khác đang dùng webcam.', true);
                    setCameraCaptureEnabled(false);
                    if (dom.chatCameraVideo) {
                        dom.chatCameraVideo.srcObject = null;
                    }
                    if (state.cameraStream) {
                        state.cameraStream.getTracks().forEach((track) => track.stop());
                        state.cameraStream = null;
                    }
                }
            }

            async function captureFromCameraAndSend() {
                if (!dom.chatCameraVideo || !dom.chatCameraCanvas) return;

                const video = dom.chatCameraVideo;
                const canvas = dom.chatCameraCanvas;
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                ctx.drawImage(video, 0, 0, width, height);

                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.92));
                if (!blob) {
                    alert('Không thể chụp ảnh, vui lòng thử lại.');
                    return;
                }

                const file = new File([blob], `camera-${Date.now()}.jpg`, {
                    type: 'image/jpeg'
                });

                stopCameraCapture();
                await sendImageMessage(file);
            }

            dom.conversationList.addEventListener('click', function(event) {
                const avatar = event.target.closest('[data-profile-user-id]');
                if (avatar) {
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileByUserId(avatar.getAttribute('data-profile-user-id'));
                    return;
                }

                const button = event.target.closest('[data-conversation-id]');
                if (!button) return;
                selectConversation(Number(button.dataset.conversationId));
            });

            dom.searchInput.addEventListener('input', function(event) {
                state.searchKeyword = event.target.value.trim().toLowerCase();
                renderConversationList();
            });

            dom.form.addEventListener('submit', sendMessage);
            dom.input.addEventListener('input', function() {
                updateSendButton();
                scheduleTypingStatusFromInput();
            });

            dom.chatBox.addEventListener('click', function(event) {
                const avatar = event.target.closest('[data-profile-user-id]');
                if (avatar) {
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileByUserId(avatar.getAttribute('data-profile-user-id'));
                    return;
                }

                const button = event.target.closest('[data-message-recall]');
                if (!button) return;

                const messageId = Number(button.dataset.messageRecall) || 0;
                if (!messageId) return;

                recallMessage(messageId).catch((error) => {
                    console.error(error);
                    alert(error.message || 'Không thể thu hồi tin nhắn.');
                });
            });

            if (dom.threadAvatar) {
                dom.threadAvatar.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileByUserId(dom.threadAvatar.getAttribute('data-profile-user-id'));
                });
            }

            if (dom.introAvatar) {
                dom.introAvatar.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileByUserId(dom.introAvatar.getAttribute('data-profile-user-id'));
                });
            }

            const currentUserAvatar = document.getElementById('ig-current-user-avatar');
            if (currentUserAvatar) {
                currentUserAvatar.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openProfileByUserId(currentUserAvatar.getAttribute('data-profile-user-id'));
                });
            }

            dom.chatBox.addEventListener('scroll', handleChatBoxScroll, {
                passive: true
            });

            if (dom.chatImagePickerToggle && dom.chatImageInput) {
                dom.chatImagePickerToggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    toggleAttachmentPicker();
                });

                dom.chatImageInput.addEventListener('change', async function(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;
                    await sendImageMessage(file);
                    event.target.value = '';
                });
            }

            if (dom.chatAttachmentFileBtn && dom.chatImageInput) {
                dom.chatAttachmentFileBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    closeAttachmentPicker();
                    dom.chatImageInput.click();
                });
            }

            if (dom.chatAttachmentCameraBtn) {
                dom.chatAttachmentCameraBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    closeAttachmentPicker();
                    startCameraCapture();
                });
            }

            if (dom.chatCameraCancelBtn) {
                dom.chatCameraCancelBtn.addEventListener('click', stopCameraCapture);
            }

            if (dom.chatCameraCaptureBtn) {
                dom.chatCameraCaptureBtn.addEventListener('click', captureFromCameraAndSend);
            }

            if (dom.voiceMessageBtn) {
                dom.voiceMessageBtn.addEventListener('click', function() {
                    if (state.isRecordingVoice) {
                        stopVoiceRecording(true);
                        return;
                    }

                    startVoiceRecording();
                });
            }

            if (dom.voiceCallBtn) {
                dom.voiceCallBtn.addEventListener('click', function() {
                    startZegoCall('voice');
                });
            }

            if (dom.videoCallBtn) {
                dom.videoCallBtn.addEventListener('click', function() {
                    startZegoCall('video');
                });
            }

            if (dom.zegoCallCloseBtn) {
                dom.zegoCallCloseBtn.addEventListener('click', function() {
                    endZegoCall(true);
                });
            }

            if (dom.incomingCallAcceptBtn) {
                dom.incomingCallAcceptBtn.addEventListener('click', async function() {
                    const incoming = state.incomingCall;
                    if (!incoming) return;

                    closeIncomingCallModal();
                    await sendCallSignal('accepted', {
                        targetUserId: Number(incoming.sender_id) || state.activePeerId,
                        roomId: incoming.room_id,
                        callType: incoming.call_type,
                    });

                    startZegoCall(String(incoming.call_type || 'video'), {
                        roomId: incoming.room_id,
                        skipSignal: true,
                    });
                });
            }

            if (dom.incomingCallRejectBtn) {
                dom.incomingCallRejectBtn.addEventListener('click', function() {
                    const incoming = state.incomingCall;
                    if (!incoming) return;

                    sendCallSignal('rejected', {
                        targetUserId: Number(incoming.sender_id) || state.activePeerId,
                        roomId: incoming.room_id,
                        callType: incoming.call_type,
                    });
                    closeIncomingCallModal();
                });
            }

            if (dom.reactionPickerToggle) {
                dom.reactionPickerToggle.addEventListener('click', function() {
                    closeMediaPickers();
                    toggleReactionPicker();
                });
            }

            if (dom.stickerPickerToggle) {
                dom.stickerPickerToggle.addEventListener('click', function() {
                    closeReactionPicker();
                    if (dom.stickerPicker.hidden) {
                        toggleMediaPicker(dom.stickerPicker, dom.stickerSearchInput, dom.stickerPickerToggle);
                        if (!dom.stickerTabs[0]?.classList.contains('is-active')) {
                            dom.stickerTabs[0]?.classList.add('is-active');
                            state.activeStickerCategory = 'popular';
                            filterMediaItems(dom.stickerItems, '', 'popular');
                        }
                    } else {
                        dom.stickerPicker.hidden = true;
                    }
                });
            }

            dom.stickerTabs.forEach((tab) => {
                tab.addEventListener('click', function() {
                    state.activeStickerCategory = tab.dataset.category || 'popular';
                    dom.stickerTabs.forEach((t) => t.classList.remove('is-active'));
                    tab.classList.add('is-active');
                    filterMediaItems(dom.stickerItems, dom.stickerSearchInput?.value || '', state.activeStickerCategory);
                });
            });

            if (dom.stickerSearchInput) {
                dom.stickerSearchInput.addEventListener('input', function(event) {
                    filterMediaItems(dom.stickerItems, event.target.value || '', state.activeStickerCategory);
                });
            }

            dom.stickerItems.forEach((item) => {
                item.addEventListener('click', async function() {
                    const stickerId = String(item.dataset.stickerId || '').trim();
                    if (!stickerId) return;
                    dom.stickerPicker.hidden = true;
                    await sendStickerMessage(stickerId);
                });
            });

            function closeMediaPickers() {
                if (dom.stickerPicker) dom.stickerPicker.hidden = true;
            }

            dom.reactionOptions.forEach((button) => {
                button.addEventListener('click', function() {
                    const reaction = button.dataset.reaction || '❤️';
                    setQuickReaction(reaction);
                    dom.input.value = `${dom.input.value}${reaction}`.slice(0, 2000);
                    dom.input.focus();
                    updateSendButton();
                    closeReactionPicker();
                });
            });

            if (dom.reactionSearchInput) {
                dom.reactionSearchInput.addEventListener('input', function(event) {
                    filterReactionOptions(event.target.value || '', state.activeReactionCategory);
                });
            }

            dom.reactionTabs.forEach((tab) => {
                tab.addEventListener('click', function() {
                    setReactionCategory(tab.dataset.category || 'smile');
                });
            });

            dom.toolButtons.forEach((button) => {
                button.addEventListener('click', function() {
                    const value = button.dataset.insert;
                    if (!value) return;
                    dom.input.value = `${dom.input.value}${value}`.slice(0, 2000);
                    dom.input.focus();
                    updateSendButton();
                });
            });

            document.addEventListener('click', function(event) {
                const target = event.target;
                if (!(target instanceof Element)) return;

                if (target.closest('#chat-image-picker-toggle')) {
                    return;
                }

                if (dom.reactionPicker && !dom.reactionPicker.hidden) {
                    if (target.closest('#reaction-picker') || target.closest('#emoji-picker-toggle')) return;
                    closeReactionPicker();
                }

                if (dom.chatAttachmentPicker && !dom.chatAttachmentPicker.hidden) {
                    if (target.closest('#chat-attachment-picker') || target.closest('#chat-image-picker-toggle')) return;
                    closeAttachmentPicker();
                }

                if (dom.stickerPicker && !dom.stickerPicker.hidden) {
                    if (target.closest('#sticker-picker') || target.closest('#sticker-picker-toggle')) return;
                    dom.stickerPicker.hidden = true;
                }

                if (dom.chatCameraModal && !dom.chatCameraModal.hidden) {
                    const cameraPanel = target.closest('.ig-camera-panel');
                    if (!cameraPanel) {
                        stopCameraCapture();
                    }
                }

                if (dom.incomingCallModal && !dom.incomingCallModal.hidden) {
                    const incomingPanel = target.closest('.ig-incoming-call-card');
                    if (!incomingPanel) {
                        const incoming = state.incomingCall;
                        if (incoming) {
                            sendCallSignal('rejected', {
                                targetUserId: Number(incoming.sender_id) || state.activePeerId,
                                roomId: incoming.room_id,
                                callType: incoming.call_type,
                            });
                        }
                        closeIncomingCallModal();
                    }
                }
            });

            window.addEventListener('resize', function() {
                if (dom.reactionPicker && !dom.reactionPicker.hidden) {
                    positionReactionPicker();
                }

                if (dom.stickerPicker && !dom.stickerPicker.hidden) {
                    positionMediaPicker(dom.stickerPicker, dom.stickerPickerToggle);
                }
            });

            if (dom.bgImageBtn && dom.bgImageInput) {
                dom.bgImageBtn.addEventListener('click', function() {
                    dom.bgImageInput.click();
                });

                dom.bgImageInput.addEventListener('change', function(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;

                    updateConversationBackground(file, false).catch(function(error) {
                        console.error(error);
                        alert(error.message || 'Không thể đổi ảnh nền cuộc trò chuyện.');
                    }).finally(function() {
                        if (dom.bgImageInput) {
                            dom.bgImageInput.value = '';
                        }
                    });
                });
            }

            if (dom.bgImageClearBtn) {
                dom.bgImageClearBtn.addEventListener('click', function() {
                    updateConversationBackground(null, true).catch(function(error) {
                        console.error(error);
                        alert(error.message || 'Không thể xóa ảnh nền cuộc trò chuyện.');
                    });
                });
            }

            window.addEventListener('beforeunload', stopCameraCapture);
            window.addEventListener('beforeunload', function() {
                stopVoiceRecording(false);
            });
            window.addEventListener('beforeunload', function() {
                endZegoCall(true);
            });

            initStickerGallery();
            setQuickReaction(localStorage.getItem(CHAT_QUICK_REACTION_STORAGE_KEY) || '❤️');
            setReactionCategory(state.activeReactionCategory);
            updateSendButton();

            fetchConversations().catch(function(error) {
                dom.conversationList.innerHTML = '<p class="ig-empty-note">Không thể tải danh sách hội thoại. Kiểm tra dữ liệu test.</p>';
                console.error(error);
            });
        })();
    </script>