"""Regressioni diagnostico backup, senza DB o configurazione reali."""
import importlib.util
from pathlib import Path
import sys
import tempfile
import unittest
from unittest import mock
import subprocess
import json

R = Path(__file__).resolve().parents[2]
s = importlib.util.spec_from_file_location('backup_http', R/'scripts/deployment/backup_http.py')
h = importlib.util.module_from_spec(s); s.loader.exec_module(h)
# Riutilizza la fixture sintetica della barriera già verificata.
from test_deploy_guards import Fixture

class BackupHTTPTests(Fixture):
    def test_header_case_is_semantic_but_value_and_marker_are_strict(self):
        for key in [b'Retry-After', b'retry-after', b'RETRY-AFTER']:
            self.assertTrue(all(h.verify('503',b'<!-- marker -->',key+b': 60\r\n',503,'marker').values()))
        for code,body,headers,failed in [
            ('200',b'marker',b'Retry-After: 60','codice'),
            ('503',b'wrong',b'Retry-After: 60','marker'),
            ('503',b'marker',b'Retry-After: 59','retry_after'),
            ('503',b'marker',b'', 'retry_after'),
            ('503',b'marker',b'Retry-After: 60\nretry-after: 60','retry_after')]:
            self.assertFalse(h.verify(code,body,headers,503,'marker')[failed])

    def synthetic(self, phase, code, marker, retry):
        script = ('import sys,pathlib; a=sys.argv; '
                  'pathlib.Path(a[a.index("-D")+1]).write_bytes('+repr(retry)+'); '
                  'pathlib.Path(a[a.index("-o")+1]).write_bytes('+repr(marker)+'); '
                  'sys.stdout.write('+repr(code)+')')
        return h.capture(self.root,phase,[sys.executable,'-c',script],503,'marker')

    def test_failure_evidence_preserved_and_barrier_removed(self):
        for item in ['codice','marker','header']:
            self.app.maintenance.close()
            try:
                with self.assertRaises(RuntimeError):
                    self.synthetic(item,'200' if item=='codice' else '503',
                                   b'wrong' if item=='marker' else b'marker',
                                   b'' if item=='header' else b'retry-after: 60')
                self.assertTrue((self.root/item/'esito.json').exists())
            finally:
                self.app.maintenance.open()
            self.assertFalse((self.root/'storage/framework/down').exists())
            self.assertFalse((self.root/'storage/framework/maintenance.php').exists())
            self.persistent()

    def test_reopening_cannot_overwrite_closure(self):
        self.app.maintenance.close()
        self.synthetic('chiusura','503',b'marker',b'retry-after: 60')
        saved={p.name:p.read_bytes() for p in (self.root/'chiusura').iterdir()}
        self.app.maintenance.open()
        script='import sys,pathlib; a=sys.argv; pathlib.Path(a[a.index("-D")+1]).write_bytes(b"HTTP/2 200"); pathlib.Path(a[a.index("-o")+1]).write_bytes(b"login"); print("200",end="")'
        h.capture(self.root,'riapertura',[sys.executable,'-c',script],200)
        self.assertEqual(saved,{p.name:p.read_bytes() for p in (self.root/'chiusura').iterdir()})
        with self.assertRaises(FileExistsError):self.synthetic('chiusura','503',b'marker',b'retry-after: 60')
        self.assertTrue((self.root/'riapertura/esito.json').exists())

    def test_timeout_keeps_evidence(self):
        with mock.patch.object(h.subprocess, 'run', side_effect=subprocess.TimeoutExpired('curl', 20)):
            with self.assertRaisesRegex(RuntimeError, 'trasporto'):
                h.capture(self.root, 'timeout', ['curl'], 503, 'marker')
        record=json.loads((self.root/'timeout/esito.json').read_text())
        self.assertFalse(record['controlli']['trasporto'])
        self.assertTrue((self.root/'timeout/codice.raw').exists())
        self.assertTrue((self.root/'timeout/stderr.raw').exists())
