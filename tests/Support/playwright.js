import { requireIsolatedRuntime } from './playwright-isolation.js';
// Also protects direct spec loading when someone omits the root config.
requireIsolatedRuntime();
export { test, expect } from '@playwright/test';
export const BASE_URL = undefined; // No development/production fallback.
