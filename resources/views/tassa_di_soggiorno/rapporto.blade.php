@extends('layouts.master')

@section('title') Rapporto Tassa di soggiorno @endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Rapporto Tassa di soggiorno @endslot
@endcomponent

<style>
    .tassa-rapporto-table {
        font-size: 0.93rem;
    }
    .tassa-rapporto-table th,
    .tassa-rapporto-table td {
        padding: 0.45rem 0.5rem;
        vertical-align: middle;
    }
    .tassa-rapporto-table .col-scheda,
    .tassa-rapporto-table .col-eta,
    .tassa-rapporto-table .col-num {
        white-space: nowrap;
    }
    .tassa-rapporto-table .col-motivo {
        min-width: 180px;
    }
</style>

@if(!empty($missingSchedina))
    <div class="alert alert-warning"><strong>Tabella schedina assente.</strong> Esegui le migrazioni o importa il dump iniziale prima di generare il rapporto.</div>
@endif

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
                        <form method="GET" class="row g-3 align-items-end" action="{{ route('tassa_di_soggiorno.rapporto') }}">
                            <div class="col-md-3">
    <label class="form-label" for="tassa-data-da">Dal</label>
    <x-calendario id="tassa-data-da" name="data_da" variant="period-start" group="rapporto-tassa" :value="$dataDa" min-date="2015-01-01" max-date="2100-12-31" :required="true" />
</div>
<div class="col-md-3">
    <label class="form-label" for="tassa-data-a">Al</label>
    <x-calendario id="tassa-data-a" name="data_a" variant="period-end" group="rapporto-tassa" :value="$dataA" min-date="2015-01-01" max-date="2100-12-31" :required="true" />
</div>
<div class="col-12 d-flex flex-wrap gap-2">
    @foreach(['Questo mese' => [now()->startOfMonth(), now()->endOfMonth()], 'Mese precedente' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()], "Quest’anno" => [now()->startOfYear(), now()->endOfYear()]] as $label => $dates)
        <a class="btn btn-outline-secondary btn-sm" href="{{ request()->url() }}?{{ http_build_query(['data_da' => $dates[0]->toDateString(), 'data_a' => $dates[1]->toDateString()]) }}">{{ $label }}</a>
    @endforeach
    <small class="text-muted align-self-center">Inclusione per data di arrivo. Intervalli annuali e personalizzati: consultazione interna; validità amministrativa dell’importazione StayTour non attestata.</small>
