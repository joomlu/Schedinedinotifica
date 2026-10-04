@extends('layouts.master')
@section('title', 'Clienti importati')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Clienti
    @endslot
    @slot('title')
        Clienti importati
    @endslot
@endcomponent

@php
    $hasFilters = $selectedStatus !== '' || $selectedTipoCliente !== '' || $selectedTipoAlloggiato !== '' || $selectedDataNascita !== '' || $selectedBatchId > 0 || $selectedImportedFrom !== '' || $selectedImportedTo !== '' || $selectedComune !== '' || $selectedProvincia !== '' || $selectedNazione !== '' || $selectedGruppo !== '' || $soloDuplicati;
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="card-title mb-1">Clienti importati</h4>
                    <p class="text-muted mb-0">Record persistenti ancora da verificare, correggere e confermare come Clienti definitivi.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('customer.import.index') }}" class="btn btn-light btn-sm">Importa file</a>
                </div>
            </div>
            <div class="card-body">
                <div class="border rounded-3 p-3 bg-light-subtle mb-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                        <div class="fw-semibold">Ricerca e filtri</div>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#customerImportedFilters" aria-expanded="{{ $hasFilters ? 'true' : 'false' }}" aria-controls="customerImportedFilters">
                            <i class="ri-filter-3-line align-middle me-1"></i>
                            Filtri
                        </button>
                    </div>

                    <form method="GET" action="{{ route('customer.imported.index') }}">
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-lg-8">
                                <label class="form-label" for="imported-search">Cerca cliente</label>
                                <input id="imported-search" type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Nome, cognome, email, telefono, documento, numero cliente...">
                            </div>
                            <div class="col-lg-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Cerca</button>
                                <a href="{{ route('customer.imported.index') }}" class="btn btn-light">Azzera</a>
                            </div>
                        </div>
                        <div id="customerImportedFilters" class="collapse {{ $hasFilters ? 'show' : '' }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-2">
                                    <label class="form-label">Data nascita</label>
                                    <input type="date" name="data_nascita" value="{{ $selectedDataNascita }}" class="form-control">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Stato</label>
                                    <select name="status" class="form-select">
                                        <option value="">Tutti</option>
                                        <option value="COMPLETO" @selected($selectedStatus === 'COMPLETO')>COMPLETO</option>
                                        <option value="DA_COMPLETARE" @selected($selectedStatus === 'DA_COMPLETARE')>DA_COMPLETARE</option>
                                        <option value="POSSIBILE_DUPLICATO" @selected($selectedStatus === 'POSSIBILE_DUPLICATO')>POSSIBILE_DUPLICATO</option>
                                        <option value="NON_IMPORTABILE" @selected($selectedStatus === 'NON_IMPORTABILE')>NON_IMPORTABILE</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Tipo cliente</label>
                                    <select name="tipo_cliente" class="form-select">
                                        <option value="">Tutti</option>
                                        <option value="Ospite" @selected($selectedTipoCliente === 'Ospite')>Ospite</option>
                                        <option value="Componente" @selected($selectedTipoCliente === 'Componente')>Componente</option>
                                        <option value="Richiesta" @selected($selectedTipoCliente === 'Richiesta')>Richiesta</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Tipo alloggiato</label>
                                    <select name="tipo_alloggiato" class="form-select">
                                        <option value="">Tutti</option>
                                        @foreach($tipoAlloggiatoOptions as $option)
                                            <option value="{{ $option }}" @selected($selectedTipoAlloggiato === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">File / batch</label>
                                    <select name="batch_id" class="form-select">
                                        <option value="">Tutti</option>
                                        @foreach($batchOptions as $batchOption)
                                            <option value="{{ $batchOption->id }}" @selected($selectedBatchId === (int) $batchOption->id)>
                                                {{ $batchOption->original_name ?: 'Importazione ' . $batchOption->created_at->format('d/m/Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Importato da</label>
                                    <input type="date" name="imported_from" value="{{ $selectedImportedFrom }}" class="form-control">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Importato a</label>
                                    <input type="date" name="imported_to" value="{{ $selectedImportedTo }}" class="form-control">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Comune</label>
                                    <input type="text" name="comune" value="{{ $selectedComune }}" class="form-control" placeholder="Comune">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Provincia</label>
                                    <input type="text" name="provincia" value="{{ $selectedProvincia }}" class="form-control" placeholder="Provincia">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Nazione</label>
                                    <input type="text" name="nazione" value="{{ $selectedNazione }}" class="form-control" placeholder="Nazione">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Gruppo</label>
                                    <input type="text" name="gruppo" value="{{ $selectedGruppo }}" class="form-control" placeholder="Gruppo">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Duplicati</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="solo_duplicati" value="1" @checked($soloDuplicati)>
                                        <label class="form-check-label">Solo duplicati</label>
                                    </div>
                                </div>
                                <div class="col-lg-2 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary">Applica filtri</button>
                                    <a href="{{ route('customer.imported.index') }}" class="btn btn-light">Azzera</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <form id="bulk-delete-form" method="POST" action="{{ route('customer.imported.bulk_destroy') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="mode" value="selected">
                    @foreach(['q', 'status', 'tipo_cliente', 'tipo_alloggiato', 'data_nascita', 'batch_id', 'imported_from', 'imported_to', 'comune', 'provincia', 'nazione', 'gruppo', 'solo_duplicati'] as $field)
                        @if(request()->filled($field))
                            <input type="hidden" name="{{ $field }}" value="{{ request()->input($field) }}">
                        @endif
                    @endforeach
                </form>

                <form id="filtered-delete-form" method="POST" action="{{ route('customer.imported.bulk_destroy') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="mode" value="filtered">
                    @foreach(['q', 'status', 'tipo_cliente', 'tipo_alloggiato', 'data_nascita', 'batch_id', 'imported_from', 'imported_to', 'comune', 'provincia', 'nazione', 'gruppo', 'solo_duplicati'] as $field)
                        @if(request()->filled($field))
                            <input type="hidden" name="{{ $field }}" value="{{ request()->input($field) }}">
                        @endif
                    @endforeach
                </form>

                <form id="batch-delete-form" method="POST" action="{{ route('customer.imported.bulk_destroy') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="mode" value="batch">
                    @if($selectedBatchId > 0)
                        <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">
                    @endif
                </form>

                <form id="all-pending-delete-form" method="POST" action="{{ route('customer.imported.bulk_destroy') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="mode" value="all_pending">
                </form>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all-imported-rows">
                            <label class="form-check-label small text-muted" for="select-all-imported-rows">Seleziona pagina</label>
                        </div>
                        <span id="selectedImportedCount" class="badge bg-secondary-subtle text-secondary">0 selezionati</span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" form="bulk-delete-form" class="btn btn-outline-danger btn-sm" onclick="return confirm('Eliminare definitivamente i clienti importati selezionati?');">Elimina selezionati</button>
                        @if($selectedBatchId > 0)
                            <button type="submit" form="batch-delete-form" class="btn btn-outline-danger btn-sm" onclick="return confirm('Eliminare definitivamente tutti i clienti importati pendenti del batch selezionato?');">Elimina pendenti del batch</button>
                        @endif
                        <button type="submit" form="filtered-delete-form" class="btn btn-outline-danger btn-sm" onclick="return confirm('Eliminare definitivamente tutti i clienti importati pendenti risultanti dal filtro corrente?');">Elimina risultati filtrati</button>
                        <button type="submit" form="all-pending-delete-form" class="btn btn-outline-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare tutti i clienti importati pendenti della struttura corrente? Questa azione non tocca i Customer definitivi.');">Elimina tutti i pendenti</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 38px;">
                                    <input type="checkbox" class="form-check-input" id="select-all-imported-rows-head" aria-label="Seleziona tutti">
                                </th>
                                <th>Nome</th>
                                <th>Data nascita</th>
                                <th>Contatti</th>
                                <th>Città</th>
                                <th>Stato</th>
                                <th>Origine</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                @php
                                    $payload = $row->normalized_payload ?? [];
                                    $nome = trim((string) ($payload['nome'] ?? ''));
                                    $cognome = trim((string) ($payload['cognome'] ?? ''));
                                    $fullName = trim($nome . ' ' . $cognome);
                                    $status = $row->status;
                                    $label = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
                                    $css = $statusClasses[$status] ?? 'bg-light text-dark';
                                    $batch = $row->batch;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input customer-import-row-check" name="ids[]" value="{{ $row->id }}" form="bulk-delete-form" aria-label="Seleziona {{ $fullName ?: 'riga importazione' }}">
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $fullName ?: 'Riga senza nominativo' }}</div>
                                        <div class="text-muted small">{{ $batch?->original_name ?: 'Importazione' }}</div>
                                    </td>
                                    <td>{{ $payload['data_nascita'] ?? '—' }}</td>
                                    <td>
                                        <div>{{ $payload['email'] ?? '—' }}</div>
                                        <div class="text-muted small">{{ $payload['cellulare'] ?: ($payload['telefono'] ?? '—') }}</div>
                                    </td>
                                    <td>{{ $payload['comune_residenza'] ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $css }}">{{ $label }}</span>
                                    </td>
                                    <td>
                                        <div>{{ optional($row->created_at)->format('d/m/Y') ?: '—' }}</div>
                                        <div class="text-muted small">{{ $batch?->created_at ? $batch->created_at->format('d/m/Y H:i') : '—' }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                                            <a href="{{ route('customer.import.row.edit', [$batch, $row]) }}" class="btn btn-soft-secondary btn-sm">Dettagli</a>
                                            <a href="{{ route('customer.import.row.edit', [$batch, $row]) }}" class="btn btn-soft-primary btn-sm">Modifica</a>
                                            <form method="POST" action="{{ route('customer.imported.use_schedina', $row) }}" onsubmit="return confirm('Aprire la nuova schedina precompilata con questo cliente importato?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Usa in schedina</button>
                                            </form>
                                            <form method="POST" action="{{ route('customer.imported.confirm', $row) }}" onsubmit="return confirm('Confermare questo cliente importato come Cliente definitivo?');">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Conferma</button>
                                            </form>
                                            <form method="POST" action="{{ route('customer.imported.destroy', $row) }}" onsubmit="return confirm('Scartare definitivamente questo cliente importato?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-soft-danger btn-sm">Elimina</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">Nessun cliente importato da processare.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $rows->links() }}
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const pageToggle = document.getElementById('select-all-imported-rows');
                        const headToggle = document.getElementById('select-all-imported-rows-head');
                        const boxes = Array.from(document.querySelectorAll('.customer-import-row-check'));
                        const counter = document.getElementById('selectedImportedCount');

                        const refreshSelection = () => {
                            const selected = boxes.filter((box) => box.checked).length;
                            if (counter) {
                                counter.textContent = selected === 0 ? '0 selezionati' : selected + ' selezionati';
                            }
                            if (pageToggle) {
                                const allChecked = boxes.length > 0 && boxes.every((box) => box.checked);
                                pageToggle.checked = allChecked;
                            }
                            if (headToggle) {
                                const hasSelected = boxes.some((box) => box.checked);
                                headToggle.checked = boxes.length > 0 && allBoxesChecked();
                                headToggle.indeterminate = hasSelected && !headToggle.checked;
                            }
                        };

                        const allBoxesChecked = () => boxes.length > 0 && boxes.every((box) => box.checked);

                        if (pageToggle) {
                            pageToggle.addEventListener('change', function () {
                                boxes.forEach((box) => box.checked = this.checked);
                                refreshSelection();
                            });
                        }

                        if (headToggle) {
                            headToggle.addEventListener('change', function () {
                                boxes.forEach((box) => box.checked = this.checked);
                                refreshSelection();
                            });
                        }

                        boxes.forEach((box) => box.addEventListener('change', refreshSelection));
                        refreshSelection();
                    });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection
