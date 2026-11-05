@extends('layouts.master')
@section('title') Proforma @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Proforma {{ $proforma->numero }} @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Proforma {{ $proforma->numero }}</h4>
            <div class="text-muted">{{ $admin->ragione_sociale ?: $admin->name }}</div>
        </div>
        <div class="d-flex gap-2">
            @if($proforma->stato === 'proforma')
                <button
                    type="button"
                    class="btn btn-soft-info"
                    onclick="window.location.href='{{ route('superadmin.amministratori.proforme.edit', ['id' => $admin->id, 'fatturazione' => $proforma->id]) }}'">
                    <i class="ri-edit-line align-bottom me-1"></i>
                    Modifica
                </button>
                <form action="{{ route('superadmin.amministratori.proforme.close', ['id' => $admin->id, 'fatturazione' => $proforma->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-soft-warning">
                        <i class="ri-lock-line align-bottom me-1"></i>
                        Chiudi
                    </button>
                </form>
            @endif
            <button
                type="button"
                class="btn btn-soft-secondary"
                onclick="window.open('{{ route('superadmin.amministratori.proforme.print', ['id' => $admin->id, 'fatturazione' => $proforma->id]) }}', '_blank')">
                <i class="ri-printer-line align-bottom me-1"></i>
                Stampa
            </button>
            <button
                type="button"
                class="btn btn-light"
                onclick="window.location.href='{{ route('superadmin.amministratori.edit', ['id' => $admin->id, 'tab' => 'fatturazione']) }}'">
                <i class="ri-arrow-left-line align-bottom me-1"></i>
                Torna a proforme
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase mb-1">Data</div>
                            <div class="fw-semibold">{{ optional($proforma->data_documento)->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase mb-1">Stato</div>
                            <div>
                                <span class="badge {{
                                    in_array($proforma->stato, ['pagata', 'fatturata'], true) ? 'bg-success-subtle text-success' :
                                    ($proforma->stato === 'chiusa' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info')
                                }}">
                                    {{ in_array($proforma->stato, ['pagata', 'fatturata'], true) ? 'Pagata' : ucfirst($proforma->stato) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase mb-1">Totale</div>
                            <div class="fw-semibold fs-4">{{ number_format((float) $proforma->totale, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($proforma->stato === 'proforma')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <div class="fw-semibold">Chiusura pagamento</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.amministratori.proforme.mark_fatturata', ['id' => $admin->id, 'fatturazione' => $proforma->id]) }}" method="POST" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Numero fattura</label>
                                <input type="text" name="numero_fattura" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data pagamento</label>
                                <x-calendario name="data_pagamento" variant="single" :value="now()->toDateString()" />
                            </div>
                            <div class="col-md-4 d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="ri-checkbox-circle-line align-bottom me-1"></i>
                                    Segna pagata
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="table-responsive border rounded-3">
                <table class="table align-middle">
                    <thead>
                        <tr class="table-light">
                            <th>Destinazione</th>
                            <th>Descrizione</th>
                            <th>Quantità</th>
                            <th>Prezzo unitario</th>
                            <th>Sconto</th>
                            <th>IVA</th>
                            <th>Imponibile</th>
                            <th class="text-end">Totale</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proforma->righe as $riga)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $admin->ragione_sociale ?: $admin->name }}</div>
                                    <div class="small text-muted">{{ $riga->servizio ? 'Servizio amministratore' : 'Voce libera per amministratore' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $riga->descrizione }}</div>
                                    @if($riga->note)
                                        <div class="small text-muted">{{ $riga->note }}</div>
                                    @endif
                                </td>
                                <td>{{ $riga->quantita }}</td>
                                <td>{{ number_format((float) $riga->prezzo_unitario, 2, ',', '.') }}</td>
                                <td>{{ $riga->sconto_tipo === 'importo' ? '€ ' : '' }}{{ number_format((float) $riga->sconto_valore, 2, ',', '.') }}{{ $riga->sconto_tipo === 'percentuale' ? '%' : '' }}</td>
                                <td>{{ number_format((float) $riga->aliquota_iva, 2, ',', '.') }}%</td>
                                <td>{{ number_format((float) $riga->imponibile, 2, ',', '.') }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $riga->totale, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-4 offset-md-8">
                    <div class="border rounded-3 p-3 bg-light-subtle">
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Imponibile</span><span class="fw-semibold">{{ number_format((float) $proforma->imponibile, 2, ',', '.') }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sconto totale</span><span class="fw-semibold">{{ number_format((float) $proforma->totale_sconto, 2, ',', '.') }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">IVA totale</span><span class="fw-semibold">{{ number_format((float) $proforma->totale_iva, 2, ',', '.') }}</span></div>
                        <div class="d-flex justify-content-between fs-5"><span class="fw-semibold">Totale</span><span class="fw-bold">{{ number_format((float) $proforma->totale, 2, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>

            @if($proforma->note)
                <div class="mt-3">
                    <div class="fw-semibold mb-1">Note</div>
                    <div class="text-muted">{{ $proforma->note }}</div>
                </div>
            @endif

            @if($proforma->numero_fattura || $proforma->data_pagamento)
                <div class="mt-3">
                    <div class="fw-semibold mb-1">Pagamento</div>
                    <div class="text-muted">
                        {{ $proforma->numero_fattura ? 'Fattura ' . $proforma->numero_fattura : 'N. fattura non indicato' }}
                        @if($proforma->data_pagamento)
                            · Pagata {{ optional($proforma->data_pagamento)->format('d/m/Y') }}
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
