"""Isolated regression tests: no local .env, real database, Git remote or SPanel.
Run: python3 -B -m unittest discover -s tests/Deployment -v
"""
import contextlib
import importlib.util
import io
import json
import os
from pathlib import Path
import shutil
import signal
import socket
import subprocess
import sys
import tempfile
import time
import unittest
import urllib.error
import urllib.request
from unittest import mock

REPO = Path(__file__).resolve().parents[2]
MODULE = REPO / 'scripts/deployment/deploy.py'
spec = importlib.util.spec_from_file_location('deployment', MODULE)
d = importlib.util.module_from_spec(spec)
spec.loader.exec_module(d)
TEMPLATE = (REPO / 'scripts/deployment/maintenance.php').read_text()
DUMMY_ENV = ('APP_ENV=production\nAPP_DEBUG=false\n'
             'APP_URL=https://schedinedinotifica.tanggo.software\n'
             'APP_KEY=base64:QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUE=\n'
             'DB_CONNECTION=mysql\nQUEUE_CONNECTION=sync\nCACHE_DRIVER=file\n'
             'SESSION_DRIVER=file\nFILESYSTEM_DRIVER=local\nLOG_CHANNEL=single\n')

class Fixture(unittest.TestCase):
    def setUp(self):
        temp = tempfile.TemporaryDirectory(prefix='spanel-regression-')
        self.addCleanup(temp.cleanup)
        self.root = Path(temp.name).resolve() / 'app'
        self.root.mkdir()
        self.write('.env', DUMMY_ENV)
        for name in ['.git','storage/app/public','public/images','storage/framework/cache/data',
                     'storage/framework/views','storage/framework/sessions','storage/logs','bootstrap/cache']:
            (self.root/name).mkdir(parents=True, exist_ok=True)
        self.write('public/index.php', (REPO/'public/index.php').read_text())
        self.write('storage/app/keep', 'persistent')
        self.write('public/images/keep', 'persistent')
        self.app = d.Deployment(self.root, REPO)
        self.app.seal = d.EnvironmentSeal(self.root)
        self.app.maintenance = d.Maintenance(self.root, TEMPLATE)

    def write(self, name, value):
        path = self.root/name
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(value)
        return path

    def persistent(self):
        self.assertEqual((self.root/'storage/app/keep').read_text(), 'persistent')
        self.assertEqual((self.root/'public/images/keep').read_text(), 'persistent')

    def acquire(self):
        self.app.lock = d.Lock(self.root/'.git/spanel-deploy.lock')
        self.addCleanup(self.app.lock.close)

    def verified_finish(self, code):
        self.app.http = lambda path, expected, bypass=False: self.app.maintenance.marker.encode()
        with contextlib.redirect_stderr(io.StringIO()) as err:
            result = self.app.finish(code)
        return result, err.getvalue()

class EnvironmentTests(Fixture):
    def test_literal_key_required(self):
        for key in ['', 'APP_KEY=\n', 'APP_KEY=""\n', 'APP_KEY=${OTHER}\n', 'APP_KEY=a\nAPP_KEY=b\n']:
            with self.subTest(key=key):
                self.write('.env', key)
                with self.assertRaises(d.Failure):
                    d.EnvironmentSeal(self.root)

    def test_every_env_mutation_is_critical(self):
        for text in [DUMMY_ENV+'MAIL_PASSWORD=dummy\n', DUMMY_ENV.replace('QUFB', 'QkJC'),
                     DUMMY_ENV.replace('APP_KEY=', 'REMOVED_KEY='), '']:
            with self.subTest(text_length=len(text)):
                self.write('.env', text)
                with self.assertRaisesRegex(d.Critical, 'ENV_INTEGRITY'):
                    self.app.seal.verify()
        (self.root/'.env').unlink()
        with self.assertRaises(d.Critical):
            self.app.seal.verify()

    def test_symlink_and_hardlink_env_rejected(self):
        path = self.root/'.env'
        path.unlink()
        other = self.write('outside', DUMMY_ENV)
        path.symlink_to(other)
        with self.assertRaises(d.Critical): self.app.seal.verify()
        path.unlink()
        os.link(other, path)
        with self.assertRaises(d.Critical): self.app.seal.verify()

    def test_failing_project_command_cannot_hide_env_change(self):
        with self.assertRaisesRegex(d.Critical, 'ENV_INTEGRITY'):
            self.app.project([sys.executable, '-c',
                "from pathlib import Path; Path('.env').write_text('APP_KEY=dummy-changed\\n'); raise SystemExit(43)"])

    def test_exit_detects_env_change_before_maintenance(self):
        self.acquire()
        self.write('.env', DUMMY_ENV+'OTHER=changed\n')
        code, output = self.verified_finish(0)
        self.assertEqual(code, 86)
        self.assertIn('ENV_INTEGRITY', output)
        self.assertNotIn('QUFB', output)
        self.app.maintenance.verify_files()
        self.assertIsNone(self.app.lock.fd)
        self.persistent()

    def test_success_exit_rechecks_env_and_releases_lock(self):
        self.acquire()
        code, output = self.verified_finish(0)
        self.assertEqual(code, 0)
        self.assertEqual(output, '')
        self.assertIsNone(self.app.lock.fd)

    def test_env_change_during_exit_cleanup_is_critical_and_closes_site(self):
        self.acquire()
        self.app.scratch = Path(tempfile.mkdtemp(prefix='.schedinedinotifica-deploy.',dir=self.root.parent))
        original = d.shutil.rmtree
        def change_env(path):
            self.write('.env',DUMMY_ENV+'CHANGED_DURING_EXIT=yes\n')
            original(path)
        with mock.patch.object(d.shutil,'rmtree',side_effect=change_env):
            code, output = self.verified_finish(0)
        self.assertEqual(code,86)
        self.assertIn('ENV_INTEGRITY',output)
        self.app.maintenance.verify_files()

