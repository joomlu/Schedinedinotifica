<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedina {{ $schedina->scheda ?: 'in compilazione' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 16px; font: 14px Arial, sans-serif; color: #2b2b2b; background: #fcf8f3; }
        .actions, .sheet { max-width: 210mm; margin: 0 auto 12px; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
        .actions a, .actions button { font: inherit; text-decoration: none; padding: 10px 14px; border: 1px solid #cfbda8; border-radius: 6px; background: #fffdfa; color: #741826; cursor: pointer; }
        .actions a:hover, .actions button:hover { background: #efe2c2; }
        .actions :focus-visible { outline: 2px solid #8c1d2c; outline-offset: 3px; }
        .sheet { background: white; padding: 16px; border: 1px solid #cfbda8; }
        h1 { font-size: 22px; color: #741826; margin: 8px 0; }
        h2 { font-size: 15px; color: #741826; margin: 14px 0 6px; break-after: avoid; }
        .fields { display: grid; grid-template-columns: 1fr 1fr; border-top: 1px solid #cfbda8; }
        .field { padding: 5px; border-bottom: 1px solid #ddd; overflow-wrap: anywhere; white-space: pre-wrap; }
        .wide { grid-column: 1 / -1; }
        .person { break-inside: avoid; }
        p { margin: 6px 0; overflow-wrap: anywhere; white-space: pre-wrap; }
        @media (max-width: 600px) { body { padding: 8px; } .sheet { padding: 10px; } .fields { grid-template-columns: 1fr; } }
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            body { padding: 0; background: white; font-size: 9pt; }
            .actions { display: none; }
            .sheet { border: 0; padding: 0; max-width: none; margin: 0; }
            .fields { grid-template-columns: 1fr 1fr; }
            h1 { font-size: 16pt; } h2 { font-size: 11pt; }
            .field { padding: 3px; }
        }
    </style>
</head>
<body>
    <nav class="actions" aria-label="Azioni schedina">
        <a href="{{ route('schedina.edit', $schedina->id) }}">Modifica schedina</a>
        <button type="button" onclick="window.print()">Stampa / Salva PDF</button>
    </nav>
    @php
        $date = fn ($value) => filled($value) ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '—';
        $join = fn ($values) => collect($values)->filter(fn ($value) => filled($value))->implode(', ') ?: '—';
        $ospite = [
            'Tipo alloggiato' => $schedina->customer_type_housed,
            'Titolo' => $schedina->type,
            'Nome' => $schedina->name, 'Cognome' => $schedina->surname,
            'Sesso' => $schedina->sex, 'Cittadinanza' => $schedina->oa_city_nac,
            'Data di nascita' => $date($schedina->oa_date_nac),
            'Luogo di nascita' => $join([$schedina->oa_city, $schedina->oa_prov, $schedina->oa_region, $schedina->oa_cap, $schedina->oa_country]),
            'Residenza' => $join([$schedina->or_typeaway, $schedina->or_address, $schedina->or_num, $schedina->or_cap, $schedina->or_city, $schedina->or_prov, $schedina->or_region, $schedina->or_country]),
            'Documento' => $join([$schedina->or_doctype, $schedina->or_doc]),
            'Data rilascio' => $date($schedina->or_published_date), 'Scadenza' => $date($schedina->or_expire),
            'Rilasciato da' => $join([$schedina->or_published, $schedina->or_published_city, $schedina->or_published_country]),
            'Parentela' => $schedina->relationship, 'Esenzione' => $schedina->exent,
            'Email' => $schedina->customer_email, 'Telefono' => $schedina->customer_phone,
            'Cellulare' => $schedina->customer_cellphone, 'Fax' => $schedina->customer_fax,
        ];
    @endphp
    <main class="sheet">
        <header>
            <strong>{{ $struttura->nome_struttura }}</strong>
            <h1>Schedina {{ $schedina->scheda ?: 'in compilazione' }}</h1>
            <p>Arrivo: {{ $date($schedina->arrive) }} · Partenza: {{ $date($schedina->departure) }} · Persone: {{ $schedina->cant_people ?: '—' }} · Camere: {{ $schedina->room ?: '—' }} · Letti: {{ $schedina->beds ?: '—' }}</p>
            @foreach($schedina->camere as $camera)
                <p>Camera {{ $camera->numero_camera }} · Posti letto: {{ $camera->posti_letto }}@if(filled($camera->note)) · {{ $camera->note }}@endif</p>
            @endforeach
        </header>
        <section>
            <h2>Ospite titolare</h2>
            <div class="fields">
                @foreach($ospite as $label => $value)
                    <div class="field"><strong>{{ $label }}:</strong> {{ filled($value) ? $value : '—' }}</div>
                @endforeach
            </div>
            @foreach(['customer_observation' => 'Osservazioni cliente', 'customer_anag_observation' => 'Osservazioni anagrafiche', 'observation' => 'Note soggiorno'] as $field => $label)
                @if(filled($schedina->$field))<p><strong>{{ $label }}:</strong> {{ $schedina->$field }}</p>@endif
            @endforeach
        </section>
        <section>
            <h2>Componenti ({{ $schedina->componenti->count() }})</h2>
            @forelse($schedina->componenti as $componente)
                <article class="person">
                    <h2>{{ $loop->iteration }}. {{ $componente->surname }} {{ $componente->name }}</h2>
                    <div class="fields">
                        <div class="field"><strong>Sesso:</strong> {{ $componente->sex ?: '—' }} · <strong>Parentela:</strong> {{ $componente->relationship ?: '—' }}</div>
                        <div class="field"><strong>Nascita:</strong> {{ $date($componente->date_nac) }}</div>
                        <div class="field"><strong>Luogo di nascita:</strong> {{ $join([$componente->comune_nac, $componente->province_nac, $componente->regione_nac, $componente->cap_nac, $componente->country_nac]) }}</div>
                        <div class="field"><strong>Cittadinanza:</strong> {{ $componente->city_nac ?: '—' }} · <strong>Esenzione:</strong> {{ $componente->exent ?: '—' }}</div>
                        <div class="field wide"><strong>Residenza:</strong> {{ $join([$componente->typeaway, $componente->address, $componente->number, $componente->cap, $componente->city, $componente->province, $componente->regione, $componente->country]) }}</div>
                    </div>
                </article>
            @empty
                <p>Nessun componente registrato.</p>
            @endforelse
        </section>
    </main>
</body>
</html>
