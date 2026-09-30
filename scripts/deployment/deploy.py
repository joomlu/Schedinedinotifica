#!/usr/bin/env python3
"""Conservative in-place SPanel deployment. No project imports. Python >= 3.9."""
import argparse
import base64
import ctypes
import fcntl
import hashlib
import hmac
import io
import json
import os
from pathlib import Path
import re
import shutil
import signal
import stat
import subprocess
import sys
import tarfile
import tempfile
import time

PRODUCTION = Path('/home/tanggosoftware/repos/schedinedinotifica')
DOMAIN = 'schedinedinotifica.tanggo.software'
INDEX_HASH = 'c96ed6bbe80f9105534af4d1510dc79f0143e6843970dba7ddd4ec7d03097cab'
BUILD_HASHES = {
    'package.json': '0f591de75ab44ad61381c0d09a564ad963aaa1b1893f9511c54d523454916590',
    'vite.config.js': 'dc907b202ee2dd410c02fd3cbddd89725494b4dff98be5039f35aed248135d33',
    'package-copy-config.json': '11bda5d30443a82687fc024afebafef1dae9941fc6dca42dccee4f6754707919',
    'scripts/verify-architecture.mjs': 'b0bf8cb296fdf164573db2276df31db8eb6f187d27709dc7dbe9b40ad0e4d403',
}
BUNDLE = ['deploy.sh', 'scripts/deployment/deploy.py', 'scripts/deployment/artisan.php', 'scripts/deployment/maintenance.php']
RUNTIME = ['storage/framework', 'storage/logs', 'bootstrap/cache']
CLEAR = ['config:clear', 'route:clear', 'view:clear', 'event:clear']
CACHE = ['config:cache', 'route:cache', 'view:cache', 'event:cache']

class Failure(Exception):
    pass

class Critical(Failure):
    code = 85

class EnvironmentIntegrity(Critical):
    code = 86

class Interrupted(Failure):
    def __init__(self, signum):
        self.code = 128 + signum
        super().__init__('Interrupted by signal {}'.format(signum))

def require(condition, message):
    if not condition:
        raise Failure(message)

def digest(data):
    return hashlib.sha256(data).hexdigest()

def physical(path, exists=False):
    """No symlink components and no hardlinked regular files, including parents."""
    path = Path(path)
    require(path.is_absolute(), 'Expected absolute path')
    require('..' not in path.parts, 'Parent traversal rejected')
    for item in reversed([path] + list(path.parents)):
        try:
            meta = item.lstat()
        except FileNotFoundError:
            if item == path and exists:
                raise Failure('Required path missing: {}'.format(item))
            continue
        require(not stat.S_ISLNK(meta.st_mode), 'Symlink path rejected: {}'.format(item))
        if stat.S_ISREG(meta.st_mode):
            require(meta.st_nlink == 1, 'Hardlinked file rejected: {}'.format(item))
    return path

class EnvironmentSeal:
    """No dotenv/project code; only literal APP_KEY syntax is accepted."""
    def __init__(self, root):
        self.path = root / '.env'
        self.snapshot = self.read()

    def read(self):
        physical(self.path, exists=True)
        require(self.path.is_file(), '.env must be a regular file')
        data = self.path.read_bytes()
        values = re.findall(rb'(?m)^[ \t]*(?:export[ \t]+)?APP_KEY[ \t]*=[ \t]*(.*)$', data)
        require(len(values) == 1, 'Exactly one APP_KEY assignment is required')
        value = values[0].strip()
        match = re.fullmatch(rb'''(?:"([^"\r\n]*)"|'([^'\r\n]*)'|([^\s#'"]+))(?:[ \t]+\#[^\r\n]*)?''', value)
        require(match is not None, 'APP_KEY must be a nonempty literal (no multiline/interpolation)')
        key = next(x for x in match.groups() if x is not None)
        require(bool(key) and b'$' not in key and b'\\' not in key, 'APP_KEY must be a nonempty literal')
        return digest(data), digest(key)

    def verify(self):
        try:
            current = self.read()
            if current != self.snapshot:
                raise Failure('changed')
        except (OSError, Failure):
            raise EnvironmentIntegrity('CRITICAL ENV_INTEGRITY: .env or APP_KEY changed, disappeared or became unsafe; never auto-restored') from None

