@extends('layouts.master')

@section('title', 'Centro notifiche')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Notifiche @endslot
    @slot('title') Centro notifiche @endslot
@endcomponent

@php
    $utenteNotifiche = auth()->user();
    $contestoNotifiche = $utenteNotifiche?->isSuperAdmin()
        ? 'SuperAdmin'
        : ($utenteNotifiche?->isAdmin()
            ? 'Admin'
            : ($utenteNotifiche?->isProprietario() ? 'Proprietario' : 'Struttura'));
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle">
        <div class="row g-3 align-items-end">
            <div class="col-xl-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $contestoNotifiche }}</span>
                    <span class="badge bg-light text-body">Centro notifiche</span>
                </div>
                <h4 class="card-title mb-1">Centro notifiche</h4>
                <p class="text-muted mb-0">
                    Qui trovi le notifiche del tuo ruolo, gli avvisi di sistema, i segnali del supporto online e, quando c è una struttura selezionata, anche il circuito operativo della struttura.
                </p>
            </div>
            <div class="col-xl-6">
                <form method="GET" action="{{ route('notifiche.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Stato</label>
                        <x-ui.select name="stato">
                            <option value="">Tutte</option>
                            <option value="da_leggere" @selected($stato === 'da_leggere')>Da vedere</option>
                            <option value="letta" @selected($stato === 'letta')>Vista</option>
                            <option value="chiusa" @selected($stato === 'chiusa')>Chiusa</option>
                        </x-ui.select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Ricerca</label>
                        <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cerca per titolo o testo...">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                        <a href="{{ route('notifiche.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Da vedere</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['da_leggere'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Ancora aperte</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['aperte'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Chiuse</div>
                        <div class="fs-3 fw-semibold">{{ $contatori['chiuse'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-sm mb-0">
            <div class="card-header border-0 bg-light-subtle">
                <h5 class="card-title mb-1">Notifiche registrate</h5>
                <p class="text-muted mb-0">Le notifiche aperte stanno sopra. Da qui puoi leggerle, segnarle come viste o chiuderle quando il tema e risolto.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Notifica</th>
                                <th>Da</th>
                                <th>A</th>
                                <th>Importanza</th>
                                <th>Stato</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifiche as $notifica)
                                <tr>
                                    <td>{{ $notifica->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            @if(($notifica->notification_kind ?? '') === 'supporto')
                                                <span class="badge bg-warning-subtle text-warning">Supporto</span>
                                            @elseif(($notifica->notification_kind ?? '') === 'sistema')
                                                <span class="badge bg-primary-subtle text-primary">Sistema</span>
                                            @elseif(($notifica->notification_kind ?? '') === 'compleanno')
                                                <span class="badge bg-success-subtle text-success">Automatico</span>
                                            @else
                                                <span class="badge bg-light text-body">Interna</span>
                                            @endif
                                            <div class="fw-semibold">{{ $notifica->titolo }}</div>
                                        </div>
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($notifica->messaggio, 110) }}</div>
                                    </td>
                                    <td>{{ $notifica->mittente_label ?? $notifica->mittente?->displayLabel() ?? '-' }}</td>
                                    <td>{{ $notifica->destinatario_label ?? $notifica->destinatario?->displayLabel() ?? 'Turno successivo' }}</td>
                                    <td>{{ ucfirst($notifica->priorita) }}</td>
                                    <td>
                                        @if($notifica->stato === 'da_leggere')
                                            <span class="badge bg-warning-subtle text-warning">Da vedere</span>
                                        @elseif($notifica->stato === 'letta')
                                            <span class="badge bg-info-subtle text-info">Vista</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Chiusa</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNotifica{{ $notifica->id }}" title="Apri notifica">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if(($notifica->can_mark ?? false) && $notifica->stato === 'da_leggere')
                                                <form method="POST" action="{{ route('gestione.operativa.comande.read', $notifica->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="redirect_to" value="notifiche">
                                                    <button type="submit" class="btn btn-soft-info btn-sm" title="Segna come vista">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(($notifica->can_close ?? false) && $notifica->stato !== 'chiusa')
                                                <form method="POST" action="{{ route('gestione.operativa.comande.close', $notifica->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="redirect_to" value="notifiche">
                                                    <button type="submit" class="btn btn-soft-success btn-sm" title="Chiudi notifica">
                                                        <i class="ri-close-circle-line"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Nessuna notifica trovata per il filtro selezionato.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($notifiche->hasPages())
                <div class="card-footer border-0 bg-white">
                    {{ $notifiche->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($notifiche as $notifica)
    <div id="modalNotifica{{ $notifica->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notifica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Titolo</div>
                            <div class="fw-semibold">{{ $notifica->titolo }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Da</div>
                            <div>{{ $notifica->mittente_label ?? $notifica->mittente?->displayLabel() ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">A</div>
                            <div>{{ $notifica->destinatario_label ?? $notifica->destinatario?->displayLabel() ?? 'Turno successivo' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Data</div>
                            <div>{{ $notifica->created_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Importanza</div>
                            <div>{{ ucfirst($notifica->priorita) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Stato</div>
                            <div>
                                @if($notifica->stato === 'da_leggere')
                                    <span class="badge bg-warning-subtle text-warning">Da vedere</span>
                                @elseif($notifica->stato === 'letta')
                                    <span class="badge bg-info-subtle text-info">Vista</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Chiusa</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Testo completo</div>
                            <div class="border rounded-3 p-3 bg-light-subtle" style="white-space: pre-wrap;">{{ $notifica->messaggio }}</div>
                        </div>
                        @if(!empty($notifica->scheda) || !empty($notifica->origine))
                            <div class="col-md-6">
                                <div class="text-muted small">Scheda</div>
                                <div>{{ $notifica->scheda ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Origine operativa</div>
                                <div>{{ $notifica->origine ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Arrivo</div>
                                <div>{{ !empty($notifica->arrivo) ? \Carbon\Carbon::parse($notifica->arrivo)->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Partenza</div>
                                <div>{{ !empty($notifica->partenza) ? \Carbon\Carbon::parse($notifica->partenza)->format('d/m/Y') : '-' }}</div>
                            </div>
                        @endif
                        @if(!empty($notifica->detail_link))
                            <div class="col-12">
                                <a href="{{ $notifica->detail_link }}" class="btn btn-light">{{ $notifica->detail_link_label ?? 'Apri dettaglio' }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    @if(($notifica->can_mark ?? false) && $notifica->stato === 'da_leggere')
                        <form method="POST" action="{{ route('gestione.operativa.comande.read', $notifica->id) }}">
                            @csrf
                            <input type="hidden" name="redirect_to" value="notifiche">
                            <button type="submit" class="btn btn-info">Segna come vista</button>
                        </form>
                    @endif
                    @if(($notifica->can_close ?? false) && $notifica->stato !== 'chiusa')
                        <form method="POST" action="{{ route('gestione.operativa.comande.close', $notifica->id) }}">
                            @csrf
                            <input type="hidden" name="redirect_to" value="notifiche">
                            <button type="submit" class="btn btn-success">Segna come risolta</button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
