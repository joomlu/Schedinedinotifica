@extends('layouts.master')
@section('title', 'Verifica import clienti')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Clienti
    @endslot
    @slot('title')
        Verifica import
    @endslot
@endcomponent

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="card-title mb-1">{{ $batch->original_name }}</h4>
                    <p class="text-muted mb-0">Verifica le righe del file, correggi quelle incomplete e conferma solo i clienti pronti.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('customer.import.index') }}" class="btn btn-light">Torna alle importazioni</a>
                    <form method="POST" action="{{ route('customer.import.destroy', $batch) }}" onsubmit="return confirm('Eliminare questa importazione e tutte le righe di staging?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-soft-danger">
                            Elimina importazione
                        </button>
                    </form>
                    @if($batch->valid_rows > 0)
                        <form method="POST" action="{{ route('customer.import.commit', $batch) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-label right">
                                <i class="ri-check-double-line label-icon align-middle fs-16 ms-2"></i>
                                Salva righe valide in Clienti
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-2"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Totale righe file</div><div class="fw-semibold fs-4">{{ $batch->total_rows }}</div></div></div>
                    <div class="col-md-2"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Valide</div><div class="fw-semibold fs-4 text-success">{{ $batch->valid_rows }}</div></div></div>
                    <div class="col-md-2"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Da completare</div><div class="fw-semibold fs-4 text-warning">{{ $batch->needs_review_rows }}</div></div></div>
                    <div class="col-md-2"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Duplicati nel file</div><div class="fw-semibold fs-4 text-danger">{{ $batch->duplicate_file_rows }}</div></div></div>
                    <div class="col-md-2"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Duplicati hotel</div><div class="fw-semibold fs-4 text-danger">{{ $batch->duplicate_hotel_rows }}</div></div></div>
                    <div class="col-md-2"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Duplicati catena</div><div class="fw-semibold fs-4 text-info">{{ $batch->duplicate_chain_rows }}</div></div></div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Riga</th>
                                <th>Cliente</th>
                                <th>Contatti</th>
                                <th>Residenza</th>
                                <th>Documento</th>
                                <th>Esito</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                @php
                                    $payload = $row->normalized_payload ?? [];
                                    $notes = $row->notes ?? [];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">Cliente {{ ($rows->firstItem() ?? 1) + $loop->index }}</div>
                                        <div class="text-muted small">Origine CSV: riga {{ $row->row_number }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ trim(($payload['nome'] ?? '') . ' ' . ($payload['cognome'] ?? '')) ?: 'Riga senza nominativo' }}</div>
                                        <div class="text-muted small">{{ $payload['tipo_cliente'] ?? '-' }} · {{ $payload['sesso'] ?? '-' }}</div>
                                        @if(!empty($payload['nome_gruppo']))
                                            <div class="text-muted small">Gruppo: {{ $payload['nome_gruppo'] }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">{{ $payload['email'] ?: '—' }}</div>
                                        <div class="text-muted small">{{ $payload['cellulare'] ?: ($payload['telefono'] ?: '—') }}</div>
                                    </td>
                                    <td>
                                        <div>{{ collect([$payload['indirizzo_residenza'] ?? null, $payload['numero_civico_residenza'] ?? null])->filter()->implode(' ') ?: '—' }}</div>
                                        <div class="text-muted small">{{ collect([$payload['cap_residenza'] ?? null, $payload['comune_residenza'] ?? null, $payload['provincia_residenza'] ?? null])->filter()->implode(' · ') ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ collect([$payload['tipo_documento'] ?? null, $payload['numero_documento'] ?? null])->filter()->implode(' / ') ?: '—' }}</div>
                                        <div class="text-muted small">{{ $payload['data_nascita'] ?: 'Data nascita mancante' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClasses[$row->status] ?? 'bg-light text-dark' }}">{{ $statusLabels[$row->status] ?? $row->status }}</span>
                                        @if(!empty($notes))
                                            <div class="mt-2 small text-muted">
                                                @foreach($notes as $note)
                                                    <div>{{ $note }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($row->duplicateCustomer)
                                            <div class="small mt-2">
                                                <span class="text-muted">Cliente correlato:</span>
                                                <span class="fw-semibold">{{ trim(($row->duplicateCustomer->name ?? '') . ' ' . ($row->duplicateCustomer->surname ?? '')) }}</span>
                                                @if($row->duplicateCustomer->struttura)
                                                    <div class="text-muted">{{ $row->duplicateCustomer->struttura->nome_struttura }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('customer.import.row.edit', [$batch, $row]) }}" class="btn btn-soft-primary btn-sm">
                                            Modifica riga
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">Nessuna riga trovata in questa importazione.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Mostrando {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} di {{ $rows->total() }} righe in verifica.
                        </div>
                        {{ $rows->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
