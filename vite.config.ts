import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from "@tailwindcss/vite";
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import { existsSync, readFileSync } from 'fs';
import os from 'os';

// ─────────────────────────────────────────────────────────────────────────────
// Resolve Herd TLS certificate (works on Windows and macOS)
// Herd for Windows: ~/Herd/config/certs/
// Herd for macOS:   ~/.config/herd/config/certs/
// ─────────────────────────────────────────────────────────────────────────────
const HOST = 'laravel-bluprint.test';

function herdHttps() {
    const certDirs = [
        path.join(os.homedir(), 'Herd', 'config', 'certs'),           // Herd for Windows
        path.join(os.homedir(), '.config', 'herd', 'config', 'certs'), // Herd for macOS
    ];

    for (const dir of certDirs) {
        const key = path.join(dir, `${HOST}.key`);
        const cert = path.join(dir, `${HOST}.crt`);
        if (existsSync(key) && existsSync(cert)) {
            return { key: readFileSync(key), cert: readFileSync(cert) };
        }
    }

    return undefined;
}

const https = herdHttps();

export default defineConfig({
    server: {
        ...(https ? { https } : {}),
        hmr: { host: HOST },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