class Lock:
    def __init__(self, path):
        self.fd = None
        physical(path)
        fd = os.open(str(path), os.O_CREAT | os.O_RDWR | getattr(os, 'O_NOFOLLOW', 0), 0o600)
        try:
            os.set_inheritable(fd, False)
            meta = os.fstat(fd)
            require(stat.S_ISREG(meta.st_mode) and meta.st_nlink == 1 and meta.st_uid == os.getuid(), 'Unsafe lock file')
            fcntl.flock(fd, fcntl.LOCK_EX | fcntl.LOCK_NB)
            self.fd = fd
        except BaseException:
            os.close(fd)
            raise

    def close(self):
        if self.fd is not None:
            try:
                os.close(self.fd)  # The supervisor is the sole holder; close releases flock.
            finally:
                self.fd = None

class Processes:
    def __init__(self):
        self.active = None
        self.subreaper = False

    def enable_subreaper(self):
        if sys.platform.startswith('linux'):
            libc = ctypes.CDLL(None, use_errno=True)
            require(libc.prctl(36, 1, 0, 0, 0) == 0, 'Cannot enable Linux child subreaper')
            self.subreaper = True

    def descendants(self):
        if not self.subreaper:
            return set()
        parents = {}
        for item in Path('/proc').iterdir():
            if not item.name.isdigit():
                continue
            try:
                # stat's comm can contain spaces/parentheses: use the last closing parenthesis.
                parts = (item / 'stat').read_text().rsplit(')', 1)[1].split()
                parents[int(item.name)] = int(parts[1])
            except (FileNotFoundError, ProcessLookupError):
                continue
        found, frontier = set(), {os.getpid()}
        while frontier:
            frontier = {pid for pid, ppid in parents.items() if ppid in frontier and pid not in found}
            found.update(frontier)
        return found

    @staticmethod
    def signal_group(pid, sig):
        try:
            os.killpg(pid, sig)
        except ProcessLookupError:
            pass

    def stop(self, process):
        # Also stop children that outlive an otherwise successful command.
        process.poll()  # Reap a dead leader; macOS rejects killpg on zombie-only groups.
        self.signal_group(process.pid, signal.SIGTERM)
        time.sleep(0.05)
        process.poll()
        self.signal_group(process.pid, signal.SIGKILL)
        process.wait(timeout=5)
        for stream in [process.stdout, process.stderr]:
            if stream is not None:
                stream.close()
        if self.subreaper:
            for _ in range(20):
                children = self.descendants()
                if not children:
                    return
                for pid in children:
                    try:
                        os.kill(pid, signal.SIGKILL)
                    except ProcessLookupError:
                        pass
                for pid in children:
                    try:
                        os.waitpid(pid, os.WNOHANG)
                    except ChildProcessError:
                        pass
                time.sleep(0.05)
            raise Critical('CRITICAL CHILDREN: could not reap descendants; restrict deployment access and inspect processes')

    def run(self, argv, cwd=None, timeout=120, env=None):
        # All subprocesses close the lock FD, including grandchildren; no shell or pipeline.
        # Defer interruptions through Popen and assignment so a signal cannot
        # strand an unrecorded child. This supervisor is deliberately single-threaded.
        blocked = {signal.SIGINT, signal.SIGTERM, signal.SIGHUP}
        previous_mask = signal.pthread_sigmask(signal.SIG_BLOCK, blocked)
        try:
            try:
                process = subprocess.Popen([str(a) for a in argv], cwd=cwd, env=env,
                                           stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                                           close_fds=True, start_new_session=True,
                                           preexec_fn=lambda: signal.pthread_sigmask(signal.SIG_SETMASK, previous_mask))
                self.active = process
            finally:
                signal.pthread_sigmask(signal.SIG_SETMASK, previous_mask)
            output, _ = process.communicate(timeout=timeout)
            if process.returncode:
                raise Failure('Command failed (exit {}, executable {})'.format(process.returncode, Path(str(argv[0])).name))
            return output
        except subprocess.TimeoutExpired:
            raise Failure('Command timed out') from None
        finally:
            if self.active is not None:
                self.stop(self.active)
                self.active = None

    def shutdown(self):
        if self.active is not None:
            self.stop(self.active)
            self.active = None

