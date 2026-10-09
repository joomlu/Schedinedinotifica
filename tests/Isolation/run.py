#!/usr/bin/env python3
"""Avviatore di test: risorse proprie, nessuna configurazione o dato di sviluppo."""
import argparse
import base64
import json
from http.client import HTTPConnection, IncompleteRead
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import urlsplit
import os
from pathlib import Path
import secrets
import signal
import sys
import select
import shutil
import socket
import subprocess
import tempfile
import threading
from database_policy import vendor, validate, CANDIDATE
import time

ROOT = Path(__file__).resolve().parents[2]


def port():
    with socket.socket() as s:
        s.bind(('127.0.0.1', 0))
        chosen = s.getsockname()[1]
        return chosen if chosen >= 49152 else port()


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--phpunit', nargs='*', default=[])
    parser.add_argument('--phpunit-global', action='store_true', help='Suite completa definita da phpunit.xml')
    parser.add_argument('--phpunit-repeat', type=int, choices=range(1, 4), default=1, help='Esecuzioni sullo stesso runtime, DB reinizializzato fra prove')
    parser.add_argument('--phpunit-discovery', action='store_true', help='Elenco dei test raccolti, senza eseguirli')
    parser.add_argument('--phpunit-random-seed', type=int, help='Ordine casuale riproducibile')
    parser.add_argument('--playwright', nargs='*')
    parser.add_argument('--fixtures', help='Script PHP di fixture esplicitamente selezionato sotto tests/')
    parser.add_argument('--include', nargs='*', default=[], help='File sorgente nuovi esplicitamente inclusi, senza staging')
    args = parser.parse_args()
    if args.phpunit_global and args.phpunit:
        parser.error('Selezionare la suite globale oppure file PHPUnit espliciti')
    if (args.phpunit_repeat != 1 or args.phpunit_discovery or args.phpunit_random_seed is not None) and not (args.phpunit_global or args.phpunit):
        parser.error('Le opzioni PHPUnit richiedono una suite')
    for name in args.include:
        path = Path(name)
        if path.is_absolute() or '..' in path.parts or not name.startswith(('app/', 'config/', 'database/migrations/', 'reference/questura/')) or not (ROOT/path).is_file() or (ROOT/path).is_symlink() or not (ROOT/path).resolve().is_relative_to(ROOT):
            parser.error('Inclusione limitata a sorgenti applicativi, configurazioni, migration e riferimenti Questura')
    if args.playwright == []:
        parser.error('--playwright richiede almeno uno spec esplicito')
    selected = args.phpunit + (args.playwright or []) + ([args.fixtures] if args.fixtures else [])
    if args.phpunit_global:
        selected += [str(p.relative_to(ROOT)) for folder in ('tests/Unit', 'tests/Feature') for p in sorted((ROOT/folder).glob('*Test.php'))]
    for name in selected:
        path = Path(name)
        if path.is_absolute() or '..' in path.parts or not name.startswith('tests/') or not (ROOT/path).is_file():
            parser.error('File di test relativo e sotto tests/ richiesto: '+name)
    for key in os.environ:
        if key.startswith(('DB_', 'DATABASE_', 'APP_', 'LARAVEL_', 'VIEW_', 'SESSION_', 'REDIS_', 'MAIL_', 'AWS_', 'TEST_ISOLATION_', 'ISOLATED_')) or key in ('BASE_URL', 'PLAYWRIGHT_BASE_URL', 'REUSE_EXISTING_SERVER'):
            raise SystemExit('TEST_ISOLATION_REQUIRED: override ereditato rifiutato: '+key)
    mysqld = shutil.which('mysqld')
    mysql = shutil.which('mysql')
    if not mysqld or not mysql:
        raise SystemExit('Servono i binari mysqld e mysql; nessun server esistente viene riutilizzato.')
    sha = subprocess.check_output(['git', 'rev-parse', 'HEAD'], cwd=ROOT, text=True).strip()
    if sha != CANDIDATE:
        raise SystemExit('SHA candidato errato: BLOCK')
    engine = vendor(subprocess.check_output([mysqld, '--version'], text=True))
    dbport, httpport, proxyport, trapport = port(), port(), port(), port()
    server_id = secrets.randbelow(2**32-1)+1
    mysqld, mysql = str(Path(mysqld).resolve()), str(Path(mysql).resolve())
    run = Path(tempfile.mkdtemp(prefix='schedine-test-', dir='/private/tmp' if sys.platform == 'darwin' else '/tmp')).resolve()
    os.chmod(run, 0o700)
    checkout = run/'checkout'
    datadir = run/'mysql-data'
    identity = secrets.token_hex(16)
    password = secrets.token_hex(32)
    dbname = 'test_geo_'+identity
    processes = []
    files = []
    broker = None
    proxy = None
    trap = None
    signal.signal(signal.SIGTERM, lambda *_: (_ for _ in ()).throw(KeyboardInterrupt()))
    exitcode = 1
    env = {'PATH': os.environ['PATH'], 'HOME': str(run/'home'), 'TMPDIR': str(run/'tmp'),
           'LANG': 'en_US.UTF-8', 'APP_DEBUG': 'false', 'ISTAT_WS_ENABLED': 'false', 'QUESTURA_WS_ENABLED': 'false', 'APP_ENV': 'testing', 'APP_URL': f'http://127.0.0.1:{httpport}',
           'APP_KEY': 'base64:'+base64.b64encode(secrets.token_bytes(32)).decode(),
           'DB_CONNECTION': 'mysql', 'DB_HOST': '127.0.0.1', 'DB_PORT': str(dbport),
           'DB_DATABASE': dbname, 'DB_USERNAME': 'fixture', 'DB_PASSWORD': password,
           'CACHE_DRIVER': 'array', 'CACHE_STORE': 'array', 'SESSION_DRIVER': 'file',
           'MAIL_MAILER': 'array', 'QUEUE_CONNECTION': 'sync', 'LOG_CHANNEL': 'single',
           'ISOLATED_BROKER': str(run/'broker.sock'), 'ISOLATED_TOKEN': secrets.token_hex(32),
           'ISOLATED_ROOT': str(run), 'ISOLATED_RUN_ID': identity,
           'BCRYPT_ROUNDS': '4', 'SESSION_SECURE_COOKIE': 'false'}
    browsers = Path.home()/'Library/Caches/ms-playwright'
    if browsers.is_dir():
        env['PLAYWRIGHT_BROWSERS_PATH'] = str(browsers)
    try:
        for folder in ('home', 'tmp', 'mysql-data'):
            (run/folder).mkdir()
        checkout.mkdir()
        # Solo file versionati e i file di test di questa funzione, nessun untracked estraneo.
        tracked = subprocess.check_output(['git', 'ls-files', '-z'], cwd=ROOT).decode().split('\0')
        extra = [str(p.relative_to(ROOT)) for folder in ('tests/Isolation', 'tests/Support') for p in (ROOT/folder).glob('*') if p.is_file() and p.suffix in ('.py', '.php', '.js')]
        extra += selected + args.include
        for name in set(tracked+extra):
            if not name or name.startswith(('tests/Feature/real-data', 'public/uploads/', 'storage/', 'bootstrap/cache/', 'public/storage', 'public/images/', '.env', 'database/seeders/')):
                continue
            source = ROOT/name
            if not source.is_file() or source.is_symlink():
                continue
            target = checkout/name
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(source, target)
        shutil.copytree(ROOT/'vendor', checkout/'vendor', symlinks=False)
        shutil.copytree(ROOT/'node_modules', checkout/'node_modules', symlinks=True)
        for link in (checkout/'node_modules').rglob('*'):
            if link.is_symlink() and not link.resolve().is_relative_to(checkout/'node_modules'):
                raise RuntimeError('Link dipendenza esterno rifiutato')
        for folder in ('storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache', 'public/images'):
            (checkout/folder).mkdir(parents=True, exist_ok=True)
        (checkout/'public/storage').symlink_to(checkout/'storage/app/public', target_is_directory=True)
        log = open(run/'mysql.log', 'w'); files.append(log)
        if engine == 'mysql':
            init = [mysqld, '--no-defaults', '--initialize-insecure', f'--datadir={datadir}', f'--log-error={run}/mysql-error.log']
        else:
            installer = shutil.which('mariadb-install-db')
            if not installer:
                raise RuntimeError('Inizializzatore MariaDB assente: BLOCK')
            init = [installer, '--no-defaults', f'--datadir={datadir}', '--auth-root-authentication-method=normal', '--skip-test-db']
        subprocess.run(init, env=env, stdout=log, stderr=log, check=True)
        db = subprocess.Popen([mysqld, '--no-defaults', f'--datadir={datadir}', '--bind-address=127.0.0.1', f'--port={dbport}', f'--socket={run}/mysql.sock', f'--pid-file={run}/mysql.pid', f'--log-error={run}/mysql-error.log', *(['--mysqlx=0'] if engine == 'mysql' else []), f'--server-id={server_id}', '--skip-log-bin', '--local-infile=0', f'--secure-file-priv={run}/tmp'], env=env, stdout=log, stderr=log)
        processes.append(db)
        admin = [mysql, '--no-defaults', '--protocol=TCP', '--host=127.0.0.1', f'--port={dbport}', '--user=root', '--batch', '--skip-column-names']
        for _ in range(100):
            if db.poll() is not None:
                raise RuntimeError('Istanza temporanea terminata; vedere mysql-error.log')
            ready = subprocess.run(admin+['-e', 'SELECT 1'], env=env, capture_output=True)
            if ready.returncode == 0:
                break
            time.sleep(.2)
        else:
            raise RuntimeError('Istanza temporanea non pronta')
        if db.poll() is not None or int((run/'mysql.pid').read_text().strip()) != db.pid:
            raise RuntimeError('Processo DB non attestato prima del DDL: BLOCK')
        expected = {'root': str(run), 'sha': sha, 'vendor': engine, 'database': dbname,
                    'datadir': str(datadir), 'port': dbport, 'socket': str(run/'mysql.sock'),
                    'server_id': server_id, 'username': 'fixture', 'app_env': 'testing',
                    'pid': db.pid, 'launcher_pid': os.getpid(), 'uid': os.getuid(), 'executable': mysqld,
                    'uuid': (datadir/'auto.cnf').read_text().split('server-uuid=')[1].strip() if engine == 'mysql' else None}
        query = "SELECT @@datadir, @@port, @@socket, @@server_id, CONCAT(VERSION(), ' ', @@version_comment)"+(', @@server_uuid' if engine == 'mysql' else '')
        row = subprocess.check_output(admin+['-e', query], env=env, text=True).strip().split('\t')
        observed = dict(zip(('datadir', 'port', 'socket', 'server_id', 'version', 'uuid'), row))
        observed.update({'vendor': vendor(observed['version']), 'database': dbname})
        validate(expected, observed)  # Istanza propria attestata prima di CREATE DATABASE/USER.
        subprocess.run(admin, input=f"CREATE DATABASE `{dbname}`; CREATE USER 'fixture'@'127.0.0.1' IDENTIFIED BY '{password}'; GRANT ALL ON `{dbname}`.* TO 'fixture'@'127.0.0.1'; ALTER USER 'root'@'localhost' IDENTIFIED BY '{secrets.token_hex(32)}';", text=True, env=env, check=True, stdout=log, stderr=log)
        uuid = expected['uuid']
        info = {'root': str(run), 'checkout': str(checkout), 'datadir': str(datadir), 'db_pid': db.pid,
                'http_pid': None, 'db_port': dbport, 'http_port': httpport, 'proxy_port': proxyport, 'database': dbname,
                'username': 'fixture', 'password': password, 'uuid': uuid, 'identity': identity,
                'vendor': engine, 'server_id': server_id, 'db_binary': Path(mysqld).name, 'db_executable': mysqld, 'db_uid': os.getuid(), 'sha': sha,
                'environment': env, 'launcher_pid': os.getpid(), 'trap_port': trapport, 'trap_hits': 0, 'denied_requests': 0}
        class ForbiddenOrigin(BaseHTTPRequestHandler):
            def log_message(self, *unused):
                pass

            def do_GET(self):
                info['trap_hits'] += 1
                self.send_response(200); self.end_headers(); self.wfile.write(b'ORIGINE VIETATA')

        trap = ThreadingHTTPServer(('127.0.0.1', trapport), ForbiddenOrigin)
        threading.Thread(target=trap.serve_forever, daemon=True).start()
        class OriginProxy(BaseHTTPRequestHandler):
            def log_message(self, *unused):
                pass

            def do_CONNECT(self):
                # APIRequestContext usa un tunnel anche per HTTP. Il tunnel può
                # raggiungere SOLO l'endpoint attestato, mai altre porte/origini.
                if self.path != f'127.0.0.1:{httpport}':
                    info['denied_requests'] += 1
                    self.send_error(403, 'TEST_ISOLATION_REQUIRED')
                    return
                with socket.create_connection(('127.0.0.1', httpport), timeout=5) as upstream:
                    self.send_response(200, 'Connection Established'); self.end_headers(); self.wfile.flush()
                    sockets = [self.connection, upstream]
                    while True:
                        ready, _, _ = select.select(sockets, [], [], 10)
                        if not ready:
                            return
                        for source in ready:
                            data = source.recv(65536)
                            if not data:
                                return
                            (upstream if source is self.connection else self.connection).sendall(data)

            def forward(self):
                target = urlsplit(self.path)
                if target.scheme != 'http' or target.netloc != f'127.0.0.1:{httpport}' or target.username or target.password:
                    info['denied_requests'] += 1
                    self.send_error(403, 'TEST_ISOLATION_REQUIRED')
                    return
                length = int(self.headers.get('Content-Length', 0))
                body = self.rfile.read(length) if length else None
                headers = {k: v for k, v in self.headers.items() if k.lower() not in ('proxy-connection', 'connection', 'host')}
                upstream = HTTPConnection('127.0.0.1', httpport, timeout=15)
                try:
                    upstream.request(self.command, target.path+('?' + target.query if target.query else ''), body=body, headers=headers)
                    response = upstream.getresponse()
                    content = response.read()
                    self.send_response(response.status)
                    for key, value in response.getheaders():
                        if key.lower() not in ('connection', 'transfer-encoding', 'content-length'):
                            self.send_header(key, value)
                    self.send_header('Content-Length', str(len(content)))
                    self.end_headers()
                    if self.command != 'HEAD':
                        self.wfile.write(content)
                except IncompleteRead as error:
                    # Non inviare un successo con un corpo incompleto; nessun URL,
                    # token o contenuto della risposta viene registrato.
                    print(f'PROXY_CORPO_INCOMPLETO ricevuti={len(error.partial)} mancanti={error.expected}', file=sys.stderr, flush=True)
                    self.send_error(502, 'ISOLATED_UPSTREAM_INCOMPLETE')
                except (BrokenPipeError, ConnectionResetError):
                    pass  # Il browser può annullare asset durante una navigazione.
                finally:
                    upstream.close()

            do_GET = do_POST = do_PUT = do_PATCH = do_DELETE = do_HEAD = forward

        proxy = ThreadingHTTPServer(('127.0.0.1', proxyport), OriginProxy)
        threading.Thread(target=proxy.serve_forever, daemon=True).start()
        broker = socket.socket(socket.AF_UNIX)
        broker.bind(env['ISOLATED_BROKER']); os.chmod(env['ISOLATED_BROKER'], 0o600); broker.listen()

        def attest():
            while True:
                try:
                    client, _ = broker.accept()
                    with client:
                        client.settimeout(3)
                        request = json.loads(client.recv(4096))
                        if not secrets.compare_digest(request.get('token', ''), env['ISOLATED_TOKEN']):
                            continue
                        if db.poll() is not None:
                            continue
                        result = dict(info)
                        result['challenge'] = request['challenge']
                        client.sendall((json.dumps(result)+'\n').encode())
                except (OSError, ValueError, KeyError):
                    if broker.fileno() == -1:
                        return
        threading.Thread(target=attest, daemon=True).start()
        http_log = open(run/'http.log', 'w'); files.append(http_log)
        http = subprocess.Popen(['php', '-S', f'127.0.0.1:{httpport}', '-t', 'public', 'tests/Isolation/router.php'], cwd=checkout, env=env, stdout=http_log, stderr=http_log)
        processes.append(http); info['http_pid'] = http.pid
        for _ in range(100):
            if http.poll() is not None:
                raise RuntimeError('Server temporaneo terminato')
            try:
                with socket.create_connection(('127.0.0.1', httpport), timeout=.1):
                    break
            except OSError:
                time.sleep(.1)
        print(f'TEST isolato: {run}; database={dbname}; MySQL pid={db.pid}, porta={dbport}; HTTP pid={http.pid}, porta={httpport}', flush=True)
        # Prove negative e identificazione reale PRIMA delle migrazioni.
        def step(command, label):
            result = subprocess.run(command, cwd=checkout, env=env, capture_output=True, text=True)
            output = result.stdout + result.stderr
            (run/(label+'.log')).write_text(output)
            if label == 'build' and result.returncode == 0:
                print('PASS: build asset e verifica GEO immutabile nel checkout temporaneo.', flush=True)
            else:
                lines = [line for line in output.splitlines() if not line.startswith(('/private/tmp/', '#'))]
                print('\n'.join(lines)[:10000], flush=True)
            if result.returncode:
                if label == 'playwright':
                    for context in (checkout/'test-results').glob('*/error-context.md'):
                        print(context.parent.name+':\n'+context.read_text()[:4000], flush=True)
                raise RuntimeError(label+' fallito: '+str(result.returncode))

        subprocess.run(['php', 'tests/Isolation/verify.php'], cwd=checkout, env=env, check=True)
        step(['npm', 'run', 'build'], 'build')
        subprocess.run(['php', 'tests/Isolation/console.php', 'migrate', '--force', '--no-interaction', '--quiet'], cwd=checkout, env=env, check=True)
        if args.phpunit or args.phpunit_global:
            command = ['php', 'vendor/bin/phpunit', *args.phpunit, '--display-deprecations', '--display-phpunit-deprecations']
            if args.phpunit_discovery:
                step(command+['--list-tests'], 'phpunit-discovery')
            else:
                if args.phpunit_random_seed is not None:
                    command += ['--order-by=random', '--random-order-seed='+str(args.phpunit_random_seed)]
                for iteration in range(1, args.phpunit_repeat+1):
                    if iteration > 1:
                        # Stesso endpoint attestato, mai un DB esterno. Storage
                        # dei singoli casi gestito da TestCase, non cancellato qui.
                        subprocess.run(['php', 'tests/Isolation/console.php', 'migrate:fresh', '--force', '--no-interaction', '--quiet'], cwd=checkout, env=env, check=True)
                    step(command+['--log-junit', 'phpunit-'+str(iteration)+'.xml'], 'phpunit-'+str(iteration))
        if args.fixtures:
            subprocess.run(['php', args.fixtures], cwd=checkout, env=env, check=True)
        if args.playwright is not None:
            step(['node', 'node_modules/@playwright/test/cli.js', 'test', *args.playwright], 'playwright')
        exitcode = 0
    except (subprocess.CalledProcessError, RuntimeError) as error:
        print('TEST interrotto senza fallback: '+str(error), flush=True)
        for name in ('mysql-error.log', 'http.log'):
            p = run/name
            if p.exists():
                print(name+':\n'+p.read_text()[-4000:])
    finally:
        for process in reversed(processes):
            if process.poll() is None:
                process.terminate()
                try:
                    process.wait(timeout=15)
                except subprocess.TimeoutExpired:
                    process.kill(); process.wait()
        if broker:
            broker.close()
        if proxy:
            proxy.shutdown(); proxy.server_close()
        if trap:
            trap.shutdown(); trap.server_close()
        for handle in files:
            handle.close()
        # Il percorso deriva solo da mkdtemp e non da argomenti o manifest esterni.
        shutil.rmtree(run)
        print('Risorse temporanee fermate e rimosse: '+str(run), flush=True)
    return exitcode


if __name__ == '__main__':
    raise SystemExit(main())