class PathTests(Fixture):
    def test_provider_manifests_only_are_removed(self):
        for name in ['packages.php','services.php']:
            self.write('bootstrap/cache/'+name,'<?php return [];')
        self.app.clear_manifests()
        for name in ['packages.php','services.php']:
            self.assertFalse((self.root/'bootstrap/cache'/name).exists())
        self.persistent()

    def test_provider_manifest_symlink_rejected(self):
        (self.root/'bootstrap/cache/packages.php').symlink_to(self.root/'storage/app/keep')
        with self.assertRaises(d.Failure): self.app.clear_manifests()
        self.persistent()

    def test_runtime_symlink_rejected(self):
        views = self.root/'storage/framework/views'
        views.rmdir(); views.symlink_to(self.root/'storage/app')
        with self.assertRaises(d.Failure): self.app.runtime()
        self.persistent()

    def test_runtime_hardlink_rejected(self):
        os.link(self.root/'storage/app/keep', self.root/'storage/framework/views/keep')
        with self.assertRaises(d.Failure): self.app.runtime()
        self.persistent()

    def test_walk_error_is_failure(self):
        def fail_walk(*args, **kwargs):
            kwargs['onerror'](PermissionError('injected enumeration failure'))
            return iter(())
        with mock.patch.object(d.os, 'walk', side_effect=fail_walk):
            with self.assertRaises(PermissionError): self.app.runtime()
            with self.assertRaises(PermissionError): self.app.permissions()

    def test_world_writable_directory_rejected(self):
        (self.root/'storage/framework/views').chmod(0o777)
        with self.assertRaises(d.Failure): self.app.runtime()

    def test_storage_link_postcondition(self):
        self.app.link()
        with self.assertRaises(d.Failure): self.app.link(required=True)
        p = self.root/'public/storage'
        p.mkdir()
        with self.assertRaises(d.Failure): self.app.link(required=True)
        p.rmdir(); p.symlink_to(self.root/'public/images')
        with self.assertRaises(d.Failure): self.app.link(required=True)
        p.unlink(); p.symlink_to(self.root/'storage/app/public')
        self.app.link(required=True)
        self.persistent()

    def test_indirect_storage_link_is_rejected(self):
        alias = self.root.parent/'outside-alias'
        alias.symlink_to(self.root/'storage/app/public')
        (self.root/'public/storage').symlink_to(alias)
        with self.assertRaises(d.Failure): self.app.link(required=True)

    def test_permissions_do_not_touch_persistent_files(self):
        files = [self.root/'storage/app/keep', self.root/'public/images/keep']
        for path in files: path.chmod(0o400)
        self.app.permissions()
        for path in files: self.assertEqual(path.stat().st_mode & 0o777, 0o400)