class Maintenance:
    def __init__(self, root, template):
        self.root = root
        self.secret = os.urandom(24).hex()
        self.marker = os.urandom(16).hex()
        self.guard = template.replace('__DEPLOY_SECRET__', self.secret).replace('__DEPLOY_MARKER__', self.marker).encode()
        self.payload = json.dumps({'status': 503, 'retry': 60, 'secret': self.secret, 'template': '<h1>Maintenance</h1>'}).encode()
        self.started = False

    def assert_index(self):
        physical(self.root / 'public/index.php', exists=True)
        require(digest((self.root / 'public/index.php').read_bytes()) == INDEX_HASH, 'Unreviewed public/index.php; maintenance contract cannot be guaranteed')

    @staticmethod
    def atomic(path, data):
        physical(path.parent, exists=True)
        physical(path)
        fd, temp = tempfile.mkstemp(prefix='.deploy-', dir=str(path.parent))
        try:
            with os.fdopen(fd, 'wb') as stream:
                os.fchmod(stream.fileno(), 0o640)
                stream.write(data)
                stream.flush()
                os.fsync(stream.fileno())
            os.replace(temp, path)
        finally:
            if os.path.exists(temp):
                os.unlink(temp)

    def close(self):
        self.assert_index()
        self.started = True
        # Guard FIRST: it returns 503 even if writing the Laravel down file fails.
        self.atomic(self.root / 'storage/framework/maintenance.php', self.guard)
        self.atomic(self.root / 'storage/framework/down', self.payload)
        self.verify_files()

    def verify_files(self):
        self.assert_index()
        for name, data in [('maintenance.php', self.guard), ('down', self.payload)]:
            p = self.root / 'storage/framework' / name
            physical(p, exists=True)
            require(p.read_bytes() == data, 'Maintenance file missing or altered')

    def open(self):
        self.verify_files()
        # Keep the independent 503 guard until the last operation.
        (self.root / 'storage/framework/down').unlink()
        (self.root / 'storage/framework/maintenance.php').unlink()

    def cookie(self):
        expires = int(time.time()) + 1800
        mac = hmac.new(self.secret.encode(), str(expires).encode(), hashlib.sha256).hexdigest()
        return base64.b64encode(json.dumps({'expires_at': expires, 'mac': mac}).encode()).decode()

