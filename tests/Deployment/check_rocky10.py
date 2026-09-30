#!/usr/bin/env python3
"""Isolated Rocky 10 acceptance test. Run in a disposable VM/container, NOT SPanel.
No deployment entry point is called. No Git network, PHP, database or npm required.
"""
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile


def main():
    if not sys.platform.startswith('linux') or sys.version_info < (3, 9):
        raise RuntimeError('Requires Linux and Python 3.9+')
    release = {}
    for line in Path('/etc/os-release').read_text().splitlines():
        if '=' in line:
            key, value = line.split('=', 1)
            release[key] = value.strip('"')
    if release.get('ID') != 'rocky' or release.get('VERSION_ID', '').split('.')[0] != '10':
        raise RuntimeError('Requires a disposable Rocky Linux 10 environment')
    if os.geteuid() == 0:
        raise RuntimeError('Run as an unprivileged test user, never root')
    if Path('/home/tanggosoftware/repos/schedinedinotifica').exists():
        raise RuntimeError('Refusing a host with the production installation path')
    source = Path(__file__).resolve().parents[2]
    # Copy only reviewed code/test fixtures. Never copy .env, vendor or application data.
    allowed = ['scripts/deployment/deploy.py', 'scripts/deployment/maintenance.php',
               'public/index.php', 'tests/Deployment/test_deploy_guards.py']
    with tempfile.TemporaryDirectory(prefix='rocky10-deploy-acceptance-') as temp:
        isolated = Path(temp).resolve()
        for name in allowed:
            destination = isolated/name
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(source/name, destination)
        runner = '''import sys,unittest
sys.path.insert(0,'tests/Deployment')
import test_deploy_guards as tests
suite=unittest.TestSuite()
for cls in [tests.ProcessTests,tests.EnvironmentTests]:
 suite.addTests(unittest.defaultTestLoader.loadTestsFromTestCase(cls))
result=unittest.TextTestRunner(verbosity=2).run(suite)
if result.skipped:
 print('FAIL: acceptance requires zero skipped tests',file=sys.stderr)
sys.exit(0 if result.wasSuccessful() and not result.skipped else 1)
'''
        clean_env = {'PATH':os.environ.get('PATH','/usr/bin:/bin'), 'HOME':str(isolated),
                     'TMPDIR':str(isolated), 'PYTHONDONTWRITEBYTECODE':'1', 'LANG':'C.UTF-8'}
        result = subprocess.run([sys.executable,'-B','-c',runner],cwd=isolated,env=clean_env)
        return result.returncode


if __name__ == '__main__':
    try:
        sys.exit(main())
    except (RuntimeError,OSError) as error:
        print('Rocky acceptance test refused: '+str(error),file=sys.stderr)
        sys.exit(1)
