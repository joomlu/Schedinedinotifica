"""No Laravel/PHPUnit/Playwright startup, socket, database or external service."""
import os
from pathlib import Path
import subprocess
import unittest
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[2]

class ClosedRuntimeTests(unittest.TestCase):
    def test_php_rejects_every_untrusted_context_before_autoload(self):
        cases = [
            {}, {'APP_ENV': 'testing'},
            {'APP_ENV': 'testing', 'DB_DATABASE': 'development'},
            {'APP_ENV': 'testing', 'DB_DATABASE': 'authorized_test', 'DB_HOST': 'production.invalid'},
            {'DATABASE_URL': 'mysql://example:fake@production.invalid/authorized_test'},
            {'DB_URL': 'mysql://example:fake@127.0.0.1/authorized_test'},
            {'DB_SOCKET': '/tmp/real-mysql.sock'},
            {'DB_CONNECTION': 'secondary', 'DB_READ_HOST': 'production.invalid'},
            {'APP_CONFIG_CACHE': str(ROOT/'bootstrap/cache/config.php')},
            {'LARAVEL_STORAGE_PATH': str(ROOT/'storage')},
            {'VIEW_COMPILED_PATH': str(ROOT/'storage/framework/views')},
            {'SESSION_DRIVER': 'database', 'SESSION_CONNECTION': 'mysql'},
            {'LOG_CHANNEL': 'single', 'FILESYSTEM_DRIVER': 'local'},
            {'APP_BASE_PATH': str(ROOT), 'PUBLIC_PATH': str(ROOT/'public')},
            {'TEST_ISOLATION_APPROVED': '1', 'APP_ENV': 'testing'},
        ]
        for context in cases:
            with self.subTest(context=list(context)):
                code = "try { require 'tests/bootstrap.php'; } catch (Throwable $e) { "
                code += "if (class_exists('Illuminate\\\\Foundation\\\\Application', false)) exit(92); "
                code += "fwrite(STDERR, $e->getMessage()); exit(86); } exit(0);"
                result = subprocess.run(['php', '-n', '-r', code], cwd=ROOT,
                    env=dict(os.environ, **context), capture_output=True, text=True, timeout=10)
                self.assertEqual(result.returncode, 86, result.stderr)
                self.assertIn('TEST_ISOLATION_REQUIRED', result.stderr)

    def test_browser_gate_rejects_hosts_servers_and_redirect_destinations(self):
        # Gate rejects BEFORE a browser or request can exist; redirects cannot start.
        for destination in ['https://schedinedinotifica.test',
                            'https://schedinedinotifica.tanggo.software',
                            'http://127.0.0.1:8000', 'https://external.invalid']:
            for key in ['BASE_URL', 'PLAYWRIGHT_BASE_URL', 'REDIRECT_URL']:
                with self.subTest(key=key, destination=destination):
                    code = "import {requireIsolatedRuntime} from './tests/Support/playwright-isolation.js';"
                    code += "try { requireIsolatedRuntime(); } catch(e) { console.error(e.message); process.exit(86); }"
                    result = subprocess.run(['node', '--input-type=module', '-e', code], cwd=ROOT,
                        env=dict(os.environ, **{key: destination, 'REUSE_EXISTING_SERVER': 'true'}),
                        capture_output=True, text=True, timeout=10)
                    self.assertEqual(result.returncode, 86, result.stderr)
                    self.assertIn('TEST_ISOLATION_REQUIRED', result.stderr)

    def test_direct_application_creation_cannot_bypass_phpunit_bootstrap(self):
        code = "require 'tests/CreatesApplication.php'; class Probe { use \\Tests\\CreatesApplication; } "
        code += "try { (new Probe)->createApplication(); } catch (Throwable $e) { fwrite(STDERR, $e->getMessage()); exit(86); }"
        result = subprocess.run(['php', '-n', '-r', code], cwd=ROOT,
                                capture_output=True, text=True, timeout=10)
        self.assertEqual(result.returncode, 86, result.stderr)
        self.assertIn('TEST_ISOLATION_REQUIRED', result.stderr)

    def test_actual_playwright_config_aborts_without_starting_playwright(self):
        result = subprocess.run(['node', '--input-type=module', '-e',
            "import('./playwright.config.js').catch(e => { console.error(e.message); process.exit(86); });"],
            cwd=ROOT, capture_output=True, text=True, timeout=10)
        self.assertEqual(result.returncode, 86, result.stderr)
        self.assertIn('TEST_ISOLATION_REQUIRED', result.stderr)

    def test_all_existing_entrypoints_are_guarded(self):
        self.assertEqual(ET.parse(ROOT/'phpunit.xml').getroot().attrib['bootstrap'], 'tests/bootstrap.php')
        create = (ROOT/'tests/CreatesApplication.php').read_text()
        self.assertLess(create.index('requireIsolatedRuntime()'), create.index("$app = require"))
        for spec in (ROOT/'tests/Feature').glob('*.playwright.spec.js'):
            source = spec.read_text()
            self.assertIn("from '../Support/playwright.js'", source)
            self.assertNotIn("const BASE_URL =", source)
        self.assertIn('requireIsolatedRuntime();', (ROOT/'playwright.config.js').read_text())
        self.assertIn('requireIsolatedRuntime();', (ROOT/'tests/Support/playwright.js').read_text())

if __name__ == '__main__':
    unittest.main(verbosity=2)
