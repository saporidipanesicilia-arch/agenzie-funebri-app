import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

// Minimal Vite config for Static SPA deployment on Netlify
export default defineConfig({
    plugins: [
        tailwindcss(),
    ],
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        rollupOptions: {
            input: 'index.html',
        },
    },
    // Proxy API requests to Laravel/Supabase if needed during dev, 
    // but for Netlify build we primarily care about outputting static assets.
});
