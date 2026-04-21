@php
$popupSenderId = auth()->id();
if (!$popupSenderId) {
$popupSenderId = (int) (\App\Models\User::query()->orderBy('ID')->value('ID') ?? 0);
}

$popupSenderId = $popupSenderId ?: 0;
$popupSenderName = auth()->user()->name ?? null;
if (!$popupSenderName && $popupSenderId) {
$popupSenderName = (string) (\App\Models\User::query()->where('ID', $popupSenderId)->value('name') ?? ('User ' . $popupSenderId));
}

$popupSenderName = trim((string) ($popupSenderName ?: 'User'));
@endphp

<div id="messenger-popup-root"
    class="messenger-popup-root"
    data-conversations-url="{{ route('chat.conversations.index') }}"
    data-mark-read-url-template="{{ url('/chat/conversations/__ID__/read') }}"
    data-call-signal-url-template="{{ url('/chat/conversations/__ID__/call-signal') }}"
    data-call-signal-latest-url-template="{{ url('/chat/conversations/__ID__/call-signal/latest') }}"
    data-typing-url-template="{{ url('/chat/conversations/__ID__/typing') }}"
    data-typing-latest-url-template="{{ url('/chat/conversations/__ID__/typing/latest') }}"
    data-chat-url="{{ route('chat.test') }}"
    data-app-env-local="{{ app()->environment('local') ? 1 : 0 }}"
    data-zego-app-id="{{ (int) env('ZEGO_APP_ID', 1404858540) }}"
    data-zego-server-secret="{{ (string) env('ZEGO_SERVER_SECRET', '43f4f3877e081c999c7baaea7011ff94') }}"
    data-sender-id="{{ $popupSenderId }}"
    data-sender-name="{{ $popupSenderName }}">
    <button type="button" class="messenger-popup-toggle" data-messenger-toggle aria-label="Mở Messenger">
        <i class="fab fa-facebook-messenger"></i>
        <span class="messenger-popup-badge" data-messenger-badge hidden>0</span>
    </button>

    <div class="messenger-popup-panel shadow-lg" data-messenger-panel hidden>
        <div class="messenger-popup-head">
            <div>
                <div class="messenger-popup-title" data-popup-title>Đoạn chat</div>
            </div>
            <div class="messenger-popup-head-actions">
                <button type="button" class="messenger-popup-back" data-messenger-back aria-label="Quay lại" hidden>&larr;</button>
                <button type="button" class="messenger-popup-close" data-messenger-close aria-label="Đóng">&times;</button>
            </div>
        </div>

        <div class="messenger-popup-view messenger-popup-view-list" data-popup-view="list">
            <label class="messenger-popup-search" for="messenger-popup-search-input">
                <i class="fas fa-search"></i>
                <input id="messenger-popup-search-input" type="search" placeholder="Tìm kiếm trên VibeTalk" autocomplete="off">
            </label>

            <div class="messenger-popup-tabs">
                <button type="button" class="messenger-popup-tab is-active" data-filter="all">Tất cả</button>
                <button type="button" class="messenger-popup-tab" data-filter="unread">Chưa đọc</button>
            </div>

            <div class="messenger-popup-list" data-messenger-list>
                <div class="messenger-popup-empty">Đang tải cuộc trò chuyện...</div>
            </div>
        </div>

        <div class="messenger-popup-view messenger-popup-view-thread" data-popup-view="thread" hidden>
            <div class="messenger-thread-header">
                <img class="messenger-thread-avatar" data-thread-avatar src="" alt="Avatar">
                <div class="messenger-thread-meta">
                    <div class="messenger-thread-name" data-thread-name>Người dùng</div>
                    <div class="messenger-thread-status" data-thread-status>Đang hoạt động</div>
                </div>
            </div>

            <div class="messenger-thread-messages" data-thread-messages>
                <div class="messenger-popup-empty">Chọn một người dùng để xem lịch sử.</div>
            </div>

            <div class="messenger-thread-typing" data-thread-typing-indicator hidden>
                <span class="messenger-thread-typing-bubble" aria-label="Đang soạn tin">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </div>

            <div class="messenger-thread-composer">
                <div class="messenger-thread-tools">
                    <button type="button" class="messenger-thread-tool" data-voice-toggle aria-label="Ghi âm">
                        <i class="fas fa-microphone"></i>
                    </button>
                    <button type="button" class="messenger-thread-tool" data-image-toggle aria-label="Gửi hình">
                        <i class="far fa-image"></i>
                    </button>
                    <button type="button" class="messenger-thread-tool" data-emoji-toggle aria-label="Emoji">
                        <i class="far fa-face-smile"></i>
                    </button>
                    <button type="button" class="messenger-thread-tool" data-video-call-toggle aria-label="Gọi video">
                        <i class="fas fa-video"></i>
                    </button>
                </div>
                <input type="text" class="messenger-thread-input" data-thread-input placeholder="Nhắn tin..." disabled>
                <button type="button" class="messenger-thread-send" data-thread-send disabled>Gửi</button>
            </div>

            <input type="file" class="messenger-thread-image-input" data-thread-image-input accept="image/*" hidden>

            <div class="messenger-thread-emoji-picker" data-thread-emoji-picker hidden>
                <button type="button" data-emoji-value="😀">😀</button>
                <button type="button" data-emoji-value="😄">😄</button>
                <button type="button" data-emoji-value="😂">😂</button>
                <button type="button" data-emoji-value="😍">😍</button>
                <button type="button" data-emoji-value="🥰">🥰</button>
                <button type="button" data-emoji-value="😎">😎</button>
                <button type="button" data-emoji-value="👍">👍</button>
                <button type="button" data-emoji-value="🙏">🙏</button>
                <button type="button" data-emoji-value="❤️">❤️</button>
                <button type="button" data-emoji-value="🔥">🔥</button>
            </div>


        </div>

        <a class="messenger-popup-footer" href="{{ route('chat.test') }}">
            Xem tất cả trong VibeTalk
        </a>
    </div>

    <div class="messenger-thread-incoming-call" data-incoming-call-modal hidden>
        <div class="messenger-thread-incoming-card">
            <p class="messenger-thread-incoming-label">Cuộc gọi đến</p>
            <strong data-incoming-call-caller>Ai đó</strong>
            <p data-incoming-call-type>Cuộc gọi video</p>
            <div class="messenger-thread-incoming-actions">
                <button type="button" class="incoming-accept" data-incoming-call-accept>Nghe máy</button>
                <button type="button" class="incoming-reject" data-incoming-call-reject>Từ chối</button>
            </div>
        </div>
    </div>

    <div class="messenger-thread-video-modal" data-thread-video-modal hidden>
        <div class="messenger-thread-video-shell">
            <div class="messenger-thread-video-head">
                <div>
                    <strong data-video-title>Cuộc gọi video</strong>
                    <p data-video-room></p>
                </div>
                <button type="button" data-video-close aria-label="Đóng">Kết thúc</button>
            </div>
            <div class="messenger-thread-video-body" data-video-container></div>
        </div>
    </div>
</div>

