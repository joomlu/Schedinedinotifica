#!/usr/bin/env python3
"""Diagnostica HTTPS limitata al progetto; non sostituisce i controlli C5."""
import argparse
import datetime
import json
import os
from pathlib import Path
import subprocess
import tempfile
import time

HOSTS = (
    'schedinedinotifica.tanggo.software',
    'mail.schedinedinotifica.tanggo.software',
    'www.schedinedinotifica.tanggo.software',
)
CAMPI = (
    'http_code', 'ssl_verify_result', 'remote_ip', 'remote_port', 'http_version',
    'time_namelookup', 'time_connect', 'time_appconnect', 'time_pretransfer',
    'time_starttransfer', 'time_total', 'num_connects', 'exitcode',
)


def utc():
    return datetime.datetime.now(datetime.timezone.utc).isoformat()


def scrivi(path, value):
    temporary = path.with_suffix('.tmp')
    temporary.write_text(json.dumps(value, ensure_ascii=False, indent=2) + '\n')
    temporary.chmod(0o600)
    os.replace(temporary, path)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--run', action='store_true', help='Avvia 31 campioni, pausa30s, timeout15s invariati')
    parser.add_argument('--output-parent', type=Path)
    parser.add_argument('--origine', choices=('Mac', 'VM', 'Server'))
    args = parser.parse_args()
    if not args.run:
        parser.print_help()
        return 0
    if args.output_parent is None or args.origine is None:
        parser.error('--run richiede --output-parent e --origine')
    parent = args.output_parent.resolve(strict=True)
    if not parent.is_dir():
        parser.error('La directory di destinazione deve esistere')
    root = Path(tempfile.mkdtemp(prefix='diagnostica-https-', dir=parent))
    root.chmod(0o700)
    print(json.dumps({'directory_risultati': str(root)}), flush=True)
    start = time.monotonic()
    result = {
        'inizio_UTC': utc(), 'origine': args.origine, 'campioni': [],
        'timeout_secondi': 15, 'pausa_secondi': 30, 'campioni_previsti': 31,
        'curl': subprocess.check_output(['curl', '--version'], text=True).splitlines()[0],
        'config_curl_utente_disabilitata': True,
        'proxy_presenti': {n: bool(os.environ.get(n)) for n in (
            'HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy')},
        'corpi_cookie_intestazioni_credenziali_non_registrati': True,
        'IP_non_forzati_TLS_obbligatorio': True,
        'sostituisce_controlli_C5': False,
        'causa_timeout_storico': 'CAUSA NON DETERMINATA',
    }
    try:
        for index in range(31):
            row = {'campione': index + 1, 'sonde': []}
            for host in HOSTS:
                begin = utc()
                process = subprocess.run([
                    'curl', '-q', '--max-time', '15', '--no-insecure', '--proto', '=https',
                    '-sS', '-o', '/dev/null', '-w', '%{json}', 'https://' + host + '/login',
                ], capture_output=True, text=True)
                try:
                    data = json.loads(process.stdout)
                except json.JSONDecodeError:
                    data = {}
                good = process.returncode == 0 and data.get('http_code') == 200 and data.get('ssl_verify_result') == 0
                row['sonde'].append({
                    'host': host, 'inizio_UTC': begin, 'fine_UTC': utc(),
                    'codice_curl': process.returncode,
                    'misure': {key: data[key] for key in CAMPI if key in data},
                    'esito': 'PASS_SONDA' if good else 'NONPASS_SONDA',
                })
            result['campioni'].append(row)
            scrivi(root / 'progresso.json', result)
            print(json.dumps({'campione': index + 1, 'esito':
                  'PASS_SONDE' if all(p['esito'] == 'PASS_SONDA' for p in row['sonde']) else 'NONPASS_SONDE'}), flush=True)
            if index < 30:
                time.sleep(30)
    except BaseException:
        result.update(esito='DIAGNOSTICA_INTERROTTA', fine_UTC=utc(), secondi=time.monotonic() - start)
        scrivi(root / 'risultato.json', result)
        raise
    result.update(fine_UTC=utc(), secondi=time.monotonic() - start)
    good = (result['secondi'] >= 900 and len(result['campioni']) == 31
            and all(p['esito'] == 'PASS_SONDA' for row in result['campioni'] for p in row['sonde']))
    result['esito'] = ('VERIFICATO_SENZA_FALLO_RIPRODOTTO_CAUSA_NON_DETERMINATA'
                      if good else 'FALLO_RIPRODOTTO_CAUSA_NON_DETERMINATA')
    scrivi(root / 'risultato.json', result)
    print(json.dumps({'esito': result['esito'], 'secondi': result['secondi']}), flush=True)
    return 0 if good else 1


if __name__ == '__main__':
    raise SystemExit(main())
