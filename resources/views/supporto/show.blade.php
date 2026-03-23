@extends('layouts.master')

@section('title', 'Ticket di supporto')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Centro di supporto @endslot
    @slot('title') Ticket {{ $ticket->ticket_code }} @endslot
@endcomponent

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card border shadow-sm mb-4">
            <div class="card-header border-0 bg-warning-subtle d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-warning text-dark">{{ $ticket->ticket_code }}</span>
                        <span class="badge bg-light text-body">{{ $ticket->categoriaLabel() }}</span>
                        <span class="badge bg-light text-body">{{ $ticket->prioritaLabel() }}</span>
                    </div>
                    <h5 class="card-title mb-1">Scheda ticket</h5>
                    <p class="text-muted mb-0">Riepilogo completo del caso, della presa in carico amministrativa e dello stato attuale della conversazione.</p>
                </div>
                @if($ticket->stato === 'aperto')
                    <span class="badge bg-danger-subtle text-danger">Aperto</span>
                @elseif($ticket->stato === 'in_lavorazione')
                    <span class="badge bg-warning-subtle text-warning">In lavorazione</span>
                @elseif($ticket->stato === 'in_attesa_struttura')
                    <span class="badge bg-info-subtle text-info">In attesa struttura</span>
                @else
                    <span class="badge bg-success-subtle text-success">Chiuso</span>
                @endif
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Titolo</div>
                    <div class="fw-semibold">{{ $ticket->titolo }}</div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Codice</div>
                        <div>{{ $ticket->ticket_code }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Priorita</div>
                        <div>{{ $ticket->prioritaLabel() }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Categoria</div>
                        <div>{{ $ticket->categoriaLabel() }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Modulo</div>
                        <div>{{ $ticket->modulo_riferimento ?: '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Aperto da</div>
                        <div>{{ $ticket->openedBy?->displayLabel() ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Struttura</div>
                        <div>{{ $ticket->struttura?->nome_struttura ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Creato il</div>
                        <div>{{ $ticket->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Ultimo aggiornamento</div>
                        <div>{{ $ticket->updated_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($isAdmin)
                        <div class="col-12">
                            <div class="text-muted small">Admin assegnato</div>
                            <div>{{ $ticket->assignedAdmin?->displayLabel() ?? 'Non ancora assegnato' }}</div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="text-muted small">Gestione supporto</div>
                            <div>{{ $ticket->assignedAdmin?->displayLabel() ? 'Seguito da ' . $ticket->assignedAdmin->displayLabel() : 'In attesa di presa in carico da parte del supporto' }}</div>
                        </div>
                    @endif
                </div>
                <div>
                    <div class="text-muted small">Richiesta iniziale</div>
                    <div class="border rounded-3 p-3 bg-light-subtle" style="white-space: pre-wrap;">{{ $ticket->descrizione }}</div>
                </div>
            </div>
        </div>

        <div class="card border shadow-sm mb-0">
            <div class="card-header border-0 bg-light-subtle">
                <h5 class="card-title mb-1">Azioni</h5>
                <p class="text-muted mb-0">Da qui cambi lo stato del ticket o, se sei admin, lo prendi in carico.</p>
            </div>
            <div class="card-body">
                @if($isAdmin && !$ticket->assigned_admin_id && $ticket->stato !== 'chiuso')
                    <form method="POST" action="{{ route('supporto.assign', $ticket->id) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">Prendi in carico il ticket</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('supporto.status', $ticket->id) }}" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Stato del ticket</label>
                        <x-ui.select name="stato">
                            @if($isAdmin)
                                @foreach($stati as $key => $label)
                                    <option value="{{ $key }}" @selected($ticket->stato === $key)>{{ $label }}</option>
                                @endforeach
                            @else
                                <option value="aperto" @selected($ticket->stato === 'aperto')>Aperto</option>
                                <option value="chiuso" @selected($ticket->stato === 'chiuso')>Chiuso</option>
                            @endif
                        </x-ui.select>
                    </div>
                    <div class="col-12 d-flex justify-content-between gap-2">
                        <a href="{{ route('supporto.index') }}" class="btn btn-light">Torna al supporto</a>
                        <button type="submit" class="btn btn-primary">Salva stato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card border shadow-sm mb-4">
            <div class="card-header border-0 bg-light-subtle">
                <h5 class="card-title mb-1">Conversazione</h5>
                <p class="text-muted mb-0">Qui restano tutte le risposte del ticket, in ordine, cosi la struttura e l'amministratore vedono sempre tutta la storia del problema.</p>
            </div>
            <div class="card-body">
                @forelse($ticket->messages as $message)
                    @php
                        $isMine = auth()->id() === $message->author_user_id;
                        $isAdminMessage = $message->author && ($message->author->isAdmin() || $message->author->isSuperAdmin());
                    @endphp
                    <div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                        <div class="border rounded-3 p-3 {{ $isAdminMessage ? 'bg-primary-subtle' : 'bg-light-subtle' }}" style="max-width: 90%; min-width: 45%;">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div class="fw-semibold">{{ $message->author?->displayLabel() ?? 'Sistema' }}</div>
                            <div class="text-muted small">{{ $message->created_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="small text-muted mb-2">
                            {{ $isAdminMessage ? 'Messaggio del supporto amministrativo' : 'Messaggio della struttura' }}
                        </div>
                        <div style="white-space: pre-wrap;">{{ $message->messaggio }}</div>
                    </div>
                </div>
                @empty
                    <div class="text-muted">Nessun messaggio registrato.</div>
                @endforelse
            </div>
        </div>

        @if($ticket->stato !== 'chiuso')
            <div class="card border shadow-sm mb-0">
                <div class="card-header border-0 bg-light-subtle">
                    <h5 class="card-title mb-1">Nuova risposta</h5>
                    <p class="text-muted mb-0">
                        @if($isAdmin)
                            Scrivi qui la risposta per la struttura. Se il problema e in gestione, puoi anche aggiornare lo stato a lato.
                        @else
                            Scrivi qui tutti i dettagli utili o la conferma che il problema e ancora aperto.
                        @endif
                    </p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('supporto.reply', $ticket->id) }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Messaggio *</label>
                            <textarea name="messaggio" class="form-control" rows="8" required placeholder="Scrivi qui la risposta completa per continuare il ticket.">{{ old('messaggio') }}</textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Invia risposta</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
