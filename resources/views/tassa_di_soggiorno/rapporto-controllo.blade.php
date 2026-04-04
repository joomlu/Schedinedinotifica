@extends('layouts.master')

@section('title') Controllo interno Tassa di soggiorno @endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Controllo interno Tassa di soggiorno @endslot
@endcomponent

<style>
    @media (min-width: 1200px) {
        .tassa-controllo-periodo .col-mese {
            flex: 0 0 220px;
            width: 220px;
        }
        .tassa-controllo-periodo .col-anno {
            flex: 0 0 120px;
            width: 120px;
        }
        .tassa-controllo-periodo .col-azioni {
            flex: 1 1 auto;
            width: auto;
        }
    }
</style>

<div class="row config-page">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="card border-0 bg-light-subtle mb-3">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-calendar-event-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Periodo e filtri</h5>
                    </div>
                    <div class="card-body pt-2">
                        <form method="GET" class="row g-3 align-items-end tassa-controllo-periodo" action="{{ route('tassa_di_soggiorno.rapporto.controllo') }}">
                            <div class="col-xl-3 col-md-6 col-mese">
                                <label class="form-label">Mese</label>
                                <x-ui.select name="mese">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int)$mese === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-xl-2 col-md-6 col-anno">
                                <label class="form-label">Anno</label>
                                <input type="number" class="form-control" name="anno" value="{{ $anno }}" min="2015" max="2100">
                            </div>
                            <div class="col-xl-7 col-md-12 col-azioni d-flex justify-content-xl-end flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ri-refresh-line me-1"></i> Aggiorna</button>
                                <a href="{{ route('tassa_di_soggiorno.rapporto', ['mese' => $mese, 'anno' => $anno]) }}" class="btn btn-light">
                                    <i class="ri-arrow-left-line me-1"></i> Rapporto ufficiale
                                </a>
                                <a href="{{ route('tassa_di_soggiorno.rapporto.controllo.csv', ['mese' => $mese, 'anno' => $anno]) }}" class="btn btn-success">
                                    <i class="ri-file-download-line me-1"></i> CSV controllo
                                </a>
                                <a href="{{ route('tassa_di_soggiorno.rapporto.controllo.print', ['mese' => $mese, 'anno' => $anno]) }}" target="_blank" class="btn btn-info text-white">
                                    <i class="ri-printer-line me-1"></i> Stampa controllo
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Struttura:</strong> {{ $struttura->nome_struttura ?? '—' }}
                    — <strong>Aliquota:</strong> {{ number_format((float) ($config->tassa_soggiorno ?? 0), 2, ',', '.') }} €
                    — <strong>Giorni max:</strong> {{ $config->giorni_massimo ?? 'n/d' }}
                    — <strong>Età max bimbi:</strong> {{ $config->max_age_children ?? 'n/d' }}
                    — <strong>Età min adulti:</strong> {{ $config->min_age_adult ?? 'n/d' }}
                </div>

                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                    <div style="width: 360px; max-width: 100%;">
                        <div class="input-group position-relative">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ri-search-line"></i>
                            </span>
                            <input
                                type="text"
                                id="tassaControlloSearch"
                                class="form-control border-start-0"
                                placeholder="Cerca per schedina, arrivo, partenza o nominativo..."
                                value="{{ $q ?? request('q', '') }}"
                                autocomplete="off"
                            >
                            <button
                                class="btn btn-light border"
                                type="button"
                                id="tassaControlloSearchClear"
                                aria-label="Pulisci"
                                style="{{ filled($q ?? request('q')) ? '' : 'display:none;' }}"
                            >
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const input = document.getElementById('tassaControlloSearch');
            const clearBtn = document.getElementById('tassaControlloSearchClear');
            if (!input) return;

            let debounceTimer = null;
            const submitSearch = function () {
                const url = new URL(window.location.href);
                const q = (input.value || '').trim();

                if (q) {
                    url.searchParams.set('q', q);
                } else {
                    url.searchParams.delete('q');
                }

                url.searchParams.delete('page');
                window.location.assign(url.toString());
            };

            input.addEventListener('input', function () {
                if (clearBtn) {
                    clearBtn.style.display = input.value.trim() ? '' : 'none';
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitSearch, 350);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                clearTimeout(debounceTimer);
                submitSearch();
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    input.value = '';
                    clearBtn.style.display = 'none';
                    submitSearch();
                });
            }
        } catch (error) {
            console.error('tassa controllo search init error', error);
        }
    });
</script>
@endpush
