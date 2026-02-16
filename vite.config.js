import { defineConfig } from "vite";
import FullReload from "vite-plugin-full-reload";
import { resolve } from "path";

export default defineConfig({
  root: "resources",
  base: "/",
  server: {
    port: 5173,
    strictPort: true,
  },
  plugins: [
    FullReload([
      resolve(__dirname, "themes/**/*.php"),
      resolve(__dirname, "routes/**/*.php"),
      resolve(__dirname, "app/**/*.php"),
    ]),
  ],
  build: {
    outDir: "../public/assets",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, "resources/js/app.js"),
    },
  },
});
