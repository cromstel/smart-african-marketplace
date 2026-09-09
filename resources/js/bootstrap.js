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

// Broadcasting is opt-in. Previously this initialised whenever a Pusher key was
// present, and .env.example ships a placeholder one -- so a fresh install with
// BROADCAST_DRIVER=log still opened a websocket to a Soketi nobody was running,
// producing connection errors and retry traffic on every page load. The client
// now agrees with the server: set BROADCAST_ENABLED=true (and run Soketi) to
// turn it on.
const broadcastEnabled =
  String(import.meta.env.VITE_BROADCAST_ENABLED ?? '').toLowerCase() === 'true';

if (broadcastEnabled && import.meta.env.VITE_PUSHER_APP_KEY) {
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? undefined,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? undefined,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? undefined,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
  });
}
