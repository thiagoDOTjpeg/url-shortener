import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const rawHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const host = String(rawHost).replace(/^['"]|['"]$/g, '');
const scheme = (import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '') || 'http').toLowerCase();
const isLocalHost = ['localhost', '127.0.0.1', '0.0.0.0'].includes(host);
const forceTLS = !isLocalHost && scheme === 'https';
const wsPort = Number(import.meta.env.VITE_REVERB_PORT || (forceTLS ? 443 : 80));
const enabledTransports = isLocalHost ? ['ws'] : ['ws', 'wss'];

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: host,
    wsPort,
    wssPort: wsPort,
    forceTLS,
    enabledTransports,
});