class GitTests(Fixture):
    def setUp(self):
        super().setUp()
        self.git('init','-q')
        self.git('config','user.name','Fixture')
        self.git('config','user.email','fixture@example.invalid')
        self.write('.gitignore','.env\n/storage/framework/\n/storage/logs/\n/bootstrap/cache/\n')
        self.write('code.php','baseline')
        self.write('database/migrations/001_initial.php','baseline')
        self.git('add','.')
        self.git('commit','-qm','isolated fixture')
        self.base = self.git('rev-parse','HEAD').strip()

    def git(self, *args):
        return subprocess.check_output(['git',*args],cwd=self.root,stderr=subprocess.PIPE,text=True)

    def candidate(self, change):
        self.git('checkout','-qb','candidate')
        change()
        self.git('add','-A')
        self.git('commit','-qm','isolated candidate')
        sha = self.git('rev-parse','HEAD').strip()
        self.git('checkout','-q','--detach',self.base)
        return sha

    def test_clean_and_dirty(self):
        self.app.clean()
        self.write('code.php','dirty')
        with self.assertRaises(d.Failure): self.app.clean()

    def test_untracked_and_hidden_index_flags(self):
        self.write('unexpected','data')
        with self.assertRaises(d.Failure): self.app.clean()
        (self.root/'unexpected').unlink()
        for flag in ['--assume-unchanged','--skip-worktree']:
            with self.subTest(flag=flag):
                self.git('update-index',flag,'code.php')
                with self.assertRaises(d.Failure): self.app.clean()
                self.git('update-index','--no-assume-unchanged','--no-skip-worktree','code.php')

    def test_regular_fast_forward_allowed(self):
        self.app.candidate(self.candidate(lambda: self.write('code.php','new')))

    def test_git_errors_never_become_clean(self):
        for fail_call in [1,2]:
            with self.subTest(call=fail_call):
                results = [b'',b'H code.php\0']
                results[fail_call-1] = d.Failure('git failed')
                with mock.patch.object(self.app,'git',side_effect=results):
                    with self.assertRaises(d.Failure): self.app.clean()

    def test_candidate_git_failures_propagate(self):
        for results in [[d.Failure('merge-base')], [b'',d.Failure('diff')],
                        [b'',b'\0',d.Failure('tree')]]:
            with self.subTest(calls=len(results)):
                with mock.patch.object(self.app,'git',side_effect=results):
                    with self.assertRaises(d.Failure): self.app.candidate('sha')

    def test_move_out_of_public_images_rejected(self):
        sha = self.candidate(lambda: self.git('mv','public/images/keep','moved-upload'))
        with self.assertRaisesRegex(d.Failure,'protected'): self.app.candidate(sha)

    def test_move_into_public_images_rejected(self):
        sha = self.candidate(lambda: self.git('mv','code.php','public/images/new'))
        with self.assertRaisesRegex(d.Failure,'protected'): self.app.candidate(sha)

    def test_move_out_of_storage_app_rejected(self):
        sha = self.candidate(lambda: self.git('mv','storage/app/keep','moved-data'))
        with self.assertRaisesRegex(d.Failure,'protected'): self.app.candidate(sha)

    def test_move_into_storage_app_rejected(self):
        sha = self.candidate(lambda: self.git('mv','code.php','storage/app/new'))
        with self.assertRaisesRegex(d.Failure,'protected'): self.app.candidate(sha)

    def test_persistent_deletion_rejected(self):
        sha = self.candidate(lambda: (self.root/'storage/app/keep').unlink())
        with self.assertRaisesRegex(d.Failure,'protected'): self.app.candidate(sha)

    def test_existing_migration_change_rejected(self):
        sha = self.candidate(lambda: self.write('database/migrations/001_initial.php','changed'))
        with self.assertRaises(d.Failure): self.app.candidate(sha)

    def test_new_migration_allowed_for_review(self):
        sha = self.candidate(lambda: self.write('database/migrations/002_next.php','new'))
        self.app.candidate(sha)

    def test_env_added_rejected(self):
        sha = self.candidate(lambda: self.git('add','-f','.env'))
        with self.assertRaises(d.Failure): self.app.candidate(sha)

