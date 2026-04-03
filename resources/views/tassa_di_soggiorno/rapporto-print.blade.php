<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Rapporto mensile tassa di soggiorno {{ ($vista ?? 'schede') === 'persone' ? 'per persona' : 'per schedina' }}</title>
    <style>
        @page { size: A4 portrait; margin: 16mm 14mm 16mm 14mm; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            background: #fff;
        }
        .page {
            max-width: 182mm;
            margin: 0 auto;
        }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 16px 0 7px; }
        .muted { color: #666; }
        .head { margin-bottom: 16px; }
        .summary-grid { display: flex; flex-wrap: wrap; gap: 6px; margin: 12px 0; }
        .summary-card { border: 1px solid #d7d7d7; padding: 7px 8px; min-width: 108px; flex: 1 1 108px; }
        .summary-card__label { color: #666; font-size: 10px; margin-bottom: 2px; }
        .summary-card__value { font-weight: bold; font-size: 14px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #d7d7d7;
            padding: 4px 5px;
            text-align: center;
            vertical-align: top;
            word-break: normal;
            overflow-wrap: normal;
            white-space: nowrap;
        }
        th { background: #f3f3f3; }
        th:first-child, td:first-child, .text-left { text-align: left; }
        .text-right { text-align: right; }
        .section { margin-top: 14px; }
        .note-box { border: 1px solid #d7d7d7; padding: 10px 12px; margin-top: 14px; }
        .epilogo { margin-top: 18px; border: 1px solid #cfc7bd; }
        .epilogo-head { background: #efebe4; padding: 8px 10px; font-weight: bold; }
        .epilogo-body { padding: 10px; }
        .epilogo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 10px; }
        .epilogo-item { border: 1px solid #d7d7d7; padding: 8px; }
        .epilogo-item__label { font-size: 10px; color: #666; margin-bottom: 2px; }
        .epilogo-item__value { font-size: 15px; font-weight: bold; }
        .epilogo-total { border: 2px solid #222; padding: 10px; font-size: 15px; font-weight: bold; text-align: right; }
        .table-schede th:nth-child(1),
        .table-schede td:nth-child(1),
        .table-schede th:nth-child(2),
        .table-schede td:nth-child(2),
        .table-persone th:nth-child(1),
        .table-persone td:nth-child(1),
        .table-persone th:nth-child(2),
        .table-persone td:nth-child(2) {
            width: 7%;
        }
        .table-schede th:nth-child(4),
        .table-schede td:nth-child(4) {
            width: 15%;
        }
        .table-persone th:nth-child(4),
        .table-persone td:nth-child(4) {
            width: 12%;
        }
        .table-persone th:nth-child(9),
        .table-persone td:nth-child(9) {
            width: 14%;
        }
        tr {
            page-break-inside: avoid;
        }
        .muted {
            white-space: nowrap;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
    @php
        $fmtData = static fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('dmy') : '—';
    @endphp
    <div class="head">
        <h1>Rapporto mensile tassa di soggiorno {{ ($vista ?? 'schede') === 'persone' ? 'per persona' : 'per schedina' }}</h1>
        <div class="muted">{{ $struttura->nome_struttura ?? 'Struttura' }}</div>
        <div class="muted">
            Periodo: {{ \Illuminate\Support\Str::title($meseLabel) }} {{ $anno }}
            | Aliquota: {{ number_format((float) ($config->tassa_soggiorno ?? 0), 2, ',', '.') }} €
            | Giorni max: {{ $config->giorni_massimo ?? 'n/d' }}
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card"><div class="summary-card__label">Schedine</div><div class="summary-card__value">{{ $summary['totale_schedine'] ?? 0 }}</div></div>
        <div class="summary-card"><div class="summary-card__label">Ospiti</div><div class="summary-card__value">{{ $summary['totale_ospiti'] ?? 0 }}</div></div>
        <div class="summary-card"><div class="summary-card__label">Paganti</div><div class="summary-card__value">{{ $summary['totale_paganti'] ?? 0 }}</div></div>
        <div class="summary-card"><div class="summary-card__label">Esenti</div><div class="summary-card__value">{{ $summary['totale_esenti'] ?? 0 }}</div></div>
        <div class="summary-card"><div class="summary-card__label">Notti imponibili</div><div class="summary-card__value">{{ $summary['totale_notti_imponibili'] ?? 0 }}</div></div>
        <div class="summary-card"><div class="summary-card__label">Totale tassa</div><div class="summary-card__value">{{ number_format((float) ($summary['totale_tassa'] ?? 0), 2, ',', '.') }} €</div></div>
    </div>

    @if(($vista ?? 'schede') === 'schede')
        <div class="section">
            <h2>Riepilogo per schedina</h2>
            <table class="table-schede">
                <thead>
                    <tr>
                        <th>N. Scheda</th>
                        <th>Arrivo</th>
                        <th>Partenza</th>
                        <th>Riferimento</th>
                        <th>Persone</th>
                        <th>Adulti</th>
                        <th>Minori</th>
                        <th>Paganti</th>
                        <th>Esenti</th>
                        <th>Notti tassate</th>
                        <th>Oltre max</th>
                        <th>Totale tassa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedeSummary as $scheda)
                        <tr>
                            <td>{{ $scheda['scheda'] }}</td>
                            <td>{{ $fmtData($scheda['arrivo']) }}</td>
                            <td>{{ $fmtData($scheda['partenza']) }}</td>
                            <td class="text-left">{{ $scheda['riferimento'] }}</td>
                            <td>{{ $scheda['persone_totali'] }}</td>
                            <td>{{ $scheda['adulti_totali'] }}</td>
                            <td>{{ $scheda['minori_totali'] }}</td>
                            <td>{{ $scheda['soggetti_paganti'] }}</td>
                            <td>{{ $scheda['soggetti_esenti'] }}</td>
                            <td>{{ $scheda['notti_imponibili'] }}</td>
                            <td>{{ $scheda['notti_oltre_max'] }}</td>
                            <td class="text-right">{{ number_format((float) $scheda['tassa_totale'], 2, ',', '.') }} €</td>
                        </tr>
                    @empty
                        <tr><td colspan="12">Nessun dato nel periodo selezionato.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="section">
            <h2>Dettaglio per persona</h2>
            <table class="table-persone">
                <thead>
                    <tr>
                        <th>Arrivo</th>
                        <th>Partenza</th>
                        <th>Nominativo</th>
                        <th>Età</th>
                        <th>Minore</th>
                        <th>Paga</th>
                        <th>Esente</th>
                        <th>Motivo</th>
                        <th>N. tot.</th>
                        <th>N. per.</th>
                        <th>N. tass.</th>
                        <th>Oltre</th>
                        <th>Tar.</th>
                        <th>Tassa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $fmtData($row['arrivo']) }}</td>
                            <td>{{ $fmtData($row['partenza']) }}</td>
                            <td class="text-left">{{ $row['nominativo'] }}</td>
                            <td>{{ $row['eta'] ?? '—' }}</td>
                            <td>{{ !empty($row['minore']) ? 'Sì' : 'No' }}</td>
                            <td>{{ !empty($row['paga']) ? 'Sì' : 'No' }}</td>
                            <td>{{ $row['esente'] ? 'Sì' : 'No' }}</td>
                            <td class="text-left">{{ $row['motivo'] ?? '—' }}</td>
                            <td>{{ $row['notti_totali'] ?? '—' }}</td>
                            <td>{{ $row['notti_periodo'] ?? '—' }}</td>
                            <td>{{ $row['pernottamenti_imponibili'] }}</td>
                            <td>{{ $row['pernottamenti_oltre_max'] }}</td>
                            <td>{{ number_format((float) $row['tariffa'], 2, ',', '.') }} €</td>
                            <td class="text-right">{{ number_format((float) $row['tassa'], 2, ',', '.') }} €</td>
                        </tr>
                    @empty
                        <tr><td colspan="14">Nessun dato nel periodo selezionato.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <h2>Riepilogo esenzioni per codice</h2>
        <table>
            <thead>
                <tr>
                    <th>Codice</th>
                    <th class="text-left">Motivo esenzione</th>
                    <th>Notti nel periodo</th>
                    <th>Quantità</th>
                </tr>
            </thead>
            <tbody>
                @forelse($esenzioniSummary as $esenzione)
                    <tr>
                        <td>{{ $esenzione['codice'] }}</td>
                        <td class="text-left">{{ $esenzione['motivo'] }}</td>
                        <td>{{ $esenzione['notti_periodo'] }}</td>
                        <td>{{ $esenzione['quantita'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Nessuna esenzione nel periodo selezionato.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="epilogo">
        <div class="epilogo-head">Epilogo mensile da versare</div>
        <div class="epilogo-body">
            <div class="epilogo-grid">
                <div class="epilogo-item">
                    <div class="epilogo-item__label">Schedine con imposta</div>
                    <div class="epilogo-item__value">{{ $epilogo['totale_schedine_con_imposta'] ?? 0 }}</div>
                </div>
                <div class="epilogo-item">
                    <div class="epilogo-item__label">Schedine senza imposta</div>
                    <div class="epilogo-item__value">{{ $epilogo['totale_schedine_senza_imposta'] ?? 0 }}</div>
                </div>
                <div class="epilogo-item">
                    <div class="epilogo-item__label">Notti oltre massimo</div>
                    <div class="epilogo-item__value">{{ $epilogo['totale_notti_oltre_max'] ?? 0 }}</div>
                </div>
                <div class="epilogo-item">
                    <div class="epilogo-item__label">Persone paganti</div>
                    <div class="epilogo-item__value">{{ $epilogo['totale_persone_paganti'] ?? 0 }}</div>
                </div>
                <div class="epilogo-item">
                    <div class="epilogo-item__label">Persone esenti</div>
                    <div class="epilogo-item__value">{{ $epilogo['totale_persone_esenti'] ?? 0 }}</div>
                </div>
                <div class="epilogo-item">
                    <div class="epilogo-item__label">Schedine del mese</div>
                    <div class="epilogo-item__value">{{ $summary['totale_schedine'] ?? 0 }}</div>
                </div>
            </div>

            <div class="epilogo-total">
                Totale mensile da versare al Comune:
                {{ number_format((float) ($epilogo['totale_da_versare'] ?? 0), 2, ',', '.') }} €
            </div>
        </div>
    </div>

    <div class="note-box">
        Il presente prospetto riepiloga il totale mensile dell'imposta di soggiorno da riversare al Comune,
        con dettaglio per schedina e per persona. Le righe con pernottamenti oltre il limite massimo
        sono conteggiate separatamente e non generano importo.
    </div>
    </div>
</body>
</html>
