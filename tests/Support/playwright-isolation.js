import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

// Verifica PHP prima di importare Playwright: supervisore, processi, DB e HTTP.
export function requireIsolatedRuntime() {
  try {
    const result = JSON.parse(execFileSync('php', [fileURLToPath(new URL('../Isolation/browser-identity.php', import.meta.url))], {
      encoding: 'utf8', timeout: 15000, stdio: ['ignore', 'pipe', 'pipe'],
    }));
    if (!/^http:\/\/127\.0\.0\.1:\d+$/.test(result.origin) || !/^http:\/\/127\.0\.0\.1:\d+$/.test(result.proxy)) {
      throw new Error('Endpoint non valido');
    }
    return result;
  } catch {
    throw new Error('TEST_ISOLATION_REQUIRED: servono supervisore vivo, checkout, database e HTTP temporanei attestati.');
  }
}
