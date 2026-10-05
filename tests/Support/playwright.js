import { requireIsolatedRuntime } from './playwright-isolation.js';
const runtime = requireIsolatedRuntime();
const { test: base, expect } = await import('@playwright/test');

export const BASE_URL = runtime.origin;
export { expect };
export const test = base.extend({
  context: async ({ browser }, use) => {
    const context = await browser.newContext({
      baseURL: runtime.origin, serviceWorkers: 'block',
      proxy: { server: runtime.proxy, bypass: '<-loopback>' },
    });
    // Il proxy controlla anche redirect, fetch e navigazioni successive.
    // Questo filtro interrompe immediatamente URL esterni prima del proxy.
    await context.route('**/*', async route => {
      if (new URL(route.request().url()).origin !== runtime.origin) {
        await route.abort('blockedbyclient');
        return;
      }
      await route.continue();
    });
    await use(context);
    await context.close();
  },
});
