// No browser, HTTP request or application startup is permitted yet.
export function requireIsolatedRuntime() {
  // Do not add a flag, URL suffix or marker-file bypass: none proves isolation.
  throw new Error('TEST_ISOLATION_REQUIRED: Playwright is locked until a disposable database and its own HTTP server have been independently verified. Herd and pre-existing servers are forbidden.');
}
