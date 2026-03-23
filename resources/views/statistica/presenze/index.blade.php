@extends('layouts.master')
@section('title', 'Presenze')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Statistica @endslot
    @slot('title') Presenze @endslot
@endcomponent
@php
    $activeTab = request('tab', 'riepilogo');
@endphp

<div class="card">
    <div class="card-header border-0">
        <h4 class="card-title mb-1">Presenze</h4>
        <p class="text-muted mb-0">Riepilogo mensile annuale e dettaglio operativo per periodo. Dati utili per reception, amministrazione e commercialista.</p>
    </div>
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'riepilogo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#presenze-riepilogo" type="button" role="tab">Riepilogo mensile</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'dettaglio' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#presenze-dettaglio" type="button" role="tab">Dettaglio periodo</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'oggi' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#presenze-oggi" type="button" role="tab">Situazione giornaliera</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'movimenti' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#presenze-movimenti" type="button" role="tab">Arrivi / Partenze</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'occupazione' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#presenze-occupazione" type="button" role="tab">Occupazione</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'riepilogo' ? 'show active' : '' }}" id="presenze-riepilogo" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">Riepilogo annuale Arrivi / Partenze / Presenze</h5>
                            <p class="text-muted mb-0">Separazione tra Italia e paesi esteri, mese per mese. La colonna presenti indica le persone già presenti a inizio mese.</p>
                        </div>
                        <a href="{{ route('presenze.print.riepilogo', ['anno' => $anno, 'mese_da' => $meseDa, 'mese_a' => $meseA]) }}" target="_blank" class="btn btn-primary">Stampa riepilogo</a>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="tab" value="riepilogo">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label">Anno</label>
                                <x-ui.select name="anno">
                                    @for($year = now()->year + 1; $year >= 2020; $year--)
                                        <option value="{{ $year }}" @selected($anno === $year)>{{ $year }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-md-3 col-lg-3">
                                <label class="form-label">Da mese</label>
                                <x-ui.select name="mese_da">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($meseDa === $m)>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-md-3 col-lg-3">
                                <label class="form-label">A mese</label>
                                <x-ui.select name="mese_a">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($meseA === $m)>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        </form>

                        @if($riepilogo->every(fn ($mese) => ($mese['totale']['arrivi'] + $mese['totale']['partenze'] + $mese['totale']['presenze']) === 0))
                            <div class="alert alert-info">Nessun movimento trovato per il filtro selezionato.</div>
                        @endif

                        @foreach($riepilogo as $mese)
                            <div class="card border border-light-subtle shadow-none mb-3">
                                <div class="card-header bg-body-tertiary border-0 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">{{ \Illuminate\Support\Str::title($mese['mese_label']) }}</h6>
                                    <a href="{{ route('presenze.print.dettaglio', ['dal' => $mese['dal']->toDateString(), 'al' => $mese['al']->toDateString()]) }}" target="_blank" class="btn btn-light">Stampa dettaglio mese</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Paesi Esteri / Italia</th>
                                                    <th class="text-center">Presenti a inizio mese</th>
                                                    <th class="text-center">Arrivi</th>
                                                    <th class="text-center">Partenze</th>
                                                    <th class="text-center">Presenze</th>
                                                    <th class="text-center">Totale Paesi Esteri</th>
                                                    <th class="text-center">Totale Italiano</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Italiane</td>
                                                    <td class="text-center">{{ $mese['italiane']['presenti'] }}</td>
                                                    <td class="text-center">{{ $mese['italiane']['arrivi'] }}</td>
                                                    <td class="text-center">{{ $mese['italiane']['partenze'] }}</td>
                                                    <td class="text-center">{{ $mese['italiane']['presenze'] }}</td>
                                                    <td class="text-center">-</td>
                                                    <td class="text-center">{{ $mese['italiane']['presenze'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Straniere</td>
                                                    <td class="text-center">{{ $mese['straniere']['presenti'] }}</td>
                                                    <td class="text-center">{{ $mese['straniere']['arrivi'] }}</td>
                                                    <td class="text-center">{{ $mese['straniere']['partenze'] }}</td>
                                                    <td class="text-center">{{ $mese['straniere']['presenze'] }}</td>
                                                    <td class="text-center">{{ $mese['straniere']['presenze'] }}</td>
                                                    <td class="text-center">-</td>
                                                </tr>
                                                <tr class="table-light fw-medium">
                                                    <td>Totale</td>
                                                    <td class="text-center">{{ $mese['totale']['presenti'] }}</td>
                                                    <td class="text-center">{{ $mese['totale']['arrivi'] }}</td>
                                                    <td class="text-center">{{ $mese['totale']['partenze'] }}</td>
                                                    <td class="text-center">{{ $mese['totale']['presenze'] }}</td>
                                                    <td class="text-center">{{ $mese['totale']['totale_esteri'] }}</td>
                                                    <td class="text-center">{{ $mese['totale']['totale_italiani'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'dettaglio' ? 'show active' : '' }}" id="presenze-dettaglio" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">Dettaglio presenze per periodo</h5>
                            <p class="text-muted mb-0">Elenco analitico delle schedine con persone, adulti, minori, provenienza e presenze generate.</p>
                        </div>
                        <a href="{{ route('presenze.print.dettaglio', ['dal' => $dal->toDateString(), 'al' => $al->toDateString(), 'categoria' => $categoria]) }}" target="_blank" class="btn btn-primary">Stampa dettaglio</a>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="tab" value="dettaglio">
                            <div class="col-md-3">
                                <label class="form-label">Dal</label>
                                <x-calendario name="dal" variant="single" :value="$dal->toDateString()" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Al</label>
                                <x-calendario name="al" variant="single" :value="$al->toDateString()" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vista</label>
                                <x-ui.select name="categoria">
                                    <option value="tutte" @selected($categoria === 'tutte')>Tutti</option>
                                    <option value="italiane" @selected($categoria === 'italiane')>Solo italiani</option>
                                    <option value="straniere" @selected($categoria === 'straniere')>Solo stranieri</option>
                                </x-ui.select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        </form>

                        @if($dettaglio['rows']->isEmpty())
                            <div class="alert alert-info">Nessuna presenza trovata per il periodo selezionato.</div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Schedine</div><div class="fs-4">{{ $dettaglio['totali']['schedine'] }}</div></div></div></div>
                            <div class="col-md-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Arrivi</div><div class="fs-4">{{ $dettaglio['totali']['arrivi'] }}</div></div></div></div>
                            <div class="col-md-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Partenze</div><div class="fs-4">{{ $dettaglio['totali']['partenze'] }}</div></div></div></div>
                            <div class="col-md-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Presenze</div><div class="fs-4">{{ $dettaglio['totali']['presenze'] }}</div></div></div></div>
                            <div class="col-md-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Adulti</div><div class="fs-4">{{ $dettaglio['totali']['adulti'] }}</div></div></div></div>
                            <div class="col-md-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Minori</div><div class="fs-4">{{ $dettaglio['totali']['minori'] }}</div></div></div></div>
                        </div>

                        <div class="card border-0 shadow-sm mb-0">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-1">Dettaglio presenze</h5>
                                <p class="text-muted mb-0">Periodo {{ $dal->format('d/m/Y') }} - {{ $al->format('d/m/Y') }}</p>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Num.</th>
                                                <th>Arrivo</th>
                                                <th>Partenza</th>
                                                <th>Ospite</th>
                                                <th class="text-center">Q. Per.</th>
                                                <th class="text-center">Adulti</th>
                                                <th class="text-center">Minori</th>
                                                <th class="text-center">Presenze</th>
                                                <th>Provenienza</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($dettaglio['rows'] as $row)
                                                <tr>
                                                    <td class="text-nowrap">{{ $row['scheda'] }}</td>
                                                    <td class="text-nowrap">{{ optional($row['arrivo'])->format('d/m/Y') ?: '-' }}</td>
                                                    <td class="text-nowrap">{{ optional($row['partenza'])->format('d/m/Y') ?: '-' }}</td>
                                                    <td>{{ $row['ospite'] ?: '-' }}</td>
                                                    <td class="text-center">{{ $row['persone'] }}</td>
                                                    <td class="text-center">{{ $row['adulti'] }}</td>
                                                    <td class="text-center">{{ $row['minori'] }}</td>
                                                    <td class="text-center">{{ $row['presenze'] }}</td>
                                                    <td>{{ $row['provenienza'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted">Nessuna presenza nel periodo selezionato.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'oggi' ? 'show active' : '' }}" id="presenze-oggi" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-1">Situazione giornaliera</h5>
                        <p class="text-muted mb-0">Persone presenti in struttura, check-in e check-out del giorno selezionato.</p>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="tab" value="oggi">
                            <input type="hidden" name="anno" value="{{ $anno }}">
                            <input type="hidden" name="mese_da" value="{{ $meseDa }}">
                            <input type="hidden" name="mese_a" value="{{ $meseA }}">
                            <input type="hidden" name="dal" value="{{ $dal->toDateString() }}">
                            <input type="hidden" name="al" value="{{ $al->toDateString() }}">
                            <input type="hidden" name="categoria" value="{{ $categoria }}">
                            <input type="hidden" name="occupazione_anno" value="{{ $occupazioneAnno }}">
                            <input type="hidden" name="occupazione_mese" value="{{ $occupazioneMese }}">
                            <div class="col-md-3">
                                <label class="form-label">Giorno</label>
                                <x-calendario name="giorno_situazione" variant="birth" :value="$giornoSituazione->toDateString()" />
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        </form>

                        <div class="alert alert-info mb-4">Data analizzata: {{ $giornoSituazione->format('d/m/Y') }}</div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Presenti</div><div class="fs-4">{{ $oggi['presenti_totali'] }}</div></div></div></div>
                            <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Adulti</div><div class="fs-4">{{ $oggi['adulti'] }}</div></div></div></div>
                            <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Minori</div><div class="fs-4">{{ $oggi['minori'] }}</div></div></div></div>
                            <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Italiani</div><div class="fs-4">{{ $oggi['italiani'] }}</div></div></div></div>
                            <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Stranieri</div><div class="fs-4">{{ $oggi['stranieri'] }}</div></div></div></div>
                            <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Camere occupate</div><div class="fs-4">{{ $oggi['camere_occupate'] }}</div></div></div></div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Check-in del giorno</div><div class="fs-4">{{ $oggi['arrivi_oggi'] }}</div></div></div></div>
                            <div class="col-md-6"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Check-out del giorno</div><div class="fs-4">{{ $oggi['partenze_oggi'] }}</div></div></div></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm mb-0">
                                    <div class="card-header border-0"><h5 class="card-title mb-0">Arrivi del giorno</h5></div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light"><tr><th>Num.</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Provenienza</th></tr></thead>
                                                <tbody>
                                                    @forelse($oggi['arrivi_rows'] as $row)
                                                        <tr><td>{{ $row['scheda'] }}</td><td>{{ $row['ospite'] }}</td><td class="text-center">{{ $row['persone'] }}</td><td>{{ $row['provenienza'] }}</td></tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center text-muted">Nessun check-in nel giorno selezionato.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm mb-0">
                                    <div class="card-header border-0"><h5 class="card-title mb-0">Partenze del giorno</h5></div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light"><tr><th>Num.</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Provenienza</th></tr></thead>
                                                <tbody>
                                                    @forelse($oggi['partenze_rows'] as $row)
                                                        <tr><td>{{ $row['scheda'] }}</td><td>{{ $row['ospite'] }}</td><td class="text-center">{{ $row['persone'] }}</td><td>{{ $row['provenienza'] }}</td></tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center text-muted">Nessun check-out nel giorno selezionato.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'movimenti' ? 'show active' : '' }}" id="presenze-movimenti" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-1">Arrivi / Partenze per periodo</h5>
                        <p class="text-muted mb-0">Elenco operativo delle schedine che entrano o escono nel periodo selezionato.</p>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="tab" value="movimenti">
                            <input type="hidden" name="anno" value="{{ $anno }}">
                            <input type="hidden" name="mese_da" value="{{ $meseDa }}">
                            <input type="hidden" name="mese_a" value="{{ $meseA }}">
                            <input type="hidden" name="categoria" value="{{ $categoria }}">
                            <input type="hidden" name="occupazione_anno" value="{{ $occupazioneAnno }}">
                            <input type="hidden" name="occupazione_mese" value="{{ $occupazioneMese }}">
                            <div class="col-md-3">
                                <label class="form-label">Dal</label>
                                <x-calendario name="dal" variant="single" :value="$dal->toDateString()" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Al</label>
                                <x-calendario name="al" variant="single" :value="$al->toDateString()" />
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        </form>

                        @if(collect($movimenti['arrivi'])->isEmpty() && collect($movimenti['partenze'])->isEmpty())
                            <div class="alert alert-info mb-4">Nessun arrivo o partenza trovato nel periodo selezionato.</div>
                        @endif
                        <div class="row g-3 mb-4">
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Schedine in arrivo</div><div class="fs-4">{{ $movimenti['totali']['schedine_arrivo'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Persone in arrivo</div><div class="fs-4">{{ $movimenti['totali']['persone_arrivo'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Schedine in partenza</div><div class="fs-4">{{ $movimenti['totali']['schedine_partenza'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Persone in partenza</div><div class="fs-4">{{ $movimenti['totali']['persone_partenza'] }}</div></div></div></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm mb-0">
                                    <div class="card-header border-0"><h5 class="card-title mb-0">Arrivi nel periodo</h5></div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light"><tr><th>Num.</th><th>Arrivo</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Provenienza</th></tr></thead>
                                                <tbody>
                                                    @forelse($movimenti['arrivi'] as $row)
                                                        <tr><td>{{ $row['scheda'] }}</td><td>{{ optional($row['arrivo'])->format('d/m/Y') ?: '-' }}</td><td>{{ $row['ospite'] }}</td><td class="text-center">{{ $row['persone'] }}</td><td>{{ $row['provenienza'] }}</td></tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center text-muted">Nessun arrivo nel periodo.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card border-0 shadow-sm mb-0">
                                    <div class="card-header border-0"><h5 class="card-title mb-0">Partenze nel periodo</h5></div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light"><tr><th>Num.</th><th>Partenza</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Provenienza</th></tr></thead>
                                                <tbody>
                                                    @forelse($movimenti['partenze'] as $row)
                                                        <tr><td>{{ $row['scheda'] }}</td><td>{{ optional($row['partenza'])->format('d/m/Y') ?: '-' }}</td><td>{{ $row['ospite'] }}</td><td class="text-center">{{ $row['persone'] }}</td><td>{{ $row['provenienza'] }}</td></tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center text-muted">Nessun check-out nel periodo.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'occupazione' ? 'show active' : '' }}" id="presenze-occupazione" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-1">Occupazione</h5>
                        <p class="text-muted mb-0">Camere e letti occupati rispetto alla disponibilità della struttura nel periodo selezionato.</p>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="tab" value="occupazione">
                            <input type="hidden" name="anno" value="{{ $anno }}">
                            <input type="hidden" name="mese_da" value="{{ $meseDa }}">
                            <input type="hidden" name="mese_a" value="{{ $meseA }}">
                            <input type="hidden" name="dal" value="{{ $dal->toDateString() }}">
                            <input type="hidden" name="al" value="{{ $al->toDateString() }}">
                            <input type="hidden" name="categoria" value="{{ $categoria }}">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label">Anno</label>
                                <x-ui.select name="occupazione_anno">
                                    @for($year = now()->year + 1; $year >= 2020; $year--)
                                        <option value="{{ $year }}" @selected($occupazioneAnno === $year)>{{ $year }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label">Mese</label>
                                <x-ui.select name="occupazione_mese">
                                    <option value="tutto" @selected($occupazioneMese === 'tutto')>Tutto l'anno</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected((string) $occupazioneMese === (string) $m)>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        </form>

                        @if(collect($occupazione['rows'])->isEmpty() || ((int) collect($occupazione['rows'])->sum('persone_presenti') === 0))
                            <div class="alert alert-info mb-4">Nessun dato di occupazione trovato per il filtro selezionato.</div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Media camere occupate</div><div class="fs-4">{{ $occupazione['totali']['media_camere_occupate'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Media letti occupati</div><div class="fs-4">{{ $occupazione['totali']['media_letti_occupati'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Occupazione camere media</div><div class="fs-4">{{ $occupazione['totali']['media_occupazione_camere'] }}%</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Picco persone presenti</div><div class="fs-4">{{ $occupazione['totali']['picco_persone_presenti'] }}</div></div></div></div>
                        </div>

                        <div class="card border-0 shadow-sm mb-0">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-1">Andamento giornaliero</h5>
                                <p class="text-muted mb-0">Periodo {{ $occupazioneDal->format('d/m/Y') }} - {{ $occupazioneAl->format('d/m/Y') }}</p>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Giorno</th>
                                                <th class="text-center">Cam. disp.</th>
                                                <th class="text-center">Cam. occup.</th>
                                                <th class="text-center">Occupaz. camere</th>
                                                <th class="text-center">Letti disp.</th>
                                                <th class="text-center">Letti occup.</th>
                                                <th class="text-center">Occupaz. letti</th>
                                                <th class="text-center">Persone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($occupazione['rows'] as $row)
                                                <tr>
                                                    <td>{{ $row['giorno']->format('d/m/Y') }}</td>
                                                    <td class="text-center">{{ $row['camere_disponibili'] }}</td>
                                                    <td class="text-center">{{ $row['camere_occupate'] }}</td>
                                                    <td class="text-center">{{ $row['occupazione_camere'] }}%</td>
                                                    <td class="text-center">{{ $row['letti_disponibili'] }}</td>
                                                    <td class="text-center">{{ $row['letti_occupati'] }}</td>
                                                    <td class="text-center">{{ $row['occupazione_letti'] }}%</td>
                                                    <td class="text-center">{{ $row['persone_presenti'] }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" class="text-center text-muted">Nessun dato di occupazione nel periodo.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
