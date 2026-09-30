"""Fail-fast Python guards. Bubblewrap is the security boundary, not these hooks."""
import os
import sys

DENIED = '/home/tanggosoftware/repos'
INSTALLED = False


class CallableGuard:
    # pathlib in Python 3.9 stores os functions as class attributes. A plain
    # Python function would become a bound method; a callable object does not.
    def __init__(self, callback):
        self.callback = callback

    def __call__(self, *args, **kwargs):
        return self.callback(*args, **kwargs)


def contained(path, root):
    return path == root or path.startswith(root + os.sep)


def install():
    global INSTALLED
    if INSTALLED:
        return
    root = os.environ['ROCKY_SANDBOX_ROOT']
    marker = os.path.join(root, '.isolation-violation')
    original_open, original_write = os.open, os.write
    original_readlink, original_cwd = os.readlink, os.getcwd
    busy = False

    def abort():
        try:
            fd = original_open(marker, os.O_WRONLY | os.O_CREAT | os.O_APPEND | os.O_NOFOLLOW, 0o600)
            original_write(fd, b'ISOLATION VIOLATION\n')
            os.close(fd)
        finally:
            original_write(2, b'CRITICAL: isolated test attempted a forbidden operation\n')
            os._exit(98)  # Cannot be swallowed by unittest or a project exception handler.

    def check(path, writing=False, dir_fd=None):
        nonlocal busy
        if busy or path is None or isinstance(path, int):
            return
        busy = True
        try:
            path = os.fsdecode(path)
            base = original_cwd() if dir_fd is None else original_readlink('/proc/self/fd/'+str(dir_fd))
            absolute = os.path.normpath(path if os.path.isabs(path) else os.path.join(base,path))
            if contained(absolute, DENIED):
                abort()
            resolved = os.path.realpath(absolute)
            if contained(resolved, DENIED):
                abort()
            if writing and resolved != '/dev/null' and not contained(resolved,root):
                abort()
        finally:
            busy = False

    def audit(event, args):
        if busy:
            return
        if event == 'open':
            mode, flags = args[1], args[2]
            writing = (isinstance(mode,str) and any(c in mode for c in 'wax+')) or (
                isinstance(flags,int) and bool(flags & (os.O_WRONLY | os.O_RDWR | os.O_CREAT | os.O_TRUNC)))
            check(args[0],writing)
        elif event in ('os.listdir','os.scandir','os.chdir'):
            check(args[0])
        elif event in ('os.remove','os.rmdir','os.mkdir','os.chmod','os.chown','os.utime','os.truncate'):
            check(args[0],True)
        elif event in ('os.rename','os.link'):
            check(args[0],True); check(args[1],True)
        elif event == 'os.symlink':
            abort()  # Process-only fixtures never need links.
        elif event == 'subprocess.Popen':
            executable, argv, cwd, env = args
            if os.path.realpath(executable) != os.path.realpath(sys.executable):
                abort()
            if '-c' not in argv or any(a not in ('-B','-s') for a in argv[1:argv.index('-c')]):
                abort()
            check(cwd)
            if env is not None and any(env.get(k) != os.environ.get(k) for k in (
                'PYTHONPATH','PYTHONNOUSERSITE','PYTHONDONTWRITEBYTECODE','ROCKY_SANDBOX_ROOT','TMPDIR')):
                abort()
        elif event in ('os.system','os.exec','os.posix_spawn') or event.startswith('socket.'):
            abort()

    # CPython does not audit stat/access/readlink. Guard them explicitly too.
    def wrap(name, writing=False):
        original = getattr(os,name)
        def guarded(path, *args, **kwargs):
            check(path,writing,kwargs.get('dir_fd'))
            return original(path,*args,**kwargs)
        setattr(os,name,CallableGuard(guarded))
    for name in ['stat','lstat','access','readlink']:
        wrap(name)
    # os.open's audit event does not include dir_fd; inspect it before the call.
    saved_open = os.open
    def guarded_open(path, flags, mode=0o777, *, dir_fd=None):
        check(path,bool(flags & (os.O_WRONLY | os.O_RDWR | os.O_CREAT | os.O_TRUNC)),dir_fd)
        return saved_open(path,flags,mode,dir_fd=dir_fd)
    os.open = CallableGuard(guarded_open)
    sys.addaudithook(audit)
    INSTALLED = True
