import { Server } from '@soketi/soketi';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import dotenv from 'dotenv';

const __dirname = dirname(fileURLToPath(import.meta.url));

// Load environment variables from the project's env files so the websocket
// server can read PUSHER_PORT / PUSHER_APP_* without a wrapper script.
dotenv.config({ path: resolve(__dirname, '../.env') });
dotenv.config({ path: resolve(__dirname, '../.env.local') });

// The websocket port is sourced from PUSHER_PORT (the same value the frontend
// uses via VITE_PUSHER_PORT), falling back to SOKETI_PORT and then 6001.
const port = parseInt(process.env.PUSHER_PORT || process.env.SOKETI_PORT || '6001', 10);
const host = process.env.SOKETI_HOST || process.env.PUSHER_HOST || '0.0.0.0';

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
