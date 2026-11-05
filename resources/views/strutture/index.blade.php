@extends('layouts.master')

@section('title') Strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Sistema @endslot
        @slot('title') Strutture @endslot
    @endcomponent

    @php
        $user = auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        $isProprietario = $user && method_exists($user, 'isProprietario') && $user->isProprietario();
        $isStrutturaUser = $user && method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser();
    @endphp

    <div class="row g-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2">Centro strutture</span>
                            <h4 class="mb-2">Accesso rapido alle aree struttura</h4>
                            <p class="text-muted mb-0">
                                Questa pagina raccoglie gli ingressi corretti del sistema per lavorare sulle strutture.
                                Da qui puoi entrare nel pannello giusto in base al tuo ruolo, scegliere la struttura corrente
                                oppure gestire gli utenti struttura.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('strutture.seleziona.index') }}" class="btn btn-primary">Seleziona struttura</a>
                            @if($isSuperAdmin)
                                <a href="{{ route('superadmin.strutture.index') }}" class="btn btn-outline-primary">Pannello superadmin</a>
                            @elseif($isAdmin)
                                <a href="{{ route('admin.strutture.index') }}" class="btn btn-outline-primary">Pannello admin</a>
                            @elseif($isProprietario)
                                <a href="{{ route('proprietario.strutture.index') }}" class="btn btn-outline-primary">Area proprietario</a>
                            @endif
                            @if(!$isStrutturaUser)
                                <a href="{{ route('strutture.utenti.index') }}" class="btn btn-outline-secondary">Utenti struttura</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-2">Selezione</div>
                    <h5 class="mb-2">Struttura corrente</h5>
                    <p class="text-muted mb-3">
                        Imposta la struttura con cui lavorare. Questo passaggio è utile soprattutto per superadmin,
                        admin e proprietario quando devono operare come se fossero dentro una struttura specifica.
                    </p>
                    <a href="{{ route('strutture.seleziona.index') }}" class="btn btn-sm btn-primary">Apri selezione</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-2">Gestione</div>
                    <h5 class="mb-2">Strutture</h5>
                    <p class="text-muted mb-3">
                        L'elenco strutture cambia in base al ruolo: il superadmin vede tutto, l'admin solo il proprio perimetro,
                        il proprietario solo le sue strutture.
                    </p>
                    @if($isSuperAdmin)
                        <a href="{{ route('superadmin.strutture.index') }}" class="btn btn-sm btn-outline-primary">Apri elenco</a>
                    @elseif($isAdmin)
                        <a href="{{ route('admin.strutture.index') }}" class="btn btn-sm btn-outline-primary">Apri elenco</a>
                    @elseif($isProprietario)
                        <a href="{{ route('proprietario.strutture.index') }}" class="btn btn-sm btn-outline-primary">Apri elenco</a>
                    @else
                        <span class="badge bg-light text-muted border">Disponibile dal pannello struttura</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-2">Utenti</div>
                    <h5 class="mb-2">Utenti struttura</h5>
                    <p class="text-muted mb-3">
                        Crea o gestisci gli account operativi collegati alle strutture. Qui trovi anche il reset password
                        degli utenti struttura.
                    </p>
                    @if(!$isStrutturaUser)
                        <a href="{{ route('strutture.utenti.index') }}" class="btn btn-sm btn-outline-secondary">Apri utenti</a>
                    @else
                        <span class="badge bg-light text-muted border">Non disponibile per ruolo struttura</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-2">Operatività</div>
                    <h5 class="mb-2">Lavoro in struttura</h5>
                    <p class="text-muted mb-3">
                        Dopo aver selezionato la struttura corrente puoi entrare nei moduli operativi del sistema:
                        schedine, calendario, notifiche, questura, tassa e tutto il resto del circuito.
                    </p>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-dark">Torna al dashboard</a>
                </div>
            </div>
        </div>
    </div>
@endsection
