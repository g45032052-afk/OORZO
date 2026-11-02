import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'
import path from 'path'

// Storefront Inertia bundle. Vite is the primary toolchain (mirrors 6amMart);
// laravel-mix (webpack.mix.js) remains only as a legacy fallback (`npm run
// dev:mix`). Entries live in resources/js + resources/css exactly like
// 6amMart — the ESM bootstrap is shared by both toolchains.
export default defineConfig({
  server: {
    // Bind IPv4 explicitly. The Herd `.test` domains (and tenant subdomains)
    // all resolve to 127.0.0.1, but `localhost` can bind IPv6 `::1` only on
    // some machines — leaving the IPv4 address the browser actually hits with
    // nothing listening (blank storefront). The hot-file URL still uses the
    // APP_URL host via the plugin's hmr.host, so the Herd cert keeps matching.
    host: '127.0.0.1',
    port: 5173,
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'Modules/Builder/resources/js'),
    },
  },
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.jsx',
      ],
      refresh: true,
    }),
    react(),
  ],
})
