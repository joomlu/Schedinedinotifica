@extends('layouts.master')
@section('title', 'Liste e export clienti')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Clienti
    @endslot
    @slot('title')
        Liste e export
    @endslot
@endcomponent

@php
    $hasActiveFilters = collect([
        request('q'),
        request('tipo_cliente'),
        request('country'),
        request('city'),
        request('group'),
        request('subgroup'),
        request('subgroup1'),
        request('stato'),
        request('privacy_consent'),
        request('marketing_consent'),
        request('communication_consent'),
        request('channel'),
        request('has_soggiorni'),
    ])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
@endphp

<div class="row g-4">
    <style>
        .customer-export-row {
            cursor: pointer;
        }

        .customer-export-row:hover > td {
            background: rgba(13, 110, 253, 0.04);
        }
    </style>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Liste e export clienti</h4>
                    <p class="text-muted mb-0">Filtra i clienti, controlla chi puoi contattare ed esporta i dati in modo semplice.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <button
                        type="button"
                        class="btn btn-light btn-sm"
                        data-bs-toggle="collapse"
                        data-bs-target="#customerExportFilters"
                        aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
                        aria-controls="customerExportFilters"
                    >
                        <i class="ri-equalizer-line align-middle fs-16 me-1"></i>
                        Filtri
                    </button>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'general'])) }}" class="btn btn-primary btn-sm">
                        <i class="ri-file-excel-2-line align-middle fs-16 me-1"></i>
                        CSV completo
                    </a>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'email'])) }}" class="btn btn-success btn-sm">
                        <i class="ri-mail-send-line align-middle fs-16 me-1"></i>
                        Email
                    </a>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'whatsapp'])) }}" class="btn btn-info btn-sm text-white">
                        <i class="ri-whatsapp-line align-middle fs-16 me-1"></i>
                        WhatsApp
                    </a>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'postal'])) }}" class="btn btn-dark btn-sm">
                        <i class="ri-map-pin-line align-middle fs-16 me-1"></i>
                        Postale
                    </a>
                </div>
            </div>

            <div id="customerExportFilters" class="collapse {{ $hasActiveFilters ? 'show' : '' }}">
                <div class="card-body border-top">
                    <form method="GET" action="{{ route('customer.export.index') }}" class="row g-3" id="customer-export-search-form">
                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="fw-semibold mb-3">Filtri</div>
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-9">
                                        <label class="form-label">Ricerca</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="ri-search-line"></i>
                                            </span>
                                            <input
                                                type="text"
                                                id="customer-export-search"
                                                name="q"
                                                class="form-control border-start-0"
                                                value="{{ request('q') }}"
                                                placeholder="Nome, cognome, email, telefono..."
                                                autocomplete="off"
                                            >
                                            <button
                                                type="button"
                                                class="btn btn-light border"
                                                id="customer-export-search-clear"
                                                aria-label="Pulisci ricerca"
                                                style="{{ request('q') ? '' : 'display:none;' }}"
                                            >
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 d-flex justify-content-end gap-2">
                                        <a href="{{ route('customer.export.index') }}" class="btn btn-light btn-label">
                                            <i class="ri-close-line label-icon align-middle fs-16 me-2"></i>
                                            Pulisci
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-label right">
                                            <i class="ri-filter-3-line label-icon align-middle fs-16 ms-2"></i>
                                            Applica filtri
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="customer-export-results">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small">Clienti filtrati</div>
                        <div class="fw-semibold fs-4">{{ $totaleFiltrati }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small">Con email</div>
                        <div class="fw-semibold fs-4">{{ $totaleConEmail }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small">Con cellulare</div>
                        <div class="fw-semibold fs-4">{{ $totaleConCellulare }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small">Con consenso marketing</div>
                        <div class="fw-semibold fs-4">{{ $totaleMarketing }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Anteprima clienti filtrati</h5>
                    <span class="text-muted small">La tabella mostra un'anteprima. Gli export usano tutti i risultati filtrati.</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">Azioni</th>
                                    <th>Codice</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Geo</th>
                                    <th>Gruppo</th>
                                    <th>Contatti</th>
                                    <th>Consensi</th>
                                    <th>Soggiorni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr class="customer-export-row" data-href="{{ route('customer.edit', $customer->id) }}" title="Apri cliente">
                                        <td>
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('customer.print', $customer->id) }}" class="btn btn-soft-secondary btn-sm js-customer-print" title="Stampa scheda cliente" data-popup-title="Scheda cliente">
                                                    <i class="ri-printer-line fs-16 align-middle"></i>
                                                </a>
                                                <a href="{{ route('customer.storico', $customer->id) }}" class="btn btn-soft-dark btn-sm" title="Storico cliente">
                                                    <i class="ri-history-line fs-16 align-middle"></i>
                                                </a>
                                                <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-soft-info btn-sm" title="Apri cliente">
                                                    <i class="ri-eye-line fs-16 align-middle"></i>
                                                </a>
                                                <form method="POST" action="{{ route('customer.export.destroy', array_merge(request()->except(['page', 'id']), ['id' => $customer->id])) }}" class="m-0"
                                                    data-confirm-text="{{ 'Sei sicuro di voler eliminare ' . (trim($customer->name . ' ' . $customer->surname) ?: 'questo cliente') . '?' }}"
                                                    onsubmit="return window.confirm(this.dataset.confirmText);">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger btn-sm" title="Elimina cliente" aria-label="Elimina cliente">
                                                        <i class="ri-delete-bin-line fs-16 align-middle" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-dark-subtle text-dark">{{ $customer->numero_cliente ?: '—' }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ $customer->full_name ?: '—' }}</div>
                                            <div class="text-muted small">{{ $customer->email ?: ($customer->cellphone ?: 'Nessun contatto') }}</div>
                                        </td>
                                        <td>{{ $customer->type_housed ?: '—' }}</td>
                                        <td>
                                            <div>{{ $customer->display_city ?: '—' }}</div>
                                            <div class="text-muted small">{{ $customer->display_country ?: '—' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $customer->group ?: '—' }}</div>
                                            @if($customer->subgroup)
                                                <div class="text-muted small">{{ $customer->subgroup }}</div>
                                            @endif
                                            @if($customer->subgroup1)
                                                <div class="text-muted small">{{ $customer->subgroup1 }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $customer->phone ?: '—' }}</div>
                                            <div class="text-muted small">{{ $customer->cellphone ?: '—' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <span class="badge {{ $customer->privacy_consent ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">Privacy {{ $customer->privacy_consent ? 'SI' : 'NO' }}</span>
                                                <span class="badge {{ $customer->marketing_consent ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">Marketing {{ $customer->marketing_consent ? 'SI' : 'NO' }}</span>
                                                <span class="badge {{ $customer->communication_consent ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">Comunicazioni {{ $customer->communication_consent ? 'SI' : 'NO' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $customer->schedine_count ?? 0 }}</div>
                                            <div class="text-muted small">Ultimo: {{ $customer->last_arrive_at ?: '—' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Nessun cliente trovato con i filtri selezionati.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($customers, 'links'))
                    <div class="card-footer bg-white border-0">
                        {{ $customers->links('vendor.pagination.bootstrap-5-clean') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bindCustomerExportInteractions() {
        document.querySelectorAll('.customer-export-row[data-href]').forEach((row) => {
            row.addEventListener('click', function (event) {
                const interactive = event.target.closest('a, button, input, select, textarea, label');
                if (interactive) return;

                const href = row.dataset.href;
                if (!href) return;
                window.location.href = href;
            });
        });

        document.querySelectorAll('.js-customer-print').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const href = link.getAttribute('href');
                if (!href) return;

                const width = 980;
                const height = 860;
                const left = Math.max(0, Math.round((window.screen.width - width) / 2));
                const top = Math.max(0, Math.round((window.screen.height - height) / 2));
                const popup = window.open(
                    href,
                    'customer-print-popup',
                    `popup=yes,width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
                );

                if (popup) {
                    popup.focus();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindCustomerExportInteractions();

        const input = document.getElementById('customer-export-search');
        const clearBtn = document.getElementById('customer-export-search-clear');
        const form = document.getElementById('customer-export-search-form');
        const results = document.getElementById('customer-export-results');

        if (!input || !results || !form) {
            return;
        }

        const updateClearButtonState = () => {
            if (!clearBtn) return;
            clearBtn.style.display = input.value.trim() ? '' : 'none';
        };

        const preserveFocus = () => {
            if (document.activeElement !== input) {
                input.focus();
            }
            const cursorPosition = input.value.length;
            input.setSelectionRange(cursorPosition, cursorPosition);
        };

        let debounceTimer = null;
        let latestRequestId = 0;

        const applyResults = (html) => {
            const parser = new DOMParser();
            const parsed = parser.parseFromString(html, 'text/html');
            const nextResults = parsed.getElementById('customer-export-results');
            if (!nextResults) {
                return;
            }

            results.innerHTML = nextResults.innerHTML;
            bindCustomerExportInteractions();
            preserveFocus();
        };

        const submitSearch = function () {
            const url = new URL(window.location.href);
            const q = (input.value || '').trim();

            if (q !== '') {
                url.searchParams.set('q', q);
            } else {
                url.searchParams.delete('q');
            }

            url.searchParams.delete('page');
            history.replaceState({}, '', url.toString());

            const currentRequestId = ++latestRequestId;
            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch customer export results');
                    }

                    return response.text();
                })
                .then((html) => {
                    if (currentRequestId !== latestRequestId) {
                        return;
                    }
                    applyResults(html);
                })
                .catch(() => {
                    console.warn('Customer export search refresh failed.');
                });
        };

        input.addEventListener('input', function () {
            updateClearButtonState();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitSearch, 350);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            clearTimeout(debounceTimer);
            submitSearch();
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                updateClearButtonState();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitSearch, 350);
                preserveFocus();
            });
        }

        updateClearButtonState();
        preserveFocus();
    });
</script>
@endpush
