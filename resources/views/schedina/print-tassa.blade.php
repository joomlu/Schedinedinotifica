@extends('layouts.master')

@section('title') Ricevuta Tassa di soggiorno @endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card shadow-sm" id="ricevuta-tassa-card">
            <div class="card-header bg-light-subtle border-0 d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-1">Ricevuta costo tassa di soggiorno</h4>
                    <div class="text-muted small">Dettaglio calcolo per il soggiorno registrato</div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Stampa</button>
            </div>
            <div class="card-body">
                <div class="border rounded-3 p-4 mb-4 ricevuta-tassa">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            @if($logoComune)
                                <img src="{{ asset($logoComune) }}" alt="Logo Comune" class="rounded shadow-sm" style="max-height:72px;">
                            @endif
                            <div>
                                <div class="fw-semibold fs-5">{{ $struttura->nome_struttura ?? 'Struttura' }}</div>
                                <div class="text-muted">{{ trim(($struttura->indirizzo ?? '') . ' ' . ($struttura->numero_civico ?? '')) }}</div>
                                <div class="text-muted">{{ $struttura->cap ?? '' }} {{ $struttura->citta ?? '' }} {{ !empty($struttura->provincia) ? '(' . $struttura->provincia . ')' : '' }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Data stampa</div>
                            <div class="fw-semibold">{{ now()->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="text-muted small">Ospite principale</div>
                                <div class="fw-semibold">{{ trim(($schedina->surname ?? '') . ' ' . ($schedina->name ?? '')) ?: '—' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="text-muted small">Periodo soggiorno</div>
                                <div class="fw-semibold">{{ $arrivo?->format('d/m/Y') ?: '—' }} - {{ $partenza?->format('d/m/Y') ?: '—' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="text-muted small">Totale da pagare</div>
                                <div class="fw-semibold">{{ number_format($dettaglio['totale'] ?? 0, 2, ',', '.') }} €</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Età</th>
                                    <th>Esente</th>
                                    <th>Motivo</th>
                                    <th>Notti</th>
                                    <th>Nel periodo</th>
                                    <th>Imponibili</th>
                                    <th>Oltre max</th>
                                    <th>Tariffa</th>
                                    <th>Subtotale</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dettaglio['righe'] as $riga)
                                    <tr>
                                        <td>{{ $riga['nome'] }}</td>
                                        <td>{{ $riga['eta'] ?? '—' }}</td>
                                        <td>{{ $riga['esente'] ? 'Sì' : 'No' }}</td>
                                        <td>{{ $riga['motivo'] ?? '—' }}</td>
                                        <td>{{ $riga['notti_totali'] }}</td>
                                        <td>{{ $riga['notti_periodo'] ?? $riga['notti_totali'] }}</td>
                                        <td>{{ $riga['notti_imponibili'] }}</td>
                                        <td>{{ $riga['notti_oltre_max'] }}</td>
                                        <td>{{ number_format($riga['aliquota'], 2, ',', '.') }} €</td>
                                        <td>{{ number_format($riga['subtotale'], 2, ',', '.') }} €</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="9" class="text-end">Totale tassa di soggiorno</th>
                                    <th>{{ number_format($dettaglio['totale'], 2, ',', '.') }} €</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-muted small">
                        La presente ricevuta riporta il dettaglio del calcolo della tassa di soggiorno secondo il regolamento comunale vigente e la configurazione impostata per la struttura.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #ricevuta-tassa-card,
        #ricevuta-tassa-card * {
            visibility: visible;
        }

        #ricevuta-tassa-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none !important;
            border: 0 !important;
        }

        .main-content,
        .page-content,
        .container-fluid,
        .card-body {
            padding: 0 !important;
            margin: 0 !important;
        }

        .card-header .btn {
            display: none !important;
        }
    }
</style>
@endpush
