#!/usr/bin/env python3
"""Lettura integrale di Tavola A: nessun DB, nessuna scrittura, solo dati aggregati."""
import calendar
import collections
import csv
import hashlib
import io
import json
import re
import sys
from pathlib import Path

MONTHS = ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre']
KINDS = ('Arrivati', 'Partiti', 'Presenti')


def audit(path):
    raw = Path(path).read_bytes()
    text = raw.decode('utf-8-sig')
    rows = list(csv.reader(io.StringIO(text), delimiter=';'))
    if len(rows) < 15 or rows[10][:3] != ['Giorno', 'Camere Occupate', 'Movimento']:
        raise ValueError('Struttura Tavola A non riconosciuta: nessuna interpretazione arbitraria.')
    year = int(rows[5][9]); month = MONTHS.index(rows[6][9].strip().lower()) + 1
    days = calendar.monthrange(year, month)[1]
    issues = []
    if not 0 <= int(rows[4][9]) <= days:
        issues.append({'regola': 'giorni_apertura', 'valore': int(rows[4][9]), 'massimo': days})
    data = [r for r in rows[12:] if len(r) > 2 and r[2] in KINDS and r[0].strip() != 'Totales']
    totals = [r for r in rows if r[0].strip() == 'Totales']
    previous = [int(v.strip()) for v in rows[11][3:86]]
    grouped = collections.defaultdict(dict)
    for number, row in enumerate(rows, 1):
        if len(row) not in (86, 87) or (len(row) == 87 and row[86].strip()):
            issues.append({'regola': 'larghezza', 'riga': number, 'colonne': len(row)})
        if row not in data and row not in totals:
            continue
        if any(not re.fullmatch(r'\d+', v.strip()) for v in row[3:86]):
            issues.append({'regola': 'interi_non_negativi', 'riga': number})
            continue
        values = [int(v.strip()) for v in row[3:86]]
        checks = [(62, sum(values[:59])), (84, sum(values[60:81])), (85, values[59] + values[81])]
        for column, expected in checks:
            if int(row[column].strip()) != expected:
                issues.append({'regola': 'somma_categorie', 'riga': number, 'movimento': row[2], 'colonna': column, 'valore': int(row[column].strip()), 'atteso': expected})
        if row in data:
            date = row[0].strip()
            if row[2] in grouped[date]:
                issues.append({'regola': 'duplicato', 'data': date, 'movimento': row[2]})
            grouped[date][row[2]] = row
    expected_dates = [f'{day:02d}/{month:02d}' for day in range(1, days + 1)]
    if list(grouped) != expected_dates:
        issues.append({'regola': 'date_periodo', 'giorni_presenti': len(grouped), 'giorni_attesi': days})
    for date, items in grouped.items():
        if set(items) != set(KINDS):
            issues.append({'regola': 'record_incompleto', 'data': date}); continue
        av, dv, pv = [[int(v.strip()) for v in items[k][3:86]] for k in KINDS]
        for j in range(83):
            if pv[j] != previous[j] + av[j] - dv[j]:
                issues.append({'regola': 'bilancio_presenze', 'data': date, 'colonna': j + 3})
        previous = pv
    for row in totals:
        selected = [r for r in data if r[2] == row[2]]
        for j in range(3, 86):
            expected = sum(int(r[j].strip()) for r in selected)
            if int(row[j].strip()) != expected:
                issues.append({'regola': 'totale_mensile', 'movimento': row[2], 'colonna': j, 'valore': int(row[j].strip()), 'atteso': expected})
    return {'file': str(path), 'sha256': hashlib.sha256(raw).hexdigest(), 'encoding': 'UTF-8', 'bom': raw.startswith(b'\xef\xbb\xbf'), 'separatore': ';',
            'righe': len(rows), 'colonne': dict(collections.Counter(map(len, rows))), 'periodo': f'{year:04d}-{month:02d}',
            'giorni': len(grouped), 'righe_movimenti': len(data), 'intestazioni': rows[10],
            'totali_calcolati': {k: sum(int(r[85].strip()) for r in data if r[2] == k) for k in KINDS},
            'massimo_camere_occupate': max(int(r[1].strip()) for r in data if r[2] == 'Arrivati'),
            'massimo_presenti': max(int(r[85].strip()) for r in data if r[2] == 'Presenti'), 'anomalie': issues}


if __name__ == '__main__':
    print(json.dumps([audit(p) for p in sys.argv[1:]], ensure_ascii=False, indent=2))
