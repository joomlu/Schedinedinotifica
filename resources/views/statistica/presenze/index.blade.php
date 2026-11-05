@extends('layouts.master')
@section('title', 'Presenze')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Statistica @endslot
    @slot('title') Presenze @endslot
@endcomponent
@php
    $activeTab = request('tab', 'riepilogo');
    $filtriApplicati = [
        'anno' => $anno, 'mese_da' => $meseDa, 'mese_a' => $meseA,
        'dal' => $dal->toDateString(), 'al' => $al->toDateString(), 'categoria' => $categoria,
        'occupazione_anno' => $occupazioneAnno, 'occupazione_mese' => $occupazioneMese,
        'giorno_situazione' => $giornoSituazione->toDateString(),
    ];
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

        @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
        <details class="mb-3"><summary>Criteri di calcolo e dati disponibili</summary><p class="mt-2">Arrivi e partenze contano le persone; le presenze sono pernottamenti, con arrivo incluso e partenza esclusa. Sono inclusi soltanto i soggiorni effettivi: Arrivi, bozze, cancellazioni e no-show devono rimanere fuori da questo circuito. La residenza è distinta dalla cittadinanza; l’età è calcolata all’arrivo. Dati sconosciuti segnalati separatamente.</p></details>
        @foreach($avvisi as $avviso)<div class="alert alert-warning">{{ $avviso }}</div>@endforeach
        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'riepilogo' ? 'show active' : '' }}" id="presenze-riepilogo" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">Riepilogo mensile Arrivi / Partenze / Presenze</h5>
                            <p class="text-muted mb-0">Movimenti e pernottamenti per mese e residenza degli ospiti.</p>
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
                        <input type="hidden" name="dal" value="{{ $dal->toDateString() }}"><input type="hidden" name="al" value="{{ $al->toDateString() }}"><input type="hidden" name="categoria" value="{{ $categoria }}"><input type="hidden" name="occupazione_anno" value="{{ $occupazioneAnno }}"><input type="hidden" name="occupazione_mese" value="{{ $occupazioneMese }}"><input type="hidden" name="giorno_situazione" value="{{ $giornoSituazione->toDateString() }}"></form>

                        @include('statistica.presenze.riepilogo')
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'dettaglio' ? 'show active' : '' }}" id="presenze-dettaglio" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">Dettaglio presenze per periodo</h5>
                            <p class="text-muted mb-0">Soggiorni che intersecano il periodo, incluse le partenze del primo giorno. Il filtro residenza conta i singoli ospiti; provenienza e nominativo identificano il titolare. Età non disponibile esclusa dai totali adulti/minori.</p>
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
                                    <option value="italiane" @selected($categoria === 'italiane')>Residenza in Italia</option>
                                    <option value="straniere" @selected($categoria === 'straniere')>Residenza estera</option><option value="sconosciute" @selected($categoria === 'sconosciute')>Residenza non disponibile</option>
                                </x-ui.select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        <input type="hidden" name="anno" value="{{ $anno }}"><input type="hidden" name="mese_da" value="{{ $meseDa }}"><input type="hidden" name="mese_a" value="{{ $meseA }}"><input type="hidden" name="occupazione_anno" value="{{ $occupazioneAnno }}"><input type="hidden" name="occupazione_mese" value="{{ $occupazioneMese }}"><input type="hidden" name="giorno_situazione" value="{{ $giornoSituazione->toDateString() }}"></form>

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
                                                <th>Residenza titolare</th>
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

                        <h6>Ospiti pernottanti nella notte selezionata</h6>
                        <div class="table-responsive mb-3"><table class="table table-hover"><thead><tr><th>Schedina</th><th>Titolare</th><th>Persone</th><th>Arrivo</th><th>Partenza</th></tr></thead><tbody>
                        @forelse($oggi['presenti_rows'] as $row)
                            <tr><td>{{ $row['scheda'] }}</td><td>{{ $row['ospite'] }}</td><td>{{ $row['persone'] }}</td><td>{{ optional($row['arrivo'])->format('d/m/Y') }}</td><td>{{ optional($row['partenza'])->format('d/m/Y') }}</td></tr>
                        @empty
                            <tr><td colspan="5">Nessun ospite pernottante nella notte selezionata.</td></tr>
                        @endforelse
                        </tbody></table></div>
                        <p>Residenza non disponibile: {{ $oggi['sconosciuti'] }} persone. Età non disponibile: {{ $oggi['eta_non_disponibile'] }} persone.</p>
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
                                                <thead class="table-light"><tr><th>Num.</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Residenza titolare</th></tr></thead>
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
                                                <thead class="table-light"><tr><th>Num.</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Residenza titolare</th></tr></thead>
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
                        <input type="hidden" name="giorno_situazione" value="{{ $giornoSituazione->toDateString() }}"></form>

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
                                                <thead class="table-light"><tr><th>Num.</th><th>Arrivo</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Residenza titolare</th></tr></thead>
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
                                                <thead class="table-light"><tr><th>Num.</th><th>Partenza</th><th>Ospite</th><th class="text-center">Q. Per.</th><th>Residenza titolare</th></tr></thead>
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
                        <p class="text-muted mb-0">Camere occupate e persone pernottanti rispetto alla capacità della struttura nei giorni di apertura. Percentuale del periodo = notti occupate / notti disponibili × 100. La capacità è quella attuale: non esiste uno storico di camere fuori servizio o cambi camera datati.</p>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="tab" value="occupazione">
                            <input type="hidden" name="anno" value="{{ $anno }}">
                            <input type="hidden" name="mese_da" value="{{ $meseDa }}">
                            <input type="hidden" name="mese_a" value="{{ $meseA }}">
                            <div class="col-md-3 presenze-custom-range"><label class="form-label">Dal (periodo personalizzato)</label><x-calendario name="dal" variant="single" :value="$dal->toDateString()" /></div>
                            <div class="col-md-3 presenze-custom-range"><label class="form-label">Al (periodo personalizzato)</label><x-calendario name="al" variant="single" :value="$al->toDateString()" /></div>
                            <input type="hidden" name="categoria" value="{{ $categoria }}">
                            <div class="col-md-3 col-lg-2 presenze-month-year">
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
                                    <option value="periodo" @selected($occupazioneMese === 'periodo')>Periodo Dal / Al delle altre schede</option><option value="tutto" @selected($occupazioneMese === 'tutto')>Tutto l'anno</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected((string) $occupazioneMese === (string) $m)>{{ \Carbon\Carbon::create(null, $m, 1)->locale('it')->monthName }}</option>
                                    @endfor
                                </x-ui.select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        <input type="hidden" name="giorno_situazione" value="{{ $giornoSituazione->toDateString() }}"></form>

                        @if($occupazione['totali']['incoerenze'])<div class="alert alert-warning">{{ $occupazione['totali']['incoerenze'] }} giorni con occupazione superiore alla capacità: verificare soggiorni e disponibilità.</div>@endif
                        <p>Camere-notte: {{ $occupazione['totali']['camere_notti_occupate'] }} occupate / {{ $occupazione['totali']['camere_notti_disponibili'] }} disponibili. Posti-notte: {{ $occupazione['totali']['posti_notti_occupati'] }} occupati / {{ $occupazione['totali']['posti_notti_disponibili'] }} disponibili. Occupazione posti letto del periodo: {{ $occupazione['totali']['media_occupazione_letti'] === null ? 'N/D' : $occupazione['totali']['media_occupazione_letti'].'%' }}.</p>
                        @if(collect($occupazione['rows'])->isEmpty() || ((int) collect($occupazione['rows'])->sum('persone_presenti') === 0))
                            <div class="alert alert-info mb-4">Nessun dato di occupazione trovato per il filtro selezionato.</div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Media camere occupate</div><div class="fs-4">{{ $occupazione['totali']['media_camere_occupate'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Media persone pernottanti</div><div class="fs-4">{{ $occupazione['totali']['media_letti_occupati'] }}</div></div></div></div>
                            <div class="col-md-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Occupazione camere del periodo</div><div class="fs-4">{{ $occupazione['totali']['media_occupazione_camere'] === null ? 'N/D' : $occupazione['totali']['media_occupazione_camere'].'%' }}</div></div></div></div>
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
                                                <th class="text-center">Persone pernottanti</th>
                                                <th class="text-center">Occupaz. posti letto</th>
                                                                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($occupazione['rows'] as $row)
                                                <tr>
                                                    <td>{{ $row['giorno']->format('d/m/Y') }}</td>
                                                    <td class="text-center">{{ $row['camere_disponibili'] }}</td>
                                                    <td class="text-center">{{ $row['camere_occupate'] }}</td>
                                                    <td class="text-center">{{ $row['occupazione_camere'] === null ? 'N/D' : $row['occupazione_camere'].'%' }}</td>
                                                    <td class="text-center">{{ $row['letti_disponibili'] }}</td>
                                                    <td class="text-center">{{ $row['letti_occupati'] }}</td>
                                                    <td class="text-center">{{ $row['occupazione_letti'] === null ? 'N/D' : $row['occupazione_letti'].'%' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Nessun dato di occupazione nel periodo.</td></tr>
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

@section('script')
<style>#presenze-occupazione form:not(:has(select[name="occupazione_mese"] option[value="periodo"]:checked)) .presenze-custom-range { display:none; }#presenze-occupazione form:has(select[name="occupazione_mese"] option[value="periodo"]:checked) .presenze-month-year { display:none; }</style>
<script>
document.querySelectorAll('form[method="GET"]').forEach(form => {
    form.addEventListener('submit', event => {
        const data = new FormData(form);
        const invalid = data.get('dal') > data.get('al') || Number(data.get('mese_da')) > Number(data.get('mese_a'));
        if (invalid) { event.preventDefault(); alert('La data o il mese iniziale non può seguire quello finale.'); }
    });
});
const presenzeFiltriApplicati = @json($filtriApplicati);
function presenzeAggiornaIndirizzo(tab) {
    const url = new URL(location.href);
    Object.entries(presenzeFiltriApplicati).forEach(([nome, valore]) => url.searchParams.set(nome, valore));
    url.searchParams.set('tab', tab);
    history.replaceState(null, '', url);
}
presenzeAggiornaIndirizzo(@json($activeTab));
document.querySelectorAll('[data-bs-target^="#presenze-"]').forEach(button => button.addEventListener('shown.bs.tab', () => {
    presenzeAggiornaIndirizzo(button.dataset.bsTarget.replace('#presenze-', ''));
}));
</script>
@endsection
