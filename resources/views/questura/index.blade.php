@extends('layouts.master')

@section('title', 'Questura')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Invio Telematico @endslot
    @slot('title') Questura @endslot
@endcomponent

@if(!empty($questuraError))
    <div class="alert alert-danger" role="alert">
        <strong class="d-block">Errore nel ciclo Questura</strong>
        <div>{{ $questuraError }}</div>
        <div>Configurazione credenziali da verificare. Test e invio diretto non disponibili.</div>
        <a href="{{ route('struttura.edit') }}" class="alert-link">Verifica configurazione</a>
    </div>
@endif

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
            <h4 class="card-title mb-1">Questura</h4>
            <div class="text-muted">Da qui puoi scaricare il file ufficiale TXT da caricare manualmente sul portale nazionale Alloggiati Web oppure usare l'invio diretto elettronico del medesimo contenuto.</div>
        </div>
        <div class="text-md-end">
            @if(!config('questura.enabled'))
                <span class="badge bg-warning-subtle text-warning">Canale Questura disabilitato</span>
                <div class="small text-muted mt-1">Nuove generazioni, Test, invii e finalizzazioni sono bloccati. Anteprime e archivi disponibili restano consultabili.</div>
            @elseif(!empty($credStatus['blocked']))
                <span class="badge bg-warning-subtle text-warning">Invio diretto non disponibile</span>
            @elseif(!empty($credStatus['simulation']))
                <span class="badge bg-info-subtle text-info">Modalità prova invio attiva</span>
                <div class="small text-muted mt-1">Prova interna: nessun invio esterno e nessuna ricevuta ufficiale simulata.</div>
            @elseif($credStatus['configured'])
                <span class="badge bg-success-subtle text-success">Invio diretto configurato</span>
            @else
                <span class="badge bg-warning-subtle text-warning">Invio diretto non completo</span>
                <div class="small text-muted mt-1">Manca: {{ implode(', ', $credStatus['missing']) }}</div>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="card border-0 bg-light-subtle mb-3">
            <div class="card-header border-0 py-2 d-flex align-items-center">
                <i class="ri-shield-check-line me-2 text-primary"></i>
                <h5 class="card-title mb-0 fs-6">Verifica configurazione Questura</h5>
            </div>
            <div class="card-body pt-2">
                <div class="row g-3 small">
                    <div class="col-xl-3 col-md-6">
                        <div class="text-muted">Servizio</div>
                        <div class="text-body">Alloggiati Web nazionale</div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="text-muted">Username Questura</div>
                        <div class="{{ filled($struttura->questura_username) ? 'text-success' : 'text-danger' }}">{{ filled($struttura->questura_username) ? 'Presente' : 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="text-muted">Password</div>
                        <div class="{{ !empty($credStatus['blocked']) ? 'text-warning' : (in_array('password', $credStatus['missing'], true) ? 'text-danger' : 'text-success') }}">{{ !empty($credStatus['blocked']) ? 'Da verificare' : (in_array('password', $credStatus['missing'], true) ? 'Mancante' : 'Presente') }}</div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="text-muted">WSKEY</div>
                        <div class="{{ !empty($credStatus['blocked']) ? 'text-warning' : (in_array('WSKEY', $credStatus['missing'], true) ? 'text-danger' : 'text-success') }}">{{ !empty($credStatus['blocked']) ? 'Da verificare' : (in_array('WSKEY', $credStatus['missing'], true) ? 'Mancante' : 'Presente') }}</div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="text-muted">Modalità</div>
                        <div class="{{ !empty($credStatus['simulation']) ? 'text-info' : 'text-body' }}">{{ !empty($credStatus['blocked']) ? 'Non disponibile' : (!empty($credStatus['simulation']) ? 'Simulazione attiva' : 'Invio reale') }}</div>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Le credenziali e il WSKEY sono quelli rilasciati per il portale Alloggiati Web della Polizia di Stato. Il servizio è nazionale; l'abilitazione della struttura resta collegata alla Questura territorialmente competente.
                </div>
                @if(!empty($latestTableSnapshot))
                    <div class="small text-muted mt-2">
                        Ultimo snapshot tabelle ufficiali: {{ \Carbon\Carbon::parse($latestTableSnapshot['archived_at'] ?? $latestTableSnapshot['downloaded_at'] ?? now())->format('d/m/Y H:i') }}
                        · codifiche conservate separatamente dai cataloghi generali
                        @if(!empty($latestTableSnapshot['simulation']))
                            · modalità simulazione
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'operativita' ? 'active' : '' }}" id="questura-tab-operativita" data-bs-toggle="pill" data-bs-target="#questura-pane-operativita" type="button" role="tab" aria-controls="questura-pane-operativita" aria-selected="{{ $tab === 'operativita' ? 'true' : 'false' }}">Operatività</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'export' ? 'active' : '' }}" id="questura-tab-storico-export" data-bs-toggle="pill" data-bs-target="#questura-pane-storico-export" type="button" role="tab" aria-controls="questura-pane-storico-export" aria-selected="{{ $tab === 'export' ? 'true' : 'false' }}">Storico TXT</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'elettronico' ? 'active' : '' }}" id="questura-tab-storico-elettronico" data-bs-toggle="pill" data-bs-target="#questura-pane-storico-elettronico" type="button" role="tab" aria-controls="questura-pane-storico-elettronico" aria-selected="{{ $tab === 'elettronico' ? 'true' : 'false' }}">Storico invio diretto</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $tab === 'operativita' ? 'show active' : '' }}" id="questura-pane-operativita" role="tabpanel" aria-labelledby="questura-tab-operativita">
                <div class="card border-0 bg-light-subtle mb-3">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-calendar-event-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Periodo e filtri</h5>
                    </div>
                    <div class="card-body pt-2">
                        <form method="GET" action="{{ route('questura.index') }}" class="row g-3 align-items-end">
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Dal</label>
                                <x-calendario name="dal" variant="single" :value="$dal->format('Y-m-d')" placeholder="gg/mm/aaaa" />
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Al</label>
                                <x-calendario name="al" variant="single" :value="$al->format('Y-m-d')" placeholder="gg/mm/aaaa" />
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label">Vista</label>
                                <x-ui.select name="filtro" id="questuraFiltroVista">
                                    <option value="tutte" {{ ($filtro ?? 'tutte') === 'tutte' ? 'selected' : '' }}>Tutte le schedine</option>
                                    <option value="pronte" {{ ($filtro ?? '') === 'pronte' ? 'selected' : '' }}>Solo pronte</option>
                                    <option value="correggere" {{ ($filtro ?? '') === 'correggere' ? 'selected' : '' }}>Solo da correggere</option>
                                    <option value="esportate" {{ ($filtro ?? '') === 'esportate' ? 'selected' : '' }}>Solo con TXT scaricato</option>
                                    <option value="inviate" {{ ($filtro ?? '') === 'inviate' ? 'selected' : '' }}>Solo inviate con invio diretto</option>
                                </x-ui.select>
                            </div>
                            <div class="col-xl-3 col-md-6 d-flex justify-content-xl-end gap-2">
                                <button type="submit" class="btn btn-primary">Aggiorna elenco</button>
                                <a href="{{ route('questura.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 mb-0">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Schedine nella lista</div>
                                <div class="fs-4">{{ $totaleSchedine }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 mb-0">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Pronte nella lista</div>
                                <div class="fs-4 text-success">{{ $totaleValide }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 mb-0">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Da correggere nella lista</div>
                                <div class="fs-4 text-danger">{{ $totaleNonValide }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-crud-table
                    title="Export Questura"
                    subtitle="Il file TXT scaricato da questa schermata e il file ufficiale da caricare manualmente nel portale Alloggiati Web. Se una schedina è segnata come pronta, il file è generabile nel formato corretto."
                    searchPlaceholder="Cerca per numero schedina, nome o cognome..."
                    searchId="questuraSearch"
                    createText=""
                    createTarget="#"
                    tableId="questuraTable"
                    :paginator="$analisi"
                >
                    <x-slot name="columns">
                        <tr>
                            <th style="width: 12%;" class="text-nowrap">Num.</th>
                            <th style="width: 11%;" class="text-nowrap">Arrivo</th>
                            <th>Ospite</th>
                            <th style="width: 7%;" class="text-center text-nowrap">Pers.</th>
                            <th style="width: 11%;" class="text-nowrap">TXT</th>
                            <th style="width: 12%;" class="text-nowrap">Invio diretto</th>
                            <th style="width: 11%;" class="text-nowrap">Ultima operazione</th>
                            <th style="width: 16%;">Verifica dati</th>
                            <th class="text-end text-nowrap" style="width: 8%;">Azioni</th>
                        </tr>
                    </x-slot>

                    <x-slot name="rows">
                        @foreach($analisi as $item)
                            @php $schedina = $item['schedina']; @endphp
                            <tr class="data-row">
                                <td class="align-middle text-nowrap small">{{ $schedina->scheda ?: '-' }}</td>
                                <td class="align-middle text-nowrap small">{{ $schedina->arrive ? \Carbon\Carbon::parse($schedina->arrive)->format('d/m/Y') : '-' }}</td>
                                <td class="align-middle small">{{ trim(($schedina->surname ?: '') . ' ' . ($schedina->name ?: '')) ?: '-' }}</td>
                                <td class="align-middle text-center text-nowrap small">{{ $item['persone'] }}</td>
                                <td class="align-middle text-nowrap small">
                                    @if(($schedina->questura_export_count ?? 0) > 0)
                                        <span class="badge bg-info-subtle text-info">Scaricato {{ $schedina->questura_export_count }}x</span>
                                    @else
                                        <span class="badge bg-light text-body">Mai scaricato</span>
                                    @endif
                                </td>
                                <td class="align-middle text-nowrap small">
                                    @if(($schedina->questura_send_count ?? 0) > 0)
                                        <span class="badge bg-light text-body">Invii registrati {{ $schedina->questura_send_count }}x (non verificati)</span>
                                    @else
                                        <span class="badge bg-light text-body">Nessun invio confermato nel contatore</span>
                                    @endif
                                </td>
                                <td class="align-middle text-nowrap small">
                                    @if($schedina->questura_sent_at)
                                        {{ \Carbon\Carbon::parse($schedina->questura_sent_at)->format('d/m/Y H:i') }}
                                    @elseif($schedina->questura_exported_at)
                                        TXT {{ \Carbon\Carbon::parse($schedina->questura_exported_at)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    @if($item['valida'])
                                        <span class="text-success">Dati completi</span>
                                        <details class="mt-1"><summary>Anteprima TXT della schedina</summary><pre class="border rounded p-2 mt-2" style="max-width:24rem;max-height:12rem;overflow:auto">{{ implode("\r\n", $item['righe']) }}</pre></details>
                                    @else
                                        <div class="small text-danger">
                                            @foreach($item['errors'] as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end align-middle text-nowrap">
                                    @if($item['valida'])
                                        <form method="POST" action="{{ route('questura.download.schedina', ['id' => $schedina->id]) }}">
                                            @csrf
                                            <input type="hidden" name="preview_hash" value="{{ $item['preview_hash'] }}">
                                            <button type="submit" class="btn btn-soft-success" title="Scarica TXT" @disabled(!config('questura.enabled'))>
                                            <i class="ri-download-2-line fs-16 align-middle"></i>
                                        </button></form>
                                    @else
                                        <a href="{{ route('schedina.edit', ['id' => $schedina->id]) }}" class="btn btn-soft-warning" title="Correggi schedina">
                                            <i class="ri-edit-line fs-16 align-middle"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-crud-table>

                <div class="card mt-3" id="questura-anteprima">
                    <div class="card-body">
                        <h5>Anteprima TXT dell’intero periodo</h5>
                        <p class="small">Comunicare entro 24 ore dall’arrivo; per soggiorni non superiori a 24 ore, entro 6 ore. Il sistema usa la data d’arrivo: l’operatore deve verificare anche l’orario e le istruzioni della propria Questura. Tracciato standard di 168 caratteri UTF-8; il profilo Gestione Appartamenti da 174 caratteri richiede una verifica dedicata prima dell’uso.</p>
                        <p class="small">Comprende tutte le schedine del periodo, anche nelle altre pagine e fuori dai filtri della lista. Il download non prova l’invio né l’accettazione da parte della Questura.</p>
                        @if($preview_txt !== null)
                            <p class="small text-break">{{ $preview_righe }} alloggiati · {{ strlen($preview_txt) }} byte UTF-8 · SHA-256: <code>{{ $preview_hash }}</code></p>
                            <details><summary>Controlla il contenuto del file</summary><pre class="border rounded p-2 mt-2" style="max-height:22rem;overflow:auto" aria-label="Anteprima TXT Questura">{{ $preview_txt }}</pre></details>
                        @else
                            <div class="alert alert-warning mb-0">{{ $preview_error }}</div>
                        @endif
                    </div>
                </div>
                <div class="card border-0 bg-light-subtle mt-3">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-send-plane-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Azioni disponibili</h5>
                    </div>
                    <div class="card-body pt-2">
                        <div class="small text-muted mb-2">
                            <strong>Scarica TXT Questura</strong> produce il file `.txt` destinato al caricamento manuale da caricare manualmente nel portale Alloggiati Web. Prima di <strong>Invia direttamente</strong> occorre un esito positivo di <strong>Verifica invio diretto</strong> sullo stesso elenco. Se cambiano i dati Questura, ripetere la verifica prima di inviare.
                        </div>
                        <div class="small text-muted mb-3">
                            @if(!empty($credStatus['blocked']))
                                Test e invio diretto richiedono la verifica della configurazione. Puoi consultare lo storico e scaricare i TXT consentiti.
                            @else
                                Se manca un dato obbligatorio della schedina o della configurazione Questura, il sistema te lo segnala al momento dell'esecuzione.
                            @endif
                            <strong>Archivia tabelle di riferimento</strong> salva uno snapshot delle codifiche pubbliche ufficiali acquisite il 06/10/2026.
                        </div>
                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <form method="POST" action="{{ route('questura.download.periodo', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')]) }}">
                                @csrf
                                <input type="hidden" name="preview_hash" value="{{ $preview_hash }}">
                                <button type="submit" class="btn btn-success" @disabled(!config('questura.enabled') || $preview_txt === null)>
                                Scarica TXT Questura
                            </button></form>
                            <form method="POST" action="{{ route('questura.ws.tables') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-soft-secondary" @disabled(!config('questura.enabled'))>Archivia tabelle di riferimento</button>
                            </form>
                            <form method="POST" action="{{ route('questura.ws.verify') }}" class="d-inline" data-confirm-kind="questura-verify">
                                @csrf
                                <input type="hidden" name="dal" value="{{ $dal->format('Y-m-d') }}">
                                <input type="hidden" name="al" value="{{ $al->format('Y-m-d') }}">
                                <input type="hidden" name="preview_hash" value="{{ $preview_hash }}">
                                <button type="submit" class="btn btn-info text-white" @disabled(!config('questura.enabled') || !empty($credStatus['blocked']) || $preview_txt === null)>Verifica invio diretto</button>
                            </form>
                            <form method="POST" action="{{ route('questura.ws.send') }}" class="d-inline" data-confirm-kind="questura-send">
                                @csrf
                                <input type="hidden" name="dal" value="{{ $dal->format('Y-m-d') }}">
                                <input type="hidden" name="al" value="{{ $al->format('Y-m-d') }}">
                                <input type="hidden" name="preview_hash" value="{{ $preview_hash }}">
                                <button type="submit" class="btn btn-primary" @disabled(!config('questura.enabled') || !empty($credStatus['blocked']) || $preview_txt === null)>Invia direttamente</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $tab === 'export' ? 'show active' : '' }}" id="questura-pane-storico-export" role="tabpanel" aria-labelledby="questura-tab-storico-export">
                <div class="card border-0 bg-light-subtle mb-0">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-folder-download-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Storico file TXT</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($storico->isEmpty())
                            <div class="p-3 text-muted">Nessun export registrato.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($storico as $export)
                                    <div class="list-group-item">
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4 text-break">
                                                <div class="fw-semibold">{{ $export->filename }}</div>
                                                <div class="small text-muted">{{ $export->dal?->format('d/m/Y') }}@if(!$export->dal?->isSameDay($export->al)) - {{ $export->al?->format('d/m/Y') }}@endif</div>
                                                <div class="small text-muted">{{ $export->schedine_count }} schedine · {{ $export->righe_count }} righe</div>
                                                <div class="small text-muted">{{ $export->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                            <div class="col-12 col-md-8 d-flex flex-column gap-1">
                                                @if($export->finalized_at)
                                                    <span class="badge bg-light text-body">{{ $export->questura_receipt_id ? 'TXT eliminato dopo ricevuta' : 'Export storico finalizzato' }}</span>
                                                    @if($export->questura_receipt_id)
                                                        <a class="btn btn-soft-secondary" href="{{ route('questura.receipts.download', ['id' => $export->questura_receipt_id]) }}">Scarica ricevuta</a>
                                                    @else
                                                        <span class="text-warning small">Finalizzazione storica da verificare: ricevuta non collegata.</span>
                                                    @endif
                                                @else
                                                    <a href="{{ route('questura.download.storico', ['id' => $export->id]) }}" class="btn btn-soft-secondary">Scarica TXT temporaneo</a>
                                                    <form method="POST" action="{{ route('questura.txt.receipt', ['id' => $export->id]) }}">
                                                        @csrf
                                                        <label class="small">Data comunicazione manuale <input class="form-control" type="date" name="communication_date" required max="{{ today()->subDay()->toDateString() }}"></label>
                                                        <label class="small d-block"><input type="checkbox" name="communication_confirmed" value="1" required> Confermo che questo elenco esatto è stato comunicato e riconciliato per la giornata indicata.</label>
                                                        <div class="small text-muted">Il download non prova l'invio. Una ricevuta valida comporta la cancellazione delle copie Questura; le Schedine PMS restano disponibili.</div>
                                                        <button type="submit" class="btn btn-soft-secondary" @disabled(!config('questura.enabled'))>Acquisisci ricevuta e finalizza TXT</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{ $storico->withQueryString()->appends(['tab' => 'export'])->links() }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $tab === 'elettronico' ? 'show active' : '' }}" id="questura-pane-storico-elettronico" role="tabpanel" aria-labelledby="questura-tab-storico-elettronico">
                <div class="card border-0 bg-light-subtle mb-0">
                    <div class="card-header border-0 py-2 d-flex align-items-center">
                        <i class="ri-cloud-line me-2 text-primary"></i>
                        <h5 class="card-title mb-0 fs-6">Storico invio diretto</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($trasmissioni->isEmpty())
                            <div class="p-3 text-muted">Nessuna trasmissione registrata.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($trasmissioni as $tx)
                                    @php
                                        $esitoTx = $tx->esitoSicuro();
                                        $testPositivo = $tx->mode === 'verify' && $esitoTx['state'] === 'unknown' && (int) $tx->righe_count > 0
                                            && ($esitoTx['valid_rows'] ?? null) === (int) $tx->righe_count && ($esitoTx['row_errors'] ?? null) === [];
                                    @endphp
                                    <div class="list-group-item">
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4 text-break">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-semibold">{{ $tx->mode === 'verify' ? 'Verifica invio diretto' : 'Invio diretto' }}</span>
                                                    <span class="badge bg-light text-body">{{ $testPositivo ? 'Verifica positiva' : match ($esitoTx['state']) { 'simulation' => 'Prova interna', 'sent' => 'Richiesta inviata', 'rejected' => 'Rifiutato', 'technical_error' => 'Errore tecnico', 'uncertain' => 'Esito incerto', 'partial' => 'Acquisizione parziale', 'in_progress' => 'In corso', default => 'Esito non verificato' } }}</span>
                                                </div>
                                                <div class="small text-muted">{{ $tx->dal?->format('d/m/Y') }}@if($tx->al && !$tx->dal?->isSameDay($tx->al)) - {{ $tx->al?->format('d/m/Y') }}@endif</div>
                                                <div class="small text-muted">{{ $tx->schedine_count }} schedine · {{ $tx->righe_count }} righe</div>
                                                <div class="small text-muted">{{ $tx->esitoSicuro()['message'] }}</div>
                                                @if($testPositivo)<div class="small">{{ $tx->righe_count }} {{ (int) $tx->righe_count === 1 ? 'riga controllata' : 'righe controllate' }}. Il Test non costituisce l’invio ufficiale.</div>@endif
                                                @foreach($tx->esitoSicuro()['row_errors'] ?? [] as $rowError)
                                                    <div class="small text-danger">Riga {{ $rowError['row'] }}: {{ match ($rowError['code']) { '11' => 'codice 11; verificare l’esito nel portale', '12' => 'codice 12; verificare l’esito nel portale', default => 'errore remoto non classificato; verificare l’esito nel portale' } }}.</div>
                                                @endforeach
                                                @if($tx->esitoSicuro()['simulated'])
                                                    <div class="small text-info">Prova interna: nessun dato è stato inviato al portale reale.</div>
                                                @endif
                                                <div class="small text-muted">{{ $tx->executed_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                            <div class="col-12 col-md-8 d-flex flex-column gap-1">
                                                @if($tx->finalized_at)
                                                    <span class="badge bg-light text-body">{{ $tx->questura_receipt_id ? 'Payload eliminato dopo ricevuta' : 'Trasmissione storica finalizzata' }}</span>
                                                    @if($tx->questura_receipt_id)
                                                        <a class="btn btn-soft-secondary" href="{{ route('questura.receipts.download', ['id' => $tx->questura_receipt_id]) }}">Scarica ricevuta</a>
                                                    @else
                                                        <span class="text-warning small">Finalizzazione storica da verificare: ricevuta non collegata.</span>
                                                    @endif
                                                @else
                                                    <a href="{{ route('questura.ws.payload', ['id' => $tx->id]) }}" class="btn btn-soft-secondary">Scarica payload temporaneo</a>
                                                @endif
                                                @if(!$tx->finalized_at && $tx->mode === 'send' && in_array($tx->status, ['sent', 'uncertain', 'partial', 'in_progress'], true))
                                                    <form method="POST" action="{{ route('questura.ws.receipt.acquire', ['id' => $tx->id]) }}">
                                                        @csrf
                                                        <button class="btn btn-soft-secondary" type="submit" @disabled(!config('questura.enabled'))>Acquisisci ricevuta giornaliera</button>
                                                    </form>
                                                    <a href="{{ route('questura.ws.receipt', ['id' => $tx->id]) }}" class="btn btn-soft-secondary" title="Scarica ricevuta archiviata">
                                                        <i class="ri-file-pdf-line fs-16 align-middle"></i>
                                                    </a>
                                                    @if(in_array($tx->status, ['uncertain', 'partial', 'in_progress'], true))
                                                        <form method="POST" action="{{ route('questura.ws.finalize', ['id' => $tx->id]) }}">
                                                            @csrf
                                                            <label class="small d-block"><input type="checkbox" name="reconciled" value="1" required> Ho riconciliato l'intero elenco con la comunicazione della giornata. La sola ricevuta giornaliera non prova ogni riga.</label>
                                                            <button class="btn btn-soft-secondary" type="submit" @disabled(!config('questura.enabled'))>Finalizza dopo riconciliazione</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{ $trasmissioni->withQueryString()->appends(['tab' => 'elettronico'])->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card mt-3">
    <div class="card-header"><h5 class="card-title">Ricevute giornaliere</h5></div>
    <div class="card-body">
        <p class="text-muted">Le copie Questura sono temporanee. La ricevuta resta conservata cinque anni dalla sua acquisizione; le Schedine PMS non vengono eliminate.</p>
        @forelse($ricevute as $ricevuta)
            <div class="d-flex justify-content-between gap-2 mb-2">
                <div>
                    <strong>Comunicazione {{ $ricevuta->remote_date?->format('d/m/Y') }}</strong>
                    <div class="small">Acquisita: {{ $ricevuta->acquired_at?->format('d/m/Y H:i') ?? 'Da verificare' }} · Conservare fino al: {{ $ricevuta->retained_until?->format('d/m/Y H:i') ?? 'Da verificare' }}</div>
                </div>
                @if(!$ricevuta->purged_at && $ricevuta->acquired_at)
                    <a class="btn btn-soft-secondary" href="{{ route('questura.receipts.download', ['id' => $ricevuta->id]) }}">Scarica ricevuta</a>
                @endif
            </div>
        @empty
            <p class="text-muted">Nessuna ricevuta archiviata.</p>
        @endforelse
        {{ $ricevute->withQueryString()->links() }}
    </div>
</div>
@endsection
