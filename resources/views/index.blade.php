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
        @include('dashboard.operativo')
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
        <div class="card border shadow-sm">
            <div class="card-body">
                <h4 class="mb-2">Scegli una struttura per iniziare</h4>
                <p class="text-muted">Gli accessi operativi saranno disponibili dopo la selezione della struttura autorizzata.</p>
                <a class="btn btn-primary" href="{{ route('strutture.seleziona.index') }}">Seleziona struttura</a>
            </div>
        </div>
    @endif
@endsection
@section('script')
@endsection
