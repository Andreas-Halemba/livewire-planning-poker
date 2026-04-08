import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const csrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? ''

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 80,
    wssPort: Number(import.meta.env.VITE_REVERB_PORT) || 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['wss', 'ws'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
        },
    },
})
