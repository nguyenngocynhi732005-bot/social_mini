@php
$popupSenderId = auth()->id();
if (!$popupSenderId) {
$popupSenderId = (int) \App\Models\User::query()->orderBy('id')->value('id');
}

$popupSenderId = $popupSenderId ?: 0;
@endphp

<div id="messenger-popup-root"
    class="messenger-popup-root"
    data-conversations-url="{{ route('chat.conversations.index') }}"
    data-chat-url="{{ route('chat.test') }}"
    data-zego-app-id="{{ (int) env('ZEGO_APP_ID', 1404858540) }}"
    data-zego-server-secret="{{ (string) env('ZEGO_SERVER_SECRET', '43f4f3877e081c999c7baaea7011ff94') }}"
    data-sender-id="{{ $popupSenderId }}">
    <button type="button" class="messenger-popup-toggle" data-messenger-toggle aria-label="Mở Messenger">
        <i class="fab fa-facebook-messenger"></i>
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
                <input id="messenger-popup-search-input" type="search" placeholder="Tìm kiếm trên Messenger" autocomplete="off">
            </label>

            <div class="messenger-popup-tabs">
                <button type="button" class="messenger-popup-tab is-active" data-filter="all">Tất cả</button>
                <button type="button" class="messenger-popup-tab" data-filter="recent">Chưa đọc</button>
                <button type="button" class="messenger-popup-tab" data-filter="all">Nhóm</button>
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

        <a class="messenger-popup-footer" href="{{ route('chat.test') }}">
            Xem tất cả trong Messenger
        </a>
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
    }

    .messenger-popup-panel {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 420px;
        max-width: calc(100vw - 24px);
        max-height: min(600px, calc(100vh - 72px));
        background: #ffffff;
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

    .messenger-thread-composer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px 12px;
        border-top: 1px solid #e4e6eb;
        background: #ffffff;
        position: relative;
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
        gap: 12px;
        padding: 10px 12px;
        border-radius: 14px;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .messenger-popup-item:hover {
        background: #f0f2f5;
    }

    .messenger-popup-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 auto;
    }

    .messenger-popup-meta {
        min-width: 0;
        flex: 1 1 auto;
    }

    .messenger-popup-name {
        font-size: 15px;
        font-weight: 700;
        color: #1c1e21;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .messenger-popup-preview {
        margin-top: 2px;
        font-size: 13px;
        color: #65676b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        padding: 12px;
    }

    .messenger-thread-video-modal[hidden] {
        display: none !important;
    }

    .messenger-thread-video-shell {
        width: min(960px, calc(100vw - 24px));
        height: min(660px, calc(100vh - 24px));
        border-radius: 16px;
        overflow: hidden;
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.45);
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
        background: #ffffff;
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
    }
</style>