class MaintenanceTests(Fixture):
    def test_static_503_with_broken_vendor(self):
        self.write('vendor/autoload.php','<?php THIS FILE IS DELIBERATELY INVALID PHP;')
        self.app.maintenance.close()
        status = self.root/'status'
        php = "register_shutdown_function(function() use ($argv) {file_put_contents($argv[2], (string)http_response_code());}); require $argv[1];"
        result = subprocess.run(['php','-r',php,str(self.root/'public/index.php'),str(status)],capture_output=True)
        self.assertEqual(result.returncode,0,result.stderr)
        self.assertEqual(status.read_text(),'503')
        self.assertIn(self.app.maintenance.marker.encode(),result.stdout)

    def test_real_http_503_headers_without_vendor(self):
        self.app.maintenance.close()
        with socket.socket() as listener:
            listener.bind(('127.0.0.1',0))
            port = listener.getsockname()[1]
        proc = subprocess.Popen(['php','-S','127.0.0.1:'+str(port),'-t',str(self.root/'public'),
                                 str(self.root/'public/index.php')],stdout=subprocess.DEVNULL,stderr=subprocess.PIPE)
        try:
            deadline = time.monotonic()+5
            while True:
                try:
                    with socket.create_connection(('127.0.0.1',port),timeout=.2): break
                except OSError:
                    if time.monotonic()>deadline: self.fail('PHP HTTP fixture did not start')
                    time.sleep(.02)
            for cookie in ['', 'laravel_maintenance[0]=malformed']:
                req = urllib.request.Request('http://127.0.0.1:'+str(port)+'/login',headers={'Cookie':cookie})
                with self.assertRaises(urllib.error.HTTPError) as error:
                    urllib.request.urlopen(req,timeout=3)
                response = error.exception
                self.assertEqual(response.code,503)
                self.assertEqual(response.headers['Retry-After'],'60')
                self.assertIn('no-store',response.headers['Cache-Control'])
                self.assertIn(self.app.maintenance.marker.encode(),response.read())
                response.close()
        finally:
            proc.terminate(); proc.communicate(timeout=5)

    def test_static_503_without_vendor_or_bootstrap(self):
        self.app.maintenance.close()
        status = self.root/'status'
        php = "register_shutdown_function(function() use ($argv) {file_put_contents($argv[2], (string)http_response_code());}); require $argv[1];"
        result = subprocess.run(['php','-r',php,str(self.root/'public/index.php'),str(status)],capture_output=True)
        self.assertEqual(result.returncode,0,result.stderr)
        self.assertEqual(status.read_text(),'503')
        self.assertIn(self.app.maintenance.marker.encode(),result.stdout)
        self.assertFalse((self.root/'vendor').exists())
        self.persistent()

    def test_malformed_cookie_still_returns_503(self):
        self.app.maintenance.close()
        for cookie in [[], {'bad':'type'}, 'invalid', 'x'*2048]:
            with self.subTest(cookie_type=type(cookie).__name__):
                status = self.root/'status'
                php = "$_COOKIE['laravel_maintenance']=json_decode($argv[3],true); register_shutdown_function(function() use ($argv) {file_put_contents($argv[2],(string)http_response_code());}); require $argv[1];"
                result = subprocess.run(['php','-r',php,str(self.root/'public/index.php'),str(status),json.dumps(cookie)],capture_output=True)
                self.assertEqual(result.returncode,0,result.stderr)
                self.assertEqual(status.read_text(),'503')

    def test_only_signed_cookie_bypasses_guard(self):
        self.app.maintenance.close()
        php = "$_COOKIE['laravel_maintenance']=$argv[2]; require $argv[1]; echo 'BYPASSED';"
        result = subprocess.run(['php','-r',php,str(self.root/'storage/framework/maintenance.php'),self.app.maintenance.cookie()],capture_output=True)
        self.assertEqual(result.returncode,0,result.stderr)
        self.assertEqual(result.stdout,b'BYPASSED')

    def test_before_maintenance_failure_keeps_original_site(self):
        self.acquire()
        code, output = self.verified_finish(42)
        self.assertEqual(code,42)
        self.assertNotIn('VERIFIED',output)
        self.assertFalse((self.root/'storage/framework/maintenance.php').exists())
        self.assertIsNone(self.app.lock.fd)

    def test_failure_after_open_reinstates_verified_guard(self):
        self.acquire()
        self.app.maintenance.close(); self.app.maintenance.open()
        code, output = self.verified_finish(42)
        self.assertEqual(code,42)
        self.assertIn('503 maintenance VERIFIED',output)
        self.app.maintenance.verify_files()
        self.persistent()

    def test_failed_recovery_never_claims_maintenance(self):
        self.acquire()
        self.app.maintenance.close()
        self.app.verify_maintenance = mock.Mock(side_effect=d.Failure('HTTPS failed'))
        with contextlib.redirect_stderr(io.StringIO()) as err:
            code = self.app.finish(42)
        self.assertEqual(code,85)
        self.assertIn('CRITICAL MAINTENANCE_UNVERIFIED',err.getvalue())
        self.assertNotIn('maintenance VERIFIED',err.getvalue())
        self.assertIsNone(self.app.lock.fd)

    def test_failed_child_cleanup_does_not_claim_safe_state(self):
        self.acquire()
        self.app.proc.shutdown = mock.Mock(side_effect=OSError('unreapable child'))
        code, output = self.verified_finish(1)
        self.assertEqual(code,85)
        self.assertIn('safe state NOT guaranteed',output)
        self.assertNotIn('maintenance VERIFIED',output)
        self.assertIsNone(self.app.lock.fd)

    def test_partial_down_file_failure_keeps_static_guard(self):
        original = self.app.maintenance.atomic
        def fail_down(path,data):
            if path.name == 'down': raise OSError('full disk')
            original(path,data)
        with mock.patch.object(self.app.maintenance,'atomic',side_effect=fail_down):
            with self.assertRaises(OSError): self.app.maintenance.close()
        self.assertTrue((self.root/'storage/framework/maintenance.php').is_file())

    def test_altered_index_rejected_before_maintenance(self):
        self.write('public/index.php','<?php require "vendor.php";')
        with self.assertRaises(d.Failure): self.app.maintenance.close()

    def test_cleanup_failure_after_open_recloses_site(self):
        self.acquire()
        self.app.maintenance.close(); self.app.maintenance.open()
        self.app.scratch = Path(tempfile.mkdtemp(prefix='.schedinedinotifica-deploy.',dir=self.root.parent))
        with mock.patch.object(d.shutil,'rmtree',side_effect=OSError('injected cleanup failure')):
            code, output = self.verified_finish(0)
        self.assertNotEqual(code,0)
        self.app.maintenance.verify_files()
        self.assertIn('503 maintenance VERIFIED',output)

