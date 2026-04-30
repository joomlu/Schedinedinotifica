@extends('layouts.master')
@section('title', 'Import clienti')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Clienti
    @endslot
    @slot('title')
        Import clienti
    @endslot
@endcomponent

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0">
                <h4 class="card-title mb-1">Importazione clienti</h4>
                <p class="text-muted mb-0">Carica il file, controlla le righe in verifica e tieni pulito lo storico eliminando le importazioni non buone prima del salvataggio finale in <strong>Clienti</strong>.</p>
            </div>
            <div class="card-body">
                <ul class="nav nav-pills custom-nav nav-justified mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#import-upload-tab" type="button" role="tab">Carica file</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#import-history-tab" type="button" role="tab">Verifica e storico</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="import-upload-tab" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-xl-5">
                                <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                    <div class="fw-semibold mb-1">Struttura corrente</div>
                                    <div>{{ $struttura->nome_struttura }}</div>
                                </div>
                            </div>
                            <div class="col-xl-7">
                                <form method="POST" action="{{ route('customer.import.store') }}" enctype="multipart/form-data" class="row g-3">
                                    @csrf
                                    <div class="col-12">
                                        <label class="form-label">File clienti / gruppi</label>
                                        <input type="file" name="file_import" class="form-control @error('file_import') is-invalid @enderror" accept=".csv,.txt" required>
                                        @error('file_import')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">CSV separato da punto e virgola. Il file non crea clienti subito: apre prima il settore di verifica.</div>
                                    </div>
                                    <div class="col-12 d-flex gap-2 flex-wrap">
                                        <button type="submit" class="btn btn-primary btn-label right">
                                            <i class="ri-upload-2-line label-icon align-middle fs-16 ms-2"></i>
                                            Carica e prepara verifica
                                        </button>
                                        <a href="{{ route('customer.import.template') }}" class="btn btn-light btn-label right">
                                            <i class="ri-file-download-line label-icon align-middle fs-16 ms-2"></i>
                                            Scarica modello CSV
                                        </a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <div class="fw-semibold mb-2">Cosa fa questo modulo</div>
                                    <div class="row g-3 text-muted">
                                        <div class="col-lg-4">Normalizza il GEO partendo dal CAP quando il nome città arriva sporco o incompleto.</div>
                                        <div class="col-lg-4">Segnala possibili duplicati nel file, nell'hotel e nella catena del proprietario.</div>
                                        <div class="col-lg-4">Ti fa correggere ogni riga con la stessa UI del cliente prima del salvataggio finale.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="import-history-tab" role="tabpanel">
                        @if($batches->count() === 0)
                            <div class="text-center py-5 text-muted">
                                Nessuna importazione presente per questa struttura.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>File</th>
                                            <th>Stato</th>
                                            <th>Righe</th>
                                            <th>Valide</th>
                                            <th>Da completare</th>
                                            <th>Importate</th>
                                            <th class="text-end">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($batches as $batch)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $batch->original_name }}</div>
                                                    <div class="text-muted small">{{ optional($batch->created_at)->format('d/m/Y H:i') }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark text-uppercase">{{ str_replace('_', ' ', $batch->status) }}</span>
                                                </td>
                                                <td>{{ $batch->total_rows }}</td>
                                                <td>{{ $batch->valid_rows }}</td>
                                                <td>{{ $batch->needs_review_rows }}</td>
                                                <td>{{ $batch->imported_rows }}</td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                                        <a href="{{ route('customer.import.show', $batch) }}" class="btn btn-soft-primary btn-sm">
                                                            Apri verifica
                                                        </a>
                                                        <form method="POST" action="{{ route('customer.import.destroy', $batch) }}" onsubmit="return confirm('Eliminare questa importazione e tutte le righe di staging?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-soft-danger btn-sm">
                                                                Elimina file
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $batches->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