</div>

                            <div class="col-xl-7 col-md-12 d-flex justify-content-xl-end flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ri-refresh-line me-1"></i> Aggiorna</button>
                                <a href="{{ route('tassa_di_soggiorno.rapporto.controllo', ['data_da' => $dataDa, 'data_a' => $dataA]) }}"
                                   class="btn btn-light {{ !empty($missingSchedina) ? 'disabled' : '' }}"
                                   @if(!empty($missingSchedina)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="ri-file-list-3-line me-1"></i> Controllo interno
                                </a>
                                <a href="{{ $storico ? route('tassa_di_soggiorno.export.download', $storico->id) : route('tassa_di_soggiorno.rapporto.csv', ['data_da' => $dataDa, 'data_a' => $dataA]) }}"
                                   class="btn btn-success {{ !empty($missingSchedina) ? 'disabled' : '' }}"
                                   @if(!empty($missingSchedina)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="ri-download-2-line me-1"></i> Scarica CSV
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                @if($storico)
                    <div class="alert alert-info">Report storico immutabile · versione {{ $storico->versione }}. I filtri di ricerca consultano la versione conservata; il controllo interno riguarda il calcolo corrente.</div>
                @else
                <form method="POST" action="{{ route('tassa_di_soggiorno.export.consolida') }}" class="mb-3">
                    @csrf
                    <input type="hidden" name="data_da" value="{{ $dataDa }}">
                    <input type="hidden" name="data_a" value="{{ $dataA }}">
                    <button class="btn btn-outline-primary" type="submit">Consolida e scarica CSV</button>
                    <small class="text-muted">La consultazione e il CSV corrente sono ricalcolati; il consolidamento conserva una versione immutabile. Un nuovo consolidamento crea una versione successiva, senza attestare un invio al Comune.</small>
                </form>
                @endif
                @if($storico)
                    <div class="mb-3">
                        @foreach(array_keys($storico->snapshot['calcoli'] ?? []) as $schedinaId)
                            <a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('schedina.tassa.print', ['id' => $schedinaId, 'export_id' => $storico->id]) }}">Ricevuta storica · Schedina {{ $schedinaId }}</a>
                            <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="{{ route('schedina.tassa.anteprima', ['id' => $schedinaId, 'export_id' => $storico->id]) }}">Anteprima storica · Schedina {{ $schedinaId }}</a>
                        @endforeach
                    </div>
                @endif
                @if($exports->isNotEmpty())
                    <details class="mb-3"><summary>Export consolidati e versioni</summary>
                        @foreach($exports as $export)
                            <a class="d-block" href="{{ route('tassa_di_soggiorno.rapporto', ['export_id' => $export->id]) }}">{{ $export->data_da }} — {{ $export->data_a }} · versione {{ $export->versione }}</a>
                        @endforeach
                    </details>
                @endif
                <div class="alert alert-success">Totale Tassa del periodo: <strong data-tassa-periodo="{{ $totalePeriodo }}">€ {{ number_format($totalePeriodo, 2, ',', '.') }}</strong> · Tutti i movimenti del periodo, prima della ricerca e della paginazione.</div>
                <div class="alert alert-info">
                    <strong>Struttura:</strong> {{ $struttura->nome_struttura ?? '—' }} — <strong>Aliquota:</strong> {{ $config->tassa_soggiorno ?? 'n/d' }} € — <strong>Giorni max:</strong> {{ $config->giorni_massimo ?? 'n/d' }}
                </div>

                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                    <div style="width: 360px; max-width: 100%;">
                        <div class="input-group position-relative">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ri-search-line"></i>
                            </span>
                            <input
                                type="text"
                                id="tassaRapportoSearch"
                                class="form-control border-start-0"
                                placeholder="Cerca per schedina, arrivo, partenza o nominativo..."
                                value="{{ $q ?? request('q', '') }}"
                                autocomplete="off"
                            >
                            <button
                                class="btn btn-light border"
                                type="button"
                                id="tassaRapportoSearchClear"
                                aria-label="Pulisci"
                                style="{{ filled($q ?? request('q')) ? '' : 'display:none;' }}"
                            >
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle tassa-rapporto-table">
                        <thead>
                            <tr>
                                <th class="col-scheda">N. Scheda</th>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>Nominativo</th>
                                <th class="text-end col-eta">Età</th>
                                <th>Esente</th>
                                <th class="col-motivo">Motivo</th>
                                <th class="text-end col-num">Pern. imp.</th>
                                <th class="text-end col-num">Oltre max</th>
                                <th class="text-end col-num">Tassa €</th>
                                <th class="text-end col-num">Tariffa €</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($righe as $riga)
                                <tr>
                                    <td class="col-scheda">{{ $riga['scheda'] }}</td>
                                    <td>{{ $riga['arrivo'] ? \Carbon\Carbon::parse($riga['arrivo'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $riga['partenza'] ? \Carbon\Carbon::parse($riga['partenza'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $riga['nominativo'] }}</td>
                                    <td class="text-end col-eta">{{ $riga['eta'] ?? '—' }}</td>
                                    <td>{{ $riga['esente'] ? 'Sì' : 'No' }}</td>
                                    <td class="col-motivo">{{ $riga['motivo'] ?? '—' }}</td>
                                    <td class="text-end col-num">{{ $riga['pernottamenti_imponibili'] }}</td>
                                    <td class="text-end col-num">{{ $riga['pernottamenti_oltre_max'] }}</td>
                                    <td class="text-end col-num">{{ number_format($riga['tassa'], 2, ',', '.') }}</td>
                                    <td class="text-end col-num">{{ number_format($riga['tariffa'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted">Nessun dato per il periodo selezionato.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($righe->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <div class="text-muted small d-flex align-items-center mb-0" style="min-height: 38px; line-height: 1;">
                            Mostrando {{ $righe->firstItem() ?? 0 }}&ndash;{{ $righe->lastItem() ?? 0 }} di {{ $righe->total() }} risultati
                        </div>
                        <div class="ms-auto d-flex align-items-center justify-content-end" style="min-height: 38px;">
                            {{ $righe->links('vendor.pagination.bootstrap-5-clean') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const input = document.getElementById('tassaRapportoSearch');
            const clearBtn = document.getElementById('tassaRapportoSearchClear');
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
            console.error('tassa rapporto search init error', error);
        }
    });
</script>
@endpush
