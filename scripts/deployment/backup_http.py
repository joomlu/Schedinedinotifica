"""Diagnostico HTTP del backup: evidenze riservate immutabili per fase."""
import json
from pathlib import Path
import subprocess
import time


def verify(code, body, headers, expected, marker=None):
    checks = {'codice': str(code).strip() == str(expected)}
    if expected == 503:
        checks['marker'] = bool(marker) and marker.encode() in body
        values = [line.split(b':', 1)[1].strip() for line in headers.splitlines()
                  if b':' in line and line.split(b':', 1)[0].strip().lower() == b'retry-after']
        checks['retry_after'] = values == [b'60']
    return checks


def capture(directory, phase, argv, expected, marker=None):
    directory = Path(directory) / phase
    directory.mkdir(mode=0o700, exist_ok=False)
    began = time.monotonic()
    # Ogni invocazione ha directory esclusiva; nessun file di un'altra fase viene riusato.
    command = list(argv) + ['-D', str(directory/'headers.raw'), '-o', str(directory/'body.raw'),
                            '-w', '%{http_code}']
    with (directory/'codice.raw').open('xb') as out, (directory/'stderr.raw').open('xb') as err:
        try:
            result = subprocess.run(command, stdout=out, stderr=err, timeout=20)
            transport_ok = result.returncode == 0
        except subprocess.TimeoutExpired:
            transport_ok = False
    code = (directory/'codice.raw').read_bytes().decode(errors='replace')
    body = (directory/'body.raw').read_bytes() if (directory/'body.raw').exists() else b''
    headers = (directory/'headers.raw').read_bytes() if (directory/'headers.raw').exists() else b''
    checks = verify(code, body, headers, expected, marker)
    checks['trasporto'] = transport_ok
    record = {'fase': phase, 'atteso': expected, 'codice': code,
              'secondi': round(time.monotonic()-began, 6), 'controlli': checks}
    with (directory/'esito.json').open('x') as out:
        json.dump(record, out, indent=2)
    if not all(checks.values()):
        raise RuntimeError('Controllo HTTP fallito: '+','.join(k for k,v in checks.items() if not v))
    return record
