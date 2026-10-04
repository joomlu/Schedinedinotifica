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

@php
    $batchItems = $batches->items();
    $totaleImportazioni = count($batchItems);
    $totaleRighe = collect($batchItems)->sum(fn ($batch) => (int) ($batch->total_rows ?? 0));
    $totaleValide = collect($batchItems)->sum(fn ($batch) => (int) ($batch->valid_rows ?? 0));
    $totaleDaCompletare = collect($batchItems)->sum(fn ($batch) => (int) ($batch->needs_review_rows ?? 0));
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="card-title mb-1">Importazione clienti</h4>
                    <p class="text-muted mb-0">Carica il file CSV, verifica le righe e conferma solo i clienti pronti a diventare definitivi.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('customer.imported.index') }}" class="btn btn-light btn-sm">Clienti importati</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">File importati</div>
                            <div class="fw-semibold fs-4">{{ $totaleImportazioni }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Righe totali</div>
                            <div class="fw-semibold fs-4">{{ $totaleRighe }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Valide</div>
                            <div class="fw-semibold fs-4 text-success">{{ $totaleValide }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Da completare</div>
                            <div class="fw-semibold fs-4 text-warning">{{ $totaleDaCompletare }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 align-items-stretch">
                    <div class="col-xl-7">
                        <div class="border rounded-3 p-3 p-xl-4 bg-light-subtle h-100">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="fw-semibold">Carica file</div>
                                    <div class="small text-muted">CSV o TXT separato da punto e virgola</div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary">CSV · TXT</span>
                            </div>

                            <form method="POST" action="{{ route('customer.import.store') }}" enctype="multipart/form-data" class="row g-3">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label">File clienti</label>
                                    <input type="file" name="file_import" class="form-control @error('file_import') is-invalid @enderror" accept=".csv,.txt" required>
                                    @error('file_import')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Il file non crea clienti subito: viene aperto prima nel settore di verifica.</div>
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
                    </div>

                    <div class="col-xl-5">
                        <div class="border rounded-3 p-3 p-xl-4 h-100">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="fw-semibold">Modello e regole</div>
                                    <div class="small text-muted">Checklist operativa</div>
                                </div>
                                <span class="badge bg-success-subtle text-success">Pronto</span>
                            </div>

                            <ul class="list-unstyled small text-muted mb-3">
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Normalizza CAP e indirizzo quando il GEO è incompleto.</li>
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Rileva duplicati nel file, nell'hotel e nella catena.</li>
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Usa la stessa verifica di correzione del cliente prima di salvare.</li>
                                <li><i class="ri-check-line text-success me-2"></i>Le righe non valide restano in staging e possono essere scartate.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h5 class="card-title mb-0">Storico importazioni</h5>
                </div>
                <div class="text-muted small">{{ $batches->total() }} file presenti</div>
            </div>
            <div class="card-body">
                @if($batches->isEmpty())
                    <div class="text-center py-5 text-muted">
                        Nessuna importazione presente per questa struttura.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
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
                                                <a href="{{ route('customer.import.show', $batch) }}" class="btn btn-soft-primary btn-sm">Apri verifica</a>
                                                <form method="POST" action="{{ route('customer.import.destroy', $batch) }}" onsubmit="return confirm('Eliminare questa importazione e tutte le righe di staging?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger btn-sm">Elimina</button>
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
@endsection