class Deployment:
    def __init__(self, root, bundle, php='php', composer='composer', origin='127.0.0.1'):
        self.root, self.bundle = Path(root), Path(bundle)
        self.php, self.composer, self.origin = php, composer, origin
        self.proc = Processes()
        self.seal = None
        self.lock = None
        self.scratch = None
        self.maintenance = None
        self.success = False
        self.before = None

    def start(self):
        physical(self.root, exists=True)
        # First operation touching application state: read/hash .env without executing project code.
        self.seal = EnvironmentSeal(self.root)
        physical(self.root / '.git', exists=True)
        self.lock = Lock(self.root / '.git/spanel-deploy.lock')
        self.proc.enable_subreaper()
        self.maintenance = Maintenance(self.root, (self.bundle / 'scripts/deployment/maintenance.php').read_text())

    def run(self, argv, **kwargs):
        return self.proc.run(argv, cwd=kwargs.pop('cwd', self.root), **kwargs)

    def project(self, argv, **kwargs):
        self.seal.verify()
        try:
            return self.run(argv, **kwargs)
        finally:
            self.seal.verify()

    def git(self, *args):
        return self.project(['git', '-c', 'core.hooksPath=/dev/null', '-c', 'core.fsmonitor=false', *args])

    def clean(self):
        require(not self.git('status', '--porcelain=v1', '--untracked-files=all').strip(), 'Dirty tree/index or untracked files; no automatic stash/reset/clean')
        for item in self.git('ls-files', '-v', '-z').split(b'\0'):
            if item:
                require(not chr(item[0]).islower() and item[0:1] != b'S', 'Hidden index flags rejected')

    @staticmethod
    def protected(path):
        if path == '.env.example':
            return False
        return path == '.env' or path.startswith('.env.') or any(path == p or path.startswith(p + '/') for p in
            ['storage', 'public/images', 'public/storage', 'public/build', 'public/hot', 'bootstrap/cache', 'vendor', 'node_modules', 'public/index.php'])

    def candidate(self, sha):
        self.git('merge-base', '--is-ancestor', 'HEAD', sha)
        fields = self.git('diff', '--no-renames', '--name-status', '-z', 'HEAD', sha).split(b'\0')
        require(fields.pop() == b'' and len(fields) % 2 == 0, 'Malformed Git diff')
        for code, raw in zip(fields[0::2], fields[1::2]):
            path = os.fsdecode(raw)
            require(code in [b'A', b'M', b'D', b'T'], 'Unsupported Git change')
            require(not self.protected(path), 'Candidate modifies protected path: {!r}'.format(path))
            if path.startswith('database/migrations/'):
                require(code == b'A', 'Existing migration changed or deleted')
        for row in self.git('ls-tree', '-rz', sha).split(b'\0'):
            if not row:
                continue
            meta, raw = row.split(b'\t', 1)
            require(meta.split()[0] not in [b'120000', b'160000'], 'Candidate symlink/submodule rejected')
            path = os.fsdecode(raw)
            require(not (path == '.env' or (path.startswith('.env.') and path != '.env.example')),
                    'Candidate tracks environment files')
            require(not any(path == p or path.startswith(p + '/') for p in ['vendor','node_modules','public/build','public/hot','public/storage']),
                    'Candidate tracks generated runtime files')

    def runtime(self):
        for name in ['storage','storage/app','storage/app/public','public','public/images','storage/framework',
                     'storage/framework/cache','storage/framework/cache/data','storage/framework/views','storage/framework/sessions','storage/logs','bootstrap/cache']:
            p = physical(self.root / name, exists=True)
            require(p.is_dir() and p.stat().st_uid == os.getuid() and os.access(p, os.W_OK), 'Invalid runtime ownership/access')
        for name in RUNTIME:
            def fail(error):
                raise error
            for directory, dirs, files in os.walk(str(self.root / name), onerror=fail, followlinks=False):
                for p in [Path(directory)] + [Path(directory) / n for n in dirs + files]:
                    physical(p, exists=True)
                    info = p.stat()
                    require(info.st_uid == os.getuid(), 'Runtime has a different owner')
                    require(not stat.S_ISDIR(info.st_mode) or not info.st_mode & stat.S_IWOTH, 'World-writable runtime directory')
        for name in ['vendor','node_modules','public/build']:
            p = physical(self.root / name)
            require(not p.exists() or (p.is_dir() and p.stat().st_uid == os.getuid()), 'Unsafe generated directory')

    def link(self, required=False):
        p = self.root / 'public/storage'
        physical(p.parent, exists=True)
        if p.is_symlink():
            expected = self.root / 'storage/app/public'
            require(os.readlink(p) == str(expected) and p.resolve(strict=True) == expected,
                    'Storage link must point directly to the exact absolute target')
        else:
            require(not p.exists() and not required, 'Storage symlink missing or replaced with a real path')

    def artisan(self, command, extra=None):
        self.runtime()
        args = [self.php, '-d', 'display_errors=0', '-d', 'log_errors=0',
                self.bundle / 'scripts/deployment/artisan.php', self.root, command]
        if extra is not None:
            args.append(extra)
        result = self.project(args, timeout=1200 if command == 'migrate' else 180)
        self.runtime()
        return result

    def pending(self, directory):
        result = json.loads(self.artisan('pending', str(directory)))
        require(isinstance(result, list) and all(isinstance(n, str) and re.fullmatch(r'[A-Za-z0-9_]+', n) for n in result), 'Invalid migration response')
        return result

    def caches(self):
        for name in CLEAR + CACHE:
            self.artisan(name)  # No aggregate optimize; each exit and postcondition is checked.

    def build_contract(self, source):
        # These reviewed scripts write only public/build. A changed build pipeline
        # needs a new review of its destinations, not an implicit trust upgrade.
        for name, expected in BUILD_HASHES.items():
            require(digest((source/name).read_bytes()) == expected, 'Build pipeline changed; review destinations and update runner: '+name)
        composer = json.loads((source/'composer.json').read_text())
        config = composer.get('config', {})
        for name, allowed in [('vendor-dir','vendor'), ('bin-dir','vendor/bin')]:
            require(config.get(name,allowed) == allowed, 'Custom Composer installation path rejected')
        for name in ['COMPOSER','COMPOSER_VENDOR_DIR','COMPOSER_BIN_DIR']:
            require(name not in os.environ, 'Composer path override rejected: '+name)

    def clear_manifests(self):
        # Composer scripts are disabled. Remove only stale provider manifests so
        # deleted production/dev packages cannot break the next guarded bootstrap.
        self.runtime()
        for name in ['packages.php', 'services.php']:
            path = physical(self.root/'bootstrap/cache'/name)
            if path.exists():
                require(path.is_file(), 'Unexpected provider manifest type')
                path.unlink()

    def http(self, path, expected, bypass=False):
        require(self.scratch is not None, 'HTTP scratch missing')
        jar = self.scratch / 'cookies'
        if bypass:
            jar.write_text('# Netscape HTTP Cookie File\n{}\tFALSE\t/\tTRUE\t0\tlaravel_maintenance\t{}\n'.format(DOMAIN, self.maintenance.cookie()))
        else:
            jar.write_text('')
        jar.chmod(0o600)
        body = self.scratch / 'http-body'
        code = self.run(['curl','--silent','--show-error','--noproxy','*','--connect-timeout','5','--max-time','15',
                         '--resolve','{}:443:{}'.format(DOMAIN,self.origin),'--cookie',jar,
                         '--output',body,'--write-out','%{http_code}','https://'+DOMAIN+path], timeout=20)
        require(code.decode() == str(expected), 'Origin HTTPS returned unexpected status')
        return body.read_bytes()

    def verify_maintenance(self):
        self.maintenance.verify_files()
        body = self.http('/login', 503)
        require(self.maintenance.marker.encode() in body, 'Origin did not return this maintenance response')

    def permissions(self):
        self.runtime()
        for name in RUNTIME:
            def fail(error):
                raise error
            for directory, dirs, files in os.walk(str(self.root/name), onerror=fail, followlinks=False):
                for p in [Path(directory)] + [Path(directory)/n for n in files]:
                    physical(p, exists=True)
                    mode = stat.S_IMODE(p.stat().st_mode)
                    p.chmod(mode | (0o700 if p.is_dir() else 0o600))
        # Never traverse/chmod storage/app or public/images.

    def prepare(self, expected):
        self.clean()
        require(self.git('branch','--show-current').strip() == b'main', 'Production must be on main')
        require(Path(os.fsdecode(self.git('rev-parse','--show-toplevel').strip())) == self.root, 'Wrong repository root')
        require(self.git('remote','get-url','origin').strip() in [b'https://github.com/joomlu/Schedinedinotifica',
                b'https://github.com/joomlu/Schedinedinotifica.git',b'git@github.com:joomlu/Schedinedinotifica.git'], 'Unexpected origin')
        require(not self.git('ls-files','--','.env').strip(), '.env is tracked')
        for name in ['public/hot','storage/framework/down','storage/framework/maintenance.php']:
            require(not os.path.lexists(self.root/name), 'Existing hot/maintenance file; inspect manually')
        self.runtime(); self.link(); self.maintenance.assert_index()
        self.project([self.php,'-r','exit(PHP_MAJOR_VERSION===8 && PHP_MINOR_VERSION===3 ? 0 : 1);'])
        self.project([self.php,'-r','foreach(["pdo_mysql","mbstring","curl","dom","fileinfo","openssl","soap","zip"] as $e) {if(!extension_loaded($e)) exit(1);}'])
        require(self.project(['node','-p','process.versions.node.split(".")[0]']).strip() == b'20', 'Node 20 required')
        require(self.project(['npm','--version']).strip().startswith(b'10.'), 'npm 10 required')
        require(self.project([self.php,self.composer,'--version','--no-ansi']).startswith(b'Composer version 2.'), 'Composer 2 PHP executable required')
        require(shutil.disk_usage(self.root).free >= 2 * 1024**3, 'Less than 2 GiB free')
        self.before = self.git('rev-parse','HEAD').decode().strip()
        self.git('fetch','--no-tags','origin','refs/heads/main')
        target = self.git('rev-parse','FETCH_HEAD').decode().strip()
        require(target == expected, 'origin/main differs from approved SHA')
        self.candidate(target)
        self.scratch = Path(tempfile.mkdtemp(prefix='.schedinedinotifica-deploy.', dir=str(self.root.parent)))
        self.scratch.chmod(0o700)
        source = self.scratch / 'source'; source.mkdir()
        archive = self.git('archive',target)
        with tarfile.open(fileobj=io.BytesIO(archive)) as tf:
            for item in tf:
                name = Path(item.name)
                require(not name.is_absolute() and '..' not in name.parts and (item.isdir() or item.isfile()), 'Unsafe archive member')
                p = source / name
                if item.isdir():
                    p.mkdir(parents=True, exist_ok=True)
                else:
                    p.parent.mkdir(parents=True, exist_ok=True)
                    with tf.extractfile(item) as stream:
                        p.write_bytes(stream.read())
                    p.chmod(0o755 if item.mode & 0o111 else 0o644)
        for name in BUNDLE:
            require((source/name).read_bytes() == (self.bundle/name).read_bytes(), 'Runner differs from candidate; install reviewed external bundle first')
        self.build_contract(source)
        for name in ['artisan','composer.lock','package-lock.json','vite.config.js','package-copy-config.json','scripts/verify-architecture.mjs','scripts/geo-immutable.hashes.json']:
            require((source/name).is_file(), 'Missing candidate file: '+name)
        for name in ['web/assets','public/site-assets','resources/views','resources/scss','resources/fonts','resources/images','resources/json','resources/js/pages','database/migrations','reference/libreria/geo']:
            require((source/name).is_dir(), 'Missing candidate directory: '+name)
        self.project([self.php,self.composer,'--no-plugins','validate','--strict','--no-check-publish'], cwd=source)
        self.project([self.php,self.composer,'--no-plugins','check-platform-reqs','--lock','--no-dev'], cwd=source)
        self.http('/login',200)
        return target, self.pending(source/'database/migrations')

    def execute(self, target, pending, migrate, backup):
        require(not pending or migrate, 'Explicit --migrate required')
        require(bool(backup), 'Verified external backup reference required')
        self.clean(); self.seal.verify()
        require(self.git('rev-parse','HEAD').decode().strip() == self.before, 'HEAD changed during preflight')
        self.maintenance.close(); self.verify_maintenance()
        self.git('merge','--ff-only',target)
        # Run guarded config clear before installing new dependencies; failure stays behind static 503.
        self.artisan('config:clear')
        self.project([self.php,self.composer,'--no-plugins','install','--no-dev','--no-scripts','--optimize-autoloader','--no-interaction','--prefer-dist'], timeout=1200)
        self.project([self.php,self.composer,'--no-plugins','check-platform-reqs','--no-dev'])
        self.clear_manifests()
        self.artisan('package:discover')
        self.project(['npm','ci','--include=dev','--engine-strict','--ignore-scripts','--no-audit','--no-fund'], timeout=1200)
        env = os.environ.copy(); env['npm_config_ignore_scripts'] = 'true'
        self.project(['npm','--ignore-scripts','run','build'], timeout=1200, env=env)
        self.assets()
        modules = physical(self.root/'node_modules', exists=True)
        require(modules.is_dir(), 'Unsafe node_modules')
        shutil.rmtree(modules)  # Only generated dependencies; no persistent-data cleanup.
        self.link()
        if not (self.root/'public/storage').is_symlink():
            self.artisan('storage:link')
        self.link(required=True)
        self.permissions()
        require(self.pending(self.root/'database/migrations') == pending, 'Migration history changed')
        if pending:
            require(migrate, 'Explicit migration approval lost')
            self.artisan('migrate',json.dumps(pending))
        self.caches()
        require(not self.pending(self.root/'database/migrations'), 'Migrations still pending')
        self.artisan('route:list'); self.artisan('schedule:list')
        self.clean(); self.seal.verify(); self.verify_maintenance()
        self.http('/login',200,True); self.http('/',200,True)
        require(self.http('/build/css/app.min.css',200,True) == (self.root/'public/build/css/app.min.css').read_bytes(), 'HTTP asset mismatch')
        require(self.git('rev-parse','HEAD').decode().strip() == target, 'Final SHA mismatch')
        self.maintenance.open()
        self.http('/login',200)
        self.clean(); self.seal.verify()
        history = physical(self.root/'.git/spanel-deploy-history')
        with history.open('a') as out:
            out.write('{} {}\n'.format(time.strftime('%Y-%m-%dT%H:%M:%SZ',time.gmtime()),target))
        self.success = True

    def assets(self):
        build = physical(self.root/'public/build', exists=True)
        manifest = json.loads((build/'.vite/manifest.json').read_text())
        require(isinstance(manifest,dict) and bool(manifest), 'Empty/invalid Vite manifest')
        for entry in manifest.values():
            for name in [entry['file']] + entry.get('css',[]) + entry.get('assets',[]):
                require(isinstance(name,str) and not Path(name).is_absolute() and '..' not in Path(name).parts, 'Unsafe manifest path')
                require(physical(build/name, exists=True).is_file(), 'Missing Vite asset')

    def finish(self, code):
        # One cleanup boundary for normal return, errors, INT/HUP/TERM; never auto-repair secrets.
        children_safe = True
        try:
            try:
                self.proc.shutdown()
            except BaseException:
                children_safe = False
                print('[deploy] CRITICAL CHILDREN: process cleanup failed', file=sys.stderr); code = 85
            try:
                if self.seal:
                    self.seal.verify()
            except (Failure,OSError):
                print('[deploy] CRITICAL ENV_INTEGRITY: .env/APP_KEY not intact; no automatic restoration', file=sys.stderr)
                code = 86
            # Clean before deciding the final service state: a cleanup failure is
            # also a failed deployment, including after maintenance was opened.
            if self.scratch:
                try:
                    physical(self.scratch, exists=True)
                    require(self.scratch.parent == self.root.parent and self.scratch.name.startswith('.schedinedinotifica-deploy.'), 'Unsafe temporary cleanup')
                    shutil.rmtree(self.scratch)
                    self.scratch = None
                except BaseException:
                    print('[deploy] Temporary cleanup failed; inspect private directory', file=sys.stderr)
                    code = code or 1
            # Recheck after cleanup too, before selecting the recovery path.
            try:
                if self.seal:
                    self.seal.verify()
            except (Failure,OSError):
                print('[deploy] CRITICAL ENV_INTEGRITY: .env/APP_KEY not intact after cleanup', file=sys.stderr)
                code = 86
            if code and self.lock and self.maintenance and (self.maintenance.started or code == 86 or not children_safe):
                try:
                    self.maintenance.close()
                    if self.scratch is None:
                        self.scratch = Path(tempfile.mkdtemp(prefix='.schedinedinotifica-deploy.',dir=str(self.root.parent)))
                    self.verify_maintenance()
                    if children_safe:
                        print('[deploy] Failure: independent 503 maintenance VERIFIED at HTTPS origin; no rollback', file=sys.stderr)
                    else:
                        print('[deploy] CRITICAL CHILDREN: 503 observed but surviving processes may change it; safe state NOT guaranteed', file=sys.stderr)
                except BaseException:
                    print('[deploy] CRITICAL MAINTENANCE_UNVERIFIED: cannot guarantee safe service; restrict traffic manually', file=sys.stderr)
                    if code != 86:
                        code = 85
            # The recovery HTTPS probe may have needed a new private scratch.
            if self.scratch:
                try:
                    physical(self.scratch, exists=True)
                    require(self.scratch.parent == self.root.parent and self.scratch.name.startswith('.schedinedinotifica-deploy.'), 'Unsafe temporary cleanup')
                    shutil.rmtree(self.scratch)
                except BaseException:
                    print('[deploy] Temporary cleanup failed; inspect private directory', file=sys.stderr)
                    code = code or 1
            try:
                if self.seal:
                    self.seal.verify()
            except (Failure,OSError):
                print('[deploy] CRITICAL ENV_INTEGRITY: final .env/APP_KEY check failed', file=sys.stderr)
                code = 86
        finally:
            if self.lock:
                self.lock.close()
        return code

