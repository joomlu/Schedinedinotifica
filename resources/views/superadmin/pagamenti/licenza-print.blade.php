<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Licenza {{ $assegnazione->numero_licenza ?: '—' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; margin: 32px; }
        .header { margin-bottom: 24px; }
        .title { font-size: 24px; font-weight: 700; }
        .muted { color: #6b7280; font-size: 13px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
        .label { font-size: 12px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .value { font-size: 16px; font-weight: 600; }
        .table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .table th, .table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        .table th { background: #f8fafc; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="title">Licenza assegnata</div>
        <div class="muted">{{ $assegnazione->codice_tracking }}</div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="label">Numero licenza</div>
            <div class="value">{{ $assegnazione->numero_licenza ?: '—' }}</div>
        </div>
        <div class="card">
            <div class="label">Codice tracking</div>
            <div class="value">{{ $assegnazione->codice_tracking }}</div>
        </div>
        <div class="card">
            <div class="label">Articolo</div>
            <div class="value">{{ $assegnazione->articolo?->nome ?: '—' }}</div>
            <div class="muted">{{ $assegnazione->articolo?->parent?->nome ?: 'Articolo principale' }}</div>
        </div>
        <div class="card">
            <div class="label">Destinazione</div>
            <div class="value">{{ $assegnazione->struttura?->nome_struttura ?: ($assegnazione->proprietario?->nome ?: '—') }}</div>
            <div class="muted">{{ $assegnazione->struttura ? 'Licenza struttura' : 'Licenza proprietario' }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Admin</th>
                <th>Prezzo</th>
                <th>Quantità</th>
                <th>Stato pagamento</th>
                <th>Data inizio</th>
                <th>Data scadenza</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $assegnazione->admin?->name ?: '—' }}</td>
                <td>{{ number_format((float) $assegnazione->prezzo, 2, ',', '.') }}</td>
                <td>{{ $assegnazione->quantita }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $assegnazione->stato_pagamento)) }}</td>
                <td>{{ optional($assegnazione->data_inizio)->format('d/m/Y') ?: '—' }}</td>
                <td>{{ optional($assegnazione->data_scadenza)->format('d/m/Y') ?: '—' }}</td>
            </tr>
        </tbody>
    </table>

    @if($assegnazione->note)
        <div class="card" style="margin-top: 20px;">
            <div class="label">Note</div>
            <div>{{ $assegnazione->note }}</div>
        </div>
    @endif
</body>
</html>
