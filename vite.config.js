import { defineConfig } from "vite";

export default defineConfig({
  root: "resources",

  server: {
    port: 5173,
    strictPort: true,
  },

  build: {
    outDir: "../public/assets",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: "/js/app.js", // ✅ BUKAN ./resources/js/app.js
      },
    },
  },
});
