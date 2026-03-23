@extends('layouts.master')
@section('title') Articoli @endsection

@php
    $activeTab = request('tab', old('nome') || old('codice') || old('accesso_key') ? 'nuovo' : 'elenco');
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Articoli @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Controlla i dati dell'articolo.</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h4 class="card-title mb-1">Catalogo articoli</h4>
            <p class="text-muted mb-0">Qui governi i prodotti che il sistema usa per licenze, assegnazioni e fatturazione. Restano separati da pagamenti e licenze per tenere pulita la gestione commerciale.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'elenco' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#articoli-pane-elenco" type="button" role="tab">Elenco articoli</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'nuovo' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#articoli-pane-nuovo" type="button" role="tab">Nuovo articolo</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade {{ $activeTab === 'elenco' ? 'show active' : '' }}" id="articoli-pane-elenco" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Elenco articoli</h5>
                            <p class="text-muted mb-0">Ogni articolo puo essere principale o secondario, puo essere attivo o disattivo e puo essere usato per licenze e fatturazione.</p>
                        </div>
                        <button type="button" class="btn btn-primary" id="goNuovoArticolo">Nuovo articolo</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Articolo</th>
                                    <th>Accesso</th>
                                    <th>Prezzo base</th>
                                    <th>Uso</th>
                                    <th>Stato</th>
                                    <th style="min-width: 360px;">Gestione</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($articoli as $articolo)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $articolo->nome }}</div>
                                            <div class="small text-muted">{{ $articolo->parent ? 'Sotto '.$articolo->parent->nome : 'Articolo principale' }}</div>
                                            @if($articolo->descrizione)
                                                <div class="small text-muted mt-1">{{ $articolo->descrizione }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $articolo->accesso_key ?: '—' }}</div>
                                            <div class="small text-muted">{{ $articolo->codice ?: 'Nessun codice' }}</div>
                                        </td>
                                        <td class="fw-semibold">{{ number_format((float) $articolo->prezzo_base, 2, ',', '.') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $articolo->assegnazioni_count }}</div>
                                            <div class="small text-muted">licenze assegnate</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $articolo->attivo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ $articolo->attivo ? 'Attivo' : 'Disattivo' }}
                                            </span>
                                        </td>
                                        <td class="text-end align-middle">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalArticoloEdit{{ $articolo->id }}">
                                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                                </button>
                                                <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalArticoloDelete{{ $articolo->id }}">
                                                    <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <div id="modalArticoloEdit{{ $articolo->id }}" class="modal fade" tabindex="-1" aria-labelledby="articoloEditLabel{{ $articolo->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="articoloEditLabel{{ $articolo->id }}">Modifica articolo</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('superadmin.articoli.update', $articolo->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <label class="form-label">Articolo padre</label>
                                                                <select name="parent_id" class="form-select">
                                                                    <option value="">Articolo principale</option>
                                                                    @foreach($articoli as $articoloPadre)
                                                                        @continue($articoloPadre->id === $articolo->id)
                                                                        <option value="{{ $articoloPadre->id }}" @selected($articolo->parent_id === $articoloPadre->id)>{{ $articoloPadre->nome }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Nome</label>
                                                                <input type="text" name="nome" class="form-control" value="{{ $articolo->nome }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Codice</label>
                                                                <input type="text" name="codice" class="form-control" value="{{ $articolo->codice }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Accesso chiave</label>
                                                                <input type="text" name="accesso_key" class="form-control" value="{{ $articolo->accesso_key }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Prezzo base</label>
                                                                <input type="number" name="prezzo_base" class="form-control" step="0.01" min="0" value="{{ $articolo->prezzo_base }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Ordine</label>
                                                                <input type="number" name="ordine" class="form-control" min="0" value="{{ $articolo->ordine }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Attivo</label>
                                                                <select name="attivo" class="form-select">
                                                                    <option value="1" @selected($articolo->attivo)>Sì</option>
                                                                    <option value="0" @selected(!$articolo->attivo)>No</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Descrizione</label>
                                                                <textarea name="descrizione" class="form-control" rows="3">{{ $articolo->descrizione }}</textarea>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Note</label>
                                                                <textarea name="note" class="form-control" rows="2">{{ $articolo->note }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Salva</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="modalArticoloDelete{{ $articolo->id }}" class="modal fade" tabindex="-1" aria-labelledby="articoloDeleteLabel{{ $articolo->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="articoloDeleteLabel{{ $articolo->id }}">Conferma eliminazione</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-0">Eliminare definitivamente "{{ $articolo->nome }}"?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Annulla</button>
                                                    <form method="POST" action="{{ route('superadmin.articoli.destroy', $articolo->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="fw-semibold mb-1">Nessun articolo presente</div>
                                            <div class="text-muted">Crea il primo articolo del catalogo per iniziare a gestire licenze e fatturazione.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'nuovo' ? 'show active' : '' }}" id="articoli-pane-nuovo" role="tabpanel">
                    <div class="row justify-content-center">
                        <div class="col-xxl-8 col-xl-9">
                            <div class="card border shadow-sm mb-0">
                                <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">Nuovo articolo</div>
                                        <div class="small text-muted">Crea un articolo principale o secondario da usare poi in licenze e fatturazione.</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light" id="goElencoArticoli">Torna all'elenco</button>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('superadmin.articoli.store') }}" class="row g-3">
                                        @csrf
                                        <div class="col-12">
                                            <label class="form-label">Articolo padre</label>
                                            <select name="parent_id" class="form-select">
                                                <option value="">Articolo principale</option>
                                                @foreach($articoli as $articoloPadre)
                                                    <option value="{{ $articoloPadre->id }}" @selected(old('parent_id') == $articoloPadre->id)>{{ $articoloPadre->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nome</label>
                                            <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Codice</label>
                                            <input type="text" name="codice" class="form-control" value="{{ old('codice') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Accesso chiave</label>
                                            <input type="text" name="accesso_key" class="form-control" value="{{ old('accesso_key') }}" placeholder="es. schedine-pro">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Prezzo base</label>
                                            <input type="number" name="prezzo_base" class="form-control" step="0.01" min="0" value="{{ old('prezzo_base', '0') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ordine</label>
                                            <input type="number" name="ordine" class="form-control" min="0" value="{{ old('ordine', '0') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Attivo</label>
                                            <select name="attivo" class="form-select">
                                                <option value="1" @selected(old('attivo', '1') == '1')>Sì</option>
                                                <option value="0" @selected(old('attivo') === '0')>No</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Descrizione</label>
                                            <textarea name="descrizione" class="form-control" rows="3">{{ old('descrizione') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Note</label>
                                            <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                                        </div>
                                        <div class="col-12 d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary">Salva articolo</button>
                                            <button type="button" class="btn btn-light" id="cancelNuovoArticolo">Annulla</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var nuovoArticoloTrigger = document.querySelector('[data-bs-target="#articoli-pane-nuovo"]');
    var elencoArticoliTrigger = document.querySelector('[data-bs-target="#articoli-pane-elenco"]');

    document.getElementById('goNuovoArticolo')?.addEventListener('click', function () {
        if (nuovoArticoloTrigger) {
            bootstrap.Tab.getOrCreateInstance(nuovoArticoloTrigger).show();
        }
    });

    document.getElementById('goElencoArticoli')?.addEventListener('click', function () {
        if (elencoArticoliTrigger) {
            bootstrap.Tab.getOrCreateInstance(elencoArticoliTrigger).show();
        }
    });

    document.getElementById('cancelNuovoArticolo')?.addEventListener('click', function () {
        if (elencoArticoliTrigger) {
            bootstrap.Tab.getOrCreateInstance(elencoArticoliTrigger).show();
        }
    });
});
</script>
@endpush
