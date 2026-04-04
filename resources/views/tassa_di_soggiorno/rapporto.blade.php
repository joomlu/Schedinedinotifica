@extends('layouts.master')

@section('title') Rapporto mensile Tassa di soggiorno @endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Rapporto mensile Tassa di soggiorno @endslot
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
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Mese</label>
                                <x-ui.select name="mese">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int)$mese === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label class="form-label">Anno</label>
                                <input type="number" class="form-control" name="anno" value="{{ $anno }}" min="2015" max="2100">
                            </div>
                            <div class="col-xl-7 col-md-12 d-flex justify-content-xl-end flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ri-refresh-line me-1"></i> Aggiorna</button>
                                <a href="{{ route('tassa_di_soggiorno.rapporto.controllo', ['mese' => $mese, 'anno' => $anno]) }}"
                                   class="btn btn-light {{ !empty($missingSchedina) ? 'disabled' : '' }}"
                                   @if(!empty($missingSchedina)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="ri-file-list-3-line me-1"></i> Controllo interno
                                </a>
                                <a href="{{ route('tassa_di_soggiorno.rapporto.csv', ['mese' => $mese, 'anno' => $anno]) }}"
                                   class="btn btn-success {{ !empty($missingSchedina) ? 'disabled' : '' }}"
                                   @if(!empty($missingSchedina)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="ri-download-2-line me-1"></i> Scarica CSV
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

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
