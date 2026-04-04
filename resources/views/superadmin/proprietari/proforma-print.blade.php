@php
    $recipientStructures = $proforma->righe->pluck('struttura')->filter()->unique('id')->values();
    $hasGeneralRows = $proforma->righe->contains(fn ($riga) => !$riga->struttura_id);
    $destinatario = ($recipientStructures->count() === 1 && !$hasGeneralRows)
        ? $recipientStructures->first()
        : $proprietario;
@endphp
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Proforma {{ $proforma->numero }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; margin: 32px; }
        h1, h2, h3, p { margin: 0; }
        .top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; }
        .muted { color:#6b7280; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border-bottom:1px solid #d1d5db; padding:10px 8px; text-align:left; font-size:14px; }
        th:last-child, td:last-child { text-align:right; }
        .total { margin-top:24px; text-align:right; font-size:18px; font-weight:bold; }
    </style>
</head>
<body onload="window.print()">
    <div class="top">
        <div>
            <h1>Proforma {{ $proforma->numero }}</h1>
            <p class="muted">{{ optional($proforma->data_documento)->format('d/m/Y') }}</p>
            <div style="margin-top:16px">
                <h3>Spire</h3>
                <p class="muted">Società emittente della proforma</p>
            </div>
        </div>
        <div style="text-align:right">
            <h3>{{ $destinatario->ragione_sociale ?? $destinatario->nome_struttura ?? $destinatario->nome ?? 'Destinatario' }}</h3>
            <p class="muted">P.IVA {{ $destinatario->partita_iva ?: '-' }}</p>
            <p class="muted">C.F. {{ $destinatario->codice_fiscale ?: '-' }}</p>
            <p class="muted">{{ trim(collect([$destinatario->indirizzo ?? null, $destinatario->numero_civico ?? null, $destinatario->cap ?? null, $destinatario->citta ?? null, $destinatario->provincia ?? null])->filter()->implode(', ')) }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Struttura</th>
                <th>Descrizione</th>
                <th>Qta</th>
                <th>Prezzo</th>
                <th>Sconto</th>
                <th>IVA</th>
                <th>Imponibile</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proforma->righe as $riga)
                <tr>
                    <td>{{ $riga->struttura?->nome_struttura ?: 'Servizio personalizzato' }}</td>
                    <td>{{ $riga->descrizione }}</td>
                    <td>{{ $riga->quantita }}</td>
                    <td>{{ number_format((float) $riga->prezzo_unitario, 2, ',', '.') }}</td>
                    <td>{{ $riga->sconto_tipo === 'importo' ? '€ ' : '' }}{{ number_format((float) $riga->sconto_valore, 2, ',', '.') }}{{ $riga->sconto_tipo === 'percentuale' ? '%' : '' }}</td>
                    <td>{{ number_format((float) $riga->aliquota_iva, 2, ',', '.') }}%</td>
                    <td>{{ number_format((float) $riga->imponibile, 2, ',', '.') }}</td>
                    <td>{{ number_format((float) $riga->totale, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <div>Imponibile: {{ number_format((float) $proforma->imponibile, 2, ',', '.') }}</div>
        <div>Sconto: {{ number_format((float) $proforma->totale_sconto, 2, ',', '.') }}</div>
        <div>IVA: {{ number_format((float) $proforma->totale_iva, 2, ',', '.') }}</div>
        <div>Totale: {{ number_format((float) $proforma->totale, 2, ',', '.') }}</div>
    </div>

    @if($proforma->note)
        <div style="margin-top:24px">
            <h3>Osservazioni</h3>
            <p class="muted">{{ $proforma->note }}</p>
        </div>
    @endif
</body>
</html>
