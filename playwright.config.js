import { requireIsolatedRuntime } from './tests/Support/playwright-isolation.js';
const runtime = requireIsolatedRuntime();
const { defineConfig } = await import('@playwright/test');

export default defineConfig({
  testDir: './tests/Feature',
  testMatch: '*.playwright.spec.js',
  workers: 1,
  retries: 0,
  reporter: 'list',
  use: {
    baseURL: runtime.origin, headless: true, serviceWorkers: 'block',
    proxy: { server: runtime.proxy, bypass: '<-loopback>' },
    launchOptions: { args: ['--disable-quic', '--proxy-bypass-list=<-loopback>'] },
  },
  // Il launcher possiede il server; nessun webServer riutilizzabile.
});
