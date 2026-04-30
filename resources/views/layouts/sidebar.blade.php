@php
    use Illuminate\Support\Str;

    $utenteSidebar = auth()->user();
    $ruoloSidebar = $utenteSidebar->ruolo ?? null;
    $isRoute = fn (...$patterns) => request()->routeIs(...$patterns);
    $openConfig = $isRoute('tassa_di_soggiorno.*', 'gruppi', 'gruppo.*', 'titolo.*', 'tipo_cliente.*', 'tipo_alloggiato.*', 'tipo_documento.*', 'rilasciato.*', 'tipovia', 'tipovia.*', 'geo.comuni.logo*');
    $openClienti = $isRoute('customers', 'newcustomer', 'customer.*', 'customer.export.*', 'customer.import.*');
    $openSchedine = $isRoute('schedina', 'newschedina', 'schedina.*', 'arrivals', 'newarrival', 'arrival.*', 'a_schedina', 'schedina.web', 'web_checkin.*');
    $openInvio = $isRoute('tassa_di_soggiorno.rapporto*', 'questura.*', 'istat.tabella_a.*');
    $openStatistica = $isRoute('presenze.*');
    $openCalendario = $isRoute('calendario.*');
    $openAdmin = $isRoute('admin.*');
    $openSuperAdmin = $isRoute('superadmin.*');
    $openQa = $isRoute('qa.*');
    $proprietarioSenzaStrutture = $ruoloSidebar === 'proprietario'
        && !\App\Support\StrutturaCorrente::getId()
        && $utenteSidebar?->proprietario_id
        && !\App\Models\Struttura::query()->where('proprietario_id', $utenteSidebar->proprietario_id)->exists();
@endphp
<div class="app-menu navbar-menu" style="min-height:100vh;">
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

    <div id="scrollbar" style="height:calc(100vh - 70px); overflow-y:auto; overflow-x:hidden;">
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
                                <li class="nav-item"><a href="{{ route('superadmin.proprietari.index') }}" class="nav-link {{ $isRoute('superadmin.proprietari.*') ? 'active' : '' }}">Proprietari</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.strutture.index') }}" class="nav-link {{ $isRoute('superadmin.strutture.*') ? 'active' : '' }}">Strutture</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.proforme.index') }}" class="nav-link {{ $isRoute('superadmin.proforme.*') ? 'active' : '' }}">Proforme</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.articoli.index') }}" class="nav-link {{ $isRoute('superadmin.articoli.*') ? 'active' : '' }}">Articoli</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.pagamenti.index') }}" class="nav-link {{ $isRoute('superadmin.pagamenti.*') ? 'active' : '' }}">Pagamenti / Licenze</a></li>
                                <li class="nav-item"><a href="{{ route('superadmin.crm.index') }}" class="nav-link {{ $isRoute('superadmin.crm.*') ? 'active' : '' }}">CRM contatti</a></li>
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
                                <li class="nav-item"><a href="{{ route('admin.proforme.index') }}" class="nav-link {{ $isRoute('admin.proforme.*') ? 'active' : '' }}">Proforme</a></li>
                                <li class="nav-item"><a href="{{ route('admin.pagamenti.index') }}" class="nav-link {{ $isRoute('admin.pagamenti.*') ? 'active' : '' }}">Pagamenti / Licenze</a></li>
                                <li class="nav-item"><a href="{{ route('admin.crm.index') }}" class="nav-link {{ $isRoute('admin.crm.*') ? 'active' : '' }}">CRM contatti</a></li>
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

                @unless($proprietarioSenzaStrutture)
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
                                <li class="nav-item"><a href="{{ route('customers') }}" class="nav-link {{ $isRoute('customers', 'customer.store', 'customer.edit', 'customer.update', 'customer.print', 'customer.storico', 'customer.destroy') ? 'active' : '' }}">Lista</a></li>
                                <li class="nav-item"><a href="{{ route('customer.import.index') }}" class="nav-link {{ $isRoute('customer.import.*') ? 'active' : '' }}">Import clienti</a></li>
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
                @endunless
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>

@once
    @push('styles')
        <style>
            .app-menu.navbar-menu,
            .app-menu.navbar-menu .sidebar-background {
                background-color: var(--vz-vertical-menu-bg);
            }

            .app-menu.navbar-menu #scrollbar .container-fluid {
                min-height: 100%;
                padding-bottom: 0;
            }

            .app-menu.navbar-menu #scrollbar {
                scrollbar-gutter: stable;
            }

            .app-menu.navbar-menu #scrollbar .simplebar-content,
            .app-menu.navbar-menu #scrollbar .simplebar-content-wrapper {
                padding-bottom: 56px !important;
            }

            .app-menu.navbar-menu #navbar-nav {
                padding-bottom: 56px;
            }

            .app-menu.navbar-menu #navbar-nav > .nav-item:last-child {
                margin-bottom: 56px;
            }
        </style>
    @endpush
@endonce
