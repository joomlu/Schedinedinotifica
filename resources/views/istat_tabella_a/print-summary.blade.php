<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Tabella A Emilia-Romagna - Riepilogo hotel</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #2b2b2b; font-size: 12px; margin: 0; }
        h1, h2, h3 { margin: 0 0 8px; }
        .header { margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid #8C1D2C; }
        .muted { color: #6b6761; }
        .grid { display: table; width: 100%; border-collapse: separate; border-spacing: 10px; margin: 0 -10px 14px; }
        .card { display: table-cell; width: 25%; border: 1px solid #e6ded4; border-radius: 8px; padding: 10px 12px; vertical-align: top; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #8C867F; margin-bottom: 4px; }
        .value { font-size: 20px; font-weight: 700; color: #8C1D2C; }
        .section { margin-top: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e6ded4; padding: 6px 8px; text-align: left; }
        th { font-size: 10px; text-transform: uppercase; color: #6b6761; }
        .text-end { text-align: right; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tabella A Emilia-Romagna</h1>
        <div class="muted">Riepilogo operativo hotel</div>
        <div class="small">Struttura: {{ $struttura->nome_struttura ?: 'Struttura' }} | Periodo: {{ $dal->format('d/m/Y') }} - {{ $al->format('d/m/Y') }}</div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="label">Schedine</div>
            <div class="value">{{ $analysis['totale_schedine'] }}</div>
        </div>
        <div class="card">
            <div class="label">Arrivi</div>
            <div class="value">{{ $analysis['totale_arrivi'] }}</div>
        </div>
        <div class="card">
            <div class="label">Presenze</div>
            <div class="value">{{ $analysis['totale_presenze'] }}</div>
        </div>
        <div class="card">
            <div class="label">Partenze</div>
            <div class="value">{{ $analysis['totale_partenze'] }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Andamento giornaliero</h3>
        <table>
            <thead>
                <tr>
                    <th>Giorno</th>
                    <th class="text-end">Arrivi</th>
                    <th class="text-end">Partenze</th>
                    <th class="text-end">Presenti</th>
                    <th class="text-end">Italiani</th>
                    <th class="text-end">Stranieri</th>
                    <th class="text-end">Cam. occ.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['rows'] as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['giorno'])->format('d/m/y') }}</td>
                        <td class="text-end">{{ $row['arrivi'] }}</td>
                        <td class="text-end">{{ $row['partenze'] }}</td>
                        <td class="text-end">{{ $row['presenti'] }}</td>
                        <td class="text-end">{{ $row['presenti_italiani'] }}</td>
                        <td class="text-end">{{ $row['presenti_stranieri'] }}</td>
                        <td class="text-end">{{ $row['camere_occupate'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Principali provenienze</h3>
        <table>
            <thead>
                <tr>
                    <th>Provenienza</th>
                    <th class="text-end">Schedine</th>
                </tr>
            </thead>
            <tbody>
                @forelse($provenienze as $item)
                    <tr>
                        <td>{{ $item['label'] }}</td>
                        <td class="text-end">{{ $item['count'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="muted">Nessuna provenienza disponibile nel periodo selezionato.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
