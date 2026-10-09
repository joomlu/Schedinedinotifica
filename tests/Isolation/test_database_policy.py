"""Regressioni della barriera, senza avviare DB o eseguire DDL."""
import copy
import os
from pathlib import Path
import tempfile
import subprocess
import json
import unittest
from unittest.mock import patch
import database_policy as policy


class DatabasePolicyTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory(prefix='schedine-test-', dir='/private/tmp' if os.uname().sysname == 'Darwin' else '/tmp')
        self.addCleanup(self.tmp.cleanup)
        self.root = Path(self.tmp.name).resolve()
        self.root.chmod(0o700)
        (self.root/'mysql-data').mkdir()
        (self.root/'mysqld').touch()

    def fixture(self, engine='mariadb'):
        e = dict(root=str(self.root), sha=policy.CANDIDATE, vendor=engine, database='test_geo_'+'a'*32,
                 datadir=str(self.root/'mysql-data'), port=49152, socket=str(self.root/'mysql.sock'),
                 server_id=178390, username='fixture', app_env='testing', uuid='synthetic-uuid',
                 pid=123, launcher_pid=os.getpid(), uid=os.getuid(), executable=str(self.root/'mysqld'))
        return e, copy.deepcopy(e)

    def snapshot(self, e):
        return dict(ppid=e['launcher_pid'], uid=e['uid'], executable=e['executable'],
                    argv=[e['executable'], '--no-defaults', '--datadir='+e['datadir'], '--socket='+e['socket'],
                          '--port='+str(e['port']), '--server-id='+str(e['server_id']), '--bind-address=127.0.0.1'])

    def test_absolute_rejections_even_when_identity_matches(self):
        cases=[('production env','app_env','production'), ('production DB','database',policy.PRODUCTION_DATABASE),
               ('unapproved DB','database','unapproved'), ('wrong SHA','sha','0'*40), ('wrong path','root','/home/tanggosoftware/repos/schedinedinotifica'),
               ('unknown vendor','vendor','unknown'), ('incomplete','port',None), ('production port','port',3306),
               ('low port','port',40000), ('production socket','socket',policy.PRODUCTION_SOCKET), ('production datadir','datadir','/var/lib/mysql'),
               ('zero PID','pid',0), ('negative PID','pid',-1), ('zero server_id','server_id',0),
               ('negative server_id','server_id',-1), ('large server_id','server_id',4294967296)]
        for label,key,value in cases:
            e,o=self.fixture();e[key]=value;o[key]=value
            with self.subTest(scenario=label), patch.object(policy,'process_snapshot',side_effect=AssertionError('non deve raggiungere il processo')):
                with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_missing_pid_is_incomplete(self):
        e,o=self.fixture();del e['pid']
        with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_traversal_with_concordant_paths(self):
        e,o=self.fixture();e['root']+='/../escape';e['datadir']=e['root']+'/mysql-data';e['socket']=e['root']+'/mysql.sock';o.update(e)
        with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_symlink_escape(self):
        e,o=self.fixture();(self.root/'alias').symlink_to('/var/lib/mysql');e['datadir']=str(self.root/'alias');o.update(e)
        with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_configured_production_endpoint_denylist(self):
        e,o=self.fixture();e['production_port']=e['port']
        with self.assertRaises(RuntimeError):policy.validate(e,o)
        e,o=self.fixture();e['production_socket']=e['socket']
        with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_process_identity_required(self):
        for key,value in [('ppid',1),('uid',-1),('executable','/usr/bin/other'),('argv',[])]:
            e,o=self.fixture();proc=self.snapshot(e);proc[key]=value
            with self.subTest(field=key),patch.object(policy,'process_snapshot',return_value=proc):
                with self.assertRaises(RuntimeError):policy.validate(e,o)
        e,o=self.fixture()
        with patch.object(policy,'process_snapshot',side_effect=RuntimeError('processo assente')):
            with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_valid_mariadb_and_mysql(self):
        for engine in ('mariadb','mysql'):
            e,o=self.fixture(engine)
            with self.subTest(vendor=engine),patch.object(policy,'process_snapshot',return_value=self.snapshot(e)):
                self.assertTrue(policy.validate(e,o))

    def test_observed_identity_mismatches(self):
        for key in ('datadir','socket','server_id','port','database','vendor','uuid'):
            e,o=self.fixture('mysql');o[key]='wrong'
            with self.subTest(field=key),patch.object(policy,'process_snapshot',return_value=self.snapshot(e)):
                with self.assertRaises(RuntimeError):policy.validate(e,o)

    def test_php_vendor_semantics_without_application_bootstrap(self):
        values = ['11.8.9-MariaDB MariaDB Server', '8.0.36 MySQL Community Server', 'unknown 8.0.36', 'MariaDB MySQL']
        source = Path(__file__).resolve().parents[1]/'Support/TestingEnvironment.php'
        code = '<?php require '+json.dumps(str(source))+''';
        foreach (json_decode($argv[1], true) as $v) {
            try { echo \Tests\Support\TestingEnvironment::databaseVendor($v)."\\n"; }
            catch (RuntimeException $e) { echo "BLOCK\\n"; }
        }'''
        result = subprocess.run(['php', '-d', 'opcache.enable_cli=0', '--', json.dumps(values)], input=code, text=True, capture_output=True, check=True)
        self.assertEqual(result.stdout.splitlines(), ['mariadb', 'mysql', 'BLOCK', 'BLOCK'])

    def test_vendor_detection_shared_semantics(self):
        for value,expected in [('mysqld Ver 11.8.9-MariaDB','mariadb'),('11.8.9-MariaDB MariaDB Server','mariadb'),
                               ('mysqld Ver 8.0.36 (MySQL Community Server)','mysql'),('8.0.36 MySQL Community Server - GPL','mysql')]:
            self.assertEqual(policy.vendor(value),expected)
        for value in ['unknown 8.0.36','8.0.36','MariaDB MySQL','Percona Server 8.0.36','']:
            with self.subTest(value=value),self.assertRaises(RuntimeError):policy.vendor(value)


if __name__ == '__main__':unittest.main(verbosity=2)
