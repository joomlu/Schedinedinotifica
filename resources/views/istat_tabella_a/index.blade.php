@extends('layouts.master')

@section('title', 'Tabella A Emilia-Romagna')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Tabella A Emilia-Romagna @endslot
@endcomponent

@php
    $activeTab = request('tab', 'operativita');
    $riepilogoTotali = [
        'camere_disponibili' => collect($analysis['rows'] ?? [])->sum('camere_disponibili'),
        'letti_disponibili' => collect($analysis['rows'] ?? [])->sum('letti_disponibili'),
        'camere_occupate' => collect($analysis['rows'] ?? [])->sum('camere_occupate'),
        'arrivi' => collect($analysis['rows'] ?? [])->sum('arrivi'),
        'partenze' => collect($analysis['rows'] ?? [])->sum('partenze'),
        'presenti' => collect($analysis['rows'] ?? [])->sum('presenti'),
        'presenti_italiani' => collect($analysis['rows'] ?? [])->sum('presenti_italiani'),
        'presenti_stranieri' => collect($analysis['rows'] ?? [])->sum('presenti_stranieri'),
    ];
    $countryLabel = function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_numeric($value)) {
            return \App\Models\GeoNazione::query()->find((int) $value)?->nome ?: (string) $value;
        }
        return (string) $value;
    };
    $regionLabel = function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_numeric($value)) {
            return \App\Models\GeoRegione::query()->find((int) $value)?->nome ?: (string) $value;
        }
        return (string) $value;
    };
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">Tabella A Emilia-Romagna</h4>
            <div class="text-muted">XML ufficiale Ross1000 Emilia-Romagna, invio diretto elettronico ed esito operativo nello stesso pannello.</div>
        </div>
        <div class="text-md-end">
            @if(!empty($credStatus['simulation']))
                <span class="badge bg-info-subtle text-info">Modalità prova invio attiva</span>
                <div class="small text-muted mt-1">Verifica, invio diretto ed esito usano risposte demo interne.</div>
            @elseif($credStatus['configured'])
                <span class="badge bg-success-subtle text-success">Invio diretto configurato</span>
            @else
                <span class="badge bg-warning-subtle text-warning">Invio diretto non completo</span>
                <div class="small text-muted mt-1">Manca: {{ implode(', ', $credStatus['missing']) }}</div>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(!$regionSupported)
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="fw-semibold mb-1">Regione struttura non supportata per questo modulo</div>
                <div class="small mb-0">{{ $regionMessage }}</div>
            </div>
        @endif

        <div class="card border-0 bg-light-subtle mb-3">
            <div class="card-header border-0 py-2 d-flex align-items-center">
                <i class="ri-shield-check-line me-2 text-primary"></i>
                <h5 class="card-title mb-0 fs-6">Verifica configurazione Tabella A</h5>
            </div>
            <div class="card-body pt-2">
                <div class="row g-3 small">
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Regione struttura</div>
                        <div class="{{ $regionSupported ? 'text-success' : 'text-danger' }}">{{ $struttura->regione ?: 'Non indicata' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Username ISTAT</div>
                        <div class="{{ filled($struttura->istat_username) ? 'text-success' : 'text-danger' }}">{{ filled($struttura->istat_username) ? 'Presente' : 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Password ISTAT</div>
                        <div class="{{ filled($struttura->istat_password) ? 'text-success' : 'text-danger' }}">{{ filled($struttura->istat_password) ? 'Presente' : 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Codice Ross1000</div>
                        <div class="{{ filled($struttura->istat_codice_struttura) ? 'text-success' : 'text-danger' }}">{{ $struttura->istat_codice_struttura ?: 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">URL Web Service</div>
                        <div class="{{ filled($struttura->istat_ws_url) || !empty($credStatus['simulation']) ? 'text-success' : 'text-warning' }}">{{ $struttura->istat_ws_url ?: 'Default Ross1000' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Modalità</div>
                        <div class="{{ !empty($credStatus['simulation']) ? 'text-info' : 'text-body' }}">{{ !empty($credStatus['simulation']) ? 'Simulazione attiva' : 'Invio reale' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'operativita' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#istat-pane-operativita" type="button" role="tab">Operatività</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'controllo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#istat-pane-controllo" type="button" role="tab">Riepilogo giornaliero</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'storico-xml' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#istat-pane-storico-xml" type="button" role="tab">Storico XML</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'storico-invio' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#istat-pane-storico-invio" type="button" role="tab">Storico invio diretto</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'operativita' ? 'show active' : '' }}" id="istat-pane-operativita" role="tabpanel">
                <div class="card border-0 bg-light-subtle mb-3">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-calendar-event-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Periodo e filtri</h5>
                    </div>
                    <div class="card-body pt-2">
                        <form method="GET" action="{{ route('istat.tabella_a.index') }}" class="row g-3 align-items-end">
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Dal</label>
                                <x-calendario name="dal" variant="single" :value="$dal->format('Y-m-d')" placeholder="gg/mm/aaaa" />
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Al</label>
                                <x-calendario name="al" variant="single" :value="$al->format('Y-m-d')" placeholder="gg/mm/aaaa" />
                            </div>
                            <div class="col-xl-3 col-md-6 d-flex justify-content-xl-end gap-2">
                                <button type="submit" class="btn btn-primary">Aggiorna periodo</button>
                                <a href="{{ route('istat.tabella_a.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Schedine nel periodo</div><div class="fs-4">{{ $analysis['totale_schedine'] }}</div></div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Arrivi del periodo</div><div class="fs-4">{{ $analysis['totale_arrivi'] }}</div></div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Presenze calcolate</div><div class="fs-4">{{ $analysis['totale_presenze'] }}</div></div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body py-3"><div class="text-muted small mb-1">Dati da correggere</div><div class="fs-4 {{ $analysis['valida'] ? 'text-success' : 'text-danger' }}">{{ count($analysis['errors']) }}</div></div></div>
                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm mb-3">
                    <div class="fw-semibold mb-1">Ambito operativo del periodo selezionato</div>
                    <div class="small mb-0">
                        L'elenco qui sotto mostra una <strong>anteprima paginata</strong> delle schedine del periodo. Le azioni
                        <strong>Scarica XML</strong>, <strong>Verifica invio diretto</strong> e <strong>Invia direttamente</strong>
                        lavorano invece su <strong>tutte le {{ $analysis['totale_schedine'] }} schedine</strong> comprese tra
                        {{ $dal->format('d/m/Y') }} e {{ $al->format('d/m/Y') }}.
                    </div>
                </div>

                <x-crud-table
                    title="Anteprima schedine per Tabella A Emilia-Romagna"
                    subtitle="Vista operativa mensile per controllare se i dati delle schedine sono coerenti prima di scaricare o inviare l XML Tabella A. Gli ospiti marcati come non turisti restano fuori dal conteggio statistico."
                    searchPlaceholder="Cerca per numero schedina, provenienza o regione..."
                    searchId="istatSearch"
                    createText=""
                    createTarget="#"
                    tableId="istatTable"
                    :paginator="$schedinePaginator"
                >
                    <x-slot name="columns">
                        <tr>
                            <th class="text-nowrap" style="width: 10%;">Num.</th>
                            <th class="text-nowrap" style="width: 8%;">Arr.</th>
                            <th class="text-nowrap" style="width: 8%;">Part.</th>
                            <th class="text-center text-nowrap" style="width: 7%;">Q. Per.</th>
                            <th class="text-center text-nowrap" style="width: 7%;">Q. Cam.</th>
                            <th class="text-center text-nowrap" style="width: 7%;">Q. Lett.</th>
                            <th style="width: 12%;">Prov.</th>
                            <th style="width: 12%;">Reg.</th>
                            <th class="text-nowrap" style="width: 10%;">XML</th>
                            <th class="text-nowrap" style="width: 10%;">Invio</th>
                            <th style="width: 18%;">Dati</th>
                            <th class="text-end text-nowrap" style="width: 9%;">Azioni</th>
                        </tr>
                    </x-slot>
                    <x-slot name="rows">
                        @foreach($schedinePaginator as $schedina)
                            @php
                                $prefix = $schedina->scheda ?: ('Schedina #' . $schedina->id);
                                $schedinaErrors = collect($analysis['errors'])->filter(fn ($error) => str_starts_with($error, $prefix . ':'))->values();
                                $provValue = $countryLabel($schedina->or_country);
                                $regValue = $regionLabel($schedina->or_region);
                            @endphp
                            <tr class="data-row">
                                <td class="align-middle text-nowrap small">{{ $schedina->scheda ?: '-' }}</td>
                                <td class="align-middle text-nowrap small">{{ $schedina->arrive ? \Carbon\Carbon::parse($schedina->arrive)->format('d/m/y') : '-' }}</td>
                                <td class="align-middle text-nowrap small">{{ $schedina->departure ? \Carbon\Carbon::parse($schedina->departure)->format('d/m/y') : '-' }}</td>
                                <td class="align-middle text-center small">{{ $schedina->cant_people ?: '-' }}</td>
                                <td class="align-middle text-center small">{{ $schedina->room ?: '-' }}</td>
                                <td class="align-middle text-center small">{{ $schedina->beds ?: '-' }}</td>
                                <td class="align-middle small">{{ $provValue }}</td>
                                <td class="align-middle small">{{ $regValue }}</td>
                                <td class="align-middle text-nowrap small">
                                    @if(($schedina->istat_export_count ?? 0) > 0)
                                        <span class="badge bg-info-subtle text-info">Scaricato {{ $schedina->istat_export_count }}x</span>
                                    @else
                                        <span class="badge bg-light text-body">Mai scaricato</span>
                                    @endif
                                </td>
                                <td class="align-middle text-nowrap small">
                                    @if(($schedina->istat_send_count ?? 0) > 0)
                                        <span class="badge bg-success-subtle text-success">Inviato {{ $schedina->istat_send_count }}x</span>
                                    @else
                                        <span class="badge bg-light text-body">Mai inviato</span>
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    @if($schedinaErrors->isEmpty())
                                        <span class="text-success">Pronta per XML</span>
                                    @else
                                        <div class="small text-danger">
                                            @foreach($schedinaErrors as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end align-middle text-nowrap">
                                    <a href="{{ route('schedina.edit', ['id' => $schedina->id]) }}" class="btn btn-soft-info" title="Apri schedina">
                                        <i class="ri-edit-line fs-16 align-middle"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-crud-table>

                <div class="card border-0 bg-light-subtle mt-3">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-send-plane-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Azioni disponibili</h5>
                    </div>
                    <div class="card-body pt-2">
                        <div class="small text-muted mb-2">
                            <strong>Scarica XML Tabella A</strong> produce il file ufficiale `.xml` del periodo selezionato da conservare e usare per il caricamento mensile. <strong>Verifica invio diretto</strong> e <strong>Invia direttamente</strong> usano lo stesso XML sul collegamento elettronico Ross1000, sempre riferito all'intero periodo selezionato.
                        </div>
                        <div class="small text-muted mb-3">
                            Le azioni restano sempre disponibili. Se manca qualche dato obbligatorio della struttura o delle schedine, il sistema te lo segnala al momento dell'esecuzione senza bloccare la schermata.
                        </div>
                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <a href="{{ route('istat.tabella_a.print.summary', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()]) }}" class="btn btn-soft-secondary" target="_blank">Stampa riepilogo hotel</a>
                            <a href="{{ route('istat.tabella_a.download.xml', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()]) }}" class="btn btn-success">Scarica XML Tabella A</a>
                            <form method="POST" action="{{ route('istat.tabella_a.ws.verify') }}" class="d-inline" data-confirm-kind="save">
                                @csrf
                                <input type="hidden" name="dal" value="{{ $dal->toDateString() }}">
                                <input type="hidden" name="al" value="{{ $al->toDateString() }}">
                                <button type="submit" class="btn btn-info text-white">Verifica invio diretto</button>
                            </form>
                            <form method="POST" action="{{ route('istat.tabella_a.ws.send') }}" class="d-inline" data-confirm-kind="save">
                                @csrf
                                <input type="hidden" name="dal" value="{{ $dal->toDateString() }}">
                                <input type="hidden" name="al" value="{{ $al->toDateString() }}">
                                <button type="submit" class="btn btn-primary">Invia direttamente</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'controllo' ? 'show active' : '' }}" id="istat-pane-controllo" role="tabpanel">
                <div class="card border-0 bg-light-subtle mb-3">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-line-chart-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Riepilogo giornaliero</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="alert alert-light border-0 rounded-0 mb-0 small">
                            Questo riepilogo è <strong>solo informativo</strong> e deriva automaticamente dalle schedine del periodo selezionato.
                            Per correggere un dato, apri la relativa <strong>schedina</strong> e modifica lì la registrazione.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Giorno</th>
                                        <th class="text-end">Cam. disp.</th>
                                        <th class="text-end">Letti disp.</th>
                                        <th class="text-end">Cam. occ.</th>
                                        <th class="text-end">Arrivi</th>
                                        <th class="text-end">Partenze</th>
                                        <th class="text-end">Presenti</th>
                                        <th class="text-end">Italiani</th>
                                        <th class="text-end">Stranieri</th>
                                        <th>Provenienze estero</th>
                                        <th>Regioni Italia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analysis['rows'] as $row)
                                        <tr>
                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($row['giorno'])->format('d/m/y') }}</td>
                                            <td class="text-end">{{ $row['camere_disponibili'] }}</td>
                                            <td class="text-end">{{ $row['letti_disponibili'] }}</td>
                                            <td class="text-end">{{ $row['camere_occupate'] }}</td>
                                            <td class="text-end">{{ $row['arrivi'] }}</td>
                                            <td class="text-end">{{ $row['partenze'] }}</td>
                                            <td class="text-end">{{ $row['presenti'] }}</td>
                                            <td class="text-end">{{ $row['presenti_italiani'] }}</td>
                                            <td class="text-end">{{ $row['presenti_stranieri'] }}</td>
                                            <td class="small">{{ $row['provenienze_nazioni'] }}</td>
                                            <td class="small">{{ $row['provenienze_regioni'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-semibold">
                                        <td>Totale mese</td>
                                        <td class="text-end">{{ $riepilogoTotali['camere_disponibili'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['letti_disponibili'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['camere_occupate'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['arrivi'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['partenze'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['presenti'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['presenti_italiani'] }}</td>
                                        <td class="text-end">{{ $riepilogoTotali['presenti_stranieri'] }}</td>
                                        <td class="small text-muted">Riepilogo estero per giorno</td>
                                        <td class="small text-muted">Riepilogo Italia per giorno</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'storico-xml' ? 'show active' : '' }}" id="istat-pane-storico-xml" role="tabpanel">
                <div class="card border-0 bg-light-subtle mb-0">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-file-list-3-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Storico XML</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($storico->isEmpty())
                            <div class="p-3 text-muted">Nessun XML registrato.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light"><tr><th>Creato</th><th>Periodo</th><th>Schedine</th><th>Movimenti</th><th class="text-end">Azioni</th></tr></thead>
                                    <tbody>
                                        @foreach($storico as $item)
                                            <tr>
                                                <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                                <td>{{ $item->dal?->format('d/m/Y') }} - {{ $item->al?->format('d/m/Y') }}</td>
                                                <td>{{ $item->schedine_count }}</td>
                                                <td>{{ $item->movimenti_count }}</td>
                                                <td class="text-end"><a href="{{ route('istat.tabella_a.download.storico', ['id' => $item->id]) }}" class="btn btn-soft-secondary">Scarica XML</a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'storico-invio' ? 'show active' : '' }}" id="istat-pane-storico-invio" role="tabpanel">
                <div class="card border-0 bg-light-subtle mb-0">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-history-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Storico invio diretto</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($trasmissioni->isEmpty())
                            <div class="p-3 text-muted">Nessuna trasmissione registrata.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light"><tr><th>Quando</th><th>Tipo</th><th>Periodo</th><th>Esito</th><th>Messaggio</th><th class="text-end">Azioni</th></tr></thead>
                                    <tbody>
                                        @foreach($trasmissioni as $tx)
                                            <tr>
                                                <td>{{ optional($tx->executed_at ?: $tx->created_at)->format('d/m/Y H:i') }}</td>
                                                <td class="text-nowrap">{{ $tx->mode === 'verify' ? 'Verifica invio diretto' : 'Invio diretto' }}</td>
                                                <td>{{ optional($tx->dal)->format('d/m/Y') }} - {{ optional($tx->al)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if(($tx->result['simulated'] ?? false) === true)
                                                        <span class="badge bg-info-subtle text-info">SIMULAZIONE</span>
                                                    @endif
                                                    @if($tx->status === 'success')
                                                        <span class="badge bg-success-subtle text-success">OK</span>
                                                    @elseif($tx->status === 'error')
                                                        <span class="badge bg-danger-subtle text-danger">Errore</span>
                                                    @else
                                                        <span class="badge bg-light text-body">In attesa</span>
                                                    @endif
                                                </td>
                                                <td class="small">{{ $tx->response_message ?: '-' }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('istat.tabella_a.ws.receipt', ['id' => $tx->id]) }}" class="btn btn-soft-secondary">Esito</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
