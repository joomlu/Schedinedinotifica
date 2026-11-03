@php
    $operations = [
        ['key' => 'clienti', 'title' => 'Clienti', 'icon' => 'ri-user-line',
            'count' => ($dashboardData['clienti'] ?? 0).' in archivio',
            'description' => 'Apri la scheda di un cliente o registra una nuova anagrafica.',
            'actions' => [['newcustomer', 'Nuovo cliente'], ['customers', 'Cerca cliente']]],
        ['key' => 'schedine', 'title' => 'Schedine', 'icon' => 'ri-file-list-3-line',
            'count' => ($dashboardData['schedine'] ?? 0).' registrate',
            'description' => 'Compila una schedina o riprendi il lavoro dall’elenco.',
            'actions' => [['newschedina', 'Nuova schedina'], ['schedina', 'Elenco schedine']]],
        ['key' => 'checkin', 'title' => 'Web Check-in', 'icon' => 'ri-smartphone-line',
            'count' => ($dashboardData['web_checkin'] ?? 0).' richieste aperte',
            'description' => 'Prepara un check-in e gestisci le pratiche online degli ospiti.',
            'actions' => [['web_checkin.create', 'Nuovo Web Check-in'], ['schedina.web', 'Gestisci richieste']]],
    ];
@endphp
<section aria-labelledby="dashboardOperationsTitle" data-dashboard-operativo>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><h4 id="dashboardOperationsTitle" class="mb-1">Operazioni quotidiane</h4><p class="text-muted mb-0">{{ $dashboardStruttura->nome_struttura }}</p></div>
        <div class="text-md-end">
            <span class="badge {{ $dashboardStruttura->servizioAttivo() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $dashboardStruttura->servizioAttivo() ? 'Servizio attivo' : 'Servizio non attivo' }}</span>
            <details class="small text-muted mt-2">
                <summary>Stato del servizio</summary>
                <div class="mt-2">{{ $dashboardData['prodotto_principale'] ?? 'Licenza non impostata' }}</div>
                <div>Scadenza: {{ optional($dashboardProssimaScadenza)->format('d/m/Y') ?: 'Non impostata' }}</div>
                <div>{{ ucfirst(str_replace('_', ' ', $dashboardStruttura->stato_pagamento ?: 'Pagamento non definito')) }}</div>
            </details>
        </div>
    </div>
    <div class="row g-3 mb-4">
        @foreach($operations as $operation)
            <div class="col-lg-4">
                <div class="card h-100 border shadow-sm mb-0" data-dashboard-area="{{ $operation['key'] }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="avatar-sm rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center"><i class="{{ $operation['icon'] }} fs-24" aria-hidden="true"></i></span>
                            <div><h5 class="mb-1">{{ $operation['title'] }}</h5><span class="text-muted small">{{ $operation['count'] }}</span></div>
                        </div>
                        <p class="text-muted">{{ $operation['description'] }}</p>
                        @if($operation['key'] === 'schedine')<a class="small mb-3" href="{{ route('arrivals') }}">Arrivi in lavorazione <span class="badge bg-light text-body ms-1">{{ $dashboardData['arrivi'] ?? 0 }}</span></a>@endif
                        <div class="d-flex gap-2 flex-wrap mt-auto">
                            @foreach($operation['actions'] as [$routeName, $label])<a class="btn {{ $loop->first ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route($routeName) }}">{{ $label }}</a>@endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row g-3">
        <div class="col-md-6"><a href="{{ route('calendario.index') }}" class="card h-100 border shadow-sm text-reset text-decoration-none mb-0" data-dashboard-area="calendario"><div class="card-body d-flex align-items-center gap-3"><i class="ri-calendar-event-line fs-24 text-primary" aria-hidden="true"></i><div class="flex-grow-1"><h5 class="mb-1">Calendario</h5><span class="text-muted">Agenda, avvisi e scadenze.</span></div><i class="ri-arrow-right-line text-primary" aria-hidden="true"></i></div></a></div>
        <div class="col-md-6"><a href="{{ route('notifiche.index') }}" class="card h-100 border shadow-sm text-reset text-decoration-none mb-0" data-dashboard-area="notifiche"><div class="card-body d-flex align-items-center gap-3"><i class="ri-notification-3-line fs-24 text-primary" aria-hidden="true"></i><div class="flex-grow-1"><h5 class="mb-1">Notifiche</h5><span class="text-muted">Avvisi e comunicazioni da seguire.</span></div><span class="badge bg-primary">{{ $dashboardData['notifiche_non_lette'] ?? 0 }} da leggere</span></div></a></div>
    </div>
</section>
