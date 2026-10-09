"""Attestazione fail-closed prima del DDL; nessuna connessione DB."""
import os
from pathlib import Path
import re
import shlex
import subprocess

CANDIDATE = 'faef3cf01f43d19b20d1558c1fbd3ae32e48d849'
PRODUCTION_DATABASE = 'tanggosoftware_schedinedinotifica'
PRODUCTION_PORT = 3306
PRODUCTION_SOCKET = '/var/lib/mysql/mysql.sock'


def vendor(version):
    maria = bool(re.search(r'\bMariaDB\b', version, re.I))
    mysql = bool(re.search(r'\bMySQL\b', version, re.I))
    if maria == mysql:
        raise RuntimeError('Vendor ambiguo/sconosciuto: BLOCK')
    return 'mariadb' if maria else 'mysql'


def physical(value):
    p = Path(value)
    if not p.is_absolute() or '..' in p.parts or str(p) != value:
        raise RuntimeError('Path non canonico: BLOCK')
    for part in [p, *p.parents]:
        if part.is_symlink():
            raise RuntimeError('Symlink non autorizzato: BLOCK')
    if str(p.resolve()) != value:
        raise RuntimeError('Path risolto differente: BLOCK')
    return p


def process_snapshot(pid):
    try:
        row = subprocess.check_output(['/bin/ps', '-p', str(pid), '-o', 'ppid=', '-o', 'uid=', '-o', 'command='], text=True, stderr=subprocess.DEVNULL).strip().split(None, 2)
    except (OSError, subprocess.CalledProcessError) as error:
        raise RuntimeError('Processo non verificabile: BLOCK') from error
    if len(row) != 3:
        raise RuntimeError('Processo assente: BLOCK')
    argv = shlex.split(row[2])
    executable = Path('/proc')/str(pid)/'exe'
    binary = os.readlink(executable) if executable.exists() else str(Path(argv[0]).resolve())
    return dict(ppid=int(row[0]), uid=int(row[1]), executable=binary, argv=argv)


def validate(expected, observed):
    required = {'vendor', 'database', 'datadir', 'port', 'socket', 'server_id', 'username', 'app_env',
                'root', 'sha', 'pid', 'launcher_pid', 'uid', 'executable'}
    if not required <= expected.keys() or any(expected[k] is None or expected[k] == '' for k in required):
        raise RuntimeError('Configurazione incompleta: BLOCK')
    if expected['app_env'] != 'testing' or expected['sha'] != CANDIDATE:
        raise RuntimeError('Ambiente/SHA non autorizzato: BLOCK')
    if expected['database'] == PRODUCTION_DATABASE or not re.fullmatch(r'test_geo_[a-f0-9]{32}', expected['database']):
        raise RuntimeError('Database non autorizzato: BLOCK')
    if expected['vendor'] not in ('mysql', 'mariadb'):
        raise RuntimeError('Vendor sconosciuto: BLOCK')
    for side in (expected, observed):
        port = side.get('port')
        server_id = side.get('server_id')
        if isinstance(port, bool) or not str(port).isdigit() or not 49152 <= int(port) <= 65535 or int(port) in {PRODUCTION_PORT, expected.get('production_port', PRODUCTION_PORT)}:
            raise RuntimeError('Porta non autorizzata: BLOCK')
        if isinstance(server_id, bool) or not str(server_id).isdigit() or not 1 <= int(server_id) <= 4294967295:
            raise RuntimeError('server_id non autorizzato: BLOCK')
        if side.get('socket') in {PRODUCTION_SOCKET, expected.get('production_socket', PRODUCTION_SOCKET)}:
            raise RuntimeError('Socket operativo: BLOCK')
    root = physical(expected['root'])
    if root.parent != Path('/private/tmp' if os.uname().sysname == 'Darwin' else '/tmp') or not root.name.startswith('schedine-test-'):
        raise RuntimeError('Root temporanea non autorizzata: BLOCK')
    if not root.is_dir() or root.stat().st_uid != os.getuid() or root.stat().st_mode & 0o077:
        raise RuntimeError('Root privata non attestata: BLOCK')
    if physical(expected['datadir']) != root/'mysql-data' or physical(expected['socket']) != root/'mysql.sock' or expected['username'] != 'fixture':
        raise RuntimeError('Risorse non isolate: BLOCK')
    for key in ('pid', 'launcher_pid'):
        if type(expected[key]) is not int or expected[key] <= 0:
            raise RuntimeError('PID non valido: BLOCK')
    if expected['launcher_pid'] != os.getpid() or expected['uid'] != os.getuid():
        raise RuntimeError('Supervisore non attestato: BLOCK')
    binary = physical(expected['executable'])
    if binary.name not in ('mysqld', 'mariadbd'):
        raise RuntimeError('Binario DB non pertinente: BLOCK')
    proc = process_snapshot(expected['pid'])
    markers = ['--no-defaults', '--datadir='+expected['datadir'], '--socket='+expected['socket'],
               '--port='+str(expected['port']), '--server-id='+str(expected['server_id']), '--bind-address=127.0.0.1']
    if proc['ppid'] != expected['launcher_pid'] or proc['uid'] != expected['uid'] or proc['executable'] != str(binary) or not all(x in proc['argv'] for x in markers):
        raise RuntimeError('Processo non attestato: BLOCK')
    for key in ('vendor', 'database', 'datadir', 'port', 'socket', 'server_id'):
        if str(observed.get(key, '')).rstrip('/') != str(expected[key]).rstrip('/'):
            raise RuntimeError('Identità istanza errata: BLOCK')
    if expected['vendor'] == 'mysql' and (not expected.get('uuid') or observed.get('uuid') != expected['uuid']):
        raise RuntimeError('UUID MySQL errato: BLOCK')
    return True
