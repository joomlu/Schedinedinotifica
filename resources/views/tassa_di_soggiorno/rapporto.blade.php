@extends('layouts.master')

@section('title') Rapporto mensile Tassa di soggiorno @endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Rapporto mensile Tassa di soggiorno @endslot
@endcomponent

@if(!empty($missingSchedina))
    <div class="alert alert-warning"><strong>Tabella schedina assente.</strong> Esegui le migrazioni o importa il dump iniziale prima di generare il rapporto.</div>
@endif

<div class="row config-page">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end mb-3" action="{{ route('tassa_di_soggiorno.rapporto') }}">
                    <div class="col-md-3">
                        <label class="form-label">Mese</label>
                        <x-ui.select name="mese">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)$mese === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                            @endfor
                        </x-ui.select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Anno</label>
                        <input type="number" class="form-control" name="anno" value="{{ $anno }}" min="2015" max="2100">
                    </div>
                    <div class="col-md-7 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="ri-refresh-line me-1"></i> Aggiorna</button>
                        <a href="{{ route('tassa_di_soggiorno.rapporto.csv', ['mese' => $mese, 'anno' => $anno]) }}"
                           class="btn btn-outline-success {{ !empty($missingSchedina) ? 'disabled' : '' }}"
                           @if(!empty($missingSchedina)) aria-disabled="true" tabindex="-1" @endif>
                            <i class="ri-download-2-line me-1"></i> Scarica CSV
                        </a>
                    </div>
                </form>

                <div class="alert alert-info">
                    <strong>Struttura:</strong> {{ $struttura->nome_struttura ?? '—' }} — <strong>Aliquota:</strong> {{ $config->tassa_soggiorno ?? 'n/d' }} € — <strong>Giorni max:</strong> {{ $config->giorni_massimo ?? 'n/d' }}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>N. Scheda</th>
                                <th>Nominativo</th>
                                <th>Età</th>
                                <th>Esente</th>
                                <th>Motivo</th>
                                <th>Pernottamenti imponibili</th>
                                <th>Pernottamenti oltre giorni max</th>
                                <th>Tassa (€)</th>
                                <th>Tariffa (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($righe as $riga)
                                <tr>
                                    <td>{{ $riga['arrivo'] ? \Carbon\Carbon::parse($riga['arrivo'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $riga['partenza'] ? \Carbon\Carbon::parse($riga['partenza'])->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $riga['scheda'] }}</td>
                                    <td>{{ $riga['nominativo'] }}</td>
                                    <td>{{ $riga['eta'] ?? '—' }}</td>
                                    <td>{{ $riga['esente'] ? 'Sì' : 'No' }}</td>
                                    <td>{{ $riga['motivo'] ?? '—' }}</td>
                                    <td>{{ $riga['pernottamenti_imponibili'] }}</td>
                                    <td>{{ $riga['pernottamenti_oltre_max'] }}</td>
                                    <td>{{ number_format($riga['tassa'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($riga['tariffa'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted">Nessun dato per il periodo selezionato.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $righe->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
