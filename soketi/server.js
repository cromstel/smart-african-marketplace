/* eslint-env node */
import { Server } from '@soketi/soketi';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import dotenv from 'dotenv';

const __dirname = dirname(fileURLToPath(import.meta.url));

// Load environment variables from the project's env files so the websocket
// server can read SOKETI_PORT / SOKETI_APP_* without a wrapper script.
dotenv.config({ path: resolve(__dirname, '../.env') });
dotenv.config({ path: resolve(__dirname, '../.env.local') });

// The websocket port is sourced from SOKETI_PORT.
const port = parseInt(process.env.SOKETI_PORT, 10);
const host = process.env.SOKETI_HOST || '0.0.0.0';

const server = new Server({
  host,
  port,
  appManager: {
    driver: 'array',
    apps: [
      {
        id: process.env.SOKETI_APP_ID || 'african-market',
        key: process.env.SOKETI_APP_KEY || 'local-app-key',
        secret: process.env.SOKETI_APP_SECRET || 'local-app-secret',
        enableClientMessages: true,
        enableStatistics: false,
      }
    ]
  }
});

server.start().then(() => {
  console.log(`Soketi running on ${host}:${port}`);
});


