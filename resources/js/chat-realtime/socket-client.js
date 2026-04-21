import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const wsHost = process.env.MIX_PUSHER_HOST || window.location.hostname;
const wsPort = Number(process.env.MIX_PUSHER_PORT || 6001);
const wsScheme = process.env.MIX_PUSHER_SCHEME || 'http';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY || 'local-key',
    cluster: process.env.MIX_PUSHER_APP_CLUSTER || 'mt1',
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wsPort,
    forceTLS: wsScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

window.subscribeToConversation = function (conversationId, onMessage, testMode = false) {
    if (!window.Echo) {
        return function () {};
    }

    const channelName = `chat.${conversationId}`;
    const channel = testMode ? window.Echo.channel(channelName) : window.Echo.private(channelName);

    channel.listen('.MessageSent', (event) => {
        if (typeof onMessage === 'function') {
            onMessage(event.message);
        }
    });

    channel.listen('.MessageDeleted', (event) => {
        if (typeof window.removeMessageFromUI === 'function') {
            window.removeMessageFromUI(event.messageId);
        }
    });

    return function unsubscribe() {
        if (window.Echo) {
            window.Echo.leave(channelName);
        }
    };
};

window.listenForMessages = function (conversationId) {
    window.subscribeToConversation(conversationId, (message) => {
        if (typeof window.appendMessageToUI === 'function') {
            window.appendMessageToUI(message);
        }
    });
};
