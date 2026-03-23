<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Dettaglio presenze</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:12px;color:#222} h1{font-size:20px;margin:0 0 6px} table{width:100%;border-collapse:collapse;margin-top:14px} th,td{border:1px solid #d7d7d7;padding:6px 8px} th{text-align:left}.text-center{text-align:center}.muted{color:#666}.cards{display:flex;gap:14px;flex-wrap:wrap;margin:12px 0}.card{border:1px solid #ddd;padding:8px 10px;min-width:110px}
    </style>
</head>
<body onload="window.print()">
    <h1>Dettaglio presenze</h1>
    <div class="muted">{{ $struttura->nome_struttura ?? 'Struttura' }} | Periodo {{ $dal->format('d/m/Y') }} - {{ $al->format('d/m/Y') }}</div>
    <div class="muted">Vista: {{ $categoria === 'italiane' ? 'Solo italiani' : ($categoria === 'straniere' ? 'Solo stranieri' : 'Tutti') }}</div>
    <div class="cards">
        <div class="card">Schedine: {{ $dettaglio['totali']['schedine'] }}</div>
        <div class="card">Arrivi: {{ $dettaglio['totali']['arrivi'] }}</div>
        <div class="card">Partenze: {{ $dettaglio['totali']['partenze'] }}</div>
        <div class="card">Presenze: {{ $dettaglio['totali']['presenze'] }}</div>
        <div class="card">Adulti: {{ $dettaglio['totali']['adulti'] }}</div>
        <div class="card">Minori: {{ $dettaglio['totali']['minori'] }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Num.</th><th>Arrivo</th><th>Partenza</th><th>Ospite</th><th class="text-center">Q. Per.</th><th class="text-center">Adulti</th><th class="text-center">Minori</th><th class="text-center">Presenze</th><th>Provenienza</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dettaglio['rows'] as $row)
                <tr>
                    <td>{{ $row['scheda'] }}</td>
                    <td>{{ optional($row['arrivo'])->format('d/m/Y') ?: '-' }}</td>
                    <td>{{ optional($row['partenza'])->format('d/m/Y') ?: '-' }}</td>
                    <td>{{ $row['ospite'] ?: '-' }}</td>
                    <td class="text-center">{{ $row['persone'] }}</td>
                    <td class="text-center">{{ $row['adulti'] }}</td>
                    <td class="text-center">{{ $row['minori'] }}</td>
                    <td class="text-center">{{ $row['presenze'] }}</td>
                    <td>{{ $row['provenienza'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
