@extends('layouts.master')

@section('title') Controllo interno Tassa di soggiorno @endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Controllo interno Tassa di soggiorno @endslot
@endcomponent

<style>
    .tassa-controllo-toolbar {
        display: flex;
        flex-wrap: nowrap;
        align-items: end;
        gap: 12px;
    }
    .tassa-controllo-toolbar__field {
        flex: 0 0 auto;
    }
    .tassa-controllo-toolbar__actions {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        margin-left: auto;
    }
    .btn-controllo-csv {
        background: #dbeafe;
        color: #0f3d91;
        border-color: transparent;
    }
    .btn-controllo-csv:hover,
    .btn-controllo-csv:focus {
        background: #bfdbfe;
        color: #0b2f73;
    }
    .btn-controllo-print {
        background: #ffe8a3;
        color: #8a4b00;
        border-color: transparent;
    }
    .btn-controllo-print:hover,
    .btn-controllo-print:focus {
        background: #ffd86b;
        color: #713f12;
    }
    .btn-controllo-ufficiale {
        background: #dff3ec;
        color: #0f6b57;
        border-color: transparent;
    }
    .btn-controllo-ufficiale:hover,
    .btn-controllo-ufficiale:focus {
        background: #c8eadf;
        color: #0c5647;
    }
    @media (max-width: 991.98px) {
        .tassa-controllo-toolbar {
            flex-wrap: wrap;
        }
        .tassa-controllo-toolbar__actions {
            flex-wrap: wrap;
            justify-content: flex-start;
            margin-left: 0;
        }
    }
</style>

<div class="row config-page">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="tassa-controllo-toolbar mb-3" action="{{ route('tassa_di_soggiorno.rapporto.controllo') }}">
                    <div class="tassa-controllo-toolbar__field">
                        <label class="form-label">Mese</label>
                        <x-ui.select name="mese">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)$mese === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                            @endfor
                        </x-ui.select>
                    </div>
                    <div class="tassa-controllo-toolbar__field">
                        <label class="form-label">Anno</label>
                        <input type="number" class="form-control" name="anno" value="{{ $anno }}" min="2015" max="2100">
                    </div>
                    <div class="tassa-controllo-toolbar__actions">
                        <button type="submit" class="btn btn-primary"><i class="ri-refresh-line me-1"></i> Aggiorna</button>
                        <a href="{{ route('tassa_di_soggiorno.rapporto', ['mese' => $mese, 'anno' => $anno]) }}" class="btn btn-controllo-ufficiale">
                            <i class="ri-arrow-left-line me-1"></i> Rapporto ufficiale
                        </a>
                        <a href="{{ route('tassa_di_soggiorno.rapporto.controllo.csv', ['mese' => $mese, 'anno' => $anno]) }}" class="btn btn-controllo-csv">
                            <i class="ri-file-download-line me-1"></i> CSV controllo
                        </a>
                        <a href="{{ route('tassa_di_soggiorno.rapporto.controllo.print', ['mese' => $mese, 'anno' => $anno]) }}" target="_blank" class="btn btn-controllo-print">
                            <i class="ri-printer-line me-1"></i> Stampa controllo
                        </a>
                    </div>
                </form>

                <div class="alert alert-info">
                    <strong>Struttura:</strong> {{ $struttura->nome_struttura ?? '—' }}
                    — <strong>Aliquota:</strong> {{ number_format((float) ($config->tassa_soggiorno ?? 0), 2, ',', '.') }} €
                    — <strong>Giorni max:</strong> {{ $config->giorni_massimo ?? 'n/d' }}
                    — <strong>Età max bimbi:</strong> {{ $config->max_age_children ?? 'n/d' }}
                    — <strong>Età min adulti:</strong> {{ $config->min_age_adult ?? 'n/d' }}
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Schedine</div>
                            <div class="fw-semibold fs-5">{{ $summary['totale_schedine'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Ospiti</div>
                            <div class="fw-semibold fs-5">{{ $summary['totale_ospiti'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Paganti</div>
                            <div class="fw-semibold fs-5">{{ $summary['totale_paganti'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Esenti</div>
                            <div class="fw-semibold fs-5">{{ $summary['totale_esenti'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Minori</div>
                            <div class="fw-semibold fs-5">{{ $summary['totale_minori'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Totale tassa</div>
                            <div class="fw-semibold fs-5">{{ number_format((float) ($summary['totale_tassa'] ?? 0), 2, ',', '.') }} €</div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Riepilogo per schedina</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-striped align-middle">
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
                                <th>Totale tassa (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedeSummary as $scheda)
                                <tr>
                                    <td>{{ $scheda['scheda'] }}</td>
                                    <td>{{ $scheda['arrivo'] ? \Carbon\Carbon::parse($scheda['arrivo'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $scheda['partenza'] ? \Carbon\Carbon::parse($scheda['partenza'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $scheda['riferimento'] }}</td>
                                    <td>{{ $scheda['persone_totali'] }}</td>
                                    <td>{{ $scheda['adulti_totali'] }}</td>
                                    <td>{{ $scheda['minori_totali'] }}</td>
                                    <td>{{ $scheda['soggetti_paganti'] }}</td>
                                    <td>{{ $scheda['soggetti_esenti'] }}</td>
                                    <td>{{ $scheda['notti_imponibili'] }}</td>
                                    <td>{{ $scheda['notti_oltre_max'] }}</td>
                                    <td>{{ number_format((float) $scheda['tassa_totale'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted">Nessun dato nel periodo selezionato.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h5 class="mb-3">Dettaglio per persona</h5>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
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
                                <th>Tar. €</th>
                                <th>Tassa €</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>{{ $row['arrivo'] ? \Carbon\Carbon::parse($row['arrivo'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $row['partenza'] ? \Carbon\Carbon::parse($row['partenza'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $row['nominativo'] }}</td>
                                    <td>{{ $row['eta'] ?? '—' }}</td>
                                    <td>{{ $row['minore'] ? 'Sì' : 'No' }}</td>
                                    <td>{{ $row['paga'] ? 'Sì' : 'No' }}</td>
                                    <td>{{ $row['esente'] ? 'Sì' : 'No' }}</td>
                                    <td>{{ $row['motivo'] ?? '—' }}</td>
                                    <td>{{ $row['notti_totali'] }}</td>
                                    <td>{{ $row['notti_periodo'] }}</td>
                                    <td>{{ $row['notti_tassate'] }}</td>
                                    <td>{{ $row['pernottamenti_oltre_max'] }}</td>
                                    <td>{{ number_format((float) $row['tariffa'], 2, ',', '.') }}</td>
                                    <td>{{ number_format((float) $row['tassa'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="14" class="text-center text-muted">Nessun dato nel periodo selezionato.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