class ExecutionTests(Fixture):
    def test_build_pipeline_changes_blocked_before_execution(self):
        for name in list(d.BUILD_HASHES)+['composer.json']:
            self.write(name,(REPO/name).read_text())
        self.app.build_contract(self.root)
        for name in d.BUILD_HASHES:
            original=(self.root/name).read_text()
            self.write(name,original+'\n')
            with self.subTest(file=name):
                with self.assertRaises(d.Failure): self.app.build_contract(self.root)
            self.write(name,original)

    def test_composer_installation_paths_restricted(self):
        for name in d.BUILD_HASHES:
            self.write(name,(REPO/name).read_text())
        for key in ['vendor-dir','bin-dir']:
            self.write('composer.json',json.dumps({'config':{key:'storage/app'}}))
            with self.assertRaises(d.Failure): self.app.build_contract(self.root)
        self.write('composer.json','{}')
        for key in ['COMPOSER','COMPOSER_VENDOR_DIR','COMPOSER_BIN_DIR']:
            with mock.patch.dict(os.environ,{key:str(self.root/'storage/app')}):
                with self.assertRaises(d.Failure): self.app.build_contract(self.root)

    def test_nonzero_commands_and_timeouts_propagate(self):
        for args, timeout in [(['-c','raise SystemExit(43)'],3),(['-c','import time; time.sleep(5)'],0.1)]:
            with self.subTest(args=args):
                with self.assertRaises(d.Failure): self.app.project([sys.executable,*args],timeout=timeout)
                self.assertIsNone(self.app.proc.active)
                self.app.seal.verify()

    def test_every_cache_failure_stops_the_sequence(self):
        commands = d.CLEAR+d.CACHE
        for failed in commands:
            with self.subTest(command=failed):
                called = []
                def artisan(command):
                    called.append(command)
                    if command == failed: raise d.Failure('injected internal cache failure')
                with mock.patch.object(self.app,'artisan',side_effect=artisan):
                    with self.assertRaises(d.Failure): self.app.caches()
                self.assertEqual(called,commands[:commands.index(failed)+1])

    def test_https_errors_and_wrong_status_propagate(self):
        self.app.scratch = self.root.parent/'scratch'; self.app.scratch.mkdir()
        for response in [d.Failure('curl failed'),b'500',b'000',b'302']:
            with self.subTest(response=str(response)):
                with mock.patch.object(self.app,'run',side_effect=[response]):
                    with self.assertRaises(d.Failure): self.app.http('/login',200)

    def test_bad_assets_rejected(self):
        self.write('public/build/.vite/manifest.json',json.dumps({'app':{'file':'missing.css'}}))
        with self.assertRaises(d.Failure): self.app.assets()
        self.write('public/build/.vite/manifest.json',json.dumps({'app':{'file':'../../.env'}}))
        with self.assertRaises(d.Failure): self.app.assets()

    def test_migrations_require_explicit_authorization(self):
        with self.assertRaisesRegex(d.Failure,'--migrate'):
            self.app.execute('sha',['001_pending'],False,'backup')
        self.assertFalse(self.app.maintenance.started)

    def test_each_deployment_stage_failure_retains_guard(self):
        stages = ['composer','npm ci','npm build','storage:link','migrate',*d.CLEAR,*d.CACHE,'route:list','schedule:list','HTTPS','assets']
        for failed in stages:
            with self.subTest(stage=failed):
                # Fresh isolated controller per injected deployment failure.
                app = d.Deployment(self.root,REPO)
                app.seal = d.EnvironmentSeal(self.root)
                app.maintenance = d.Maintenance(self.root,TEMPLATE)
                app.lock = d.Lock(self.root/'.git/spanel-deploy.lock')
                app.before = 'old'
                app.clean = lambda: None
                app.git = lambda *args: b'old' if args == ('rev-parse','HEAD') else b''
                app.verify_maintenance = lambda: app.maintenance.verify_files()
                app.pending = lambda directory: ['001_pending']
                app.permissions = lambda: None
                app.assets = lambda: (_ for _ in ()).throw(d.Failure('assets')) if failed == 'assets' else None
                def project(argv,**kwargs):
                    label = 'composer' if 'install' in argv else ('npm ci' if 'ci' in argv else ('npm build' if 'build' in argv else 'other'))
                    if label == failed: raise d.Failure(label)
                    if label == 'npm ci': (self.root/'node_modules').mkdir(exist_ok=True)
                    return b''
                app.project = project
                def artisan(command,extra=None):
                    if command == failed: raise d.Failure(command)
                    if command == 'storage:link': (self.root/'public/storage').symlink_to(self.root/'storage/app/public')
                    return b''
                app.artisan = artisan
                # Reach HTTP/cache later stages by reporting no pending after caches.
                calls = iter([['001_pending'],[]])
                app.pending = lambda directory: next(calls)
                app.http = lambda *args: (_ for _ in ()).throw(d.Failure('HTTPS'))
                try:
                    with self.assertRaises(d.Failure) as failure: app.execute('new',['001_pending'],True,'backup')
                    self.assertEqual(str(failure.exception),failed)
                    with contextlib.redirect_stderr(io.StringIO()): code = app.finish(1)
                    self.assertNotEqual(code,0)
                    app.maintenance.verify_files()
                    self.assertIsNone(app.lock.fd)
                    self.persistent()
                finally:
                    app.lock.close()
                    for path in ['storage/framework/down','storage/framework/maintenance.php','public/storage']:
                        (self.root/path).unlink(missing_ok=True)

