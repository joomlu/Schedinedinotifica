@extends('layouts.master')
@section('title', 'Utenti e consegne')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Gestione @endslot
    @slot('title') Utenti e consegne @endslot
@endcomponent
@php
    $tab = $activeTab ?: 'profilo';
    $utentiSubTab = old('name') || old('display_name') || old('password') || old('shared_username') ? 'nuovo' : 'elenco';
    $consegneSubTab = old('titolo') || old('messaggio') ? 'nuova' : 'elenco';
@endphp
<div class="card">
    <div class="card-header border-0">
        <h4 class="card-title mb-1">Utenti e consegne</h4>
        <p class="text-muted mb-0">Da qui puoi creare le persone che lavorano nella struttura, aggiornare il tuo profilo, lasciare consegne e vedere chi ha usato il programma.</p>
    </div>
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item"><button class="nav-link {{ $tab === 'utenti' ? 'active' : '' }} {{ $canManage ? '' : 'disabled' }}" data-bs-toggle="pill" data-bs-target="#tab-utenti" type="button" title="Crea e gestisci le persone che possono entrare nella struttura.">Utenti</button></li>
                <li class="nav-item"><button class="nav-link {{ $tab === 'profilo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-profilo" type="button" title="Aggiorna i tuoi dati visibili in alto nel sistema.">Profilo</button></li>
                <li class="nav-item"><button class="nav-link {{ $tab === 'messaggi' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-messaggi" type="button" title="Lascia consegne e leggi i messaggi del cambio turno.">Consegne</button></li>
                <li class="nav-item"><button class="nav-link {{ $tab === 'accessi' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-accessi" type="button" title="Controlla chi e entrato e chi e uscito dal programma.">Entrate / uscite</button></li>
                <li class="nav-item"><button class="nav-link {{ $tab === 'operazioni' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-operazioni" type="button" title="Vedi in quali parti del sistema ha lavorato ogni persona.">Attivita</button></li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $tab === 'utenti' ? 'show active' : '' }}" id="tab-utenti">
                <div class="card border shadow-sm mb-0">
                    <div class="card-header border-0 bg-light-subtle">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <h5 class="card-title mb-1">Utenti della struttura</h5>
                                <p class="text-muted mb-0">Nome di accesso comune della struttura: <span class="fw-semibold">{{ $sharedUsername }}</span>. Prima vedi gli utenti gia creati. Se devi aggiungerne uno nuovo, apri la seconda scheda.</p>
                            </div>
                            @unless($canManage)
                                <span class="badge bg-warning-subtle text-warning">Solo proprietario</span>
                            @endunless
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link {{ $utentiSubTab === 'elenco' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#utenti-elenco" type="button" role="tab">Utenti gia creati</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link {{ $utentiSubTab === 'nuovo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#utenti-nuovo" type="button" role="tab">Nuovo utente</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade {{ $utentiSubTab === 'elenco' ? 'show active' : '' }}" id="utenti-elenco" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Persona</th>
                                                <th>Ruolo</th>
                                                <th>Telefono</th>
                                                <th>Stato</th>
                                                <th class="text-end">Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($utenti as $u)
                                                <tr>
                                                    <td>
                                                        <div>{{ $u->displayLabel() }}</div>
                                                        <div class="text-muted small">{{ $u->name }}</div>
                                                    </td>
                                                    <td>{{ $u->ruoloOperativoLabel() }}</td>
                                                    <td>{{ $u->telefono ?: '-' }}</td>
                                                    <td>{{ $u->attivo ? 'Attiva' : 'Disattiva' }}</td>
                                                    <td class="text-end">
                                                        @if($canManage)
                                                            <div class="d-inline-flex gap-1 align-items-center flex-wrap justify-content-end">
                                                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalUtente{{ $u->id }}">Modifica</button>
                                                                <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPasswordUtente{{ $u->id }}">Cambia password</button>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Solo lettura</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted">Nessun utente creato.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $utentiSubTab === 'nuovo' ? 'show active' : '' }}" id="utenti-nuovo" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <p class="text-muted mb-0">Qui il proprietario crea le persone che possono entrare nel programma della struttura. Il nome di accesso resta uguale per tutti, cambia solo la password personale.</p>
                                    </div>
                                    <div class="col-12 col-xl-8">
                                        <form method="POST" action="{{ route('gestione.operativa.utenti.store') }}" class="row g-3">
                                            @csrf
                                            <input type="hidden" name="shared_username" value="{{ $sharedUsername }}">
                                            <div class="col-12">
                                                <label class="form-label">Nome di accesso della struttura</label>
                                                <input type="text" class="form-control" value="{{ $sharedUsername }}" disabled>
                                                <div class="form-text">E lo stesso per proprietario e reception. La persona viene riconosciuta dalla sua password personale.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nome e cognome *</label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" @disabled(!$canManage) required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nome da vedere in alto *</label>
                                                <input type="text" name="display_name" class="form-control" value="{{ old('display_name') }}" @disabled(!$canManage) required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Telefono</label>
                                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" @disabled(!$canManage)>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Ruolo *</label>
                                                <x-ui.select name="ruolo_operativo">
                                                    <option value="reception" @selected(old('ruolo_operativo') === 'reception')>Reception</option>
                                                    <option value="proprietario" @selected(old('ruolo_operativo') === 'proprietario')>Proprietario</option>
                                                </x-ui.select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Email di emergenza</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" @disabled(!$canManage)>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Password personale *</label>
                                                <input type="password" name="password" class="form-control" @disabled(!$canManage) required>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <div class="form-check form-switch form-switch-md mb-2">
                                                    <input class="form-check-input" type="checkbox" name="attivo" id="utenteAttivo" value="1" checked @disabled(!$canManage)>
                                                    <label class="form-check-label" for="utenteAttivo">Persona attiva</label>
                                                </div>
                                            </div>
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary" @disabled(!$canManage)>Aggiungi utente</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($canManage)
                            @foreach($utenti as $u)
                                <div id="modalUtente{{ $u->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifica persona</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                            </div>
                                            <form method="POST" action="{{ route('gestione.operativa.utenti.update', $u->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <input type="hidden" name="shared_username" value="{{ $sharedUsername }}">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nome di accesso della struttura</label>
                                                        <input type="text" class="form-control" value="{{ $sharedUsername }}" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nome e cognome *</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nome da vedere in alto *</label>
                                                        <input type="text" name="display_name" class="form-control" value="{{ $u->display_name }}" required>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Telefono</label>
                                                            <input type="text" name="telefono" class="form-control" value="{{ $u->telefono }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Ruolo *</label>
                                                            <x-ui.select name="ruolo_operativo">
                                                                <option value="reception" @selected($u->ruolo_operativo === 'reception')>Reception</option>
                                                                <option value="proprietario" @selected($u->ruolo_operativo === 'proprietario')>Proprietario</option>
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <label class="form-label">Email di emergenza</label>
                                                        <input type="email" name="email" class="form-control" value="{{ str_ends_with($u->email, '.local') ? '' : $u->email }}">
                                                    </div>
                                                    <div class="mt-3">
                                                        <div class="form-check form-switch form-switch-md">
                                                            <input class="form-check-input" type="checkbox" name="attivo" id="attivoUtente{{ $u->id }}" value="1" @checked($u->attivo)>
                                                            <label class="form-check-label" for="attivoUtente{{ $u->id }}">Persona attiva</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                                    <button type="submit" class="btn btn-primary">Salva</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @foreach($utenti as $u)
                                <div id="modalPasswordUtente{{ $u->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cambia password</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                            </div>
                                            <form method="POST" action="{{ route('gestione.operativa.utenti.password', $u->id) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <p class="text-muted mb-3">Stai cambiando la password di <span class="fw-semibold">{{ $u->displayLabel() }}</span>.</p>
                                                    <div>
                                                        <label class="form-label">Nuova password *</label>
                                                        <input type="password" name="password" class="form-control" required minlength="8">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                                    <button type="submit" class="btn btn-primary">Salva password</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $tab === 'profilo' ? 'show active' : '' }}" id="tab-profilo">
                <div class="row g-4">
                    <div class="col-xl-4">
                        <div class="card border shadow-sm mb-0">
                            <div class="card-header border-0 bg-light-subtle">
                                <h5 class="card-title mb-0">Chi sta lavorando adesso</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img class="rounded-circle flex-shrink-0" src="@if ($utenteCorrente->avatar != ''){{ URL::asset('images/' . $utenteCorrente->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }}@endif" alt="Avatar" width="72" height="72" style="object-fit: cover;">
                                    <div>
                                        <h5 class="mb-1">{{ $utenteCorrente->displayLabel() }}</h5>
                                        <p class="text-muted mb-1">{{ $utenteCorrente->ruoloOperativoLabel() }}</p>
                                        <p class="text-muted mb-0">{{ $struttura->nome_struttura }}</p>
                                    </div>
                                </div>
                                <div class="border rounded-3 p-3 bg-light-subtle">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="text-muted small">Nome completo</div>
                                            <div>{{ $utenteCorrente->name }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted small">Telefono</div>
                                            <div>{{ $utenteCorrente->telefono ?: 'Non disponibile' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted small">Email</div>
                                            <div>{{ str_ends_with($utenteCorrente->email, '.local') ? 'Non disponibile' : $utenteCorrente->email }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted small">Ultimo accesso</div>
                                            <div>{{ $utenteCorrente->ultimo_accesso_at?->format('d/m/Y H:i') ?: 'Non disponibile' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="card border shadow-sm mb-4">
                            <div class="card-header border-0 bg-light-subtle">
                                <h5 class="card-title mb-1">Il mio profilo</h5>
                                <p class="text-muted mb-0">Qui puoi aggiornare i tuoi dati personali, il contatto da usare fuori servizio e la foto che si vede nel sistema.</p>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('gestione.operativa.profile.update') }}" enctype="multipart/form-data" class="row g-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-6">
                                        <label class="form-label">Nome e cognome *</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $utenteCorrente->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nome da vedere in alto *</label>
                                        <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $utenteCorrente->display_name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Telefono</label>
                                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $utenteCorrente->telefono) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email di contatto</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', str_ends_with($utenteCorrente->email, '.local') ? '' : $utenteCorrente->email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Foto profilo</label>
                                        <input type="file" name="avatar" class="form-control" accept="image/png,image/jpeg">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ruolo nel sistema</label>
                                        <input type="text" class="form-control" value="{{ $utenteCorrente->ruoloOperativoLabel() }}" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nome di accesso della struttura</label>
                                        <input type="text" class="form-control" value="{{ $sharedUsername }}" disabled>
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Salva la mia scheda</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card border shadow-sm mb-0">
                            <div class="card-header border-0 bg-light-subtle">
                                <h5 class="card-title mb-1">Password personale</h5>
                                <p class="text-muted mb-0">La password personale identifica chi sta lavorando con il nome di accesso condiviso della struttura.</p>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('gestione.operativa.profile.password') }}" class="row g-3">
                                    @csrf
                                    <div class="col-md-4">
                                        <label class="form-label">Password attuale *</label>
                                        <input type="password" name="current_password" class="form-control" required minlength="8">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nuova password *</label>
                                        <input type="password" name="password" class="form-control" required minlength="8">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Conferma nuova password *</label>
                                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Salva password personale</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $tab === 'messaggi' ? 'show active' : '' }}" id="tab-messaggi">
                <div class="card border shadow-sm mb-0">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-1">Consegne di turno</h5>
                        <p class="text-muted mb-0">Qui trovi prima le consegne aperte. Se devi lasciarne una nuova, apri la seconda scheda.</p>
                    </div>
                    <div class="card-body">
                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link {{ $consegneSubTab === 'elenco' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#consegne-elenco" type="button" role="tab">Consegne registrate</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link {{ $consegneSubTab === 'nuova' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#consegne-nuova" type="button" role="tab">Nuova consegna</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade {{ $consegneSubTab === 'elenco' ? 'show active' : '' }}" id="consegne-elenco" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Data</th>
                                                <th>Titolo</th>
                                                <th>Da</th>
                                                <th>A</th>
                                                <th>Importanza</th>
                                                <th>Stato</th>
                                                <th class="text-end">Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($comande as $comanda)
                                                <tr>
                                                    <td>{{ $comanda->created_at?->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <div>{{ $comanda->titolo }}</div>
                                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($comanda->messaggio, 90) }}</div>
                                                    </td>
                                                    <td>{{ $comanda->mittente?->displayLabel() ?? '-' }}</td>
                                                    <td>{{ $comanda->destinatario?->displayLabel() ?? 'Turno successivo' }}</td>
                                                    <td>{{ ucfirst($comanda->priorita) }}</td>
                                                    <td>
                                                        @if($comanda->stato === 'da_leggere')
                                                            <span class="badge bg-warning-subtle text-warning">Da vedere</span>
                                                        @elseif($comanda->stato === 'letta')
                                                            <span class="badge bg-info-subtle text-info">Vista</span>
                                                        @else
                                                            <span class="badge bg-success-subtle text-success">Chiusa</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-inline-flex gap-1">
                                                            <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalConsegna{{ $comanda->id }}">Apri</button>
                                                            @if($comanda->stato === 'da_leggere')
                                                                <form method="POST" action="{{ route('gestione.operativa.comande.read', $comanda->id) }}">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-soft-info btn-sm">Segna come vista</button>
                                                                </form>
                                                            @endif
                                                            @if($comanda->stato !== 'chiusa')
                                                                <form method="POST" action="{{ route('gestione.operativa.comande.close', $comanda->id) }}">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-soft-success btn-sm">Chiudi</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Nessuna consegna registrata.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $consegneSubTab === 'nuova' ? 'show active' : '' }}" id="consegne-nuova" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <p class="text-muted mb-0">Serve per il cambio turno: una persona esce e lascia le cose importanti alla persona successiva.</p>
                                    </div>
                                    <div class="col-12 col-xl-8">
                                        <form method="POST" action="{{ route('gestione.operativa.comande.store') }}" class="row g-3">
                                            @csrf
                                            <div class="col-12">
                                                <label class="form-label">Titolo *</label>
                                                <input type="text" name="titolo" class="form-control" value="{{ old('titolo') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Per chi</label>
                                                <x-ui.select name="destinatario_id">
                                                    <option value="">Per il turno successivo</option>
                                                    @foreach($utenti as $u)
                                                        <option value="{{ $u->id }}" @selected((string) old('destinatario_id') === (string) $u->id)>{{ $u->displayLabel() }}</option>
                                                    @endforeach
                                                </x-ui.select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Importanza</label>
                                                <x-ui.select name="priorita">
                                                    <option value="bassa" @selected(old('priorita') === 'bassa')>Bassa</option>
                                                    <option value="normale" @selected(old('priorita', 'normale') === 'normale')>Normale</option>
                                                    <option value="alta" @selected(old('priorita') === 'alta')>Alta</option>
                                                </x-ui.select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Messaggio *</label>
                                                <textarea name="messaggio" class="form-control" rows="6" required>{{ old('messaggio') }}</textarea>
                                            </div>
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary">Salva consegna</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($comande as $comanda)
                            <div id="modalConsegna{{ $comanda->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Consegna</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-6">
                                                    <div class="text-muted small">Titolo</div>
                                                    <div class="fw-semibold">{{ $comanda->titolo }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small">Da</div>
                                                    <div>{{ $comanda->mittente?->displayLabel() ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small">A</div>
                                                    <div>{{ $comanda->destinatario?->displayLabel() ?? 'Turno successivo' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Data</div>
                                                    <div>{{ $comanda->created_at?->format('d/m/Y H:i') }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Importanza</div>
                                                    <div>{{ ucfirst($comanda->priorita) }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Stato</div>
                                                    <div>
                                                        @if($comanda->stato === 'da_leggere')
                                                            <span class="badge bg-warning-subtle text-warning">Da vedere</span>
                                                        @elseif($comanda->stato === 'letta')
                                                            <span class="badge bg-info-subtle text-info">Vista</span>
                                                        @else
                                                            <span class="badge bg-success-subtle text-success">Chiusa</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="text-muted small">Testo completo</div>
                                                    <div class="border rounded-3 p-3 bg-light-subtle" style="white-space: pre-wrap;">{{ $comanda->messaggio }}</div>
                                                </div>
                                            </div>

                                            @if($comanda->stato !== 'chiusa')
                                                <div class="border-top pt-4">
                                                    <h6 class="mb-3">Rispondi</h6>
                                                    <form method="POST" action="{{ route('gestione.operativa.comande.store') }}" class="row g-3">
                                                        @csrf
                                                        <input type="hidden" name="titolo" value="R: {{ $comanda->titolo }}">
                                                        <input type="hidden" name="destinatario_id" value="{{ $comanda->mittente_id }}">
                                                        <input type="hidden" name="priorita" value="{{ $comanda->priorita }}">
                                                        <div class="col-12">
                                                            <label class="form-label">Messaggio *</label>
                                                            <textarea name="messaggio" class="form-control" rows="5" required placeholder="Scrivi qui la risposta per il turno successivo o per la persona interessata"></textarea>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-end">
                                                            <button type="submit" class="btn btn-primary">Salva risposta</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $tab === 'accessi' ? 'show active' : '' }}" id="tab-accessi">
                <div class="card border shadow-sm mb-0">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-1">Entrate e uscite dal programma</h5>
                        <p class="text-muted mb-0">Qui vedi quando una persona e entrata e quando e uscita dal sistema.</p>
                    </div>
                    <div class="card-body pt-0">
                        <div class="small text-muted mb-3">Doppio clic su una riga per aprire il dettaglio completo della sessione.</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Persona</th>
                                        <th>Entrata</th>
                                        <th>Uscita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accessi as $accesso)
                                        @php
                                            $durataAccesso = null;
                                            if ($accesso->entrata_at && $accesso->uscita_at) {
                                                $durataAccesso = $accesso->entrata_at->diffForHumans($accesso->uscita_at, [
                                                    'parts' => 3,
                                                    'short' => true,
                                                    'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                                ]);
                                            }
                                        @endphp
                                        <tr role="button" class="cursor-pointer" ondblclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAccesso{{ $accesso->id }}')).show()">
                                            <td>{{ $accesso->utente?->displayLabel() ?? '-' }}</td>
                                            <td>{{ $accesso->entrata_at?->format('d/m/Y H:i') }}</td>
                                            <td>{{ $accesso->uscita_at?->format('d/m/Y H:i') ?: 'Ancora dentro' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">Nessun ingresso registrato.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @foreach($accessi as $accesso)
                            @php
                                $durataAccesso = null;
                                if ($accesso->entrata_at && $accesso->uscita_at) {
                                    $durataAccesso = $accesso->entrata_at->diffForHumans($accesso->uscita_at, [
                                        'parts' => 3,
                                        'short' => true,
                                        'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                    ]);
                                }
                            @endphp
                            <div id="modalAccesso{{ $accesso->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Dettaglio entrata / uscita</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="text-muted small">Persona</div>
                                                    <div class="fw-semibold">{{ $accesso->utente?->displayLabel() ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small">Ruolo</div>
                                                    <div>{{ $accesso->utente?->ruoloOperativoLabel() ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small">Stato sessione</div>
                                                    <div>{{ $accesso->uscita_at ? 'Chiusa' : 'Ancora dentro' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Entrata</div>
                                                    <div>{{ $accesso->entrata_at?->format('d/m/Y H:i:s') ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Uscita</div>
                                                    <div>{{ $accesso->uscita_at?->format('d/m/Y H:i:s') ?: 'Ancora dentro' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Durata</div>
                                                    <div>{{ $durataAccesso ?: 'Sessione aperta' }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted small">IP entrata</div>
                                                    <div>{{ $accesso->ip_entrata ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted small">IP uscita</div>
                                                    <div>{{ $accesso->ip_uscita ?: '-' }}</div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="text-muted small">User agent</div>
                                                    <div class="border rounded-3 p-3 bg-light-subtle text-break">{{ $accesso->user_agent ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $tab === 'operazioni' ? 'show active' : '' }}" id="tab-operazioni">
                <div class="card border shadow-sm mb-0">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-1">Attivita svolte</h5>
                        <p class="text-muted mb-0">Qui trovi dove ha lavorato ogni persona e cosa ha fatto: clienti, schedine, invii, configurazioni e altro.</p>
                    </div>
                    <div class="card-body pt-0">
                        <div class="small text-muted mb-3">Doppio clic su una riga per aprire il dettaglio completo dell'attivita registrata.</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Data</th>
                                        <th>Persona</th>
                                        <th>Parte del programma</th>
                                        <th>Operazione</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($auditLogs as $log)
                                        <tr role="button" class="cursor-pointer" ondblclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAudit{{ $log->id }}')).show()">
                                            <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                            <td>{{ $log->utente?->displayLabel() ?? '-' }}</td>
                                            <td>{{ $log->sezione_label ?? '-' }}</td>
                                            <td>{{ $log->descrizione }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">Nessuna attivita registrata.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @foreach($auditLogs as $log)
                            <div id="modalAudit{{ $log->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Dettaglio attivita</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Data e ora</div>
                                                    <div class="fw-semibold">{{ $log->created_at?->format('d/m/Y H:i:s') ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Persona</div>
                                                    <div>{{ $log->utente?->displayLabel() ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Ruolo</div>
                                                    <div>{{ $log->utente?->ruoloOperativoLabel() ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Parte del programma</div>
                                                    <div>{{ $log->sezione_label ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Metodo</div>
                                                    <div>{{ $log->metodo ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">IP</div>
                                                    <div>{{ $log->ip ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted small">Route name</div>
                                                    <div class="text-break">{{ $log->route_name ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small">Tipo entita</div>
                                                    <div>{{ $log->entita_tipo ?: '-' }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small">ID entita</div>
                                                    <div>{{ $log->entita_id ?: '-' }}</div>
                                                </div>
                                                @if(!empty($log->dettaglio_context))
                                                    <div class="col-md-4">
                                                        <div class="text-muted small">Record</div>
                                                        <div>{{ $log->dettaglio_context['record_type'] ?? '-' }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-muted small">Codice / scheda</div>
                                                        <div>{{ $log->dettaglio_context['scheda'] ?? $log->dettaglio_context['codice'] ?? '-' }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-muted small">Origine operativa</div>
                                                        <div>{{ $log->dettaglio_context['origine'] ?? '-' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="text-muted small">Nominativo</div>
                                                        <div>{{ $log->dettaglio_context['ospite'] ?? '-' }}</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-muted small">Arrivo</div>
                                                        <div>{{ !empty($log->dettaglio_context['arrivo']) ? \Carbon\Carbon::parse($log->dettaglio_context['arrivo'])->format('d/m/Y') : '-' }}</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-muted small">Partenza</div>
                                                        <div>{{ !empty($log->dettaglio_context['partenza']) ? \Carbon\Carbon::parse($log->dettaglio_context['partenza'])->format('d/m/Y') : '-' }}</div>
                                                    </div>
                                                    @if(isset($log->dettaglio_context['persone']))
                                                        <div class="col-md-3">
                                                            <div class="text-muted small">Persone</div>
                                                            <div>{{ $log->dettaglio_context['persone'] }}</div>
                                                        </div>
                                                    @endif
                                                    @if(!empty($log->dettaglio_context['stato']))
                                                        <div class="col-md-3">
                                                            <div class="text-muted small">Stato</div>
                                                            <div>{{ $log->dettaglio_context['stato'] }}</div>
                                                        </div>
                                                    @endif
                                                @endif
                                                <div class="col-12">
                                                    <div class="text-muted small">Descrizione completa</div>
                                                    <div class="border rounded-3 p-3 bg-light-subtle text-break">{{ $log->descrizione ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
