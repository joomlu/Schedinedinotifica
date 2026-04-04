@extends('layouts.master')
@section('title') Proforma @endsection

@php
    $areaLabel = $areaLabel ?? 'SuperAdmin';
    $ownerRoutePrefix = $ownerRoutePrefix ?? 'superadmin.proprietari';
    $recipientStructures = $proforma->righe->pluck('struttura')->filter()->unique('id')->values();
    $hasGeneralRows = $proforma->righe->contains(fn ($riga) => !$riga->struttura_id);
    $destinatario = ($recipientStructures->count() === 1 && !$hasGeneralRows)
        ? $recipientStructures->first()
        : $proprietario;
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') {{ $areaLabel }} @endslot
        @slot('title') Proforma {{ $proforma->numero }} @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Proforma {{ $proforma->numero }}</h4>
            <div class="text-muted">Emittente: Spire</div>
        </div>
        <div class="d-flex gap-2">
            @if(($canManageProforma ?? false) && $proforma->stato === 'proforma')
                <button
                    type="button"
                    class="btn btn-soft-info"
                    onclick="window.location.href='{{ route($ownerRoutePrefix . '.proforme.edit', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}'">
                    <i class="ri-edit-line align-bottom me-1"></i>
                    Modifica
                </button>
                <form action="{{ route($ownerRoutePrefix . '.proforme.close', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-soft-warning">
                        <i class="ri-lock-line align-bottom me-1"></i>
                        Chiudi
                    </button>
                </form>
                <form action="{{ route($ownerRoutePrefix . '.proforme.mark_fatturata', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="numero_fattura" value="{{ $proforma->numero_fattura }}">
                    <input type="hidden" name="data_pagamento" value="{{ optional($proforma->data_pagamento)->toDateString() }}">
                    <button type="submit" class="btn btn-success">
                        <i class="ri-checkbox-circle-line align-bottom me-1"></i>
                        Segna pagata
                    </button>
                </form>
            @endif
            <button
                type="button"
                class="btn btn-soft-secondary"
                onclick="window.open('{{ route($ownerRoutePrefix . '.proforme.print', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}', '_blank')">
                <i class="ri-printer-line align-bottom me-1"></i>
                Stampa
            </button>
            <button
                type="button"
                class="btn btn-light"
                onclick="window.location.href='{{ route($ownerRoutePrefix . '.edit', ['id' => $proprietario->id, 'tab' => 'storico']) }}'">
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
                                    in_array($proforma->stato, ['fatturata', 'pagata'], true) ? 'bg-success-subtle text-success' :
                                    ($proforma->stato === 'chiusa' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info')
                                }}">
                                    {{ in_array($proforma->stato, ['fatturata', 'pagata'], true) ? 'Pagata' : ucfirst($proforma->stato) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase mb-1">Destinatario</div>
                            <div class="fw-semibold">{{ $destinatario->ragione_sociale ?? $destinatario->nome_struttura ?? $destinatario->nome ?? '—' }}</div>
                            <div class="small text-muted">
                                P.IVA {{ $destinatario->partita_iva ?: '-' }}
                                @if(!empty($destinatario->codice_fiscale))
                                    · C.F. {{ $destinatario->codice_fiscale }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(($canManageProforma ?? false) && $proforma->stato === 'proforma')
                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-light-subtle">
                        <div class="fw-semibold">Chiusura pagamento</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route($ownerRoutePrefix . '.proforme.mark_fatturata', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}" method="POST" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Numero fattura</label>
                                <input type="text" name="numero_fattura" class="form-control" value="{{ old('numero_fattura', $proforma->numero_fattura) }}" placeholder="Es. FAT-2026-0012">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data pagamento</label>
                                <x-calendario
                                    name="data_pagamento"
                                    variant="single"
                                    :value="old('data_pagamento', optional($proforma->data_pagamento)->toDateString() ?: now()->toDateString())"
                                    placeholder="gg/mm/aaaa"
                                />
                            </div>
                            <div class="col-md-4 d-grid">
                                <button type="submit" class="btn btn-success">
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
                                    @if($riga->struttura)
                                        <div class="fw-semibold">{{ $riga->struttura->nome_struttura }}</div>
                                        <div class="small text-muted">Licenza o servizio di struttura</div>
                                    @else
                                        <div class="fw-semibold">{{ $proprietario->ragione_sociale ?: $proprietario->nome }}</div>
                                        <div class="small text-muted">Voce generale del proprietario</div>
                                    @endif
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
                    <div class="fw-semibold mb-1">Osservazioni</div>
                    <div class="text-muted">{{ $proforma->note }}</div>
                </div>
            @endif

            @if(in_array($proforma->stato, ['pagata', 'fatturata', 'ok'], true) && ($proforma->data_pagamento || $proforma->numero_fattura))
                <div class="mt-3">
                    <div class="fw-semibold mb-1">Riferimenti pagamento</div>
                    <div class="text-muted">
                        {{ optional($proforma->data_pagamento)->format('d/m/Y') ?: 'Data pagamento da indicare' }}
                        @if($proforma->numero_fattura)
                            · Fattura {{ $proforma->numero_fattura }}
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
