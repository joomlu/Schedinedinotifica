@extends('layouts.master')
@section('title', 'Import componenti schedina')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Schedine
    @endslot
    @slot('title')
        Import componenti
    @endslot
@endcomponent

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="card-title mb-1">Importazione componenti</h4>
                    <p class="text-muted mb-0">Carica un CSV o un TXT delimitato per ottenere solo la preview: nessun componente viene salvato in questa fase.</p>
                </div>
                <a href="{{ route('schedina.edit', ['id' => $schedina->id, 'active_tab' => 'schedina-step-comp']) }}" class="btn btn-light">Torna ai componenti</a>
            </div>
            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small">Schedina</div>
                            <div class="fw-semibold">{{ $schedina->scheda ?? $schedina->id }}</div>
                            <div class="text-muted small">Struttura: {{ $struttura->nome_struttura ?? 'N/D' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small">Formato supportato</div>
                            <div class="fw-semibold">CSV e TXT</div>
                            <div class="text-muted small">CSV con ;, TXT con tabulazione.</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small">Limiti</div>
                            <div class="fw-semibold">{{ number_format($maxRows, 0, ',', '.') }} righe max</div>
                            <div class="text-muted small">File max {{ number_format($maxBytes / 1024 / 1024, 0) }} MB.</div>
                        </div>
                    </div>
                </div>

                @if ($errors->has('file_import'))
                    <div class="alert alert-danger">{{ $errors->first('file_import') }}</div>
                @endif

                @if ($errors->has('import_batch_token'))
                    <div class="alert alert-danger">{{ $errors->first('import_batch_token') }}</div>
                @endif

                <div class="row g-4">
                    <div class="col-xl-5">
                        <div class="border rounded-3 p-3 h-100">
                            <h5 class="mb-3">Carica file</h5>
                            <form method="POST" action="{{ route('schedina.componenti.import.preview', ['schedina' => $schedina->id]) }}" enctype="multipart/form-data" class="row g-3">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label">File componenti</label>
                                    <input type="file" name="file_import" class="form-control @error('file_import') is-invalid @enderror" accept=".csv,.txt" required>
                                    @error('file_import')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Il file viene solo validato e trasformato in preview. Il salvataggio dei componenti arriverà in una fase successiva.</div>
                                </div>
                                <div class="col-12 d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-primary btn-label right">
                                        <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>
                                        Genera preview
                                    </button>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Scarica modello
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('schedina.componenti.import.template', ['schedina' => $schedina->id, 'format' => 'csv']) }}">Modello CSV</a></li>
                                            <li><a class="dropdown-item" href="{{ route('schedina.componenti.import.template', ['schedina' => $schedina->id, 'format' => 'txt']) }}">Modello TXT</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-xl-7">
                        <div class="border rounded-3 p-3 h-100">
                            <h5 class="mb-3">Contratto di importazione</h5>
                            <div class="row g-2 text-muted small">
                                @foreach($templateHeaders as $header)
                                    <div class="col-md-6 col-lg-4"><span class="badge bg-light text-dark border">{{ $header }}</span></div>
                                @endforeach
                            </div>
                            <div class="alert alert-info mt-3 mb-0 py-2">
                                Tipo alloggiato e Esente non fanno parte del file: vengono applicati automaticamente dal backend.
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($preview))
                    <div class="mt-5">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Righe lette</div><div class="fw-semibold fs-4">{{ $preview['totale_righe'] }}</div></div></div>
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Valide</div><div class="fw-semibold fs-4 text-success">{{ $preview['righe_valide'] }}</div></div></div>
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Con errori</div><div class="fw-semibold fs-4 text-danger">{{ $preview['righe_in_errore'] }}</div></div></div>
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Default tipo alloggiato</div><div class="fw-semibold fs-4">{{ $preview['metadata']['default_relationship_descrizione'] ?? '-' }}</div></div></div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                            <div class="text-muted">
                                Verranno importati <strong>{{ $preview['righe_valide'] }}</strong> componenti.
                                @if(!empty($preview['righe_in_errore']))
                                    <span class="ms-2">Righe con errori: <strong>{{ $preview['righe_in_errore'] }}</strong>.</span>
                                @endif
                            </div>
                            @if(($preview['righe_valide'] ?? 0) > 0 && !empty($preview['batch_token']))
                                <form method="POST" action="{{ route('schedina.componenti.import.confirm', ['schedina' => $schedina->id]) }}">
                                    @csrf
                                    <input type="hidden" name="import_batch_token" value="{{ $preview['batch_token'] }}">
                                    <button type="submit" class="btn btn-success btn-label right">
                                        <i class="ri-check-double-line label-icon align-middle fs-16 ms-2"></i>
                                        Conferma importazione
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Riga</th>
                                        <th>Nome</th>
                                        <th>Cognome</th>
                                        <th>Sesso</th>
                                        <th>Data di nascita</th>
                                        <th>Nazione nascita</th>
                                        <th>Tipo alloggiato</th>
                                        <th>Esente</th>
                                        <th>Stato</th>
                                        <th>Errori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($preview['rows'] as $row)
                                        <tr>
                                            <td class="fw-semibold">{{ $row['row_number'] }}</td>
                                            <td>{{ $row['name'] ?: '—' }}</td>
                                            <td>{{ $row['surname'] ?: '—' }}</td>
                                            <td>{{ $row['sex'] ?: '—' }}</td>
                                            <td>{{ $row['date_nac'] ?: '—' }}</td>
                                            <td>{{ $row['country_nac'] ?: '—' }}</td>
                                            <td>{{ $row['relationship_label'] ?: $row['relationship'] ?: '—' }}</td>
                                            <td>{{ $row['exent'] ?: '—' }}</td>
                                            <td>
                                                <span class="badge {{ $row['status'] === 'VALIDO' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $row['status'] }}</span>
                                            </td>
                                            <td>
                                                @if(empty($row['errors']))
                                                    <span class="text-muted">—</span>
                                                @else
                                                    <div class="small">
                                                        @foreach($row['errors'] as $error)
                                                            <div><strong>{{ $error['label'] }}:</strong> {{ $error['message'] }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection