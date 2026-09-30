#!/usr/bin/env python3
"""Process-only Rocky acceptance, isolated from other installations on this host."""
import argparse
import os
from pathlib import Path
import re
import shutil
import stat
import subprocess
import sys
import tempfile

SOURCE = Path('/home/tanggosoftware/deploy-rocky-test')
FILES = ('scripts/deployment/deploy.py', 'scripts/deployment/maintenance.php',
         'public/index.php', 'tests/Deployment/test_deploy_guards.py',
         'tests/Deployment/rocky_isolation.py')
TESTS = (
    'test_lock_exclusion_and_release',
    'test_subprocess_does_not_inherit_lock_descriptor',
    'test_signals_stop_child_release_lock_and_retain_guard',
    'test_detached_daemon_is_reaped',
    'test_signals_reap_detached_descendants_ignoring_term',
    'test_sigkill_cannot_leave_lock_inherited_by_child',
)


def physical(path, regular=False):
    """Check only the allowed clone/system inputs, never inspect production."""
    for part in reversed([path]+list(path.parents)):
        info = part.lstat()
        if stat.S_ISLNK(info.st_mode):
            raise RuntimeError('Symlink in input path; refusing to follow it')
    info = path.stat()
    if regular and (not stat.S_ISREG(info.st_mode) or info.st_nlink != 1):
        raise RuntimeError('Input must be a regular, non-hardlinked file')
    return path


def validate_source(source, cwd):
    # Lexical comparison precedes any stat/resolve: wrong cwd never probes its target.
    if source != SOURCE or cwd != SOURCE:
        raise RuntimeError('Run this reviewed script from exactly '+str(SOURCE))
    physical(source)
    if source.stat().st_uid != os.getuid() or source.stat().st_mode & 0o022:
        raise RuntimeError('Clone must be owned by this user and not writable by group/others')
    for name in FILES + ('tests/Deployment/check_rocky10.py',):
        physical(source/name,regular=True)


def validate_mounts(source, mountinfo):
    # Detect mount aliases without stat-ing or otherwise consulting production.
    def decode(value):
        return re.sub(r'\\([0-7]{3})',lambda m:chr(int(m.group(1),8)),value)
    for line in mountinfo.splitlines():
        fields = line.split()
        if len(fields) < 6:
            raise RuntimeError('Cannot validate mount topology')
        mount = Path(decode(fields[4]))
        if mount == source or source in mount.parents:
            raise RuntimeError('Mount/bind inside the clone is not permitted')
        if mount in source.parents and decode(fields[3]) != '/':
            raise RuntimeError('Clone has a bind/subtree mount ancestor; isolation not proven')


def sandbox_command(bwrap, python, run, libraries, loader_cache, namespaces):
    code = run/'code'
    command = [str(bwrap),'--unshare-all','--unshare-user','--disable-userns',
               '--die-with-parent','--new-session','--cap-drop','ALL','--clearenv',
               '--ro-bind','/usr','/usr']
    for path in libraries:
        command += ['--ro-bind',str(path),str(path)]
    command += ['--symlink','usr/bin','/bin','--symlink','usr/sbin','/sbin']
    if loader_cache:
        command += ['--ro-bind','/etc/ld.so.cache','/etc/ld.so.cache']
    # No host /, /home, /etc, /proc or production bind. Only this fresh directory is writable.
    command += ['--proc','/proc','--dev','/dev', '--bind',str(run),str(run),
                '--ro-bind',str(code),str(code), '--remount-ro','/', '--chdir',str(code)]
    env = {'PATH':'/usr/bin:/bin','HOME':str(run/'home'),'TMPDIR':str(run/'tmp'),
           'PYTHONPATH':str(code/'guard'),'PYTHONNOUSERSITE':'1','PYTHONDONTWRITEBYTECODE':'1',
           'PYTHONSAFEPATH':'1','ROCKY_SANDBOX_ROOT':str(run),'LANG':'C.UTF-8'}
    env.update({'PARENT_NS_'+key:value for key,value in namespaces.items()})
    for key,value in env.items():
        command += ['--setenv',key,value]
    runner = '''import os,sys,unittest
from pathlib import Path
import rocky_isolation
if not rocky_isolation.INSTALLED: raise SystemExit('Missing isolation guard')
for ns in ['pid','mnt','net','user']:
 if os.readlink('/proc/self/ns/'+ns)==os.environ['PARENT_NS_'+ns]:
  raise SystemExit('Required namespace was not isolated: '+ns)
sys.path.insert(0,'tests/Deployment')
import test_deploy_guards as tests
names=TEST_NAMES
suite=unittest.TestSuite(tests.ProcessTests(name) for name in names)
result=unittest.TextTestRunner(verbosity=2).run(suite)
violated=(Path(os.environ['ROCKY_SANDBOX_ROOT'])/'.isolation-violation').exists()
sys.exit(0 if result.wasSuccessful() and result.testsRun==len(names) and not result.skipped and not violated else 1)
'''.replace('TEST_NAMES',repr(TESTS))
    command += [str(python),'-B','-s','-c',runner]
    return command


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--run-process-tests',action='store_true',
                        help='Explicitly run six process tests inside the mandatory Bubblewrap sandbox')
    args = parser.parse_args(argv)  # --help exits before platform, paths or test setup.
    if not args.run_process_tests:
        parser.print_help()
        return 0
    if not sys.platform.startswith('linux') or sys.version_info < (3,9):
        raise RuntimeError('Requires Linux and Python 3.9+')
    if os.geteuid() == 0:
        raise RuntimeError('Run as an unprivileged user, never root')
    source = Path(os.path.abspath(__file__)).parents[2]
    validate_source(source,Path.cwd())
    validate_mounts(source,Path('/proc/self/mountinfo').read_text())
    release = dict(line.split('=',1) for line in Path('/etc/os-release').read_text().splitlines() if '=' in line)
    if release.get('ID','').strip('"') != 'rocky' or release.get('VERSION_ID','').strip('"').split('.')[0] != '10':
        raise RuntimeError('Requires Rocky Linux 10')
    # Only trusted system executables; never search the caller's PATH or clone.
    bwrap = Path('/usr/bin/bwrap')
    if not bwrap.is_file() or not os.access(bwrap,os.X_OK):
        raise RuntimeError('Bubblewrap /usr/bin/bwrap required; no unsandboxed fallback')
    python = Path(sys.executable).resolve(strict=True)
    if not str(python).startswith('/usr/'):
        raise RuntimeError('Requires a system Python under /usr')
    namespaces = {key:os.readlink('/proc/self/ns/'+key) for key in ['pid','mnt','net','user']}
    libraries = [Path(p) for p in ['/lib','/lib64'] if Path(p).is_dir()]
    # No exists/stat/resolve/open against the production directory anywhere in this launcher.
    with tempfile.TemporaryDirectory(prefix='.rocky-process-',dir=source) as temp:
        run = physical(Path(temp))
        (run/'tmp').mkdir(mode=0o700); (run/'home').mkdir(mode=0o700)
        for name in FILES:
            destination = run/'code'/name
            destination.parent.mkdir(parents=True,exist_ok=True)
            shutil.copyfile(source/name,destination,follow_symlinks=False)
            physical(destination,regular=True)
        guard = run/'code/guard'; guard.mkdir()
        shutil.copyfile(source/'tests/Deployment/rocky_isolation.py',guard/'rocky_isolation.py')
        (guard/'sitecustomize.py').write_text('import os\ntry:\n import rocky_isolation\n rocky_isolation.install()\nexcept BaseException:\n os._exit(97)\n')
        command = sandbox_command(bwrap,python,run,libraries,Path('/etc/ld.so.cache').is_file(),namespaces)
        # stdin is not a host terminal/file; descendants inherit only private pipes.
        process = subprocess.Popen(command,cwd=run,env={'PATH':'/usr/bin:/bin','LANG':'C.UTF-8'},
                                   stdin=subprocess.DEVNULL,stdout=subprocess.PIPE,stderr=subprocess.STDOUT,
                                   close_fds=True,start_new_session=True)
        try:
            output,_ = process.communicate(timeout=180)
            print(output.decode('utf-8',errors='replace'),end='')
            if (run/'.isolation-violation').exists():
                raise RuntimeError('ISOLATION VIOLATION: entire acceptance run rejected')
            return process.returncode
        finally:
            if process.poll() is None:
                process.terminate()
                try: process.wait(timeout=5)
                except subprocess.TimeoutExpired: process.kill(); process.wait()
            process.stdout.close()


if __name__ == '__main__':
    try:
        sys.exit(main())
    except (RuntimeError,OSError,subprocess.SubprocessError) as error:
        print('Rocky acceptance test refused: '+str(error),file=sys.stderr)
        sys.exit(1)