class ProcessTests(Fixture):
    def test_lock_exclusion_and_release(self):
        self.acquire()
        with self.assertRaises(BlockingIOError): d.Lock(self.root/'.git/spanel-deploy.lock')
        self.app.lock.close()
        lock = d.Lock(self.root/'.git/spanel-deploy.lock'); lock.close()

    def test_subprocess_does_not_inherit_lock_descriptor(self):
        self.acquire()
        script = "import os,sys; fd=int(sys.argv[1]);\ntry: os.fstat(fd)\nexcept OSError: sys.exit(0)\nsys.exit(1)"
        self.app.project([sys.executable,'-c',script,str(self.app.lock.fd)])

    def test_sigkill_cannot_leave_lock_inherited_by_child(self):
        # SIGKILL cannot run the supervisor's cleanup. Prove the surviving child
        # holds no lock, then kill/reap that fixture child explicitly in the test.
        # Linux subreaper is needed to avoid leaving an orphan for the host's init.
        if not sys.platform.startswith('linux'):
            self.skipTest('Orphan adoption test requires Linux subreaper')
        script = '''import importlib.util,sys,os,signal,subprocess,time
from pathlib import Path
spec=importlib.util.spec_from_file_location('d',sys.argv[1]);d=importlib.util.module_from_spec(spec);spec.loader.exec_module(d)
root=Path(sys.argv[2]); reaper=d.Processes();reaper.enable_subreaper()
driver="import importlib.util,sys;from pathlib import Path;s=importlib.util.spec_from_file_location('d',sys.argv[1]);d=importlib.util.module_from_spec(s);s.loader.exec_module(d);a=d.Deployment(Path(sys.argv[2]),Path(sys.argv[3]));a.start();a.maintenance.close();a.project([sys.executable,'-c',\\\"import os,time;from pathlib import Path;Path('survivor').write_text(str(os.getpid()));time.sleep(60)\\\"])"
p=subprocess.Popen([sys.executable,'-B','-c',driver,sys.argv[1],sys.argv[2],sys.argv[3]],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
try:
 deadline=time.monotonic()+5
 while not (root/'survivor').exists() and time.monotonic()<deadline: time.sleep(.02)
 assert (root/'survivor').exists(), 'child never started'
 child=int((root/'survivor').read_text());p.kill();p.wait(timeout=5)
 os.kill(child,0)
 lock=d.Lock(root/'.git/spanel-deploy.lock');lock.close()
 assert (root/'storage/framework/maintenance.php').is_file()
finally:
 if p.poll() is None: p.kill();p.wait(timeout=5)
 for child in reaper.descendants():
  try: os.kill(child,signal.SIGKILL)
  except ProcessLookupError: pass
 for child in reaper.descendants():
  try: os.waitpid(child,0)
  except ChildProcessError: pass
'''
        result = subprocess.run([sys.executable,'-B','-c',script,str(MODULE),str(self.root),str(REPO)],capture_output=True,timeout=15)
        self.assertEqual(result.returncode,0,result.stderr)

    def test_signals_stop_child_release_lock_and_retain_guard(self):
        for sig in [signal.SIGTERM,signal.SIGINT,signal.SIGHUP]:
            with self.subTest(signal=sig):
                ready = self.root/'ready'; ready.unlink(missing_ok=True)
                child_pid = self.root/'child'; child_pid.unlink(missing_ok=True)
                driver = '''import importlib.util,signal,sys,os
from pathlib import Path
spec=importlib.util.spec_from_file_location('d',sys.argv[1]); d=importlib.util.module_from_spec(spec);spec.loader.exec_module(d)
a=d.Deployment(Path(sys.argv[2]),Path(sys.argv[3]));a.start();a.maintenance.close()
a.http=lambda *args: a.maintenance.marker.encode()
def stop(sig,frame): raise d.Interrupted(sig)
for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]: signal.signal(sig,stop)
code=0
try:
 a.project([sys.executable,'-c',"import os,time;from pathlib import Path;Path('child').write_text(str(os.getpid()));Path('ready').write_text('ready');time.sleep(60)"])
except d.Interrupted as e: code=e.code
finally:
 for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]: signal.signal(sig,signal.SIG_IGN)
 code=a.finish(code)
sys.exit(code)
'''
                proc = subprocess.Popen([sys.executable,'-B','-c',driver,str(MODULE),str(self.root),str(REPO)],stdout=subprocess.PIPE,stderr=subprocess.PIPE)
                try:
                    deadline = time.monotonic()+5
                    while not ready.exists() and time.monotonic()<deadline: time.sleep(0.02)
                    self.assertTrue(ready.exists())
                    pid = int(child_pid.read_text())
                    proc.send_signal(sig)
                    _, err = proc.communicate(timeout=10)
                    self.assertEqual(proc.returncode,128+sig,err)
                    with self.assertRaises(ProcessLookupError): os.kill(pid,0)
                    lock = d.Lock(self.root/'.git/spanel-deploy.lock'); lock.close()
                    self.assertTrue((self.root/'storage/framework/maintenance.php').exists())
                    self.app.seal.verify(); self.persistent()
                finally:
                    if proc.poll() is None: proc.kill(); proc.communicate()
                    for path in ['storage/framework/down','storage/framework/maintenance.php']:
                        (self.root/path).unlink(missing_ok=True)

    @unittest.skipUnless(sys.platform.startswith('linux'),'Linux subreaper requires /proc and prctl; run again on Rocky')
    def test_detached_daemon_is_reaped(self):
        # Separate supervisor so prctl cannot adopt unrelated unittest children.
        script = '''import importlib.util,sys,os
spec=importlib.util.spec_from_file_location('d',sys.argv[1]);d=importlib.util.module_from_spec(spec);spec.loader.exec_module(d)
p=d.Processes();p.enable_subreaper();p.run([sys.executable,'-c',"import os,time;pid=os.fork();os._exit(0) if pid else None;os.setsid();open('daemon','w').write(str(os.getpid()));os.close(1);os.close(2);time.sleep(60)"],cwd=sys.argv[2]);assert not p.descendants()
'''
        result = subprocess.run([sys.executable,'-B','-c',script,str(MODULE),str(self.root)],capture_output=True,timeout=10)
        self.assertEqual(result.returncode,0,result.stderr)

    @unittest.skipUnless(sys.platform.startswith('linux'),'Linux double-fork cleanup requires /proc and prctl')
    def test_signals_reap_detached_descendants_ignoring_term(self):
        # Real setsid + double fork, with SIGTERM deliberately ignored in the
        # daemon. The deployment supervisor must still kill/reap it and unlock.
        for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]:
            with self.subTest(signal=sig):
                script = '''import importlib.util,sys,signal,time,subprocess,os
from pathlib import Path
spec=importlib.util.spec_from_file_location('d',sys.argv[1]);d=importlib.util.module_from_spec(spec);spec.loader.exec_module(d)
root=Path(sys.argv[2]);root.joinpath('daemon-ready').unlink(missing_ok=True)
child="import os,signal,time;from pathlib import Path;pid=os.fork();os._exit(0) if pid else None;os.setsid();pid=os.fork();os._exit(0) if pid else None;signal.signal(signal.SIGTERM,signal.SIG_IGN);Path('daemon-ready').write_text(str(os.getpid()));time.sleep(60)"
driver="import importlib.util,sys,signal;from pathlib import Path;s=importlib.util.spec_from_file_location('d',sys.argv[1]);d=importlib.util.module_from_spec(s);s.loader.exec_module(d);a=d.Deployment(Path(sys.argv[2]),Path(sys.argv[3]));a.start();a.maintenance.close();a.http=lambda *args:a.maintenance.marker.encode()\\ndef stop(sig,frame):raise d.Interrupted(sig)\\nfor sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]:signal.signal(sig,stop)\\ncode=0\\ntry:a.project([sys.executable,'-c',sys.argv[4]])\\nexcept d.Interrupted as e:code=e.code\\nfinally:\\n for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]:signal.signal(sig,signal.SIG_IGN)\\n code=a.finish(code)\\nsys.exit(code)"
reaper=d.Processes();reaper.enable_subreaper()
p=subprocess.Popen([sys.executable,'-B','-c',driver,sys.argv[1],sys.argv[2],sys.argv[3],child],stdout=subprocess.PIPE,stderr=subprocess.PIPE)
try:
 deadline=time.monotonic()+5
 while not root.joinpath('daemon-ready').exists() and time.monotonic()<deadline:time.sleep(.02)
 assert root.joinpath('daemon-ready').exists(), 'daemon never started'
 pid=int(root.joinpath('daemon-ready').read_text());p.send_signal(int(sys.argv[4]));out,err=p.communicate(timeout=8)
 assert p.returncode==128+int(sys.argv[4]), (p.returncode,err)
 try:os.kill(pid,0)
 except ProcessLookupError:pass
 else:raise AssertionError('surviving daemon or zombie')
 lock=d.Lock(root/'.git/spanel-deploy.lock');lock.close()
finally:
 if p.poll() is None:p.kill();p.communicate(timeout=3)
 for pid in reaper.descendants():
  try:os.kill(pid,signal.SIGKILL)
  except ProcessLookupError:pass
 for pid in reaper.descendants():
  try:os.waitpid(pid,0)
  except ChildProcessError:pass
'''
                result = subprocess.run([sys.executable,'-B','-c',script,str(MODULE),str(self.root),str(REPO),str(int(sig))],capture_output=True,timeout=20)
                self.assertEqual(result.returncode,0,result.stderr)
                self.persistent(); self.app.seal.verify()

