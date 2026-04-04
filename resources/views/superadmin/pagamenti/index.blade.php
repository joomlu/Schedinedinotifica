@extends('layouts.master')
@section('title') Pagamenti @endsection

@php
    $isAdminArea = isset($pagamentiBaseRoute) && $pagamentiBaseRoute === 'admin.pagamenti';
    $areaLabel = $isAdminArea ? 'Admin' : 'SuperAdmin';
    $activeTab = request('tab', isset($assegnazioni) ? 'licenze' : 'riepilogo');
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') {{ $areaLabel }} @endslot
        @slot('title') Pagamenti e Licenze @endslot
    @endcomponent

    @if(!isset($assegnazioni))
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Situazione licenze</h4>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Struttura</th>
                                <th>Città</th>
                                <th>Provincia</th>
                                <th>Attiva</th>
                                <th>Scadenza</th>
                                <th>Piano</th>
                                <th>Stato pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($strutture as $struttura)
                                <tr>
                                    <td>{{ $struttura->nome_struttura }}</td>
                                    <td>{{ $struttura->citta }}</td>
                                    <td>{{ $struttura->provincia }}</td>
                                    <td>{{ $struttura->attiva ? 'Sì' : 'No' }}</td>
                                    <td>{{ $struttura->scadenza_servizio }}</td>
                                    <td>{{ $struttura->piano }}</td>
                                    <td>{{ $struttura->stato_pagamento }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Strutture</div>
                        <div class="fw-semibold fs-4">{{ $summary['totale'] ?? $strutture->count() }}</div>
                        <div class="small text-muted">Portafoglio strutture in gestione.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Licenze attive</div>
                        <div class="fw-semibold fs-4">{{ $summary['licenze_attive'] ?? 0 }}</div>
                        <div class="small text-muted">Licenze operative e attive.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Scadute</div>
                        <div class="fw-semibold fs-4">{{ $summary['scadute'] ?? 0 }}</div>
                        <div class="small text-muted">Servizi o licenze da riallineare.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Documenti conto</div>
                        <div class="fw-semibold fs-4">{{ ($statoConto['righe'] ?? collect())->count() }}</div>
                        <div class="small text-muted">Licenze e proforme collegate.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route($pagamentiBaseRoute . '.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label">Ricerca rapida admin</label>
                        <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Hotel Capadue, proprietario, licenza, proforma...">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Stato</label>
                        <x-ui.select name="attiva">
                            <option value="">Tutte</option>
                            <option value="1" {{ ($filters['attiva'] ?? '') === '1' ? 'selected' : '' }}>Online</option>
                            <option value="0" {{ ($filters['attiva'] ?? '') === '0' ? 'selected' : '' }}>Offline</option>
                        </x-ui.select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Pagamento</label>
                        <x-ui.select name="stato_pagamento">
                            <option value="">Tutti</option>
                            @foreach(($statiPagamento ?? collect()) as $statoOption)
                                <option value="{{ $statoOption }}" {{ ($filters['stato_pagamento'] ?? '') === $statoOption ? 'selected' : '' }}>
                                    {{ in_array($statoOption, ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $statoOption)) }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Scadenza</label>
                        <x-ui.select name="scadenza">
                            <option value="">Tutte</option>
                            <option value="scadute" {{ ($filters['scadenza'] ?? '') === 'scadute' ? 'selected' : '' }}>Scadute</option>
                            <option value="entro_30" {{ ($filters['scadenza'] ?? '') === 'entro_30' ? 'selected' : '' }}>Entro 30 gg</option>
                            <option value="senza_data" {{ ($filters['scadenza'] ?? '') === 'senza_data' ? 'selected' : '' }}>Senza data</option>
                        </x-ui.select>
                    </div>
                    <div class="col-lg-1 d-grid">
                        <button type="submit" class="btn btn-primary">Cerca</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="step-arrow-nav mb-4">
                    <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'riepilogo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pagamenti-pane-riepilogo" type="button" role="tab">Riepilogo</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'licenze' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pagamenti-pane-licenze" type="button" role="tab">Licenze</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'conto' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pagamenti-pane-conto" type="button" role="tab">Storico pagamenti</button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade {{ $activeTab === 'riepilogo' ? 'show active' : '' }}" id="pagamenti-pane-riepilogo" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Struttura</th>
                                        <th>Proprietario</th>
                                        <th>Città</th>
                                        <th>Provincia</th>
                                        <th>Attiva</th>
                                        <th>Scadenza</th>
                                        <th>Piano</th>
                                        <th>Stato pagamento</th>
                                        <th class="text-end">Scheda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($strutture as $struttura)
                                        <tr>
                                            <td>{{ $struttura->nome_struttura }}</td>
                                            <td>{{ $struttura->proprietario?->nome ?: '—' }}</td>
                                            <td>{{ $struttura->citta }}</td>
                                            <td>{{ $struttura->provincia }}</td>
                                            <td>{{ $struttura->attiva ? 'Sì' : 'No' }}</td>
                                            <td>{{ optional($struttura->scadenza_servizio)->format('d/m/Y') ?: '—' }}</td>
                                            <td>{{ $struttura->piano ?: '—' }}</td>
                                            <td>{{ in_array(($struttura->stato_pagamento ?? 'pagato'), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $struttura->stato_pagamento ?? 'pagato')) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route($strutturaEditRoute, ['id' => $struttura->id, 'tab' => 'storico']) }}" class="btn btn-sm btn-outline-secondary">
                                                    Apri struttura
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'licenze' ? 'show active' : '' }}" id="pagamenti-pane-licenze" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Struttura</th>
                                        <th>Proprietario</th>
                                        <th>Servizio / Prodotto</th>
                                        <th>N. licenza</th>
                                        <th>Pagamento</th>
                                        <th>Dal</th>
                                        <th>Al</th>
                                        <th>Prezzo</th>
                                        <th class="text-end">Licenza</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assegnazioni as $assegnazione)
                                        <tr>
                                            <td>{{ $assegnazione->struttura?->nome_struttura ?: 'Servizio generale' }}</td>
                                            <td>{{ $assegnazione->proprietario?->nome ?: '—' }}</td>
                                            <td>{{ $assegnazione->articolo?->nome ?: '—' }}</td>
                                            <td>{{ $assegnazione->numero_licenza ?: '—' }}</td>
                                            <td>{{ in_array(($assegnazione->stato_pagamento ?? 'pagato'), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $assegnazione->stato_pagamento ?? 'pagato')) }}</td>
                                            <td>{{ optional($assegnazione->data_inizio)->format('d/m/Y') ?: '—' }}</td>
                                            <td>{{ optional($assegnazione->data_scadenza)->format('d/m/Y') ?: '—' }}</td>
                                            <td>{{ number_format((float) $assegnazione->prezzo, 2, ',', '.') }} €</td>
                                            <td class="text-end">
                                                <a href="{{ route($pagamentiBaseRoute . '.licenze.print', $assegnazione->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    Apri licenza
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'conto' ? 'show active' : '' }}" id="pagamenti-pane-conto" role="tabpanel">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small text-uppercase mb-1">Totale movimenti</div>
                                    <div class="fw-semibold fs-4">{{ number_format((float) ($statoConto['totale'] ?? 0), 2, ',', '.') }} €</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small text-uppercase mb-1">Licenze</div>
                                    <div class="fw-semibold fs-4">{{ number_format((float) ($statoConto['licenze'] ?? 0), 2, ',', '.') }} €</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small text-uppercase mb-1">Proforme</div>
                                    <div class="fw-semibold fs-4">{{ number_format((float) ($statoConto['proforme'] ?? 0), 2, ',', '.') }} €</div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Data</th>
                                        <th>Proprietario</th>
                                        <th>Struttura</th>
                                        <th>Documento</th>
                                        <th>Stato</th>
                                        <th>Totale</th>
                                        <th class="text-end">Documenti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($statoConto['righe'] ?? collect()) as $riga)
                                        @php
                                            $rigaLabel = in_array(($riga['stato'] ?? ''), ['pagata', 'fatturata', 'ok', 'pagato'], true) ? 'Pagata' : 'Proforma';
                                        @endphp
                                        <tr>
                                            <td>{{ $riga['tipo'] }}</td>
                                            <td>{{ optional($riga['data'])->format('d/m/Y') ?: '—' }}</td>
                                            <td>{{ $riga['proprietario'] ?: '—' }}</td>
                                            <td>{{ $riga['struttura'] ?: '—' }}</td>
                                            <td>{{ $riga['documento'] ?: '—' }}</td>
                                            <td>{{ ($riga['tipo'] ?? '') === 'Proforma proprietario' ? $rigaLabel : (in_array(($riga['stato'] ?? ''), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst((string) ($riga['stato'] ?: '—'))) }}</td>
                                            <td>{{ number_format((float) ($riga['totale'] ?? 0), 2, ',', '.') }} €</td>
                                            <td class="text-end">
                                                @if(!empty($riga['licenza_id']))
                                                    <a href="{{ route($pagamentiBaseRoute . '.licenze.print', $riga['licenza_id']) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                        Apri licenza
                                                    </a>
                                                @endif
                                                @if(!empty($riga['proforma_id']) && !empty($riga['proprietario_id']))
                                                    <a href="{{ route(($isAdminArea ? 'admin.proprietari.proforme.show' : 'superadmin.proprietari.proforme.show'), ['id' => $riga['proprietario_id'], 'fatturazione' => $riga['proforma_id']]) }}" class="btn btn-sm btn-outline-secondary">
                                                        {{ $rigaLabel }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