def main():
    parser = argparse.ArgumentParser(description='Reviewed SPanel deployment; default is preflight only.')
    parser.add_argument('--sha',required=True)
    modes=parser.add_mutually_exclusive_group(); modes.add_argument('--check',action='store_true'); modes.add_argument('--execute',action='store_true')
    parser.add_argument('--backup-ref'); parser.add_argument('--migrate',action='store_true')
    args=parser.parse_args()
    require(sys.version_info >= (3,9), 'Python 3.9+ required')
    require(sys.platform.startswith('linux') and os.geteuid() != 0, 'Run on Linux as tanggosoftware, not root')
    import pwd
    require(pwd.getpwuid(os.getuid()).pw_name == 'tanggosoftware','Wrong Unix user')
    require(re.fullmatch('[0-9a-f]{40}',args.sha) is not None,'Full approved SHA required')
    if args.execute:
        require(args.backup_ref is not None and re.fullmatch('[A-Za-z0-9._:/-]{1,200}',args.backup_ref) is not None,'External backup reference required')
    import ipaddress
    origin=os.environ.get('DEPLOY_ORIGIN_IP','127.0.0.1'); ipaddress.ip_address(origin)
    php=shutil.which(os.environ.get('PHP_BIN','php')); composer=shutil.which(os.environ.get('COMPOSER_BIN','composer'))
    require(php and composer and all(shutil.which(c) for c in ['git','curl','node','npm']), 'Required executable missing')
    deployment=Deployment(PRODUCTION,Path(__file__).resolve().parents[2],php,composer,origin)
    def interrupted(signum, _frame):
        raise Interrupted(signum)
    for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]:
        signal.signal(sig,interrupted)
    code=0
    try:
        deployment.start()
        target,pending=deployment.prepare(args.sha)
        print('[deploy] Current SHA: {}; target: {}'.format(deployment.before,target))
        if pending:
            print('[deploy] Pending migrations: '+', '.join(pending))
        require(not pending or args.migrate,'Pending migrations require explicit --migrate')
        if args.execute:
            deployment.execute(target,pending,args.migrate,args.backup_ref)
        else:
            deployment.success=True
    except Interrupted as error:
        code=error.code; print('[deploy] Interrupted',file=sys.stderr)
    except Critical as error:
        code=error.code; print('[deploy] '+str(error),file=sys.stderr)
    except (Failure,OSError,ValueError,KeyError,TypeError) as error:
        code=1; print('[deploy] FAILED: '+str(error),file=sys.stderr)
    except BaseException:
        code=1; print('[deploy] FAILED: unexpected exception (details withheld)',file=sys.stderr)
    finally:
        for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP]:
            signal.signal(sig,signal.SIG_IGN)
        code=deployment.finish(code)
    if code == 0:
        print('[deploy] '+('SUCCESS; deployed SHA: '+args.sha if args.execute else 'Preflight passed; no deployment performed'))
    return code

if __name__ == '__main__':
    try:
        sys.exit(main())
    except (Failure,ValueError,OSError) as error:
        print('[deploy] FAILED: '+str(error),file=sys.stderr)
        sys.exit(1)
