# Soketi (self-hosted WebSockets)

We use a self-hosted WebSocket server (Soketi) to avoid external costs while keeping Laravel Echo + Pusher compatibility.

## 1) Install Soketi dependencies

```bash
cd soketi
npm install
```

## 2) Start Soketi locally

Windows:
```bash
cd soketi
run-local.cmd
```

Linux/macOS:
```bash
cd soketi
PUSHER_APP_ID=african-market \
PUSHER_APP_KEY=local-app-key \
PUSHER_APP_SECRET=local-app-secret \
SOKETI_PORT=6001 \
SOKETI_HOST=0.0.0.0 \
node server.js
```

## 3) Configure Laravel `.env`

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=african-market
PUSHER_APP_KEY=local-app-key
PUSHER_APP_SECRET=local-app-secret
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## 4) Rebuild frontend assets

```bash
npm install
npm run build
```

## 5) Run the app

Soketi must be running while the site is open for realtime chat updates.
