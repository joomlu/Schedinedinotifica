@php
    $hasPersistedSchedina = !empty($schedina) && $schedina instanceof \App\Models\Schedina && $schedina->exists && !empty($schedina->getKey());
    $schedinaId = $hasPersistedSchedina ? $schedina->id : null;
    $importBackRoute = $schedinaId
        ? route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp'])
        : route('newschedina', ['active_tab' => 'schedina-step-comp']);
    $previewRoute = $schedinaId
        ? route('schedina.componenti.import.preview', ['schedina' => $schedinaId])
        : route('schedina.componenti.import.new.preview');
    $confirmRoute = $schedinaId
        ? route('schedina.componenti.import.confirm', ['schedina' => $schedinaId])
        : route('schedina.componenti.import.new.confirm');
    $templateCsvRoute = $schedinaId
        ? route('schedina.componenti.import.template', ['schedina' => $schedinaId, 'format' => 'csv'])
        : route('schedina.componenti.import.new.template', ['format' => 'csv']);
    $templateTxtRoute = $schedinaId
        ? route('schedina.componenti.import.template', ['schedina' => $schedinaId, 'format' => 'txt'])
        : route('schedina.componenti.import.new.template', ['format' => 'txt']);
    $templateXlsxRoute = $schedinaId
        ? route('schedina.componenti.import.template', ['schedina' => $schedinaId, 'format' => 'xlsx'])
        : route('schedina.componenti.import.new.template', ['format' => 'xlsx']);
@endphp

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
                    <p class="text-muted mb-0">Carica un CSV, un TXT delimitato o un XLSX per ottenere solo la preview: nessun componente viene salvato in questa fase.</p>
                </div>
                <a href="{{ $importBackRoute }}" class="btn btn-light">Torna ai componenti</a>
            </div>
            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small">Schedina</div>
                            <div class="fw-semibold">{{ $schedina?->scheda ?? ($hasPersistedSchedina ? $schedina->id : 'Nuova schedina') }}</div>
                            <div class="text-muted small">Struttura: {{ $struttura->nome_struttura ?? 'N/D' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small">Formato supportato</div>
                            <div class="fw-semibold">CSV, TXT e XLSX</div>
                            <div class="text-muted small">CSV con ;, TXT con tabulazione, XLSX a foglio singolo.</div>
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
                            <form method="POST" action="{{ $previewRoute }}" enctype="multipart/form-data" class="row g-3">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label">File componenti</label>
                                    <input type="file" name="file_import" class="form-control @error('file_import') is-invalid @enderror" accept=".csv,.txt,.xlsx" required>
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
                                </div>
                                <div class="col-12">
                                    <div class="border rounded-3 p-3 bg-light-subtle">
                                        <div class="fw-semibold mb-2">Scarica modello</div>
                                        <p class="text-muted mb-2">Scarica il modello, compilalo con i componenti e caricalo nuovamente in questa pagina.</p>
                                        <div class="d-flex gap-2 flex-wrap mb-2">
                                            <a class="btn btn-light" href="{{ $templateCsvRoute }}" download>Scarica CSV</a>
                                            <a class="btn btn-light" href="{{ $templateTxtRoute }}" download>Scarica TXT</a>
                                            <a class="btn btn-light" href="{{ $templateXlsxRoute }}" download>Scarica XLSX</a>
                                        </div>
                                        <div class="small text-muted">
                                            Tipo alloggiato viene impostato automaticamente come <strong>MEMBRO GRUPPO</strong> ed Esente come <strong>NO</strong>.
                                            Potrai modificare questi valori successivamente nella scheda del componente.
                                        </div>
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
                        @php
                            $statiPreview = $preview['stati'] ?? [
                                \App\Support\Componenti\DatiComponenteNormalizzati::STATO_COMPLETO => 0,
                                \App\Support\Componenti\DatiComponenteNormalizzati::STATO_DA_VERIFICARE => 0,
                                \App\Support\Componenti\DatiComponenteNormalizzati::STATO_DA_COMPLETARE => 0,
                                \App\Support\Componenti\DatiComponenteNormalizzati::STATO_NON_IMPORTABILE => 0,
                            ];
                        @endphp
                        <div class="row g-3 mb-3">
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Righe lette</div><div class="fw-semibold fs-4">{{ $preview['totale_righe'] }}</div></div></div>
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Complete</div><div class="fw-semibold fs-4 text-success">{{ $statiPreview[\App\Support\Componenti\DatiComponenteNormalizzati::STATO_COMPLETO] ?? 0 }}</div></div></div>
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Da verificare</div><div class="fw-semibold fs-4 text-warning">{{ $statiPreview[\App\Support\Componenti\DatiComponenteNormalizzati::STATO_DA_VERIFICARE] ?? 0 }}</div></div></div>
                            <div class="col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Da completare</div><div class="fw-semibold fs-4 text-danger">{{ $statiPreview[\App\Support\Componenti\DatiComponenteNormalizzati::STATO_DA_COMPLETARE] ?? 0 }}</div></div></div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                            <div class="text-muted">
                                Verranno importati <strong>{{ $preview['righe_valide'] }}</strong> componenti.
                                @if(!empty($preview['righe_in_errore']))
                                    <span class="ms-2">Righe con attenzione o da completare: <strong>{{ $preview['righe_in_errore'] }}</strong>.</span>
                                @endif
                            </div>
                            @if(($preview['righe_valide'] ?? 0) > 0 && !empty($preview['batch_token']))
                                <form method="POST" action="{{ $confirmRoute }}">
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
                                                @php
                                                    $statusClass = match ($row['status'] ?? 'COMPLETO') {
                                                        \App\Support\Componenti\DatiComponenteNormalizzati::STATO_COMPLETO => 'bg-success-subtle text-success',
                                                        \App\Support\Componenti\DatiComponenteNormalizzati::STATO_DA_VERIFICARE => 'bg-warning-subtle text-warning',
                                                        \App\Support\Componenti\DatiComponenteNormalizzati::STATO_DA_COMPLETARE => 'bg-danger-subtle text-danger',
                                                        default => 'bg-secondary-subtle text-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $row['status'] ?? 'COMPLETO' }}</span>
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