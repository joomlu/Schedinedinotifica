<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Riepilogo presenze {{ $anno }}</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:12px;color:#222} h1{font-size:20px;margin:0 0 6px} h2{font-size:15px;margin:18px 0 8px} table{width:100%;border-collapse:collapse;margin-bottom:12px} th,td{border:1px solid #d7d7d7;padding:6px 8px;text-align:center} th:first-child,td:first-child{text-align:left} .muted{color:#666}.head{margin-bottom:16px}
    </style>
</head>
<body onload="window.print()">
    <div class="head">
        <h1>Riepilogo presenze {{ $anno }}</h1>
        <div class="muted">{{ $struttura->nome_struttura ?? 'Struttura' }}</div>
        <div class="muted">Periodo mensile da {{ \Carbon\Carbon::create($anno, $meseDa, 1)->locale('it')->monthName }} a {{ \Carbon\Carbon::create($anno, $meseA, 1)->locale('it')->monthName }}</div>
        <div class="muted">La colonna presenti indica le persone già presenti a inizio mese.</div>
    </div>
    @foreach($riepilogo as $mese)
        <h2>{{ \Illuminate\Support\Str::title($mese['mese_label']) }}</h2>
        <table>
            <thead><tr><th>Paesi Esteri / Italia</th><th>Presenti a inizio mese</th><th>Arrivi</th><th>Partenze</th><th>Presenze</th><th>Totale Paesi Esteri</th><th>Totale Italiano</th></tr></thead>
            <tbody>
                <tr><td>Italiane</td><td>{{ $mese['italiane']['presenti'] }}</td><td>{{ $mese['italiane']['arrivi'] }}</td><td>{{ $mese['italiane']['partenze'] }}</td><td>{{ $mese['italiane']['presenze'] }}</td><td>-</td><td>{{ $mese['italiane']['presenze'] }}</td></tr>
                <tr><td>Straniere</td><td>{{ $mese['straniere']['presenti'] }}</td><td>{{ $mese['straniere']['arrivi'] }}</td><td>{{ $mese['straniere']['partenze'] }}</td><td>{{ $mese['straniere']['presenze'] }}</td><td>{{ $mese['straniere']['presenze'] }}</td><td>-</td></tr>
                <tr><td><strong>Totale</strong></td><td>{{ $mese['totale']['presenti'] }}</td><td>{{ $mese['totale']['arrivi'] }}</td><td>{{ $mese['totale']['partenze'] }}</td><td>{{ $mese['totale']['presenze'] }}</td><td>{{ $mese['totale']['totale_esteri'] }}</td><td>{{ $mese['totale']['totale_italiani'] }}</td></tr>
            </tbody>
        </table>
    @endforeach
</body>
</html>