<script>
    (function() {
        const root = document.getElementById('messenger-popup-root');
        if (!root) return;

        const toggle = root.querySelector('[data-messenger-toggle]');
        const panel = root.querySelector('[data-messenger-panel]');
        const closeBtn = root.querySelector('[data-messenger-close]');
        const backBtn = root.querySelector('[data-messenger-back]');
        const list = root.querySelector('[data-messenger-list]');
        const searchInput = root.querySelector('#messenger-popup-search-input');
        const popupTitle = root.querySelector('[data-popup-title]');
        const listView = root.querySelector('[data-popup-view="list"]');
        const threadView = root.querySelector('[data-popup-view="thread"]');
        const threadMessages = root.querySelector('[data-thread-messages]');
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
        const tabs = Array.from(root.querySelectorAll('[data-messenger-tab], .messenger-popup-tab'));
        const conversationsUrl = root.dataset.conversationsUrl;
        const zegoAppId = Number(root.dataset.zegoAppId || 0);
        const zegoServerSecret = String(root.dataset.zegoServerSecret || '').trim();
        const senderId = root.dataset.senderId || '0';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let conversations = [];
        let loaded = false;
        let activeConversation = null;
        let activeMessages = [];
        let voiceRecorder = null;
        let voiceChunks = [];
        let voiceStream = null;
        let recordingVoice = false;
        let zegoInstance = null;

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
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

        function previewText(item) {
            const text = String(item.last_message || '').trim();
            return text || 'Bắt đầu cuộc trò chuyện';
        }

        function previewForFile(file) {
            const type = String(file?.type || '').toLowerCase();
            if (type.startsWith('audio/')) return 'Đã gửi tin nhắn thoại';
            if (type.startsWith('image/')) return 'Đã gửi ảnh';
            return 'Đã gửi tệp';
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
            const fileUrl = escapeHtml(attachment?.file_url || attachment?.file_path || '');
            const fileName = escapeHtml(attachment?.file_name || 'attachment');

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
            const content = body ? `<div>${escapeHtml(body)}</div>` : '';
            const media = attachments.map(renderAttachment).join('');

            return `
                <div class="messenger-thread-message ${mine ? 'me' : 'incoming'}">
                    <div class="messenger-thread-bubble">
                        ${content}
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
            formData.append('test_mode', '1');
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

                threadMessages.insertAdjacentHTML('beforeend', renderThreadMessage(payload));
                threadMessages.scrollTop = threadMessages.scrollHeight;
                refreshConversationPreview(activeConversation.id, previewForFile(file));
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

        function closeVideoModal() {
            if (zegoInstance && typeof zegoInstance.destroy === 'function') {
                zegoInstance.destroy();
            }
            zegoInstance = null;
            if (videoContainer) videoContainer.innerHTML = '';
            if (videoModal) videoModal.hidden = true;
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

        async function startVideoCall() {
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

            const roomId = `room_${activeConversation.id}`;
            const userId = `user_${senderId}`;
            const userName = 'User ' + senderId;
            const kitToken = zegoSdk.generateKitTokenForTest(zegoAppId, zegoServerSecret, roomId, userId, userName);

            closeVideoModal();
            if (videoModal) videoModal.hidden = false;
            if (videoTitle) videoTitle.textContent = 'Cuộc gọi video';
            if (videoRoom) videoRoom.textContent = `Phòng: ${roomId}`;

            zegoInstance = zegoSdk.create(kitToken);
            zegoInstance.joinRoom({
                container: videoContainer,
                scenario: {
                    mode: zegoSdk.OneONoneCall
                },
                turnOnMicrophoneWhenJoining: true,
                turnOnCameraWhenJoining: true,
                showPreJoinView: false,
                showScreenSharingButton: false,
                showRoomDetailsButton: false,
                maxUsers: 2,
                onLeaveRoom: () => closeVideoModal(),
            });
        }

        function setView(view) {
            const isThread = view === 'thread';
            listView.hidden = isThread;
            threadView.hidden = !isThread;
            backBtn.hidden = !isThread;
            popupTitle.textContent = isThread ? 'Cuộc trò chuyện' : 'Đoạn chat';
        }

        function openThread(conversation) {
            activeConversation = conversation;
            threadName.textContent = conversation.name || 'Người dùng';
            threadAvatar.src = conversation.avatar || 'https://ui-avatars.com/api/?name=User';
            threadStatus.textContent = conversation.last_message_at ? `Hoạt động · ${formatTime(conversation.last_message_at)}` : 'Đang hoạt động';
            threadMessages.innerHTML = '<div class="messenger-popup-empty">Đang tải lịch sử trò chuyện...</div>';
            threadInput.disabled = true;
            threadSend.disabled = true;
            setView('thread');
            loadThreadMessages(conversation.id);
        }

        function refreshConversationPreview(conversationId, body) {
            const conversation = conversations.find((item) => Number(item.id) === Number(conversationId));
            if (!conversation) return;

            conversation.last_message = body;
            conversation.last_message_at = new Date().toISOString();
            conversations = conversations
                .slice()
                .sort((a, b) => {
                    const lastA = new Date(a.last_message_at || 0).getTime() || 0;
                    const lastB = new Date(b.last_message_at || 0).getTime() || 0;
                    return lastB - lastA;
                });
            renderList();
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
                        test_mode: true,
                    }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload?.message || 'Không thể gửi tin nhắn.');
                }

                threadInput.value = '';
                threadMessages.insertAdjacentHTML('beforeend', renderThreadMessage(payload));
                threadMessages.scrollTop = threadMessages.scrollHeight;
                refreshConversationPreview(activeConversation.id, text);
            } catch (error) {
                console.error(error);
                threadMessages.insertAdjacentHTML('beforeend', '<div class="messenger-popup-empty">Không thể gửi tin nhắn.</div>');
                threadMessages.scrollTop = threadMessages.scrollHeight;
            } finally {
                threadSend.disabled = false;
            }
        }

        async function loadThreadMessages(conversationId) {
            try {
                const url = new URL(`/chat/conversations/${conversationId}/messages`, window.location.origin);
                url.searchParams.set('sender_id', senderId);
                if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                    url.searchParams.set('test_mode', '1');
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                activeMessages = Array.isArray(payload) ? payload : [];
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
            const keyword = String(searchInput?.value || '').trim().toLowerCase();
            const filtered = conversations.filter((item) => {
                if (!keyword) return true;
                const haystack = `${item.name || ''} ${item.last_message || ''} ${item.email || ''}`.toLowerCase();
                return haystack.includes(keyword);
            });

            if (!filtered.length) {
                list.innerHTML = '<div class="messenger-popup-empty">Không tìm thấy cuộc trò chuyện phù hợp.</div>';
                return;
            }

            list.innerHTML = filtered.map((item) => `
                <button type="button" class="messenger-popup-item" data-conversation-id="${item.id}">
                    <img class="messenger-popup-avatar" src="${escapeHtml(item.avatar)}" alt="${escapeHtml(item.name)}">
                    <div class="messenger-popup-meta">
                        <div class="messenger-popup-name">${escapeHtml(item.name)}</div>
                        <div class="messenger-popup-preview">${escapeHtml(previewText(item))}</div>
                    </div>
                    <div class="messenger-popup-time">${escapeHtml(formatTime(item.last_message_at))}</div>
                </button>
            `).join('');

            list.querySelectorAll('[data-conversation-id]').forEach((button) => {
                button.addEventListener('click', function() {
                    const conversationId = Number(button.dataset.conversationId) || 0;
                    const conversation = conversations.find((item) => Number(item.id) === conversationId);
                    if (conversation) {
                        openThread(conversation);
                    }
                });
            });
        }

        async function loadConversations() {
            if (loaded) return;
            loaded = true;
            list.innerHTML = '<div class="messenger-popup-empty">Đang tải cuộc trò chuyện...</div>';

            try {
                const url = new URL(conversationsUrl, window.location.origin);
                url.searchParams.set('sender_id', senderId);
                if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                    url.searchParams.set('test_mode', '1');
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                conversations = Array.isArray(payload) ? payload : [];
                renderList();
            } catch (error) {
                console.error(error);
                list.innerHTML = '<div class="messenger-popup-empty">Không thể tải danh sách chat.</div>';
            }
        }

        function openPanel() {
            panel.hidden = false;
            if (searchInput) {
                searchInput.value = '';
            }
            loadConversations();
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
            setView('list');
            activeConversation = null;
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
            closeVideoModal();
        });

        tabs.forEach((tab) => {
            tab.addEventListener('click', function() {
                tabs.forEach((item) => item.classList.remove('is-active'));
                tab.classList.add('is-active');
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

        threadInput.addEventListener('input', updateComposerState);
        updateComposerState();
    })();
</script>