@unittest.skipUnless((REPO/'vendor/autoload.php').is_file() and shutil.which('php'), 'Installed Laravel vendor and PHP required')
class ArtisanIntegrationTests(Fixture):
    """Real Laravel commands, only fixture runtime and dummy environment; no DB calls."""
    def setUp(self):
        super().setUp()
        for directory in ['config','app','routes','resources/views']:
            shutil.copytree(REPO/directory,self.root/directory,dirs_exist_ok=True)
        self.write('bootstrap/app.php',(REPO/'bootstrap/app.php').read_text())
        self.write('composer.json',(REPO/'composer.json').read_text())
        (self.root/'vendor').symlink_to(REPO/'vendor')
        self.clean_env = {key: value for key,value in os.environ.items()
                          if not key.startswith(('APP_','DB_','LARAVEL_','VIEW_','LOG_','CACHE_','SESSION_','QUEUE_','FILESYSTEM_'))}
        # DB calls must fail locally rather than reach any service if a future provider changes.
        self.write('.env',DUMMY_ENV+'DB_HOST=127.0.0.1\nDB_PORT=1\nDB_DATABASE=isolated_no_database\nDB_USERNAME=fixture\nDB_PASSWORD=fixture\n')
        self.original_env = (self.root/'.env').read_bytes()

    def artisan(self, operation, env=None):
        return subprocess.run(['php','-d','display_errors=0','-d','log_errors=0',
                               str(REPO/'scripts/deployment/artisan.php'),str(self.root),operation],
                              cwd=self.root,env=env or self.clean_env,capture_output=True,timeout=45)

    def test_actual_individual_cache_commands(self):
        for command in d.CLEAR+d.CACHE:
            with self.subTest(command=command):
                result = self.artisan(command)
                self.assertEqual(result.returncode,0,result.stderr.decode()+result.stdout.decode())
        self.assertEqual((self.root/'bootstrap/cache/config.php').stat().st_mode & 0o777, 0o600)
        self.assertEqual((self.root/'.env').read_bytes(),self.original_env)
        self.persistent()

    def test_configured_app_key_cannot_diverge_from_dotenv(self):
        config = (self.root/'config/app.php').read_text()
        self.assertIn("env('APP_KEY')",config)
        self.write('config/app.php',config.replace("env('APP_KEY')", "'deliberately-wrong-fixture-key'"))
        result = self.artisan('config:cache')
        self.assertNotEqual(result.returncode,0)
        self.assertEqual((self.root/'.env').read_bytes(),self.original_env)

    def test_view_compiled_path_cannot_delete_persistent_data(self):
        with (self.root/'.env').open('a') as out:
            out.write('VIEW_COMPILED_PATH='+str(self.root/'storage/app')+'\n')
        result = self.artisan('view:clear')
        self.assertNotEqual(result.returncode,0)
        self.persistent()

    def test_all_environment_path_overrides_blocked(self):
        for name in ['VIEW_COMPILED_PATH','APP_BASE_PATH','LARAVEL_STORAGE_PATH','APP_CONFIG_CACHE',
                     'APP_ROUTES_CACHE','APP_EVENTS_CACHE','APP_SERVICES_CACHE','APP_PACKAGES_CACHE']:
            with self.subTest(variable=name):
                env = dict(self.clean_env, **{name: str(self.root/'storage/app')})
                result = self.artisan('view:clear',env)
                self.assertNotEqual(result.returncode,0)
                self.persistent()

    def test_cached_config_cannot_hide_unsafe_raw_config(self):
        result = self.artisan('config:cache')
        self.assertEqual(result.returncode,0,result.stderr)
        self.write('config/view.php',"<?php return ['paths'=>[resource_path('views')], 'compiled'=>storage_path('app')];")
        result = self.artisan('view:clear')
        self.assertNotEqual(result.returncode,0)
        self.persistent()

    def test_each_configurable_runtime_path_is_restricted(self):
        mutations = {
            'config/view.php':("realpath(storage_path('framework/views'))", "storage_path('app')"),
            'config/session.php':("storage_path('framework/sessions')", "storage_path('app')"),
            'config/cache.php':("storage_path('framework/cache/data')", "storage_path('app')"),
            'config/logging.php':("storage_path('logs/laravel.log')", "storage_path('app/keep')"),
            'config/filesystems.php':("public_path('storage')", "public_path('images')"),
        }
        for name,(old,new) in mutations.items():
            with self.subTest(config=name):
                original = (self.root/name).read_text()
                self.assertIn(old,original)
                self.write(name,original.replace(old,new))
                result = self.artisan('view:clear')
                self.assertNotEqual(result.returncode,0)
                self.persistent()
                self.write(name,original)

    def test_internal_cache_write_failure_is_nonzero(self):
        for command, path in [('config:cache','bootstrap/cache/config.php'),
                              ('route:cache','bootstrap/cache/routes-v7.php'),
                              ('event:cache','bootstrap/cache/events.php')]:
            with self.subTest(command=command):
                destination = self.root/path
                destination.mkdir()
                result = self.artisan(command)
                self.assertNotEqual(result.returncode,0)
                destination.rmdir()
                self.persistent()

    def test_internal_view_compilation_failure_is_nonzero(self):
        self.write('resources/views/deploy-invalid.blade.php', '@break(broken syntax)')
        # Invalid directive argument may compile as text; an unknown component is deterministic.
        self.write('resources/views/deploy-invalid.blade.php', '<x-deployment-component-that-does-not-exist />')
        result = self.artisan('view:cache')
        self.assertNotEqual(result.returncode,0)
        self.persistent()

    def test_storage_link_real_command_and_exact_target(self):
        result = self.artisan('storage:link')
        self.assertEqual(result.returncode,0,result.stderr)
        self.app.link(required=True)
        self.persistent()

    @unittest.skipIf(os.geteuid() == 0, 'Readonly-directory failure must run as unprivileged user')
    def test_actual_cache_writes_fail_on_readonly_runtime(self):
        # Providers have already generated their manifests; normal bootstrap still
        # succeeds, isolating the failure to the actual command's cache write.
        result = self.artisan('package:discover')
        self.assertEqual(result.returncode,0,result.stderr)
        cache = self.root/'bootstrap/cache'
        views = self.root/'storage/framework/views'
        cache.chmod(0o500); views.chmod(0o500)
        try:
            result = self.artisan('route:list')
            self.assertEqual(result.returncode,0,result.stderr)
            for command in d.CACHE:
                with self.subTest(command=command):
                    result = self.artisan(command)
                    self.assertNotEqual(result.returncode,0)
                    self.persistent()
        finally:
            cache.chmod(0o700); views.chmod(0o700)

    @unittest.skipIf(os.geteuid() == 0, 'Readonly-directory failure must run as unprivileged user')
    def test_actual_cache_clear_failures_are_nonzero(self):
        for command in d.CACHE:
            result = self.artisan(command)
            self.assertEqual(result.returncode,0,result.stderr)
        cache = self.root/'bootstrap/cache'; views = self.root/'storage/framework/views'
        cache.chmod(0o500); views.chmod(0o500)
        try:
            for command in d.CLEAR:
                with self.subTest(command=command):
                    result = self.artisan(command)
                    self.assertNotEqual(result.returncode,0)
                    self.persistent()
        finally:
            cache.chmod(0o700); views.chmod(0o700)
