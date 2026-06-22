import { Server } from '@soketi/soketi';

const port = parseInt(process.env.SOKETI_PORT || '6001', 10);
const host = process.env.SOKETI_HOST || '0.0.0.0';

const server = new Server({
  host,
  port,
  appManager: {
    driver: 'array',
    apps: [
      {
        id: process.env.PUSHER_APP_ID || 'african-market',
        key: process.env.PUSHER_APP_KEY || 'local-app-key',
        secret: process.env.PUSHER_APP_SECRET || 'local-app-secret',
        enableClientMessages: true,
        enableStatistics: false,
      }
    ]
  }
});

server.start().then(() => {
  console.log(`Soketi running on ${host}:${port}`);
});
