"""Diagnosi A05 del vero handler estratto via AST: solo doppi in memoria, nessun socket."""
import ast
import io
import unittest
from pathlib import Path
from http.client import HTTPResponse, IncompleteRead
from http.server import BaseHTTPRequestHandler
from urllib.parse import urlsplit
from contextlib import redirect_stderr

class SocketDouble:
    def __init__(self, body):
        self.body = body
    def makefile(self, *args):
        return io.BytesIO(self.body)

class ProxyResponseTests(unittest.TestCase):
    def forward(self, advertised, body):
        response = HTTPResponse(SocketDouble(b'HTTP/1.1 200 OK\r\nContent-Length: '+str(advertised).encode()+b'\r\n\r\n'+body))
        response.begin()
        class ConnectionDouble:
            closed = False
            def __init__(self, *args, **kwargs): pass
            def request(self, *args, **kwargs): pass
            def getresponse(self): return response
            def close(self): self.closed = True
        tree = ast.parse(Path(__file__).with_name('run.py').read_text())
        node = next(n for n in ast.walk(tree) if isinstance(n, ast.ClassDef) and n.name == 'OriginProxy')
        namespace = dict(BaseHTTPRequestHandler=BaseHTTPRequestHandler, HTTPConnection=ConnectionDouble,
                         urlsplit=urlsplit, httpport=55555, info={'denied_requests': 0},
                         IncompleteRead=IncompleteRead, sys=__import__('sys'))
        exec(compile(ast.Module(body=[node], type_ignores=[]), 'handler-originale', 'exec'), namespace)
        handler = object.__new__(namespace['OriginProxy'])
        handler.path = 'http://127.0.0.1:55555/file-sintetico.csv'
        handler.command = 'GET'
        handler.headers = {}
        handler.rfile = io.BytesIO()
        handler.wfile = io.BytesIO()
        events = []
        handler.send_response = lambda status: events.append(('status', status))
        handler.send_header = lambda key, value: events.append((key, value))
        handler.end_headers = lambda: None
        handler.send_error = lambda status, message: events.append(('error', status, message))
        stderr = io.StringIO()
        with redirect_stderr(stderr): handler.forward()
        return events, handler.wfile.getvalue(), stderr.getvalue()

    def test_corpo_completo_preservato(self):
        events, body, log = self.forward(3, b'abc')
        self.assertIn(('status', 200), events)
        self.assertEqual(body, b'abc')
        self.assertEqual(log, '')

    def test_corpo_troncato_non_diventa_successo(self):
        events, body, log = self.forward(10, b'abc')
        self.assertIn(('error', 502, 'ISOLATED_UPSTREAM_INCOMPLETE'), events)
        self.assertNotIn(('status', 200), events)
        self.assertEqual(body, b'')
        self.assertIn('ricevuti=3 mancanti=7', log)
        self.assertNotIn('abc', log)

if __name__ == '__main__': unittest.main()
