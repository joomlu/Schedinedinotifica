@php
    use Illuminate\Support\Str;

    $utenteSidebar = auth()->user();
    $ruoloSidebar = $utenteSidebar->ruolo ?? null;
    $currentStrutturaId = \App\Support\StrutturaCorrente::getId() ?? ($utenteSidebar->struttura_id ?? null);
    $currentStrutturaSidebar = $currentStrutturaId ? \App\Models\Struttura::query()->find($currentStrutturaId) : null;
    $strutturaCodeSidebar = 'ST';
    if ($currentStrutturaSidebar) {
        $tipologiaSidebar = Str::lower((string) ($currentStrutturaSidebar->tipologia_struttura ?? $currentStrutturaSidebar->tipologia_generale ?? ''));
        $strutturaCodeSidebar = match (true) {
            Str::contains($tipologiaSidebar, 'hotel') => 'H',
            Str::contains($tipologiaSidebar, 'camp') => 'C',
            Str::contains($tipologiaSidebar, 'residence') => 'R',
            Str::contains($tipologiaSidebar, 'appart') => 'A',
            Str::contains($tipologiaSidebar, 'villaggio') => 'V',
            Str::contains($tipologiaSidebar, 'b&b'), Str::contains($tipologiaSidebar, 'bed') => 'B',
            default => 'ST',
        };
    }
    $strutturaBadgeSidebar = $strutturaCodeSidebar . '-' . str_pad((string) $currentStrutturaSidebar?->id, 3, '0', STR_PAD_LEFT);
    $isRoute = fn (...$patterns) => request()->routeIs(...$patterns);
    $openConfig = $isRoute('tassa_di_soggiorno.*', 'gruppi', 'gruppo.*', 'titolo.*', 'tipo_cliente.*', 'tipo_alloggiato.*', 'tipo_documento.*', 'rilasciato.*', 'tipovia', 'tipovia.*', 'geo.comuni.logo*');
    $openClienti = $isRoute('customers', 'newcustomer', 'customer.*', 'customer.export.*');
    $openSchedine = $isRoute('schedina', 'newschedina', 'schedina.*', 'arrivals', 'newarrival', 'arrival.*', 'a_schedina', 'schedina.web', 'web_checkin.*');
    $openInvio = $isRoute('tassa_di_soggiorno.rapporto*', 'questura.*', 'istat.tabella_a.*');
    $openStatistica = $isRoute('presenze.*');
    $openCalendario = $isRoute('calendario.*');
    $openAdmin = $isRoute('admin.*');
    $openSuperAdmin = $isRoute('superadmin.*');
    $openQa = $isRoute('qa.*');
    $superadminNeedsStrutturaSelection = $ruoloSidebar === 'super_admin';
    $superadminHasSelectedStruttura = !$superadminNeedsStrutturaSelection || (bool) $currentStrutturaSidebar;
