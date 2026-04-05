@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    @php
        $dashboardData = $dashboardData ?? null;
        $superadminDashboardData = $superadminDashboardData ?? null;
        $dashboardStruttura = $dashboardData['struttura'] ?? null;
        $dashboardOwner = $dashboardData['owner'] ?? null;
        $dashboardAdmin = $dashboardData['ownerAdmin'] ?? null;
        $dashboardLicenza = $dashboardData['licenza_principale'] ?? null;
        $dashboardServizioInizio = $dashboardData['servizio_data_inizio'] ?? null;
        $dashboardProssimaScadenza = $dashboardData['prossima_scadenza'] ?? null;
        $dashboardStrutturaCode = strtoupper(substr((string) ($dashboardStruttura?->tipologia_struttura ?? $dashboardStruttura?->tipologia_generale ?? 'STR'), 0, 1));
        $dashboardStrutturaBadge = $dashboardStruttura ? $dashboardStrutturaCode . '-' . str_pad((string) $dashboardStruttura->id, 3, '0', STR_PAD_LEFT) : null;
        $dashboardServizioBadgeClass = 'bg-info-subtle text-info';

        if ($dashboardStruttura?->servizioAttivo()) {
            $dashboardServizioBadgeClass = 'bg-success-subtle text-success';
        } elseif ($dashboardStruttura?->scadenza_servizio?->isPast()) {
            $dashboardServizioBadgeClass = 'bg-danger-subtle text-danger';
        } elseif ($dashboardStruttura?->scadenza_servizio && $dashboardStruttura->scadenza_servizio->diffInDays(now()) <= 30) {
            $dashboardServizioBadgeClass = 'bg-warning-subtle text-warning';
        }
    @endphp

    @if($dashboardStruttura)
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-2">Servizio struttura</div>
                        <div class="fw-semibold fs-5">{{ $dashboardStruttura->nome_struttura }}</div>
                        <div class="small text-muted mt-2">
                            {{ $dashboardStruttura->citta ?: 'Citta non impostata' }}
                            @if($dashboardStruttura->provincia)
                                · {{ $dashboardStruttura->provincia }}
                            @endif
                            @if($dashboardStrutturaBadge)
                                · {{ $dashboardStrutturaBadge }}
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="badge {{ $dashboardStruttura->servizioAttivo() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                {{ $dashboardStruttura->servizioAttivo() ? 'Servizio attivo' : 'Servizio offline' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-2">Licenza in uso</div>
                        <div class="fw-semibold fs-5">{{ $dashboardData['prodotto_principale'] ?? 'Nessuna licenza attiva' }}</div>
                        <div class="small text-muted mt-2">{{ $dashboardLicenza?->codice_tracking ?: 'Tracking non disponibile' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-2">Scadenza e stato</div>
                        <div class="fw-semibold fs-5">{{ optional($dashboardProssimaScadenza)->format('d/m/Y') ?: 'Non impostata' }}</div>
                        <div class="small text-muted mt-2">
                            {{ $dashboardLicenza && $dashboardLicenza->data_scadenza ? 'Licenza struttura' : 'Servizio struttura' }}
                            · {{ ucfirst(str_replace('_', ' ', $dashboardStruttura->stato_pagamento ?: 'non definito')) }}
                        </div>
                        <div class="small text-muted mt-2">{{ (int) ($dashboardData['totale_licenze'] ?? 0) }} totale licenze</div>
                        @if($dashboardServizioInizio)
                            <div class="mt-3">
                                <span class="badge {{ $dashboardServizioBadgeClass }}">
                                    Servizio dal {{ $dashboardServizioInizio->format('d/m/Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-2">Alert da seguire</div>
                        <div class="fw-semibold fs-5">{{ $dashboardData['notifiche_non_lette'] ?? 0 }} notifiche</div>
                        <div class="small text-muted mt-2">{{ $dashboardData['supporto_aperto'] ?? 0 }} ticket aperti · {{ $dashboardData['licenze_da_pagare'] ?? 0 }} licenze da pagare</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Clienti registrati</div>
                        <div class="fw-bold fs-3">{{ $dashboardData['clienti'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Schedine totali</div>
                        <div class="fw-bold fs-3">{{ $dashboardData['schedine'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Arrivi in lavorazione</div>
                        <div class="fw-bold fs-3">{{ $dashboardData['arrivi'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">Web check-in aperti</div>
                        <div class="fw-bold fs-3">{{ $dashboardData['web_checkin'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @foreach(($dashboardData['sections'] ?? []) as $section)
                <div class="col-xxl-3 col-lg-4 col-md-6">
                    <a href="{{ $section['route'] }}" class="text-decoration-none text-reset">
                        <div class="card h-100 border shadow-sm mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                    <div class="avatar-sm bg-light-subtle text-primary rounded d-flex align-items-center justify-content-center">
                                        <i class="{{ $section['icon'] }} fs-22"></i>
                                    </div>
                                    @if(!empty($section['badge']))
                                        <span class="badge bg-light text-body">{{ $section['badge'] }}</span>
                                    @endif
                                </div>
                                <h5 class="mb-2">{{ $section['title'] }}</h5>
                                <p class="text-muted mb-3">{{ $section['description'] }}</p>
                                @if(!empty($section['links']))
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach($section['links'] as $link)
                                            <span class="badge bg-light text-body">{{ $link['label'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <span class="text-primary fw-semibold">Apri sezione <i class="ri-arrow-right-line align-middle"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @elseif($superadminDashboardData)
        <div class="row g-3 mb-4">
            @foreach(($superadminDashboardData['stats'] ?? []) as $stat)
                <div class="col-xl-3 col-md-6">
                    <div class="card border shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase mb-2">{{ $stat['title'] }}</div>
                            <div class="fw-bold fs-3">{{ $stat['value'] }}</div>
                            <div class="small text-muted mt-2">{{ $stat['description'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="text-muted small text-uppercase mb-2">Controllo centrale</div>
                                <h4 class="mb-1">Dashboard SuperAdmin</h4>
                                <p class="text-muted mb-0">Amministratori, proprietari, strutture, proforme e licenze in una sola vista centrale.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-start">
                                <a href="{{ route('superadmin.amministratori.index') }}" class="btn btn-primary">Amministratori</a>
                                <a href="{{ route('superadmin.proprietari.index') }}" class="btn btn-light">Proprietari</a>
                                <a href="{{ route('superadmin.strutture.index') }}" class="btn btn-light">Strutture</a>
                                <a href="{{ route('superadmin.pagamenti.index') }}" class="btn btn-info text-white">Pagamenti / Licenze</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Prossime scadenze licenze</h5>
                        <p class="text-muted mb-0">Primi documenti da controllare nel circuito economico del superadmin.</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Licenza</th>
                                        <th>Struttura</th>
                                        <th>Proprietario</th>
                                        <th>Servizio</th>
                                        <th>Pagamento</th>
                                        <th>Scadenza</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($superadminDashboardData['prossime_scadenze'] ?? collect()) as $licenza)
                                        <tr>
                                            <td>{{ $licenza->numero_licenza ?: '—' }}</td>
                                            <td>{{ $licenza->struttura?->nome_struttura ?: 'Servizio generale' }}</td>
                                            <td>{{ $licenza->proprietario?->nome ?: '—' }}</td>
                                            <td>{{ $licenza->articolo?->nome ?: '—' }}</td>
                                            <td>{{ in_array(($licenza->stato_pagamento ?? ''), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', (string) ($licenza->stato_pagamento ?: 'da_pagare'))) }}</td>
                                            <td>{{ optional($licenza->data_scadenza)->format('d/m/Y') ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted">Nessuna scadenza da mostrare.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @foreach(($superadminDashboardData['sections'] ?? []) as $section)
                <div class="col-xxl-3 col-lg-4 col-md-6">
                    <a href="{{ $section['route'] }}" class="text-decoration-none text-reset">
                        <div class="card h-100 border shadow-sm mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                    <div class="avatar-sm bg-light-subtle text-primary rounded d-flex align-items-center justify-content-center">
                                        <i class="{{ $section['icon'] }} fs-22"></i>
                                    </div>
                                    @if(!empty($section['badge']))
                                        <span class="badge bg-light text-body">{{ $section['badge'] }}</span>
                                    @endif
                                </div>
                                <h5 class="mb-2">{{ $section['title'] }}</h5>
                                <p class="text-muted mb-3">{{ $section['description'] }}</p>
                                <span class="text-primary fw-semibold">Apri sezione <i class="ri-arrow-right-line align-middle"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                        <div>
                            <h4 class="mb-1">Dashboard</h4>
                            <p class="text-muted mb-0">Accedi rapidamente alle aree principali.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a href="{{ route('newschedina') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-primary-subtle text-primary rounded"><i class="ri-file-add-line"></i></div>
                            <div>
                                <h6 class="mb-1">Nuova Schedina</h6>
                                <p class="text-muted mb-0">Crea una nuova schedina</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a href="{{ route('schedina') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-info-subtle text-info rounded"><i class="ri-list-check"></i></div>
                            <div>
                                <h6 class="mb-1">Schedine</h6>
                                <p class="text-muted mb-0">Vedi tutte le schedine</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a href="{{ route('arrivals') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-success-subtle text-success rounded"><i class="ri-plane-line"></i></div>
                            <div>
                                <h6 class="mb-1">Schedine Arrivi</h6>
                                <p class="text-muted mb-0">Gestisci arrivi in attesa</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a href="{{ route('newcustomer') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-warning-subtle text-warning rounded"><i class="ri-user-add-line"></i></div>
                            <div>
                                <h6 class="mb-1">Nuovo Cliente</h6>
                                <p class="text-muted mb-0">Registra un cliente</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a href="{{ route('customers') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-danger-subtle text-danger rounded"><i class="ri-group-line"></i></div>
                            <div>
                                <h6 class="mb-1">Clienti</h6>
                                <p class="text-muted mb-0">Lista clienti</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endif
@endsection
@section('script')
@endsection
