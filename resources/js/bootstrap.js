/**
 * Load dependencies
 */
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Broadcasting is opt-in. When disabled, no WebSocket connection is attempted
// at all (see BROADCAST_ENABLED / VITE_BROADCAST_ENABLED in .env).
if (import.meta.env.VITE_BROADCAST_ENABLED === 'true' || import.meta.env.VITE_BROADCAST_ENABLED === true) {
    // Soketi speaks the Pusher protocol, so pusher-js is the protocol client.
    // 0.0.0.0 is a server bind address and is not reachable from a browser,
    // so map it to the loopback address for the client connection.
    const rawHost = import.meta.env.VITE_SOKETI_HOST ?? '127.0.0.1';
    const wsHost = rawHost === '0.0.0.0' ? '127.0.0.1' : rawHost;

    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_SOKETI_APP_KEY,
            cluster: import.meta.env.VITE_SOKETI_APP_CLUSTER ?? 'mt1',
            wsHost: wsHost,
            wsPort: import.meta.env.VITE_SOKETI_PORT,
            wssPort: import.meta.env.VITE_SOKETI_PORT,
            forceTLS: (import.meta.env.VITE_SOKETI_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (e) {
        console.warn('Realtime broadcasting could not be initialized:', e?.message ?? e);
    }
} else {
    window.Echo = null;
}