@endphp
<style>
    .app-menu.navbar-menu {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    .app-menu.navbar-menu #scrollbar {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }

    .app-menu.navbar-menu .struttura-sidebar-meta {
        flex-shrink: 0;
        background: var(--vz-vertical-menu-bg, #fff);
        position: relative;
        z-index: 2;
    }
</style>
<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
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

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isRoute('root') ? 'active' : '' }}" href="{{ route('root') }}">
                        <i data-feather="home" class="icon-dual"></i> <span>Dashboard</span>
                    </a>
                </li>

                @if($ruoloSidebar === 'super_admin')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $openSuperAdmin ? '' : 'collapsed' }}" href="#sidebarSuperadmin" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openSuperAdmin ? 'true' : 'false' }}" aria-controls="sidebarSuperadmin">
                            <i data-feather="shield" class="icon-dual"></i> <span>SuperAdmin</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $openSuperAdmin ? 'show' : '' }}" id="sidebarSuperadmin">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('superadmin.amministratori.index') }}" class="nav-link {{ $isRoute('superadmin.amministratori.*') ? 'active' : '' }}">Amministratori</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.articoli.index') }}" class="nav-link {{ $isRoute('superadmin.articoli.*') ? 'active' : '' }}">Articoli</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.proprietari.index') }}" class="nav-link {{ $isRoute('superadmin.proprietari.*') ? 'active' : '' }}">Proprietari</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.strutture.index') }}" class="nav-link {{ $isRoute('superadmin.strutture.*') ? 'active' : '' }}">Strutture</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.pagamenti.index') }}" class="nav-link {{ $isRoute('superadmin.pagamenti.*') ? 'active' : '' }}">Pagamenti / Licenze</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.impersonazione.index') }}" class="nav-link {{ $isRoute('superadmin.impersonazione.*') ? 'active' : '' }}">Impersonazione</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $openQa ? '' : 'collapsed' }}" href="#sidebarQa" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openQa ? 'true' : 'false' }}" aria-controls="sidebarQa">
                            <i data-feather="check-square" class="icon-dual"></i> <span>QA</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $openQa ? 'show' : '' }}" id="sidebarQa">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('qa.index') }}" class="nav-link {{ $isRoute('qa.index') ? 'active' : '' }}">Dashboard QA</a></li>
                                <li class="nav-item"><a href="{{ route('qa.session') }}" class="nav-link {{ $isRoute('qa.session') ? 'active' : '' }}">Sessione</a></li>
                                <li class="nav-item"><a href="{{ route('qa.accesso') }}" class="nav-link {{ $isRoute('qa.accesso') ? 'active' : '' }}">Accesso</a></li>
                                <li class="nav-item"><a href="{{ route('qa.tenancy') }}" class="nav-link {{ $isRoute('qa.tenancy') ? 'active' : '' }}">Tenancy</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if($ruoloSidebar === 'admin')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $openAdmin ? '' : 'collapsed' }}" href="#sidebarAdmin" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openAdmin ? 'true' : 'false' }}" aria-controls="sidebarAdmin">
                            <i data-feather="briefcase" class="icon-dual"></i> <span>Area Admin</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $openAdmin ? 'show' : '' }}" id="sidebarAdmin">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('admin.proprietari.index') }}" class="nav-link {{ $isRoute('admin.proprietari.*') ? 'active' : '' }}">Proprietari</a></li>
                                <li class="nav-item"><a href="{{ route('admin.strutture.index') }}" class="nav-link {{ $isRoute('admin.strutture.*') ? 'active' : '' }}">Strutture</a></li>
                                <li class="nav-item"><a href="{{ route('admin.pagamenti.index', ['tab' => 'articoli']) }}" class="nav-link {{ $isRoute('admin.pagamenti.*') && request('tab') === 'articoli' ? 'active' : '' }}">Catalogo articoli</a></li>
                                <li class="nav-item"><a href="{{ route('admin.pagamenti.index') }}" class="nav-link {{ $isRoute('admin.pagamenti.*') ? 'active' : '' }}">Pagamenti / Licenze</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if($ruoloSidebar === 'proprietario')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isRoute('proprietario.strutture.*') ? 'active' : '' }}" href="{{ route('proprietario.strutture.index') }}">
                            <i data-feather="layers" class="icon-dual"></i> <span>Le mie strutture</span>
                        </a>
                    </li>
                @endif

                @if($ruoloSidebar === 'super_admin')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isRoute('strutture.seleziona.*') ? 'active' : '' }}" href="{{ route('strutture.seleziona.index') }}">
                            <i data-feather="crosshair" class="icon-dual"></i> <span>Seleziona struttura</span>
                        </a>
                    </li>
                @endif

                @if($superadminNeedsStrutturaSelection && !$superadminHasSelectedStruttura)
                    <li class="nav-item">
                        <div class="mx-3 my-2 p-3 rounded-3 border bg-body-secondary">
                            <div class="fw-semibold mb-1">Struttura non selezionata</div>
                            <div class="small text-muted">Per vedere il menu operativo della struttura seleziona prima una struttura dal pannello dedicato.</div>
                        </div>
                    </li>
                @endif

                @if(!$superadminNeedsStrutturaSelection || $superadminHasSelectedStruttura)
                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isRoute('struttura.edit') ? 'active' : '' }}" href="{{ route('struttura.edit') }}">
                        <i data-feather="building" class="icon-dual"></i> <span>Dati struttura</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $openConfig ? '' : 'collapsed' }}" href="#sidebarConfigurations" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openConfig ? 'true' : 'false' }}" aria-controls="sidebarConfigurations">
                        <i data-feather="settings" class="icon-dual"></i> <span>Configurazioni</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $openConfig ? 'show' : '' }}" id="sidebarConfigurations">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('tassa_di_soggiorno.edit') }}" class="nav-link {{ $isRoute('tassa_di_soggiorno.edit') ? 'active' : '' }}">Tassa di soggiorno</a></li>
                            <li class="nav-item"><a href="{{ route('gruppi') }}" class="nav-link {{ $isRoute('gruppi', 'gruppo.*') ? 'active' : '' }}">Gruppi</a></li>
                            <li class="nav-item"><a href="{{ route('titolo.index') }}" class="nav-link {{ $isRoute('titolo.*') ? 'active' : '' }}">Titoli</a></li>
                            <li class="nav-item"><a href="{{ route('tipo_cliente.index') }}" class="nav-link {{ $isRoute('tipo_cliente.*') ? 'active' : '' }}">Tipo cliente</a></li>
                            <li class="nav-item"><a href="{{ route('tipo_alloggiato.index') }}" class="nav-link {{ $isRoute('tipo_alloggiato.*') ? 'active' : '' }}">Tipo alloggiato</a></li>
                            <li class="nav-item"><a href="{{ route('tipo_documento.index') }}" class="nav-link {{ $isRoute('tipo_documento.*') ? 'active' : '' }}">Tipo documenti</a></li>
                            <li class="nav-item"><a href="{{ route('rilasciato.index') }}" class="nav-link {{ $isRoute('rilasciato.*') ? 'active' : '' }}">Rilasciato da</a></li>
                            <li class="nav-item"><a href="{{ route('tipovia') }}" class="nav-link {{ $isRoute('tipovia', 'tipovia.*') ? 'active' : '' }}">Tipo via</a></li>
                            @if(in_array($ruoloSidebar, ['super_admin', 'admin'], true))
                                <li class="nav-item"><a href="{{ route('geo.comuni.logo') }}" class="nav-link {{ $isRoute('geo.comuni.logo*') ? 'active' : '' }}">Geo Comuni (logo)</a></li>
                            @endif
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $openClienti ? '' : 'collapsed' }}" href="#sidebarCustomers" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openClienti ? 'true' : 'false' }}" aria-controls="sidebarCustomers">
                        <i data-feather="users" class="icon-dual"></i> <span>Clienti</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $openClienti ? 'show' : '' }}" id="sidebarCustomers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('newcustomer') }}" class="nav-link {{ $isRoute('newcustomer') ? 'active' : '' }}">Nuovo</a></li>
                            <li class="nav-item"><a href="{{ route('customers') }}" class="nav-link {{ $isRoute('customers', 'customer.*') ? 'active' : '' }}">Lista</a></li>
                            <li class="nav-item"><a href="{{ route('customer.export.index') }}" class="nav-link {{ $isRoute('customer.export.*') ? 'active' : '' }}">Liste e export</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $openSchedine ? '' : 'collapsed' }}" href="#sidebarTickets" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openSchedine ? 'true' : 'false' }}" aria-controls="sidebarTickets">
                        <i data-feather="calendar" class="icon-dual"></i> <span>Schedine</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $openSchedine ? 'show' : '' }}" id="sidebarTickets">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('schedina') }}" class="nav-link {{ $isRoute('schedina', 'schedina.*') ? 'active' : '' }}">Schedine liste</a></li>
                            <li class="nav-item"><a href="{{ route('schedina.bozze') }}" class="nav-link {{ $isRoute('schedina.bozze') ? 'active' : '' }}">Schedine bozze</a></li>
                            <li class="nav-item"><a href="{{ route('arrivals') }}" class="nav-link {{ $isRoute('arrivals', 'newarrival', 'arrival.*', 'a_schedina') ? 'active' : '' }}">Schedine arrivi</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isRoute('schedina.web', 'web_checkin.*') ? 'active' : '' }}" href="{{ route('schedina.web') }}">
                        <i data-feather="globe" class="icon-dual"></i> <span>Web Check-in</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $openInvio ? '' : 'collapsed' }}" href="#sidebarInvio" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openInvio ? 'true' : 'false' }}" aria-controls="sidebarInvio">
                        <i data-feather="cloud" class="icon-dual"></i> <span>Invio telematico</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $openInvio ? 'show' : '' }}" id="sidebarInvio">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('tassa_di_soggiorno.rapporto') }}" class="nav-link {{ $isRoute('tassa_di_soggiorno.rapporto*') ? 'active' : '' }}">Tassa di soggiorno</a></li>
                            <li class="nav-item"><a href="{{ route('istat.tabella_a.index') }}" class="nav-link {{ $isRoute('istat.tabella_a.*') ? 'active' : '' }}">Tavola A</a></li>
                            <li class="nav-item"><a href="{{ route('questura.index') }}" class="nav-link {{ $isRoute('questura.*') ? 'active' : '' }}">Questura</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $openStatistica ? '' : 'collapsed' }}" href="#sidebarStatistica" data-bs-toggle="collapse" role="button" aria-expanded="{{ $openStatistica ? 'true' : 'false' }}" aria-controls="sidebarStatistica">
                        <i data-feather="pie-chart" class="icon-dual"></i> <span>Statistica</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $openStatistica ? 'show' : '' }}" id="sidebarStatistica">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('presenze.index') }}" class="nav-link {{ $isRoute('presenze.*') ? 'active' : '' }}">Presenze</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $openCalendario ? 'active' : '' }}" href="{{ route('calendario.index') }}">
                        <i data-feather="calendar" class="icon-dual"></i> <span>Calendario</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isRoute('cestino.*') ? 'active' : '' }}" href="{{ route('cestino.index') }}">
                        <i data-feather="trash-2" class="icon-dual"></i> <span>Cestino</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
    @if($currentStrutturaSidebar)
        <div class="border-top px-3 py-3 struttura-sidebar-meta">
            <div class="rounded-3 border bg-body-secondary p-3">
                <div class="small text-muted text-uppercase mb-1">Struttura selezionata</div>
                <div class="fw-semibold text-truncate">{{ $currentStrutturaSidebar->nome_struttura }}</div>
                <div class="small text-muted text-truncate">
                    {{ $currentStrutturaSidebar->citta ?: 'Citta non impostata' }}
                    @if($currentStrutturaSidebar->provincia)
                        · {{ $currentStrutturaSidebar->provincia }}
                    @endif
                </div>
                <div class="small text-muted mt-2">ID: {{ $strutturaBadgeSidebar }}</div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge {{ $currentStrutturaSidebar->servizioAttivo() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                        {{ $currentStrutturaSidebar->servizioAttivo() ? 'Servizio attivo' : 'Servizio offline' }}
                    </span>
                    @if($currentStrutturaSidebar->scadenza_servizio)
                        <span class="badge {{ $currentStrutturaSidebar->scadenza_servizio->isPast() ? 'bg-danger-subtle text-danger' : ($currentStrutturaSidebar->scadenza_servizio->diffInDays(now()) <= 30 ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info') }}">
                            Scadenza {{ $currentStrutturaSidebar->scadenza_servizio->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
