import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "node:path";

export default defineConfig({
    plugins: [react()],
    base: "/admin-build/",
    publicDir: false,
    resolve: { alias: { "@": path.resolve(__dirname, "./src") } },
    build: {
        outDir: "../public/admin-build",
        emptyOutDir: true,
        manifest: "manifest.json",
        rollupOptions: { input: path.resolve(__dirname, "src/admin/main.tsx") },
    },
});
