@php
    use App\Models\StrutturaComanda;

    $utenteTopbar = auth()->user();
    $nomeTopbar = $utenteTopbar?->display_name ?? $utenteTopbar?->name ?? 'Utente';
    $ruoloTopbar = match ($utenteTopbar->ruolo ?? null) {
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'proprietario' => 'Proprietario',
        default => (($utenteTopbar->ruolo_operativo ?? '') === 'proprietario' ? 'Proprietario' : 'Reception'),
    };
    $avatarTopbar = !empty($utenteTopbar?->avatar) ? asset('images/' . $utenteTopbar->avatar) : null;
    $gestioneTabTopbar = method_exists($utenteTopbar, 'canManageGestioneOperativa') && $utenteTopbar->canManageGestioneOperativa($utenteTopbar->struttura_id ?? null)
        ? 'utenti'
        : 'profilo';

    $notificheTopbar = collect();
    $notificheNonLette = 0;

    if ($utenteTopbar && $utenteTopbar->struttura_id) {
        $notificheTopbar = StrutturaComanda::query()
            ->with(['mittente'])
            ->visibleForUser($utenteTopbar)
            ->orderByRaw("case when stato = 'da_leggere' then 0 when stato = 'letta' then 1 else 2 end")
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $notificheNonLette = StrutturaComanda::query()
            ->unreadForUser($utenteTopbar)
            ->count();
    }
@endphp
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('root') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('images/tango.png') }}" alt="Tango" width="40" height="40" style="object-fit: contain;">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('images/tango.png') }}" alt="Tango" width="70" height="70" style="object-fit: contain;">
                        </span>
                    </a>
                    <a href="{{ route('root') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('images/tango.png') }}" alt="Tango" width="40" height="40" style="object-fit: contain;">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('images/tango.png') }}" alt="Tango" width="70" height="70" style="object-fit: contain;">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="ms-1 header-item">
                    <a href="{{ route('calendario.index') }}" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" title="Calendario">
                        <i class="ri-calendar-2-line fs-22"></i>
                    </a>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-expanded="false" title="Centro servizi">
                        <i class="bx bx-category-alt fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end">
                        <div class="p-3 border-bottom">
                            <h6 class="m-0 fw-semibold fs-15">Centro servizi</h6>
                        </div>
                        <div class="p-2">
                            <div class="row g-0">
                                <div class="col-6">
                                    <a class="dropdown-icon-item" href="{{ route('help.index') }}">
                                        <span class="avatar-title bg-info-subtle text-info rounded-circle mx-auto mb-2" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                                            <i class="ri-question-line fs-22"></i>
                                        </span>
                                        <span>Aiuto</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a class="dropdown-icon-item" href="{{ route('supporto.index') }}">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded-circle mx-auto mb-2" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                                            <i class="ri-customer-service-2-line fs-22"></i>
                                        </span>
                                        <span>Supporto online</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifiche">
                        <i class="bx bx-bell fs-22"></i>
                        @if($notificheNonLette > 0)
                            <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">{{ $notificheNonLette }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
                            <h6 class="m-0 fs-16 fw-semibold">Notifiche</h6>
                            <a href="{{ route('supporto.index') }}" class="btn btn-sm btn-light">Ticket e supporto</a>
                        </div>

                        <div data-simplebar style="max-height: 320px;">
                            @forelse($notificheTopbar as $notifica)
                                @php
                                    $badgeClass = match ($notifica->stato) {
                                        'da_leggere' => 'bg-danger-subtle text-danger',
                                        'letta' => 'bg-warning-subtle text-warning',
                                        default => 'bg-success-subtle text-success',
                                    };
                                    $statoLabel = match ($notifica->stato) {
                                        'da_leggere' => 'Da vedere',
                                        'letta' => 'Vista',
                                        default => 'Chiusa',
                                    };
                                @endphp
                                <div class="dropdown-item text-wrap py-3 border-bottom border-light-subtle">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="avatar-xs flex-shrink-0">
                                            <span class="avatar-title rounded-circle bg-info-subtle text-info fs-16">
                                                <i class="ri-notification-3-line"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                                <div class="fw-semibold text-truncate">{{ $notifica->titolo }}</div>
                                                <span class="badge {{ $badgeClass }}">{{ $statoLabel }}</span>
                                            </div>
                                            <div class="text-muted small mb-1">Da {{ $notifica->mittente?->display_name ?? $notifica->mittente?->name ?? 'Sistema' }}</div>
                                            <div class="text-muted small mb-1">{{ optional($notifica->created_at)->format('d/m/Y H:i') }}</div>
                                            <div class="text-muted small text-truncate mb-2">{{ $notifica->messaggio }}</div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-soft-primary btn-sm topbar-open-notifica" data-bs-toggle="modal" data-bs-target="#topbar-notifica-{{ $notifica->id }}">Apri</button>
                                                <a href="{{ route('notifiche.index') }}" class="btn btn-light btn-sm">Vai al centro</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">
                                    Nessuna notifica interna.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <span class="flex-shrink-0">
                                @if($avatarTopbar)
                                    <img class="rounded-circle header-profile-user" src="{{ $avatarTopbar }}" alt="Avatar" style="width:36px;height:36px;object-fit:cover;">
                                @else
                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                        {{ strtoupper(substr($nomeTopbar, 0, 1)) }}
                                    </span>
                                @endif
                            </span>
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $nomeTopbar }}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ $ruoloTopbar }}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">{{ $nomeTopbar }}</h6>
                        <a class="dropdown-item" href="{{ route('gestione.operativa.index', ['tab' => 'profilo']) }}"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i><span class="align-middle">Profilo</span></a>
                        <a class="dropdown-item" href="{{ route('gestione.operativa.index', ['tab' => 'messaggi']) }}"><i class="ri-chat-1-line text-muted fs-16 align-middle me-1"></i><span class="align-middle">Consegne</span></a>
                        <a class="dropdown-item" href="{{ route('notifiche.index') }}"><i class="ri-notification-3-line text-muted fs-16 align-middle me-1"></i><span class="align-middle">Notifiche</span></a>
                        <a class="dropdown-item" href="{{ route('gestione.operativa.index', ['tab' => $gestioneTabTopbar]) }}"><i class="ri-settings-3-line text-muted fs-16 align-middle me-1"></i><span class="align-middle">Impostazioni di gestione</span></a>
                        <a class="dropdown-item" href="{{ route('supporto.index') }}"><i class="ri-customer-service-2-line text-muted fs-16 align-middle me-1"></i><span class="align-middle">Supporto online</span></a>
                        <a class="dropdown-item" href="{{ route('help.index') }}"><i class="ri-question-line text-muted fs-16 align-middle me-1"></i><span class="align-middle">Aiuto</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout.get') }}"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i><span class="align-middle">Logout</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
