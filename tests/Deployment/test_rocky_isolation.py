"""Local policy tests; never query the real production path or execute Bubblewrap."""
import importlib.util
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile
import unittest
from unittest import mock

HERE = Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('rocky_launcher',HERE/'check_rocky10.py')
launcher = importlib.util.module_from_spec(spec); spec.loader.exec_module(launcher)
PROTECTED = '/home/tanggosoftware/repos/schedinedinotifica'


class RockyIsolationTests(unittest.TestCase):
    def setUp(self):
        temp = tempfile.TemporaryDirectory(prefix='rocky-policy-')
        self.addCleanup(temp.cleanup)
        self.root = Path(temp.name).resolve()
        guard = self.root/'guard';guard.mkdir()
        shutil.copyfile(HERE/'rocky_isolation.py',guard/'rocky_isolation.py')
        (guard/'sitecustomize.py').write_text('import os\ntry:\n import rocky_isolation\n rocky_isolation.install()\nexcept BaseException:\n os._exit(97)\n')
        self.env = dict(os.environ,PYTHONPATH=str(guard),PYTHONNOUSERSITE='1',PYTHONDONTWRITEBYTECODE='1',
                        ROCKY_SANDBOX_ROOT=str(self.root),TMPDIR=str(self.root))

    def guarded(self, code):
        return subprocess.run([sys.executable,'-B','-s','-c',code],cwd=self.root,env=self.env,
                              capture_output=True,timeout=20)

    def denied(self, code):
        result = self.guarded(code)
        self.assertEqual(result.returncode,98,result.stderr)
        self.assertTrue((self.root/'.isolation-violation').exists())

    def test_help_has_no_platform_or_filesystem_checks(self):
        with mock.patch.object(launcher,'validate_source',side_effect=AssertionError('unexpected check')):
            with self.assertRaises(SystemExit) as exit:
                launcher.main(['--help'])
            self.assertEqual(exit.exception.code,0)

    def test_default_does_not_execute_tests(self):
        with mock.patch.object(launcher.subprocess,'Popen',side_effect=AssertionError('unexpected execution')):
            self.assertEqual(launcher.main([]),0)

    def test_wrong_clone_rejected_before_stat(self):
        with mock.patch.object(Path,'lstat',side_effect=AssertionError('must not stat wrong paths')):
            for path in [Path(PROTECTED),Path('/tmp/clone')]:
                with self.assertRaises(RuntimeError): launcher.validate_source(path,path)

    def test_clone_symlink_rejected(self):
        alias=self.root/'alias';alias.symlink_to(self.root/'guard')
        with self.assertRaises(RuntimeError): launcher.physical(alias)

    def test_mount_aliases_rejected_without_probing_production(self):
        for mount,subtree in [(str(launcher.SOURCE),'/some/subtree'),
                              (str(launcher.SOURCE/'scripts'),'/'),
                              ('/home/tanggosoftware','/some/subtree')]:
            with self.subTest(mount=mount):
                with self.assertRaises(RuntimeError):
                    launcher.validate_mounts(launcher.SOURCE,'1 0 0:1 '+subtree+' '+mount+' rw - ext4 /dev/test rw')
        launcher.validate_mounts(launcher.SOURCE,'1 0 0:1 / / rw - ext4 /dev/test rw')

    def test_input_hardlink_rejected(self):
        path=self.root/'data';path.write_text('fixture')
        os.link(path,self.root/'alias')
        with self.assertRaises(RuntimeError): launcher.physical(path,regular=True)

    def test_mounts_expose_only_system_and_private_run(self):
        run=launcher.SOURCE/'.rocky-process-fixture'
        command=launcher.sandbox_command('/usr/bin/bwrap','/usr/bin/python3.12',run,[Path('/lib64')],True,
                                        {k:'parent' for k in ['pid','mnt','net','user']})
        mounts=[]
        for i,arg in enumerate(command):
            if arg in ['--bind','--ro-bind']:mounts.append((arg,command[i+1],command[i+2]))
        self.assertEqual([x for x in mounts if x[0]=='--bind'],[('--bind',str(run),str(run))])
        self.assertEqual({x[1] for x in mounts if x[0]=='--ro-bind'},
                         {'/usr','/lib64','/etc/ld.so.cache',str(run/'code')})
        for flag in ['--unshare-all','--unshare-user','--disable-userns','--die-with-parent',
                     '--new-session','--cap-drop','--clearenv','--remount-ro']:
            self.assertIn(flag,command)
        self.assertNotIn(PROTECTED,command)
        self.assertNotIn('--share-net',command)
        compile(command[-1],'<sandbox-runner>','exec')

    def test_exactly_six_process_tests_selected(self):
        self.assertEqual(len(launcher.TESTS),6)
        self.assertTrue(all(name.startswith('test_') for name in launcher.TESTS))
        command=launcher.sandbox_command('bwrap','python',self.root,[],False,{})
        self.assertIn('tests.ProcessTests(name)',command[-1])
        self.assertNotIn('tests.EnvironmentTests',command[-1])
        self.assertNotIn('discover(',command[-1])

    def test_allowed_temporary_io(self):
        result=self.guarded("from pathlib import Path;Path('ok').write_text('fixture');assert Path('ok').read_text()=='fixture'")
        self.assertEqual(result.returncode,0,result.stderr)
        self.assertFalse((self.root/'.isolation-violation').exists())

    def test_production_read_aborts_before_open(self):
        self.denied('open('+repr(PROTECTED+'/.env')+')')

    def test_production_write_aborts(self):
        self.denied('open('+repr(PROTECTED+'/test')+",'w')")

    def test_production_metadata_aborts(self):
        for operation in ['stat','lstat','access','readlink','listdir','scandir']:
            with self.subTest(operation=operation):
                extra=',0' if operation=='access' else ''
                self.denied('import os;os.'+operation+'('+repr(PROTECTED)+extra+')')

    def test_production_rename_and_delete_abort(self):
        for code in ['import os;os.rename('+repr(PROTECTED)+",'moved')",
                     'import os;os.unlink('+repr(PROTECTED+'/.env')+')']:
            with self.subTest(code=code):self.denied(code)

    def test_relative_escape_to_production_aborts(self):
        relative=os.path.relpath(PROTECTED,self.root)
        self.denied('import os;os.stat('+repr(relative)+')')

    def test_symlinks_are_forbidden(self):
        self.denied('import os;os.symlink('+repr(PROTECTED)+",'alias')")

    def test_non_python_subprocess_is_forbidden(self):
        self.denied("import subprocess;subprocess.run(['/usr/bin/git','status'])")

    def test_guard_cannot_be_removed_from_child_environment(self):
        self.denied("import subprocess,sys;subprocess.run([sys.executable,'-c','pass'],env={})")

    def test_python_disable_site_flag_is_forbidden(self):
        self.denied("import subprocess,sys;subprocess.run([sys.executable,'-S','-c','pass'])")

    def test_network_is_forbidden(self):
        self.denied('import socket;socket.socket()')

    def test_child_attempt_poison_entire_run_even_if_exit_is_ignored(self):
        child='open('+repr(PROTECTED+'/.env')+')'
        result=self.guarded('import subprocess,sys;subprocess.run([sys.executable,"-c",'+repr(child)+'])')
        # Outer program deliberately ignores the child exit: marker still rejects the run.
        self.assertEqual(result.returncode,0,result.stderr)
        self.assertTrue((self.root/'.isolation-violation').exists())

    def test_process_fixtures_work_with_guard_on_this_platform(self):
        script='import sys,unittest;sys.path.insert(0,'+repr(str(HERE))+');import test_deploy_guards as t;'
        script+='r=unittest.TextTestRunner().run(unittest.defaultTestLoader.loadTestsFromTestCase(t.ProcessTests));sys.exit(0 if r.wasSuccessful() else 1)'
        result=self.guarded(script)
        self.assertEqual(result.returncode,0,result.stderr)
        self.assertFalse((self.root/'.isolation-violation').exists())
