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

// Broadcasting is always enabled using the internal Soketi server.
// No third-party provider (pusher.com) is used. Soketi speaks the Pusher
// protocol, so the pusher-js client library is the protocol client only.
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_SOKETI_APP_KEY,
    cluster: import.meta.env.VITE_SOKETI_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_SOKETI_HOST ?? '127.0.0.1',
    wsPort: import.meta.env.VITE_SOKETI_PORT ?? 6001,
    wssPort: import.meta.env.VITE_SOKETI_PORT ?? 6001,
    forceTLS: (import.meta.env.VITE_SOKETI_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});
