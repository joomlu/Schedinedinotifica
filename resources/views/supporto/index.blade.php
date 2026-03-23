@extends('layouts.master')

@section('title', 'Supporto online')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Centro di supporto @endslot
    @slot('title') Supporto online @endslot
@endcomponent

@php
    $tab = request('tab', $isAdmin ? 'ticket' : (old('titolo') || old('descrizione') ? 'nuovo' : 'ticket'));
    $supportoContesto = ($isSuperAdmin ?? false) ? 'SuperAdmin' : ($isAdmin ? 'Admin' : 'Struttura');
    $pagamentiRoute = ($isSuperAdmin ?? false) ? route('superadmin.pagamenti.index', ['tab' => 'conto']) : ($isAdmin ? route('admin.pagamenti.index', ['tab' => 'conto']) : null);
    $supportoDescrizione = ($isSuperAdmin ?? false)
        ? 'Qui il superamministratore legge l intero flusso di supporto tra strutture e amministratori, può controllare tutte le strutture e intervenire sull intero circuito.'
        : ($isAdmin
            ? 'Qui l amministratore del software prende in carico i ticket delle proprie strutture, coordina le risposte e chiude i casi quando sono risolti.'
            : 'Qui la struttura apre ticket verso l amministrazione del software, segue le risposte e tiene tutta la conversazione ordinata fino alla chiusura.');
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-warning-subtle">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $supportoContesto }}</span>
                    <span class="badge bg-warning text-dark">Supporto</span>
                    <span class="badge bg-danger-subtle text-danger">Ticket operativi importanti</span>
                </div>
                <h4 class="card-title mb-1">Centro supporto online</h4>
                <p class="text-muted mb-0">{{ $supportoDescrizione }}</p>
            </div>
            @unless($isAdmin)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoTicketSupporto">
                    Nuovo ticket supporto
                </button>
            @endunless
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border shadow-sm h-100 mb-0 bg-danger-subtle">
                    <div class="card-body">
                        <div class="text-danger small fw-semibold mb-1">Aperti / in lavorazione</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['aperti'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border shadow-sm h-100 mb-0 bg-info-subtle">
                    <div class="card-body">
                        <div class="text-info small fw-semibold mb-1">In attesa struttura</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['attesa'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border shadow-sm h-100 mb-0 bg-success-subtle">
                    <div class="card-body">
                        <div class="text-success small fw-semibold mb-1">Chiusi</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['chiusi'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border shadow-sm h-100 mb-0 bg-warning-subtle">
                    <div class="card-body">
                        <div class="text-warning small fw-semibold mb-1">Priorita urgente</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['urgenti'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border shadow-sm h-100 mb-0 bg-primary-subtle">
                    <div class="card-body">
                        <div class="text-primary small fw-semibold mb-1">Messaggi da leggere</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['non_letti'] }}</div>
                    </div>
                </div>
            </div>
            @if($isAdmin)
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border shadow-sm h-100 mb-0 bg-warning-subtle">
                        <div class="card-body">
                            <div class="text-warning small fw-semibold mb-1">Servizi in scadenza</div>
                            <div class="fs-3 fw-semibold">{{ $contatori['servizi_in_scadenza'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border shadow-sm h-100 mb-0 bg-dark-subtle">
                        <div class="card-body">
                            <div class="text-body small fw-semibold mb-1">Strutture offline</div>
                            <div class="fs-3 fw-semibold">{{ $contatori['strutture_offline'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border shadow-sm h-100 mb-0 bg-danger-subtle">
                        <div class="card-body">
                            <div class="text-danger small fw-semibold mb-1">Licenze da pagare</div>
                            <div class="fs-3 fw-semibold">{{ $contatori['licenze_da_pagare'] }}</div>
                        </div>
                    </div>
                </div>
            @endif
            @if($isAdmin)
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border shadow-sm h-100 mb-0 bg-dark-subtle">
                        <div class="card-body">
                            <div class="text-body small fw-semibold mb-1">Assegnati a me</div>
                            <div class="fs-3 fw-semibold">{{ $contatori['assegnati_a_me'] }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="card border shadow-sm mt-4 mb-0">
            <div class="card-header border-0 bg-light-subtle">
                <h5 class="card-title mb-1">Filtri</h5>
                <p class="text-muted mb-0">Usa i filtri per restringere i ticket per stato, priorita, struttura o testo cercato.</p>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('supporto.index') }}" class="row g-3 align-items-end">
                    @if(!$isAdmin)
                        <input type="hidden" name="tab" value="ticket">
                    @endif
                    @if($isAdmin)
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Struttura</label>
                            <x-ui.select name="struttura_id">
                                <option value="">Tutte le strutture</option>
                                @foreach($struttureFiltro as $item)
                                    <option value="{{ $item->id }}" @selected($filters['struttura_id'] === (int) $item->id)>{{ $item->nome_struttura }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label">Stato</label>
                        <x-ui.select name="stato">
                            <option value="">Tutti</option>
                            @foreach($stati as $key => $label)
                                <option value="{{ $key }}" @selected($filters['stato'] === $key)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label">Priorita</label>
                        <x-ui.select name="priorita">
                            <option value="">Tutte</option>
                            @foreach($priorita as $key => $label)
                                <option value="{{ $key }}" @selected($filters['priorita'] === $key)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label">Ricerca</label>
                        <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Codice, titolo o testo...">
                    </div>
                    <div class="col-lg-auto col-md-12 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary">Aggiorna</button>
                        <a href="{{ route('supporto.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if($isAdmin)
            <div class="card border shadow-sm mt-4 mb-0">
                <div class="card-header border-0 bg-light-subtle">
                    <h5 class="card-title mb-1">Collegamenti di gestione</h5>
                    <p class="text-muted mb-0">Da qui passi subito dal supporto al controllo commerciale e alle notifiche del tuo perimetro.</p>
                </div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @if($pagamentiRoute)
                        <a href="{{ $pagamentiRoute }}" class="btn btn-soft-primary">Apri stato conto</a>
                    @endif
                    <a href="{{ route('notifiche.index') }}" class="btn btn-soft-info">Apri notifiche</a>
                    <a href="{{ route('calendario.index', ['contesto' => 'personale']) }}" class="btn btn-soft-secondary">Apri calendario</a>
                </div>
            </div>
        @endif
    </div>
</div>

@if($isAdmin)
    <div class="card border shadow-sm mb-0">
        <div class="card-header border-0 bg-light-subtle">
            <h5 class="card-title mb-1">Ticket delle strutture</h5>
            <p class="text-muted mb-0">
                @if($isSuperAdmin ?? false)
                    Qui il superamministratore vede tutte le richieste del sistema, indipendentemente dall amministratore assegnato.
                @else
                    Qui l amministratore vede solo le richieste delle proprie strutture, controlla la priorita, prende in carico i ticket e risponde alla struttura.
                @endif
            </p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket n.</th>
                                            <th>Struttura</th>
                                            <th>Oggetto</th>
                                            <th>Emesso da</th>
                                            <th>Categoria</th>
                                            <th>Priorita</th>
                                            <th>Stato</th>
                                            <th>Gestione admin</th>
                                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $ticket->ticket_code }}</div>
                                    <div class="text-muted small">{{ $ticket->updated_at?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>{{ $ticket->struttura?->nome_struttura ?? '-' }}</td>
                                <td>
                                    <div>{{ $ticket->titolo }}</div>
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($ticket->descrizione, 100) }}</div>
                                </td>
                                <td>{{ $ticket->openedBy?->displayLabel() ?? '-' }}</td>
                                <td>{{ $ticket->categoriaLabel() }}</td>
                                <td>{{ $ticket->prioritaLabel() }}</td>
                                <td>
                                    @if($ticket->stato === 'aperto')
                                        <span class="badge bg-danger-subtle text-danger">Aperto</span>
                                    @elseif($ticket->stato === 'in_lavorazione')
                                        <span class="badge bg-warning-subtle text-warning">In lavorazione</span>
                                    @elseif($ticket->stato === 'in_attesa_struttura')
                                        <span class="badge bg-info-subtle text-info">In attesa struttura</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Chiuso</span>
                                    @endif
                                    @if($ticket->hasUnreadForAdmin())
                                        <div class="small text-danger mt-1">Nuova risposta</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $ticket->assignedAdmin?->displayLabel() ?? 'Non assegnato' }}</div>
                                    <div class="text-muted small">
                                        @if($ticket->assigned_admin_id)
                                            Preso in carico
                                        @else
                                            Da assegnare
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('supporto.show', $ticket->id) }}" class="btn btn-soft-primary">Apri ticket</a>
                                        @if(!$ticket->assigned_admin_id && $ticket->stato !== 'chiuso')
                                            <form method="POST" action="{{ route('supporto.assign', $ticket->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-soft-info">Prendi in carico</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Nessun ticket trovato per il filtro selezionato.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer border-0 bg-white">{{ $tickets->links() }}</div>
        @endif
    </div>
@else
    <div class="card border shadow-sm mb-0">
        <div class="card-body">
            <div class="step-arrow-nav mb-4">
                <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {{ $tab === 'ticket' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#supporto-ticket" type="button">Ticket aperti</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $tab === 'nuovo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#supporto-nuovo" type="button">Nuovo ticket</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $tab === 'storico' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#supporto-storico" type="button">Storico chiusi</button>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade {{ $tab === 'ticket' ? 'show active' : '' }}" id="supporto-ticket">
                    <div class="card border shadow-sm mb-0">
                        <div class="card-header border-0 bg-light-subtle">
                            <h5 class="card-title mb-1">Ticket della struttura</h5>
                            <p class="text-muted mb-0">Qui vedi le richieste ancora aperte, le risposte dell'amministratore e lo stato di avanzamento.</p>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket n.</th>
                                            <th>Oggetto</th>
                                            <th>Emesso da</th>
                                            <th>Categoria</th>
                                            <th>Priorita</th>
                                            <th>Stato</th>
                                            <th class="text-end">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $openCount = 0; @endphp
                                        @forelse($tickets as $ticket)
                                            @continue($ticket->stato === 'chiuso')
                                            @php $openCount++; @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $ticket->ticket_code }}</div>
                                                    <div class="text-muted small">{{ $ticket->updated_at?->format('d/m/Y H:i') }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $ticket->titolo }}</div>
                                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($ticket->descrizione, 100) }}</div>
                                                </td>
                                                <td>{{ $ticket->openedBy?->displayLabel() ?? '-' }}</td>
                                                <td>{{ $ticket->categoriaLabel() }}</td>
                                                <td>{{ $ticket->prioritaLabel() }}</td>
                                                <td>
                                                    @if($ticket->stato === 'aperto')
                                                        <span class="badge bg-danger-subtle text-danger">Aperto</span>
                                                    @elseif($ticket->stato === 'in_lavorazione')
                                                        <span class="badge bg-warning-subtle text-warning">In lavorazione</span>
                                                    @elseif($ticket->stato === 'in_attesa_struttura')
                                                        <span class="badge bg-info-subtle text-info">Risposta arrivata</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">Chiuso</span>
                                                    @endif
                                                    @if($ticket->hasUnreadForStruttura())
                                                        <div class="small text-danger mt-1">Nuova risposta</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('supporto.show', $ticket->id) }}" class="btn btn-soft-primary">Apri ticket</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Nessun ticket trovato per il filtro selezionato.</td>
                                            </tr>
                                        @endforelse
                                        @if($tickets->count() > 0 && $openCount === 0)
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Non ci sono ticket aperti con il filtro selezionato.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $tab === 'nuovo' ? 'show active' : '' }}" id="supporto-nuovo">
                    <div class="card border shadow-sm mb-0">
                        <div class="card-header border-0 bg-light-subtle">
                            <h5 class="card-title mb-1">Nuovo ticket</h5>
                            <p class="text-muted mb-0">Scrivi qui il problema in modo chiaro. Se indichi modulo e priorita, l'amministratore riesce a intervenire piu velocemente.</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('supporto.store') }}" class="row g-3">
                                @csrf
                                <div class="col-md-8">
                                    <label class="form-label">Titolo *</label>
                                    <input type="text" name="titolo" class="form-control" value="{{ old('titolo') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Categoria *</label>
                                    <x-ui.select name="categoria">
                                        @foreach($categorie as $key => $label)
                                            <option value="{{ $key }}" @selected(old('categoria') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Priorita *</label>
                                    <x-ui.select name="priorita">
                                        @foreach($priorita as $key => $label)
                                            <option value="{{ $key }}" @selected(old('priorita', 'normale') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Modulo o pagina coinvolta</label>
                                    <input type="text" name="modulo_riferimento" class="form-control" value="{{ old('modulo_riferimento') }}" placeholder="Esempio: Clienti, Schedine, Questura, Tabella A Emilia-Romagna...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descrizione del problema *</label>
                                    <textarea name="descrizione" class="form-control" rows="8" required placeholder="Spiega cosa non funziona, cosa stavi facendo e quale risultato ti aspettavi.">{{ old('descrizione') }}</textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Apri ticket</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $tab === 'storico' ? 'show active' : '' }}" id="supporto-storico">
                    <div class="card border shadow-sm mb-0">
                        <div class="card-header border-0 bg-light-subtle">
                            <h5 class="card-title mb-1">Storico ticket chiusi</h5>
                            <p class="text-muted mb-0">Qui restano le richieste gia risolte, utili per ricontrollare una procedura o una risposta ricevuta in passato.</p>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket n.</th>
                                            <th>Oggetto</th>
                                            <th>Emesso da</th>
                                            <th>Categoria</th>
                                            <th>Chiuso il</th>
                                            <th class="text-end">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $closedCount = 0; @endphp
                                        @forelse($tickets as $ticket)
                                            @continue($ticket->stato !== 'chiuso')
                                            @php $closedCount++; @endphp
                                            <tr>
                                                <td>{{ $ticket->ticket_code }}</td>
                                                <td>
                                                    <div>{{ $ticket->titolo }}</div>
                                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($ticket->descrizione, 100) }}</div>
                                                </td>
                                                <td>{{ $ticket->openedBy?->displayLabel() ?? '-' }}</td>
                                                <td>{{ $ticket->categoriaLabel() }}</td>
                                                <td>{{ $ticket->chiuso_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('supporto.show', $ticket->id) }}" class="btn btn-soft-primary">Apri ticket</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Nessun ticket trovato per il filtro selezionato.</td>
                                            </tr>
                                        @endforelse
                                        @if($tickets->count() > 0 && $closedCount === 0)
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Non ci sono ticket chiusi con il filtro selezionato.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if($tickets->hasPages())
                <div class="mt-3">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
@endif

@unless($isAdmin)
    <div id="modalNuovoTicketSupporto" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuovo ticket supporto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <form method="POST" action="{{ route('supporto.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Titolo *</label>
                                <input type="text" name="titolo" class="form-control" value="{{ old('titolo') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Categoria *</label>
                                <x-ui.select name="categoria">
                                    @foreach($categorie as $key => $label)
                                        <option value="{{ $key }}" @selected(old('categoria') === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priorita *</label>
                                <x-ui.select name="priorita">
                                    @foreach($priorita as $key => $label)
                                        <option value="{{ $key }}" @selected(old('priorita', 'normale') === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Modulo o pagina coinvolta</label>
                                <input type="text" name="modulo_riferimento" class="form-control" value="{{ old('modulo_riferimento') }}" placeholder="Esempio: Clienti, Schedine, Questura, Tabella A Emilia-Romagna...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrizione del problema *</label>
                                <textarea name="descrizione" class="form-control" rows="8" required placeholder="Spiega cosa non funziona, cosa stavi facendo e quale risultato ti aspettavi.">{{ old('descrizione') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                        <button type="submit" class="btn btn-primary">Apri ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endunless
@endsection