<style>
    .messenger-popup-root {
        position: relative;
        display: inline-flex;
        align-items: center;
        margin-right: 8px;
    }

    .messenger-popup-toggle {
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #ffe0e6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #1877f2;
        font-size: 19px;
        padding: 0;
        position: relative;
    }

    .messenger-popup-toggle i {
        background: linear-gradient(180deg, #ff85a2, #ba62ff);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .messenger-popup-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #ef4444;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        border: 2px solid #ffffff;
        line-height: 1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
    }

    .messenger-popup-badge[hidden] {
        display: none !important;
    }

    .messenger-popup-panel {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 420px;
        max-width: calc(100vw - 24px);
        max-height: min(600px, calc(100vh - 72px));
        background: linear-gradient(135deg, #f7c9ec 0%, #f8edbf 100%);
        border-radius: 18px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        overflow: hidden;
        z-index: 2000;
        display: flex;
        flex-direction: column;
    }

    .messenger-popup-panel[hidden] {
        display: none !important;
    }

    .messenger-popup-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 16px 16px 10px;
    }

    .messenger-popup-head-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .messenger-popup-title {
        font-size: 24px;
        font-weight: 800;
        color: #1c1e21;
        line-height: 1.1;
    }

    .messenger-popup-close {
        border: 0;
        background: transparent;
        color: #65676b;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }

    .messenger-popup-back {
        border: 0;
        background: #f0f2f5;
        color: #1c1e21;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }

    .messenger-popup-tabs {
        display: flex;
        gap: 8px;
        padding: 0 16px 12px;
    }

    .messenger-popup-tab {
        border: 0;
        border-radius: 999px;
        background: #eef3ff;
        color: #1c1e21;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .messenger-popup-tab.is-active {
        background: #1877f2;
        color: #ffffff;
    }

    .messenger-popup-search {
        margin: 0 16px 12px;
        background: #f0f2f5;
        border-radius: 999px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        color: #8a8d91;
    }

    .messenger-popup-search input {
        border: 0;
        outline: none;
        background: transparent;
        width: 100%;
        font-size: 15px;
        color: #1c1e21;
    }

    .messenger-popup-view-thread {
        display: flex;
        flex-direction: column;
        min-height: 0;
        flex: 1 1 auto;
    }

    .messenger-popup-view-list {
        display: flex;
        flex-direction: column;
        min-height: 0;
        flex: 1 1 auto;
    }

    .messenger-thread-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px 12px;
        border-bottom: 1px solid #e4e6eb;
    }

    .messenger-thread-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 auto;
    }

    .messenger-thread-meta {
        min-width: 0;
        flex: 1 1 auto;
    }

    .messenger-thread-name {
        font-size: 16px;
        font-weight: 800;
        color: #1c1e21;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .messenger-thread-status {
        margin-top: 2px;
        font-size: 12px;
        color: #8a8d91;
    }

    .messenger-thread-messages {
        flex: 1 1 auto;
        min-height: 300px;
        max-height: 320px;
        overflow-y: auto;
        padding: 14px 14px 10px;
        background: #f8fafc;
    }

    .messenger-thread-message {
        display: flex;
        margin-bottom: 10px;
        gap: 8px;
    }

    .messenger-thread-message.me {
        justify-content: flex-end;
    }

    .messenger-thread-message.incoming {
        justify-content: flex-start;
    }

    .messenger-thread-bubble {
        max-width: 80%;
        border-radius: 18px;
        padding: 10px 12px;
        font-size: 14px;
        line-height: 1.35;
        word-break: break-word;
        box-shadow: 0 1px 1px rgba(15, 23, 42, 0.05);
    }

    .messenger-thread-bubble.is-sticker {
        width: fit-content;
        max-width: min(180px, 72vw);
        padding: 8px;
    }

    .messenger-thread-message.me .messenger-thread-bubble {
        background: linear-gradient(180deg, #7b8fe7 0%, #5f6cff 100%);
        color: #ffffff;
    }

    .messenger-thread-message.incoming .messenger-thread-bubble {
        background: #ffffff;
        color: #1c1e21;
        border: 1px solid #e4e6eb;
    }

    .messenger-thread-media {
        display: block;
        max-width: 100%;
        border-radius: 14px;
        margin-top: 6px;
    }

    .messenger-thread-audio {
        width: min(240px, 100%);
        margin-top: 6px;
    }

    .messenger-thread-sticker {
        width: 100%;
        max-width: 160px;
        border-radius: 14px;
        margin-top: 0;
    }

    .messenger-thread-composer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px 12px;
        border-top: 1px solid #e4e6eb;
        background: #ffffff;
        position: relative;
    }

    .messenger-thread-typing {
        padding: 0 14px 8px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        background: #f8fafc;
    }

    .messenger-thread-typing[hidden] {
        display: none !important;
    }

    .messenger-thread-typing-bubble {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 1px rgba(15, 23, 42, 0.05);
    }

    .messenger-thread-typing-bubble span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #9ca3af;
        animation: messengerTypingPulse 1.1s infinite ease-in-out;
    }

    .messenger-thread-typing-bubble span:nth-child(2) {
        animation-delay: 0.18s;
    }

    .messenger-thread-typing-bubble span:nth-child(3) {
        animation-delay: 0.36s;
    }

    @keyframes messengerTypingPulse {

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

    .messenger-thread-tools {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex: 0 0 auto;
    }

    .messenger-thread-tool {
        border: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f0f2f5;
        color: #1c1e21;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 15px;
    }

    .messenger-thread-tool:hover {
        background: #e7f0ff;
        color: #1877f2;
    }

    .messenger-thread-input {
        flex: 1 1 auto;
        border: 0;
        outline: none;
        background: #f0f2f5;
        border-radius: 999px;
        padding: 10px 14px;
        font-size: 14px;
    }

    .messenger-thread-send {
        border: 0;
        border-radius: 999px;
        padding: 10px 16px;
        background: #1877f2;
        color: #ffffff;
        font-weight: 700;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .messenger-thread-send:disabled,
    .messenger-thread-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .messenger-popup-banner a {
        color: #2e89ff;
        font-weight: 700;
        text-decoration: none;
    }

    .messenger-popup-list {
        flex: 1 1 auto;
        min-height: 0;
        max-height: none;
        overflow-y: auto;
        padding: 2px 8px 8px;
    }

    .messenger-popup-item {
        width: 100%;
        border: 0;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 14px;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        text-align: left;
    }

    .messenger-popup-item:hover {
        background: #f0f2f5;
    }

    .messenger-popup-item.is-unread {
        background: #f6f9ff;
    }

    .messenger-popup-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 auto;
    }

    .messenger-popup-avatar-wrap {
        position: relative;
        width: 56px;
        height: 56px;
        flex: 0 0 auto;
    }

    .messenger-popup-avatar-wrap .messenger-popup-avatar {
        display: block;
    }

    .messenger-popup-active-dot {
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

    .messenger-popup-meta {
        min-width: 0;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
    }

    .messenger-popup-name {
        font-size: 15px;
        font-weight: 700;
        color: #1c1e21;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .messenger-popup-item.is-unread .messenger-popup-name,
    .messenger-popup-item.is-unread .messenger-popup-preview-text {
        font-weight: 800;
        color: #111827;
    }

    .messenger-popup-preview {
        margin-top: 2px;
        font-size: 13px;
        color: #65676b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .messenger-popup-preview-row {
        margin-top: 2px;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 4px;
        min-width: 0;
        color: #65676b;
        font-size: 13px;
    }

    .messenger-popup-preview-text {
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 0 1 auto;
    }

    .messenger-popup-sep,
    .messenger-popup-time-inline {
        color: #8a8d91;
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .messenger-popup-unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2e89ff;
        flex: 0 0 auto;
        margin-left: 8px;
    }

    .messenger-popup-time {
        font-size: 12px;
        color: #8a8d91;
        white-space: nowrap;
        flex: 0 0 auto;
        margin-left: 8px;
    }

    .messenger-popup-empty {
        padding: 18px 16px;
        text-align: center;
        color: #8a8d91;
        font-size: 14px;
    }

    .messenger-thread-emoji-picker {
        position: absolute;
        left: 12px;
        bottom: 62px;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 6px;
        width: 240px;
        padding: 10px;
        background: #ffffff;
        border: 1px solid #e4e6eb;
        border-radius: 14px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18);
        z-index: 10;
    }

    .messenger-thread-emoji-picker[hidden] {
        display: none;
    }

    .messenger-thread-emoji-picker button {
        border: 0;
        background: transparent;
        font-size: 20px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
    }

    .messenger-thread-emoji-picker button:hover {
        background: #f0f2f5;
    }

    .messenger-thread-video-modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 84px 16px 64px;
    }

    .messenger-thread-video-modal[hidden] {
        display: none !important;
    }

    .messenger-thread-video-shell {
        width: min(960px, calc(100vw - 24px));
        height: min(560px, calc(100vh - 180px));
        max-height: 78vh;
        border-radius: 16px;
        overflow: hidden;
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.45);
        margin: auto;
    }

    .messenger-thread-video-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: #111827;
        color: #f9fafb;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }

    .messenger-thread-video-head strong {
        font-size: 15px;
    }

    .messenger-thread-video-head p {
        margin: 2px 0 0;
        font-size: 12px;
        color: #c9d1e0;
    }

    .messenger-thread-video-head button {
        border: 0;
        border-radius: 999px;
        background: #dc2626;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 14px;
        cursor: pointer;
    }

    .messenger-thread-video-body {
        min-height: 0;
        width: 100%;
        height: 100%;
    }

    .messenger-thread-incoming-call {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: clamp(84px, 16vh, 140px) 16px 16px;
        z-index: 3100;
        pointer-events: none;
    }

    .messenger-thread-incoming-call[hidden] {
        display: none !important;
    }

    .messenger-thread-incoming-card {
        width: min(320px, calc(100vw - 24px));
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.1);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.22);
        padding: 14px;
        color: #111827;
        pointer-events: auto;
    }

    .messenger-thread-incoming-label {
        margin: 0;
        font-size: 12px;
        color: #6b7280;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .messenger-thread-incoming-card strong {
        display: block;
        margin-top: 6px;
        font-size: 18px;
        font-weight: 800;
    }

    .messenger-thread-incoming-card p[data-incoming-call-type] {
        margin: 4px 0 0;
        color: #4b5563;
        font-size: 14px;
    }

    .messenger-thread-incoming-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .messenger-thread-incoming-actions button {
        border: 0;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .messenger-thread-incoming-actions .incoming-accept {
        background: #16a34a;
        color: #ffffff;
    }

    .messenger-thread-incoming-actions .incoming-reject {
        background: #ef4444;
        color: #ffffff;
    }

    .messenger-thread-image-input {
        display: none;
    }

    .messenger-popup-footer {
        display: block;
        border-top: 1px solid #e4e6eb;
        text-align: center;
        padding: 14px 12px;
        font-size: 15px;
        font-weight: 700;
        color: #2e89ff;
        text-decoration: none;
        background: transparent;
    }

    @media (max-width: 576px) {
        .messenger-popup-panel {
            width: min(360px, calc(100vw - 16px));
            right: -8px;
            max-height: min(540px, calc(100vh - 16px));
        }

        .messenger-thread-emoji-picker {
            width: min(240px, calc(100vw - 56px));
        }

        .messenger-thread-incoming-call {
            padding: clamp(72px, 14vh, 110px) 10px 10px;
        }
    }
</style>

<script>
    (function() {
        const root = document.getElementById('messenger-popup-root');
        if (!root) return;

        const toggle = root.querySelector('[data-messenger-toggle]');
        const unreadBadge = root.querySelector('[data-messenger-badge]');
        const panel = root.querySelector('[data-messenger-panel]');
        const closeBtn = root.querySelector('[data-messenger-close]');
        const backBtn = root.querySelector('[data-messenger-back]');
        const list = root.querySelector('[data-messenger-list]');
        const searchInput = root.querySelector('#messenger-popup-search-input');
        const popupTitle = root.querySelector('[data-popup-title]');
        const listView = root.querySelector('[data-popup-view="list"]');
        const threadView = root.querySelector('[data-popup-view="thread"]');
        const threadMessages = root.querySelector('[data-thread-messages]');
        const threadTypingIndicator = root.querySelector('[data-thread-typing-indicator]');
        const threadAvatar = root.querySelector('[data-thread-avatar]');
        const threadName = root.querySelector('[data-thread-name]');
        const threadStatus = root.querySelector('[data-thread-status]');
        const threadInput = root.querySelector('[data-thread-input]');
        const threadSend = root.querySelector('[data-thread-send]');
        const voiceToggle = root.querySelector('[data-voice-toggle]');
        const imageToggle = root.querySelector('[data-image-toggle]');
        const emojiToggle = root.querySelector('[data-emoji-toggle]');
        const videoToggle = root.querySelector('[data-video-call-toggle]');
        const imageInput = root.querySelector('[data-thread-image-input]');
        const emojiPicker = root.querySelector('[data-thread-emoji-picker]');
        const videoModal = root.querySelector('[data-thread-video-modal]');
        const videoTitle = root.querySelector('[data-video-title]');
        const videoRoom = root.querySelector('[data-video-room]');
        const videoContainer = root.querySelector('[data-video-container]');
        const videoClose = root.querySelector('[data-video-close]');
        const incomingCallModal = root.querySelector('[data-incoming-call-modal]');
        const incomingCallCaller = root.querySelector('[data-incoming-call-caller]');
        const incomingCallType = root.querySelector('[data-incoming-call-type]');
        const incomingCallAccept = root.querySelector('[data-incoming-call-accept]');
        const incomingCallReject = root.querySelector('[data-incoming-call-reject]');
        const tabs = Array.from(root.querySelectorAll('[data-messenger-tab], .messenger-popup-tab'));
        const conversationsUrl = root.dataset.conversationsUrl;
        const markReadUrlTemplate = root.dataset.markReadUrlTemplate || '';
        const callSignalUrlTemplate = root.dataset.callSignalUrlTemplate || '';
        const callSignalLatestUrlTemplate = root.dataset.callSignalLatestUrlTemplate || '';
        const typingUrlTemplate = root.dataset.typingUrlTemplate || '';
        const typingLatestUrlTemplate = root.dataset.typingLatestUrlTemplate || '';
        const zegoAppId = Number(root.dataset.zegoAppId || 0);
        const zegoServerSecret = String(root.dataset.zegoServerSecret || '').trim();
        const senderId = root.dataset.senderId || '0';
        const senderName = String(root.dataset.senderName || '').trim() || ('User ' + senderId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let conversations = [];
        let loadingConversations = false;
        let activeConversation = null;
        let activeMessages = [];
        let activeMessagesMeta = {
            has_more: false,
            next_before_id: null,
        };
        let messageCache = {};
        let voiceRecorder = null;
        let voiceChunks = [];
        let voiceStream = null;
        let recordingVoice = false;
        let zegoInstance = null;
        let activeFilter = 'all';
        let conversationSubscriptions = new Map();
        let conversationSeenAt = new Map();
        let markReadInFlight = new Set();
        let latestCallSignalByConversation = new Map();
        let latestTypingSignalByConversation = new Map();
        let refreshTimer = null;
        let callSignalTimer = null;
        let realtimeSyncTimer = null;
        let fastSyncTimer = null;
        let realtimeConnected = false;
        let echoConnectionBound = false;
        let activePeerId = null;
        let activeCallRoomId = null;
        let activeCallType = 'video';
        let incomingCall = null;
        let typingStopTimer = null;
        let typingIndicatorResetTimer = null;
        let typingLastSentState = false;
        let typingLastSentAt = 0;
        const typingPreviewByConversation = new Map();
        const typingPreviewResetTimers = new Map();

        const isLocalTestMode = root.dataset.appEnvLocal === '1' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        const CALL_SIGNAL_STORAGE_KEY = 'socialMini.callSignal.sync.v1';
        const DISCONNECTED_REFRESH_INTERVAL_MS = 2000;
        const CONNECTED_WATCHDOG_INTERVAL_MS = 15000;
        const TYPING_IDLE_MS = 1400;
        const TYPING_STATUS_RESET_MS = 2600;
        const TYPING_THROTTLE_MS = 900;
        const STICKER_TOKEN_PREFIX = '[STICKER:';
        const STICKER_TOKEN_SUFFIX = ']';

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

        function getEchoConnection() {
            return window.Echo?.connector?.pusher?.connection || null;
        }

        function setRealtimeConnected(nextState) {
            const normalized = Boolean(nextState);
            if (realtimeConnected === normalized) {
                return;
            }

            realtimeConnected = normalized;
            stopAutoRefresh();
            startAutoRefresh();
            stopRealtimeSyncWatchdog();
            startRealtimeSyncWatchdog();
            scheduleFastConversationSync(200);
        }

        function bindEchoConnectionEvents() {
            if (echoConnectionBound) {
                return;
            }

            const connection = getEchoConnection();
            if (!connection || typeof connection.bind !== 'function') {
                return;
            }

            echoConnectionBound = true;
            setRealtimeConnected(String(connection.state || '') === 'connected');

            connection.bind('connected', function() {
                setRealtimeConnected(true);
            });

            connection.bind('connecting', function() {
                setRealtimeConnected(false);
            });

            connection.bind('unavailable', function() {
                setRealtimeConnected(false);
            });

            connection.bind('failed', function() {
                setRealtimeConnected(false);
            });

            connection.bind('disconnected', function() {
                setRealtimeConnected(false);
            });
        }

        function scheduleFastConversationSync(delayMs = 350) {
            if (fastSyncTimer) {
                window.clearTimeout(fastSyncTimer);
            }

            fastSyncTimer = window.setTimeout(function() {
                fastSyncTimer = null;
                loadConversations({
                    silent: true
                });
            }, Math.max(100, Number(delayMs) || 350));
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

        function formatTime(isoTime) {
            if (!isoTime) return '';
            const date = new Date(isoTime);
            if (Number.isNaN(date.getTime())) return '';
            const diffMinutes = Math.floor((Date.now() - date.getTime()) / 60000);
            if (diffMinutes < 1) return 'Vừa xong';
            if (diffMinutes < 60) return `${diffMinutes} phút`;
            if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)} giờ`;
            return `${Math.floor(diffMinutes / 1440)} ngày`;
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

        function updateConversationBackground(conversationId, backgroundUrl) {
            const targetId = Number(conversationId) || 0;
            if (!targetId) {
                return;
            }

            const conversation = conversations.find((item) => Number(item.id) === targetId);
            if (!conversation) {
                return;
            }

            conversation.chat_background_url = resolveConversationBackgroundUrl(backgroundUrl) || null;
        }

        function applyActiveThreadBackground() {
            if (!threadMessages) {
                return;
            }

            const activeConversationId = Number(activeConversation?.id) || 0;
            const conversation = conversations.find((item) => Number(item.id) === activeConversationId);
            const backgroundUrl = resolveConversationBackgroundUrl(conversation?.chat_background_url || '');

            if (!backgroundUrl) {
                threadMessages.style.backgroundImage = 'none';
                threadMessages.style.backgroundColor = '#f8fafc';
                threadMessages.style.backgroundBlendMode = 'normal';
                return;
            }

            const safeUrl = backgroundUrl.replace(/"/g, '\\"');
            threadMessages.style.backgroundImage = `url("${safeUrl}")`;
            threadMessages.style.backgroundSize = 'cover';
            threadMessages.style.backgroundPosition = 'center';
            threadMessages.style.backgroundRepeat = 'no-repeat';
            threadMessages.style.backgroundColor = '#f8fafc';
            threadMessages.style.backgroundBlendMode = 'normal';
        }

        function toTimestamp(isoTime) {
            if (!isoTime) return 0;
            const value = new Date(isoTime).getTime();
            return Number.isFinite(value) ? value : 0;
        }

        function previewText(item) {
            const conversationId = Number(item?.id) || 0;
            const typingState = typingPreviewByConversation.get(conversationId) || null;
            if (typingState && Boolean(typingState.is_typing)) {
                const typingName = String(typingState.sender_name || item?.name || 'Người dùng').trim();
                return `${typingName} đang soạn tin...`;
            }

            const text = String(item.last_message || '').trim();
            const sender = Number(item.last_message_sender_id || 0);
            const me = Number(senderId || 0);
            const preview = text || 'Bắt đầu cuộc trò chuyện';
            return sender > 0 && me > 0 && sender === me ? `Bạn: ${preview}` : preview;
        }

        function setConversationTypingPreview(conversationId, isTyping, senderDisplayName = '') {
            const targetId = Number(conversationId) || 0;
            if (!targetId) {
                return;
            }

            const existingTimer = typingPreviewResetTimers.get(targetId);
            if (existingTimer) {
                window.clearTimeout(existingTimer);
                typingPreviewResetTimers.delete(targetId);
            }

            if (Boolean(isTyping)) {
                typingPreviewByConversation.set(targetId, {
                    is_typing: true,
                    sender_name: String(senderDisplayName || '').trim(),
                    updated_at: Date.now(),
                });

                const resetTimer = window.setTimeout(function() {
                    typingPreviewByConversation.delete(targetId);
                    typingPreviewResetTimers.delete(targetId);
                    renderList();
                }, TYPING_STATUS_RESET_MS);
                typingPreviewResetTimers.set(targetId, resetTimer);
            } else {
                typingPreviewByConversation.delete(targetId);
            }

            const isActiveThread = Boolean(activeConversation) && Number(activeConversation.id) === targetId && !threadView.hidden;
            if (isActiveThread && threadTypingIndicator) {
                threadTypingIndicator.hidden = !Boolean(isTyping);
                if (Boolean(isTyping)) {
                    threadMessages.scrollTop = threadMessages.scrollHeight;
                }
            }

            renderList();
        }

        function isConversationUnread(item) {
            return Boolean(item?.has_unread);
        }

        function normalizeConversation(item) {
            const id = Number(item?.id || 0);
            const me = Number(senderId || 0);
            const lastSenderId = Number(item?.last_message_sender_id || 0);
            const hasMessage = String(item?.last_message || '').trim() !== '';
            const lastMessageAt = toTimestamp(item?.last_message_at);
            const seenAt = Number(conversationSeenAt.get(id) || 0);
            const initialUnread = hasMessage && lastSenderId > 0 && lastSenderId !== me && lastMessageAt > seenAt;

            const existing = conversations.find((entry) => Number(entry?.id) === id);
            const existingUnread = Boolean(existing?.has_unread);
            const hasUnread = Boolean((item?.has_unread ?? initialUnread) || existingUnread);

            return {
                ...item,
                is_recently_active: Boolean(item?.is_recently_active),
                has_unread: hasUnread,
            };
        }

        function updateUnreadBadge() {
            if (!unreadBadge) {
                return;
            }

            const totalUnread = conversations.reduce((sum, item) => {
                const unreadCount = Number(item?.unread_count || 0);
                if (unreadCount > 0) {
                    return sum + unreadCount;
                }

                return sum + (Boolean(item?.has_unread) ? 1 : 0);
            }, 0);

            if (totalUnread > 0) {
                unreadBadge.hidden = false;
                unreadBadge.textContent = totalUnread > 99 ? '99+' : String(totalUnread);
            } else {
                unreadBadge.hidden = true;
                unreadBadge.textContent = '0';
            }
        }

        function markConversationRead(conversationId) {
            const targetId = Number(conversationId) || 0;
            if (!targetId) return;

            conversationSeenAt.set(targetId, Date.now());

            conversations = conversations.map((item) => {
                if (Number(item.id) !== targetId) {
                    return item;
                }

                return {
                    ...item,
                    has_unread: false,
                    unread_count: 0,
                };
            });

            updateUnreadBadge();
            persistConversationRead(targetId);
        }

        async function persistConversationRead(conversationId) {
            const targetId = Number(conversationId) || 0;
            if (!targetId || !markReadUrlTemplate) {
                return;
            }

            if (markReadInFlight.has(targetId)) {
                return;
            }

            markReadInFlight.add(targetId);

            try {
                const response = await fetch(markReadUrlTemplate.replace('__ID__', String(targetId)), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        sender_id: Number(senderId) || 0,
                        test_mode: isLocalTestMode ? 1 : 0,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Không thể lưu trạng thái đã đọc.');
                }
            } catch (error) {
                console.error(error);
            } finally {
                markReadInFlight.delete(targetId);
            }
        }

        function previewForFile(file) {
            const type = String(file?.type || '').toLowerCase();
            if (type.startsWith('audio/')) return 'Đã gửi tin nhắn thoại';
            if (type.startsWith('image/')) return 'Đã gửi ảnh';
            return 'Đã gửi tệp';
        }

        function buildStickerDataUrl(sticker) {
            const svg = `
                <svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320">
                    <defs>
                        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="${sticker.bgStart}"/>
                            <stop offset="100%" stop-color="${sticker.bgEnd}"/>
                        </linearGradient>
                    </defs>
                    <rect x="0" y="0" width="320" height="320" rx="56" fill="url(#bg)"/>
                    <text x="160" y="168" text-anchor="middle" font-size="112">${sticker.emoji}</text>
                    <text x="160" y="285" text-anchor="middle" font-size="28" font-weight="700" font-family="Segoe UI, Arial, sans-serif" fill="#24324a">${sticker.caption}</text>
                </svg>
            `.trim();

            return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
        }

        function ensureStickerUrls() {
            Object.values(STICKER_CATALOG).forEach((sticker) => {
                if (!sticker.url) {
                    sticker.url = buildStickerDataUrl(sticker);
                }
            });
        }

        function parseStickerIdFromBody(body) {
            const normalizedBody = String(body || '').trim();
            if (!normalizedBody) {
                return null;
            }

            const legacy = LEGACY_STICKER_BODY_MAP[normalizedBody];
            if (legacy) {
                return legacy;
            }

            const match = normalizedBody.match(/^\[STICKER:([a-z0-9_-]+)\]$/i);
            if (!match) {
                return null;
            }

            const stickerId = String(match[1] || '').toLowerCase();
            return STICKER_CATALOG[stickerId] ? stickerId : null;
        }

        function previewFromMessage(message) {
            const body = String(message?.body || '').trim();
            if (parseStickerIdFromBody(body)) {
                return 'Đã gửi sticker';
            }
            if (body) {
                return body;
            }

            const attachments = Array.isArray(message?.attachments) ? message.attachments : [];
            const firstAttachment = attachments[0];
            if (!firstAttachment) {
                return 'Đã gửi tin nhắn';
            }

            const mimeType = String(firstAttachment?.mime_type || '').toLowerCase();
            if (mimeType.startsWith('audio/') || isAudioAttachment(firstAttachment)) {
                return 'Đã gửi tin nhắn thoại';
            }
            if (mimeType.startsWith('image/')) {
                return 'Đã gửi ảnh';
            }

            return 'Đã gửi tệp';
        }

        function getConversationCache(conversationId) {
            const key = String(Number(conversationId) || 0);
            if (key === '0') {
                return null;
            }

            return messageCache[key] || null;
        }

        function setConversationCache(conversationId, payload) {
            const key = String(Number(conversationId) || 0);
            if (key === '0') {
                return;
            }

            messageCache[key] = {
                messages: Array.isArray(payload?.messages) ? payload.messages.slice() : [],
                meta: payload && typeof payload.meta === 'object' ? {
                    has_more: Boolean(payload.meta.has_more),
                    next_before_id: Number(payload.meta.next_before_id) || null,
                    limit: Number(payload.meta.limit) || 80,
                } : {
                    has_more: false,
                    next_before_id: null,
                    limit: 80,
                },
                updated_at: new Date().toISOString(),
            };
        }

        function mergeMessageIntoCache(conversationId, message) {
            const cache = getConversationCache(conversationId);
            if (!cache || !message) {
                return;
            }

            const messageId = Number(message.id) || 0;
            if (messageId && cache.messages.some((item) => Number(item?.id) === messageId)) {
                return;
            }

            cache.messages.push(message);
            cache.updated_at = new Date().toISOString();
        }

        function renderConversationFromCache(conversationId) {
            const cache = getConversationCache(conversationId);
            if (!cache) {
                return false;
            }

            activeMessages = Array.isArray(cache.messages) ? cache.messages.slice() : [];
            activeMessagesMeta = cache.meta && typeof cache.meta === 'object' ? {
                has_more: Boolean(cache.meta.has_more),
                next_before_id: Number(cache.meta.next_before_id) || null,
                limit: Number(cache.meta.limit) || 80,
            } : {
                has_more: false,
                next_before_id: null,
                limit: 80,
            };

            threadMessages.innerHTML = activeMessages.length ?
                activeMessages.map(renderThreadMessage).join('') :
                '<div class="messenger-popup-empty">Chưa có lịch sử trò chuyện.</div>';
            threadMessages.scrollTop = threadMessages.scrollHeight;
            threadInput.disabled = false;
            threadSend.disabled = false;
            return true;
        }

        function getLatestCachedMessageAt(conversationId) {
            const cache = getConversationCache(conversationId);
            if (!cache || !Array.isArray(cache.messages) || !cache.messages.length) {
                return 0;
            }

            return cache.messages.reduce((latest, message) => {
                const timestamp = toTimestamp(message?.created_at);
                return timestamp > latest ? timestamp : latest;
            }, 0);
        }

        function shouldUseConversationCache(conversation) {
            if (!conversation || !conversation.id) {
                return false;
            }

            const cache = getConversationCache(conversation.id);
            if (!cache) {
                return false;
            }

            const serverLatest = toTimestamp(conversation.last_message_at);
            const cacheLatest = getLatestCachedMessageAt(conversation.id);

            if (serverLatest > 0 && cacheLatest > 0 && serverLatest > cacheLatest) {
                return false;
            }

            return true;
        }

        function hasMessageInThread(messageId) {
            const id = Number(messageId) || 0;
            if (!id) return false;
            return activeMessages.some((item) => Number(item?.id) === id);
        }

        function conversationLink(conversationId) {
            return `#conversation-${conversationId}`;
        }

        function messageTime(isoTime) {
            if (!isoTime) return '';
            const date = new Date(isoTime);
            if (Number.isNaN(date.getTime())) return '';
            return date.toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function isAudioAttachment(attachment) {
            const mimeType = String(attachment?.mime_type || '').toLowerCase();
            if (mimeType.startsWith('audio/')) return true;
            const name = String(attachment?.file_name || attachment?.file_path || '').toLowerCase();
            return /\.(webm|ogg|mp3|wav|m4a|aac|mp4)$/i.test(name);
        }

        function renderAttachment(attachment) {
            const mimeType = String(attachment?.mime_type || '').toLowerCase();
            const fileUrl = escapeHtml(resolveAttachmentUrl(attachment));
            const fileName = escapeHtml(attachment?.file_name || 'attachment');

            if (!fileUrl) {
                return '';
            }

            if (mimeType.startsWith('image/')) {
                return `<img class="messenger-thread-media" src="${fileUrl}" alt="${fileName}">`;
            }

            if (isAudioAttachment(attachment)) {
                return `<audio class="messenger-thread-audio" controls preload="metadata" src="${fileUrl}"></audio>`;
            }

            return `<a class="messenger-thread-media" href="${fileUrl}" target="_blank" rel="noopener noreferrer">${fileName}</a>`;
        }

        function renderThreadMessage(message) {
            const mine = Number(message.sender_id) === Number(senderId);
            const attachments = Array.isArray(message.attachments) ? message.attachments : [];
            const body = String(message.body || '').trim();
            const stickerId = parseStickerIdFromBody(body);
            const sticker = stickerId ? STICKER_CATALOG[stickerId] : null;
            const isStickerOnly = Boolean(sticker) && !attachments.length;
            if (sticker && !sticker.url) {
                ensureStickerUrls();
            }
            const content = (body && !sticker) ? `<div>${escapeHtml(body)}</div>` : '';
            const stickerMarkup = sticker ? `<img class="messenger-thread-media messenger-thread-sticker" src="${escapeHtml(sticker.url)}" alt="${escapeHtml(sticker.label)}">` : '';
            const media = attachments.map(renderAttachment).join('');

            return `
                <div class="messenger-thread-message ${mine ? 'me' : 'incoming'}">
                    <div class="messenger-thread-bubble ${isStickerOnly ? 'is-sticker' : ''}">
                        ${content}
                        ${stickerMarkup}
                        ${media}
                        <div style="margin-top:4px;font-size:11px;opacity:.75;">${escapeHtml(messageTime(message.created_at))}</div>
                    </div>
                </div>
            `;
        }

        function updateComposerState() {
            const hasText = String(threadInput.value || '').trim().length > 0;
            threadSend.disabled = !activeConversation;
            threadSend.textContent = hasText ? 'Gửi' : 'Gửi';
        }

        function closeEmojiPicker() {
            if (emojiPicker) emojiPicker.hidden = true;
        }

        function toggleEmojiPicker() {
            if (!emojiPicker) return;
            const opening = emojiPicker.hidden;
            emojiPicker.hidden = !emojiPicker.hidden;
            if (opening) {
                closeVideoModal();
                imageInput && (imageInput.value = '');
            }
        }

        function insertEmoji(emoji) {
            const value = String(emoji || '');
            if (!value) return;
            threadInput.value = `${threadInput.value || ''}${value}`;
            threadInput.focus();
            updateComposerState();
        }

        async function sendFileMessage(file) {
            if (!file || !activeConversation) return;

            const formData = new FormData();
            formData.append('sender_id', String(senderId));
            formData.append('test_mode', isLocalTestMode ? '1' : '0');
            formData.append('body', '');

            const fileType = String(file.type || '').toLowerCase();
            if (fileType.startsWith('audio/')) {
                formData.append('type', 'audio');
                formData.append('audio', file);
            } else {
                formData.append('type', 'image');
                formData.append('image', file);
            }

            try {
                const response = await fetch(new URL(`/chat/conversations/${activeConversation.id}/messages`, window.location.origin).toString(), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData,
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload?.message || 'Không thể gửi tệp.');
                }

                mergeMessageIntoCache(activeConversation.id, payload);
                activeMessages.push(payload);
                threadMessages.insertAdjacentHTML('beforeend', renderThreadMessage(payload));
                threadMessages.scrollTop = threadMessages.scrollHeight;
                refreshConversationPreview(activeConversation.id, previewForFile(file), Number(senderId));
                scheduleFastConversationSync(250);
                updateComposerState();
            } catch (error) {
                console.error(error);
                alert(error.message || 'Không thể gửi tệp.');
            }
        }

        async function startVoiceRecording() {
            if (!activeConversation) {
                alert('Chọn một cuộc trò chuyện trước khi ghi âm.');
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia || typeof window.MediaRecorder === 'undefined') {
                alert('Trình duyệt không hỗ trợ ghi âm.');
                return;
            }

            try {
                voiceStream = await navigator.mediaDevices.getUserMedia({
                    audio: true
                });
                const preferredTypes = ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/mp4'];
                let recorder = null;
                for (const mimeType of preferredTypes) {
                    if (window.MediaRecorder.isTypeSupported(mimeType)) {
                        recorder = new MediaRecorder(voiceStream, {
                            mimeType
                        });
                        break;
                    }
                }
                recorder = recorder || new MediaRecorder(voiceStream);

                voiceChunks = [];
                voiceRecorder = recorder;
                recordingVoice = true;
                voiceToggle?.classList.add('is-recording');

                recorder.ondataavailable = function(event) {
                    if (event.data && event.data.size > 0) {
                        voiceChunks.push(event.data);
                    }
                };

                recorder.onstop = async function() {
                    const chunks = voiceChunks.slice();
                    const stream = voiceStream;
                    voiceRecorder = null;
                    voiceChunks = [];
                    recordingVoice = false;
                    voiceToggle?.classList.remove('is-recording');
                    if (stream) {
                        stream.getTracks().forEach((track) => track.stop());
                    }
                    voiceStream = null;

                    if (!chunks.length) return;

                    const mimeType = chunks[0].type || 'audio/webm';
                    const blob = new Blob(chunks, {
                        type: mimeType
                    });
                    const extension = mimeType.includes('ogg') ? 'ogg' : (mimeType.includes('mp4') ? 'm4a' : 'webm');
                    const file = new File([blob], `voice-${Date.now()}.${extension}`, {
                        type: mimeType
                    });
                    await sendFileMessage(file);
                };

                recorder.start(250);
            } catch (error) {
                console.error(error);
                recordingVoice = false;
                voiceToggle?.classList.remove('is-recording');
                alert('Không thể truy cập micro.');
            }
        }

        function stopVoiceRecording() {
            if (voiceRecorder && recordingVoice) {
                voiceRecorder.stop();
                return;
            }
            recordingVoice = false;
            voiceToggle?.classList.remove('is-recording');
        }

        function resolveTargetPeerId(conversation) {
            if (!conversation || typeof conversation !== 'object') {
                return null;
            }

            const directPeerId = Number(conversation.peer_id) || 0;
            if (directPeerId > 0) {
                return directPeerId;
            }

            const participants = Array.isArray(conversation.participants) ? conversation.participants : [];
            const me = Number(senderId) || 0;
            for (const participant of participants) {
                const candidateId = Number(participant?.user_id || participant?.id || 0);
                if (candidateId > 0 && candidateId !== me) {
                    return candidateId;
                }
            }

            return null;
        }

        function closeIncomingCallModal() {
            incomingCall = null;
            if (incomingCallModal) {
                incomingCallModal.hidden = true;
            }
        }

        function openIncomingCallModal(payload) {
            incomingCall = payload || null;
            if (!incomingCallModal || !payload) {
                return;
            }

            if (incomingCallCaller) {
                incomingCallCaller.textContent = payload.caller_name || 'Ai đó';
            }

            if (incomingCallType) {
                incomingCallType.textContent = String(payload.call_type || 'video') === 'voice' ? 'Cuộc gọi thoại' : 'Cuộc gọi video';
            }

            incomingCallModal.hidden = false;
        }

        async function sendCallSignal(action, payload = {}) {
            const conversationId = Number(payload.conversationId || activeConversation?.id || 0);
            if (!conversationId || !callSignalUrlTemplate) {
                return;
            }

            const targetId = Number(payload.targetUserId || activePeerId || 0) || null;
            const roomId = String(payload.roomId || activeCallRoomId || `room_${conversationId}`);
            const callType = String(payload.callType || activeCallType || 'video');
            const callerName = String(payload.callerName || senderName);
            const body = {
                action,
                sender_id: Number(senderId) || 0,
                target_user_id: targetId,
                call_type: callType,
                room_id: roomId,
                caller_name: callerName,
                test_mode: isLocalTestMode ? 1 : 0,
            };

            try {
                localStorage.setItem(CALL_SIGNAL_STORAGE_KEY, JSON.stringify({
                    ...body,
                    conversation_id: conversationId,
                    created_at: new Date().toISOString(),
                }));

                const response = await fetch(callSignalUrlTemplate.replace('__ID__', String(conversationId)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(body),
                });

                if (!response.ok) {
                    throw new Error('Không thể gửi tín hiệu cuộc gọi.');
                }

                const responsePayload = await response.json().catch(() => ({}));
                const signal = responsePayload?.signal || null;
                const signalAt = String(signal?.created_at || '');
                if (signalAt) {
                    latestCallSignalByConversation.set(conversationId, signalAt);
                }
            } catch (error) {
                console.error(error);
            }
        }

        function handleCallSignalEvent(event) {
            const payload = event || {};
            const conversationId = Number(payload.conversation_id) || 0;
            const sender = Number(payload.sender_id) || 0;
            const target = Number(payload.target_user_id) || 0;
            const me = Number(senderId) || 0;
            const action = String(payload.action || '').toLowerCase();

            if (!conversationId || !action || !me || sender === me) {
                return;
            }

            if (target > 0 && target !== me) {
                return;
            }

            const conversation = conversations.find((item) => Number(item.id) === conversationId) || null;

            if (action === 'incoming') {
                if (zegoInstance) {
                    sendCallSignal('rejected', {
                        conversationId,
                        targetUserId: sender,
                        roomId: payload.room_id,
                        callType: payload.call_type,
                    });
                    return;
                }

                openIncomingCallModal({
                    ...payload,
                    conversation_id: conversationId,
                    target_user_id: me,
                    sender_id: sender,
                    caller_name: payload.caller_name || conversation?.name || 'Ai đó',
                    call_type: payload.call_type || 'video',
                });
                return;
            }

            if (action === 'rejected' && zegoInstance) {
                alert('Người nhận đã từ chối cuộc gọi.');
                closeVideoModal(false);
                return;
            }

            if (action === 'ended' && zegoInstance) {
                closeVideoModal(false);
            }
        }

        async function pollLatestCallSignals() {
            if (!callSignalLatestUrlTemplate) {
                return;
            }

            if (!conversations.length) {
                await loadConversations({
                    silent: true
                });
            }

            const ids = new Set(conversations.map((item) => Number(item.id)).filter((id) => id > 0));
            if (activeConversation && Number(activeConversation.id) > 0) {
                ids.add(Number(activeConversation.id));
            }

            for (const conversationId of ids) {
                try {
                    const latestUrl = new URL(callSignalLatestUrlTemplate.replace('__ID__', String(conversationId)), window.location.origin);
                    latestUrl.searchParams.set('sender_id', String(senderId));

                    const response = await fetch(latestUrl.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        continue;
                    }

                    const payload = await response.json();
                    const signal = payload?.signal || null;
                    if (!signal) {
                        continue;
                    }

                    const signalAt = String(signal.created_at || '');
                    const previousSignalAt = String(latestCallSignalByConversation.get(conversationId) || '');
                    if (signalAt && previousSignalAt && signalAt === previousSignalAt) {
                        continue;
                    }

                    if (signalAt) {
                        latestCallSignalByConversation.set(conversationId, signalAt);
                    }
                    handleCallSignalEvent(signal);
                } catch (error) {
                    console.error(error);
                }
            }
        }

        async function pollLatestTypingStatus() {
            if (!typingLatestUrlTemplate || !activeConversation || !threadView || threadView.hidden) {
                return;
            }

            const conversationId = Number(activeConversation.id) || 0;
            if (!conversationId) {
                return;
            }

            try {
                const latestUrl = new URL(typingLatestUrlTemplate.replace('__ID__', String(conversationId)), window.location.origin);
                latestUrl.searchParams.set('sender_id', String(senderId));
                if (isLocalTestMode) {
                    latestUrl.searchParams.set('test_mode', '1');
                }

                const response = await fetch(latestUrl.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const typing = payload?.typing || null;
                if (!typing) {
                    setConversationTypingPreview(conversationId, false);
                    return;
                }

                const typingAt = String(typing.created_at || '');
                const previousTypingAt = String(latestTypingSignalByConversation.get(conversationId) || '');
                if (typingAt && previousTypingAt && typingAt === previousTypingAt) {
                    return;
                }

                if (typingAt) {
                    latestTypingSignalByConversation.set(conversationId, typingAt);
                }

                setConversationTypingPreview(conversationId, Boolean(typing.is_typing), String(typing.sender_name || ''));
            } catch (error) {
                console.error(error);
            }
        }

        function startCallSignalPolling() {
            if (callSignalTimer) {
                return;
            }

            callSignalTimer = window.setInterval(function() {
                pollLatestCallSignals();
                pollLatestTypingStatus();
            }, 1500);

            pollLatestCallSignals();
            pollLatestTypingStatus();
        }

        function stopCallSignalPolling() {
            if (!callSignalTimer) {
                return;
            }

            window.clearInterval(callSignalTimer);
            callSignalTimer = null;
        }

        function startRealtimeSyncWatchdog() {
            if (realtimeSyncTimer) {
                return;
            }

            realtimeSyncTimer = window.setInterval(function() {
                if (!realtimeConnected) {
                    return;
                }

                loadConversations({
                    silent: true
                });
            }, CONNECTED_WATCHDOG_INTERVAL_MS);
        }

        function stopRealtimeSyncWatchdog() {
            if (!realtimeSyncTimer) {
                return;
            }

            window.clearInterval(realtimeSyncTimer);
            realtimeSyncTimer = null;
        }

        function closeVideoModal(notifyRemote = false) {
            if (notifyRemote && activeConversation) {
                sendCallSignal('ended', {
                    conversationId: Number(activeConversation.id),
                    targetUserId: activePeerId,
                    roomId: activeCallRoomId,
                    callType: activeCallType,
                });
            }

            if (zegoInstance && typeof zegoInstance.destroy === 'function') {
                zegoInstance.destroy();
            }
            zegoInstance = null;
            activeCallRoomId = null;
            activeCallType = 'video';
            if (videoContainer) videoContainer.innerHTML = '';
            if (videoModal) videoModal.hidden = true;
            closeIncomingCallModal();
        }

        async function ensureZegoLoaded() {
            if (window.ZegoUIKitPrebuilt) return window.ZegoUIKitPrebuilt;

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

                    if (window.ZegoUIKitPrebuilt) return window.ZegoUIKitPrebuilt;
                } catch (error) {
                    // try next CDN
                }
            }

            throw new Error('Zego SDK load failed');
        }

        async function startVideoCall(options = {}) {
            if (!activeConversation) {
                alert('Chọn một cuộc trò chuyện trước khi gọi video.');
                return;
            }

            if (!zegoAppId || !zegoServerSecret) {
                alert('Thiếu cấu hình ZegoCloud.');
                return;
            }

            let zegoSdk;
            try {
                zegoSdk = await ensureZegoLoaded();
            } catch (error) {
                console.error(error);
                alert('Không tải được SDK gọi video.');
                return;
            }

            const callType = String(options.callType || 'video');
            const roomId = String(options.roomId || `room_${activeConversation.id}`);
            const userId = `user_${senderId}`;
            const userName = senderName;
            const kitToken = zegoSdk.generateKitTokenForTest(zegoAppId, zegoServerSecret, roomId, userId, userName);

            closeVideoModal(false);
            if (videoModal) videoModal.hidden = false;
            if (videoTitle) videoTitle.textContent = callType === 'voice' ? 'Cuộc gọi thoại' : 'Cuộc gọi video';
            if (videoRoom) videoRoom.textContent = `Phòng: ${roomId}`;

            zegoInstance = zegoSdk.create(kitToken);
            activeCallRoomId = roomId;
            activeCallType = callType;

            if (!options.skipSignal) {
                sendCallSignal('incoming', {
                    conversationId: Number(activeConversation.id),
                    targetUserId: activePeerId,
                    roomId,
                    callType,
                });
            }

            zegoInstance.joinRoom({
                container: videoContainer,
                scenario: {
                    mode: zegoSdk.OneONoneCall
                },
                turnOnMicrophoneWhenJoining: true,
                turnOnCameraWhenJoining: callType !== 'voice',
                showPreJoinView: false,
                showScreenSharingButton: false,
                showRoomDetailsButton: false,
                maxUsers: 2,
                onLeaveRoom: () => closeVideoModal(false),
            });
        }

        function setView(view) {
            const isThread = view === 'thread';
            listView.hidden = isThread;
            threadView.hidden = !isThread;
            backBtn.hidden = !isThread;
            popupTitle.textContent = isThread ? 'Cuộc trò chuyện' : 'Đoạn chat';

            if (!isThread && threadTypingIndicator) {
                threadTypingIndicator.hidden = true;
            }
        }

        function getDefaultThreadStatus(conversation = activeConversation) {
            if (!conversation) {
                return 'Đang hoạt động';
            }

            return conversation.last_message_at ? `Hoạt động · ${formatTime(conversation.last_message_at)}` : 'Đang hoạt động';
        }

        function resetThreadTypingIndicator() {
            if (typingIndicatorResetTimer) {
                window.clearTimeout(typingIndicatorResetTimer);
                typingIndicatorResetTimer = null;
            }

            if (activeConversation) {
                setConversationTypingPreview(activeConversation.id, false);
            }
        }

        function showThreadTypingIndicator(senderName = '') {
            if (!activeConversation) {
                return;
            }

            const displayName = String(senderName || activeConversation?.name || 'Người dùng').trim();
            setConversationTypingPreview(activeConversation.id, true, displayName);

            if (typingIndicatorResetTimer) {
                window.clearTimeout(typingIndicatorResetTimer);
            }

            typingIndicatorResetTimer = window.setTimeout(function() {
                resetThreadTypingIndicator();
            }, TYPING_STATUS_RESET_MS);
        }

        async function sendTypingStatus(isTyping) {
            if (!activeConversation || !typingUrlTemplate) {
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
                await fetch(typingUrlTemplate.replace('__ID__', String(activeConversation.id)), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        sender_id: Number(senderId),
                        is_typing: Boolean(isTyping),
                        test_mode: isLocalTestMode ? 1 : 0,
                    }),
                });
            } catch (error) {
                console.error(error);
            }
        }

        function scheduleTypingStatusFromInput() {
            if (!activeConversation) {
                return;
            }

            const hasText = String(threadInput.value || '').trim().length > 0;

            if (hasText) {
                sendTypingStatus(true);

                if (typingStopTimer) {
                    window.clearTimeout(typingStopTimer);
                }

                typingStopTimer = window.setTimeout(function() {
                    sendTypingStatus(false);
                    typingStopTimer = null;
                }, TYPING_IDLE_MS);
                return;
            }

            if (typingStopTimer) {
                window.clearTimeout(typingStopTimer);
                typingStopTimer = null;
            }

            sendTypingStatus(false);
        }

        function handleTypingStatusEvent(event) {
            const payload = event && typeof event === 'object' ? event : null;
            const conversationId = Number(payload?.conversation_id) || 0;
            if (!conversationId) {
                return;
            }

            const sender = Number(payload?.sender_id) || 0;
            if (sender === Number(senderId || 0)) {
                return;
            }

            if (Boolean(payload?.is_typing)) {
                setConversationTypingPreview(conversationId, true, String(payload?.sender_name || ''));
                return;
            }

            setConversationTypingPreview(conversationId, false);
        }

        function openThread(conversation) {
            activeConversation = conversation;
            activePeerId = resolveTargetPeerId(conversation);
            markConversationRead(conversation.id);
            threadName.textContent = conversation.name || 'Người dùng';
            threadAvatar.src = conversation.avatar || 'https://ui-avatars.com/api/?name=User';
            threadStatus.textContent = getDefaultThreadStatus(conversation);
            applyActiveThreadBackground();
            setView('thread');

            if (threadTypingIndicator) {
                const typingState = typingPreviewByConversation.get(Number(conversation.id)) || null;
                threadTypingIndicator.hidden = !Boolean(typingState?.is_typing);
            }

            if (shouldUseConversationCache(conversation) && renderConversationFromCache(conversation.id)) {
                return;
            }

            threadMessages.innerHTML = '<div class="messenger-popup-empty">Đang tải lịch sử trò chuyện...</div>';
            threadInput.disabled = true;
            threadSend.disabled = true;
            loadThreadMessages(conversation.id, {
                forceRefresh: true,
            });
        }

        function refreshConversationPreview(conversationId, body, messageSenderId = null) {
            const conversation = conversations.find((item) => Number(item.id) === Number(conversationId));
            if (!conversation) return;

            const me = Number(senderId || 0);

            conversation.last_message = body;
            setConversationTypingPreview(conversationId, false);
            if (messageSenderId !== null) {
                conversation.last_message_sender_id = Number(messageSenderId) || null;
            }
            conversation.has_unread = Number(messageSenderId || 0) > 0 && Number(messageSenderId || 0) !== me;
            conversation.unread_count = conversation.has_unread ? Math.max(1, Number(conversation.unread_count || 0) + 1) : 0;
            conversation.last_message_at = new Date().toISOString();
            if (!conversation.has_unread) {
                conversationSeenAt.set(Number(conversation.id), Date.now());
            }
            conversations = conversations
                .slice()
                .sort((a, b) => {
                    const unreadA = isConversationUnread(a) ? 1 : 0;
                    const unreadB = isConversationUnread(b) ? 1 : 0;
                    if (unreadA !== unreadB) {
                        return unreadB - unreadA;
                    }

                    const lastA = new Date(a.last_message_at || 0).getTime() || 0;
                    const lastB = new Date(b.last_message_at || 0).getTime() || 0;
                    return lastB - lastA;
                });
            renderList();
            updateUnreadBadge();
        }

        function handleRealtimeMessage(rawMessage) {
            const message = rawMessage && typeof rawMessage === 'object' ? rawMessage : null;
            const conversationId = Number(message?.conversation_id) || 0;
            if (!conversationId) return;

            const conversation = conversations.find((item) => Number(item.id) === conversationId);
            if (!conversation) return;

            const sender = Number(message?.sender_id) || 0;
            const isActiveThread = Boolean(activeConversation) && Number(activeConversation.id) === conversationId && !threadView.hidden;

            if (isActiveThread && !hasMessageInThread(message?.id)) {
                activeMessages = [...activeMessages, message];
                threadMessages.insertAdjacentHTML('beforeend', renderThreadMessage(message));
                threadMessages.scrollTop = threadMessages.scrollHeight;
                resetThreadTypingIndicator();
            }

            mergeMessageIntoCache(conversationId, message);

            const preview = previewFromMessage(message);
            refreshConversationPreview(conversationId, preview, sender);

            if (isActiveThread) {
                markConversationRead(conversationId);
                renderList();
            }
        }

        function unsubscribeConversationRealtime(conversationId) {
            const key = Number(conversationId) || 0;
            if (!key) return;

            const unsubscribe = conversationSubscriptions.get(key);
            if (typeof unsubscribe === 'function') {
                unsubscribe();
            } else if (window.Echo) {
                window.Echo.leave(`chat.${key}`);
            }

            conversationSubscriptions.delete(key);
        }

        function subscribeConversationRealtime(conversationId) {
            const key = Number(conversationId) || 0;
            if (!key || conversationSubscriptions.has(key)) return;

            if (window.Echo) {
                bindEchoConnectionEvents();
                const channelName = `chat.${key}`;
                const channels = [];
                const primaryChannel = isLocalTestMode ? window.Echo.channel(channelName) : window.Echo.private(channelName);
                channels.push(primaryChannel);

                // Defensive fallback: listen on both channel types to avoid missing events when mode drifts.
                channels.push(isLocalTestMode ? window.Echo.private(channelName) : window.Echo.channel(channelName));

                channels.forEach((channel) => {
                    channel.listen('.MessageSent', (event) => {
                        handleRealtimeMessage(event?.message);
                        scheduleFastConversationSync(250);
                    });

                    channel.listen('.ChatBackgroundChanged', (event) => {
                        const updatedConversationId = Number(event?.conversationId) || 0;
                        if (!updatedConversationId) {
                            return;
                        }

                        updateConversationBackground(updatedConversationId, event?.backgroundUrl || null);

                        if (updatedConversationId === Number(activeConversation?.id || 0)) {
                            applyActiveThreadBackground();
                        }
                    });

                    channel.listen('.CallSignal', (event) => {
                        handleCallSignalEvent(event);
                    });

                    channel.listen('.TypingStatusChanged', (event) => {
                        handleTypingStatusEvent(event);
                    });
                });

                conversationSubscriptions.set(key, () => {
                    if (window.Echo) {
                        window.Echo.leave(channelName);
                    }
                });
                return;
            }

            if (typeof window.subscribeToConversation === 'function') {
                const unsubscribe = window.subscribeToConversation(
                    key,
                    (message) => handleRealtimeMessage(message),
                    isLocalTestMode
                );
                if (typeof unsubscribe === 'function') {
                    conversationSubscriptions.set(key, unsubscribe);
                }
                return;
            }

            if (!window.Echo) return;
        }

        function syncRealtimeSubscriptions() {
            const nextIds = new Set(conversations.map((item) => Number(item.id)).filter((id) => id > 0));

            Array.from(conversationSubscriptions.keys()).forEach((existingId) => {
                if (!nextIds.has(existingId)) {
                    unsubscribeConversationRealtime(existingId);
                }
            });

            nextIds.forEach((id) => subscribeConversationRealtime(id));
        }

        function mergeConversations(payload) {
            const incoming = (Array.isArray(payload) ? payload : []).map(normalizeConversation);
            conversations = incoming
                .slice()
                .sort((a, b) => {
                    const unreadA = isConversationUnread(a) ? 1 : 0;
                    const unreadB = isConversationUnread(b) ? 1 : 0;
                    if (unreadA !== unreadB) {
                        return unreadB - unreadA;
                    }

                    const lastA = new Date(a.last_message_at || 0).getTime() || 0;
                    const lastB = new Date(b.last_message_at || 0).getTime() || 0;
                    return lastB - lastA;
                });

            updateUnreadBadge();
        }

        function latestThreadMessageTimestamp() {
            if (!Array.isArray(activeMessages) || !activeMessages.length) {
                return 0;
            }

            return activeMessages.reduce((latest, message) => {
                const timestamp = toTimestamp(message?.created_at);
                return timestamp > latest ? timestamp : latest;
            }, 0);
        }

        function syncActiveThreadIfStale() {
            if (!activeConversation || threadView.hidden) {
                return;
            }

            const activeId = Number(activeConversation.id) || 0;
            if (!activeId) {
                return;
            }

            const conversation = conversations.find((item) => Number(item.id) === activeId);
            if (!conversation) {
                return;
            }

            const serverLatest = toTimestamp(conversation.last_message_at);
            const localLatest = latestThreadMessageTimestamp();

            if (serverLatest > localLatest) {
                loadThreadMessages(activeId, {
                    forceRefresh: true
                });
            }
        }

        async function sendThreadMessage() {
            const text = String(threadInput.value || '').trim();
            if (!text || !activeConversation) return;

            threadSend.disabled = true;

            try {
                const url = new URL(`/chat/conversations/${activeConversation.id}/messages`, window.location.origin);
                const response = await fetch(url.toString(), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        body: text,
                        type: 'text',
                        sender_id: Number(senderId),
                        test_mode: isLocalTestMode ? 1 : 0,
                    }),
                });

                const contentType = String(response.headers.get('content-type') || '').toLowerCase();
                const isJsonResponse = contentType.includes('application/json');
                const payload = isJsonResponse ? await response.json() : null;

                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Phiên đăng nhập đã hết. Vui lòng tải lại trang và đăng nhập lại.');
                    }

                    if (response.status === 419) {
                        throw new Error('Phiên làm việc đã hết hạn (CSRF). Vui lòng tải lại trang.');
                    }

                    throw new Error(payload?.message || `Không thể gửi tin nhắn (HTTP ${response.status}).`);
                }

                if (!payload || typeof payload !== 'object' || !payload.id) {
                    throw new Error('Phản hồi gửi tin nhắn không hợp lệ.');
                }

                mergeMessageIntoCache(activeConversation.id, payload);
                activeMessages.push(payload);
                threadInput.value = '';
                sendTypingStatus(false);
                threadMessages.insertAdjacentHTML('beforeend', renderThreadMessage(payload));
                threadMessages.scrollTop = threadMessages.scrollHeight;
                refreshConversationPreview(activeConversation.id, text, Number(senderId));
                scheduleFastConversationSync(200);
            } catch (error) {
                console.error(error);
                const errorMessage = escapeHtml(error?.message || 'Không thể gửi tin nhắn.');
                threadMessages.insertAdjacentHTML('beforeend', `<div class="messenger-popup-empty">${errorMessage}</div>`);
                threadMessages.scrollTop = threadMessages.scrollHeight;
            } finally {
                threadSend.disabled = false;
            }
        }

        async function loadThreadMessages(conversationId, options = {}) {
            const forceRefresh = Boolean(options.forceRefresh);
            const cached = forceRefresh ? null : getConversationCache(conversationId);
            if (cached && Array.isArray(cached.messages)) {
                activeMessages = cached.messages.slice();
                activeMessagesMeta = cached.meta && typeof cached.meta === 'object' ? {
                    has_more: Boolean(cached.meta.has_more),
                    next_before_id: Number(cached.meta.next_before_id) || null,
                    limit: Number(cached.meta.limit) || 80,
                } : {
                    has_more: false,
                    next_before_id: null,
                    limit: 80,
                };

                threadMessages.innerHTML = activeMessages.length ?
                    activeMessages.map(renderThreadMessage).join('') :
                    '<div class="messenger-popup-empty">Chưa có lịch sử trò chuyện.</div>';
                threadMessages.scrollTop = threadMessages.scrollHeight;
                threadInput.disabled = false;
                threadSend.disabled = false;
                return;
            }

            try {
                const url = new URL(`/chat/conversations/${conversationId}/messages`, window.location.origin);
                url.searchParams.set('sender_id', senderId);
                url.searchParams.set('limit', '80');
                if (isLocalTestMode) {
                    url.searchParams.set('test_mode', '1');
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                activeMessages = Array.isArray(payload) ? payload : (Array.isArray(payload?.data) ? payload.data : []);
                activeMessagesMeta = (!Array.isArray(payload) && payload?.meta && typeof payload.meta === 'object') ? payload.meta : {
                    has_more: false,
                    next_before_id: null,
                };
                setConversationCache(conversationId, {
                    messages: activeMessages,
                    meta: activeMessagesMeta,
                });
                threadMessages.innerHTML = activeMessages.length ?
                    activeMessages.map(renderThreadMessage).join('') :
                    '<div class="messenger-popup-empty">Chưa có lịch sử trò chuyện.</div>';
                threadMessages.scrollTop = threadMessages.scrollHeight;
                threadInput.disabled = false;
                threadSend.disabled = false;
            } catch (error) {
                console.error(error);
                threadMessages.innerHTML = '<div class="messenger-popup-empty">Không thể tải lịch sử trò chuyện.</div>';
            }
        }

        function renderList() {
            updateUnreadBadge();

            const keyword = String(searchInput?.value || '').trim().toLowerCase();
            const filtered = conversations.filter((item) => {
                if (activeFilter === 'unread' && !isConversationUnread(item)) {
                    return false;
                }

                if (!keyword) return true;
                const haystack = `${item.name || ''} ${item.last_message || ''} ${item.email || ''}`.toLowerCase();
                return haystack.includes(keyword);
            });

            if (!filtered.length) {
                list.innerHTML = '<div class="messenger-popup-empty">Không tìm thấy cuộc trò chuyện phù hợp.</div>';
                return;
            }

            list.innerHTML = filtered.map((item) => {
                const preview = previewText(item);
                const time = formatTime(item.last_message_at);
                const sep = time ? '<span class="messenger-popup-sep">·</span>' : '';
                const timeMarkup = time ? `<span class="messenger-popup-time-inline">${escapeHtml(time)}</span>` : '';
                const unreadClass = isConversationUnread(item) ? 'is-unread' : '';
                const unreadDot = isConversationUnread(item) ? '<span class="messenger-popup-unread-dot" aria-hidden="true"></span>' : '';
                const activeDot = item.is_recently_active ? '<span class="messenger-popup-active-dot" aria-label="Đang hoạt động"></span>' : '';

                return `
                <button type="button" class="messenger-popup-item ${unreadClass}" data-conversation-id="${item.id}">
                    <span class="messenger-popup-avatar-wrap">
                        <img class="messenger-popup-avatar" src="${escapeHtml(item.avatar)}" alt="${escapeHtml(item.name)}">
                        ${activeDot}
                    </span>
                    <div class="messenger-popup-meta">
                        <div class="messenger-popup-name">${escapeHtml(item.name)}</div>
                        <div class="messenger-popup-preview-row">
                            <span class="messenger-popup-preview-text">${escapeHtml(preview)}</span>
                            ${sep}
                            ${timeMarkup}
                        </div>
                    </div>
                    ${unreadDot}
                </button>
            `;
            }).join('');

            list.querySelectorAll('[data-conversation-id]').forEach((button) => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const conversationId = Number(button.dataset.conversationId) || 0;
                    const conversation = conversations.find((item) => Number(item.id) === conversationId);
                    if (conversation) {
                        openThread(conversation);
                    }
                });
            });
        }

        async function loadConversations(options = {}) {
            const {
                silent = false
            } = options;

            if (loadingConversations) return;
            loadingConversations = true;

            if (!silent) {
                list.innerHTML = '<div class="messenger-popup-empty">Đang tải cuộc trò chuyện...</div>';
            }

            try {
                const url = new URL(conversationsUrl, window.location.origin);
                url.searchParams.set('sender_id', senderId);
                if (isLocalTestMode) {
                    url.searchParams.set('test_mode', '1');
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                mergeConversations(payload);
                if (activeConversation && !threadView.hidden) {
                    applyActiveThreadBackground();
                }
                renderList();
                syncRealtimeSubscriptions();
                syncActiveThreadIfStale();
            } catch (error) {
                console.error(error);
                if (!silent) {
                    list.innerHTML = '<div class="messenger-popup-empty">Không thể tải danh sách chat.</div>';
                }
            } finally {
                loadingConversations = false;
            }
        }

        function startAutoRefresh() {
            if (refreshTimer) return;
            refreshTimer = window.setInterval(() => {
                if (realtimeConnected) {
                    return;
                }

                loadConversations({
                    silent: true
                });
            }, DISCONNECTED_REFRESH_INTERVAL_MS);
        }

        function stopAutoRefresh() {
            if (!refreshTimer) return;
            window.clearInterval(refreshTimer);
            refreshTimer = null;
        }

        function openPanel() {
            panel.hidden = false;
            if (searchInput) {
                searchInput.value = '';
            }
            loadConversations({
                silent: false
            });
            startAutoRefresh();
            startCallSignalPolling();
            setView('list');
        }

        function closePanel() {
            panel.hidden = true;
        }

        function togglePanel() {
            if (panel.hidden) openPanel();
            else closePanel();
        }

        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            togglePanel();
        });

        closeBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            closePanel();
        });

        backBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            sendTypingStatus(false);
            setView('list');
            activeConversation = null;
            activePeerId = null;
            resetThreadTypingIndicator();
            renderList();
        });

        searchInput?.addEventListener('input', renderList);

        threadSend.addEventListener('click', function() {
            sendThreadMessage();
        });

        threadInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                threadSend.click();
            }
        });

        voiceToggle?.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (recordingVoice) stopVoiceRecording();
            else startVoiceRecording();
        });

        imageToggle?.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            imageInput?.click();
        });

        imageInput?.addEventListener('change', function() {
            const file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
            if (file) {
                closeEmojiPicker();
                sendFileMessage(file);
                imageInput.value = '';
            }
        });

        emojiToggle?.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            toggleEmojiPicker();
        });

        emojiPicker?.querySelectorAll('[data-emoji-value]').forEach((button) => {
            button.addEventListener('click', function() {
                insertEmoji(button.dataset.emojiValue || '');
            });
        });

        videoToggle?.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            startVideoCall();
        });

        videoClose?.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            closeVideoModal(true);
        });

        incomingCallAccept?.addEventListener('click', async function(event) {
            event.preventDefault();
            event.stopPropagation();

            const pendingCall = incomingCall;
            if (!pendingCall) return;

            const conversationId = Number(pendingCall.conversation_id) || 0;
            if (!conversationId) return;

            const matchedConversation = conversations.find((item) => Number(item.id) === conversationId) || null;
            if (matchedConversation) {
                openThread(matchedConversation);
            } else {
                activeConversation = {
                    id: conversationId,
                    name: pendingCall.caller_name || 'Người dùng',
                    avatar: 'https://ui-avatars.com/api/?name=User',
                };
                activePeerId = Number(pendingCall.sender_id) || null;
            }

            closeIncomingCallModal();
            await sendCallSignal('accepted', {
                conversationId,
                targetUserId: Number(pendingCall.sender_id) || activePeerId,
                roomId: pendingCall.room_id,
                callType: pendingCall.call_type,
            });

            startVideoCall({
                roomId: pendingCall.room_id,
                callType: pendingCall.call_type || 'video',
                skipSignal: true,
            });
        });

        incomingCallReject?.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();

            const pendingCall = incomingCall;
            if (!pendingCall) return;

            sendCallSignal('rejected', {
                conversationId: Number(pendingCall.conversation_id) || 0,
                targetUserId: Number(pendingCall.sender_id) || activePeerId,
                roomId: pendingCall.room_id,
                callType: pendingCall.call_type,
            });

            closeIncomingCallModal();
        });

        tabs.forEach((tab) => {
            tab.addEventListener('click', function() {
                tabs.forEach((item) => item.classList.remove('is-active'));
                tab.classList.add('is-active');
                activeFilter = String(tab.dataset.filter || 'all');
                renderList();
            });
        });

        document.addEventListener('click', function(event) {
            if (panel.hidden) return;
            if (root.contains(event.target)) return;
            closePanel();
            closeEmojiPicker();
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closePanel();
            if (event.key === 'Escape') closeEmojiPicker();
        });

        window.addEventListener('storage', function(event) {
            if (event.key !== CALL_SIGNAL_STORAGE_KEY || !event.newValue) {
                return;
            }

            try {
                const payload = JSON.parse(event.newValue);
                if (!payload || !payload.conversation_id) {
                    return;
                }

                handleCallSignalEvent(payload);
            } catch (error) {
                console.error(error);
            }
        });

        window.addEventListener('beforeunload', function() {
            sendTypingStatus(false);
            Array.from(conversationSubscriptions.keys()).forEach((conversationId) => {
                unsubscribeConversationRealtime(conversationId);
            });
            stopAutoRefresh();
            stopCallSignalPolling();
            stopRealtimeSyncWatchdog();
            if (fastSyncTimer) {
                window.clearTimeout(fastSyncTimer);
                fastSyncTimer = null;
            }

            if (typingStopTimer) {
                window.clearTimeout(typingStopTimer);
                typingStopTimer = null;
            }

            if (typingIndicatorResetTimer) {
                window.clearTimeout(typingIndicatorResetTimer);
                typingIndicatorResetTimer = null;
            }
        });

        threadInput.addEventListener('input', function() {
            updateComposerState();
            scheduleTypingStatusFromInput();
        });
        updateComposerState();
        bindEchoConnectionEvents();
        loadConversations({
            silent: true
        });
        startAutoRefresh();
        startCallSignalPolling();
        startRealtimeSyncWatchdog();
    })();
</script>