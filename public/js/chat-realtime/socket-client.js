import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher', // Chúng ta dùng driver pusher để kết nối với server nội bộ [cite: 120]
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

/**
 * Hàm lắng nghe tin nhắn mới từ kênh hội thoại 
 */
// Viết thế này để trình duyệt "thấy" được hàm từ bên ngoài
window.listenForMessages = function (conversationId) {
    console.log("Bắt đầu lắng nghe hội thoại ID: " + conversationId);
    
    window.Echo.private(`chat.${conversationId}`)
        .listen('.MessageSent', (e) => {
            console.log('Đã nhận tin nhắn mới:', e.message);
            if (typeof appendMessageToUI === "function") {
                appendMessageToUI(e.message);
            }
        });
};