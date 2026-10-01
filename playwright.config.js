import { requireIsolatedRuntime } from './tests/Support/playwright-isolation.js';
requireIsolatedRuntime();
export default {}; // No webServer or reuseExistingServer while isolation is pending.