@foreach($notificheTopbar as $notifica)
    @php
        $badgeClass = match ($notifica->stato) {
            'da_leggere' => 'bg-danger-subtle text-danger',
            'letta' => 'bg-warning-subtle text-warning',
            default => 'bg-success-subtle text-success',
        };
        $statoLabel = match ($notifica->stato) {
            'da_leggere' => 'Da vedere',
            'letta' => 'Vista',
            default => 'Chiusa',
        };
    @endphp
    <div class="modal fade" id="topbar-notifica-{{ $notifica->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $notifica->titolo }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge {{ $badgeClass }}">{{ $statoLabel }}</span>
                        <span class="badge bg-light text-body">{{ ucfirst($notifica->priorita) }}</span>
                    </div>
                    <div class="small text-muted mb-2">Da {{ $notifica->mittente?->display_name ?? $notifica->mittente?->name ?? 'Sistema' }}</div>
                    <div class="small text-muted mb-3">{{ optional($notifica->created_at)->format('d/m/Y H:i') }}</div>
                    <div style="white-space: pre-wrap;">{{ $notifica->messaggio }}</div>
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="{{ route('notifiche.index') }}" class="btn btn-light">Apri centro notifiche</a>
                    <div class="d-flex gap-2">
                        @if($notifica->stato === 'da_leggere')
                            <form method="POST" action="{{ route('gestione.operativa.comande.read', $notifica->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-warning">Segna vista</button>
                            </form>
                        @endif
                        @if($notifica->stato !== 'chiusa')
                            <form method="POST" action="{{ route('gestione.operativa.comande.close', $notifica->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">Chiudi</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
    #page-topbar .dropdown-menu:focus,
    #page-topbar .dropdown-menu.show:focus,
    #page-topbar .btn-topbar:focus,
    #page-topbar .btn-topbar:focus-visible,
    #page-topbar .topbar-user .btn:focus,
    #page-topbar .topbar-user .btn:focus-visible {
        outline: none;
        box-shadow: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topbar = document.getElementById('page-topbar');

        if (topbar && window.bootstrap && window.bootstrap.Dropdown) {
            const triggers = Array.from(topbar.querySelectorAll('[data-bs-toggle="dropdown"]'));

            const closeOthers = function (currentTrigger) {
                triggers.forEach(function (trigger) {
                    if (trigger === currentTrigger) return;
                    window.bootstrap.Dropdown.getOrCreateInstance(trigger).hide();
                });
            };

            triggers.forEach(function (trigger) {
                if (trigger.dataset.topbarIsolatedClick === '1') return;
                trigger.dataset.topbarIsolatedClick = '1';

                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const instance = window.bootstrap.Dropdown.getOrCreateInstance(trigger);
                    const menu = trigger.nextElementSibling;
                    const isShown = !!(menu && menu.classList.contains('show'));

                    closeOthers(trigger);

                    if (isShown) {
                        instance.hide();
                    } else {
                        instance.show();
                    }
                });
            });
        }

        document.querySelectorAll('.topbar-open-notifica').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.stopPropagation();
                const trigger = document.getElementById('page-header-notifications-dropdown');
                if (trigger) {
                    bootstrap.Dropdown.getOrCreateInstance(trigger).hide();
                }
            });
        });
    });
</script>
