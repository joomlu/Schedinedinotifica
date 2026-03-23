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
                        class="btn btn-light btn-label right"
                        data-bs-toggle="collapse"
                        data-bs-target="#customerExportFilters"
                        aria-expanded="{{ $hasActiveFilters ? 'false' : 'true' }}"
                        aria-controls="customerExportFilters"
                    >
                        <i class="ri-equalizer-line label-icon align-middle fs-16 ms-2"></i>
                        Filtri speciali
                    </button>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'general'])) }}" class="btn btn-primary btn-label right">
                        <i class="ri-file-excel-2-line label-icon align-middle fs-16 ms-2"></i>
                        Esporta CSV completo
                    </a>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'email'])) }}" class="btn btn-success btn-label right">
                        <i class="ri-mail-send-line label-icon align-middle fs-16 ms-2"></i>
                        Esporta email
                    </a>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'whatsapp'])) }}" class="btn btn-info btn-label right">
                        <i class="ri-whatsapp-line label-icon align-middle fs-16 ms-2"></i>
                        Esporta WhatsApp
                    </a>
                    <a href="{{ route('customer.export.csv', array_merge(request()->query(), ['mode' => 'postal'])) }}" class="btn btn-dark btn-label right">
                        <i class="ri-map-pin-line label-icon align-middle fs-16 ms-2"></i>
                        Esporta postale
                    </a>
                </div>
            </div>
            <div id="customerExportFilters" class="collapse {{ $hasActiveFilters ? '' : 'show' }}">
            <div class="card-body border-top">
                <form method="GET" action="{{ route('customer.export.index') }}" class="row g-3">
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="fw-semibold mb-3">Filtri principali</div>
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <label class="form-label">Ricerca</label>
                                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nome, cognome, email, telefono...">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Tipo cliente</label>
                                    <x-ui.select name="tipo_cliente" data-min-search="0">
                                        <option value="">Tutti</option>
                                        @foreach($tipiClienti as $tipo)
                                            <option value="{{ $tipo->descrizione }}" @selected(request('tipo_cliente') === $tipo->descrizione)>{{ $tipo->descrizione }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Nazione</label>
                                    <x-ui.select name="country">
                                        <option value="">Tutte</option>
                                        @foreach($nazioni as $nazione)
                                            <option value="{{ $nazione->nome }}" @selected(request('country') === $nazione->nome)>{{ $nazione->nome }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Città</label>
                                    <x-ui.select name="city">
                                        <option value="">Tutte</option>
                                        @foreach($citta as $row)
                                            <option value="{{ $row->nome }}" @selected(request('city') === $row->nome)>{{ $row->nome }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-3">Segmentazione clienti</div>
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <label class="form-label">Gruppo I</label>
                                    <x-ui.select name="group">
                                        <option value="">Tutti</option>
                                        @foreach($gruppiLivello1 as $row)
                                            <option value="{{ $row->nome }}" @selected(request('group') === $row->nome)>{{ $row->nome }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Gruppo II</label>
                                    <x-ui.select name="subgroup">
                                        <option value="">Tutti</option>
                                        @foreach($gruppiLivello2 as $row)
                                            <option value="{{ $row->nome }}" @selected(request('subgroup') === $row->nome)>{{ $row->nome }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Gruppo III</label>
                                    <x-ui.select name="subgroup1">
                                        <option value="">Tutti</option>
                                        @foreach($gruppiLivello3 as $row)
                                            <option value="{{ $row->nome }}" @selected(request('subgroup1') === $row->nome)>{{ $row->nome }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Stato scheda</label>
                                    <x-ui.select name="stato" data-min-search="0">
                                        <option value="">Tutti</option>
                                        <option value="completo" @selected(request('stato') === 'completo')>Completati</option>
                                        <option value="bozza" @selected(request('stato') === 'bozza')>Bozze / incompleti</option>
                                    </x-ui.select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-3">Consensi e canali</div>
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <label class="form-label">Privacy</label>
                                    <x-ui.select name="privacy_consent" data-min-search="0">
                                        <option value="">Tutti</option>
                                        <option value="1" @selected(request('privacy_consent') === '1')>Consenso privacy: sì</option>
                                        <option value="0" @selected(request('privacy_consent') === '0')>Consenso privacy: no</option>
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Marketing</label>
                                    <x-ui.select name="marketing_consent" data-min-search="0">
                                        <option value="">Tutti</option>
                                        <option value="1" @selected(request('marketing_consent') === '1')>Marketing: sì</option>
                                        <option value="0" @selected(request('marketing_consent') === '0')>Marketing: no</option>
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Comunicazioni</label>
                                    <x-ui.select name="communication_consent" data-min-search="0">
                                        <option value="">Tutti</option>
                                        <option value="1" @selected(request('communication_consent') === '1')>Comunicazioni: sì</option>
                                        <option value="0" @selected(request('communication_consent') === '0')>Comunicazioni: no</option>
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Canale pronto</label>
                                    <x-ui.select name="channel" data-min-search="0">
                                        <option value="">Tutti</option>
                                        <option value="email" @selected(request('channel') === 'email')>Email marketing</option>
                                        <option value="whatsapp" @selected(request('channel') === 'whatsapp')>WhatsApp</option>
                                        <option value="postal" @selected(request('channel') === 'postal')>Postale</option>
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Storico soggiorni</label>
                                    <x-ui.select name="has_soggiorni" data-min-search="0">
                                        <option value="">Tutti</option>
                                        <option value="yes" @selected(request('has_soggiorni') === 'yes')>Con soggiorni</option>
                                        <option value="no" @selected(request('has_soggiorni') === 'no')>Senza soggiorni</option>
                                    </x-ui.select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('customer.export.index') }}" class="btn btn-light btn-label">
                            <i class="ri-close-line label-icon align-middle fs-16 me-2"></i>
                            Pulisci
                        </a>
                        <button type="submit" class="btn btn-primary btn-label right">
                            <i class="ri-filter-3-line label-icon align-middle fs-16 ms-2"></i>
                            Applica filtri
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>

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
                                <th style="width: 120px;">Azioni</th>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
    });
</script>
@endpush
