import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

// Builds the island runtime to dist/. The entry (rjb-viz.js) keeps a stable,
// unhashed name so WordPress can enqueue a fixed path; individual islands are
// emitted as hashed, lazy-loaded chunks. `base: './'` makes those chunk URLs
// resolve relative to the entry module, so the bundle works under whatever
// path the plugin is installed at.
export default defineConfig({
  plugins: [svelte()],
  base: './',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    target: 'es2020',
    rollupOptions: {
      input: 'src/mount.js',
      output: {
        entryFileNames: 'rjb-viz.js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash][extname]',
      },
    },
  },
});
