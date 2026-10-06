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
            @if(!empty($credStatus['blocked']))
                <span class="badge bg-warning-subtle text-warning">Invio diretto non disponibile</span>
            @elseif(!empty($credStatus['simulation']))
                <span class="badge bg-info-subtle text-info">Modalità prova invio attiva</span>
                <div class="small text-muted mt-1">Invio esterno disabilitato dal guardrail tecnico. Nessuna ricevuta ufficiale viene simulata.</div>
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
                        <div class="text-muted">Codici accesso</div>
                        <div class="{{ filled($struttura->questura_codici) ? 'text-success' : 'text-body' }}">{{ filled($struttura->questura_codici) ? 'Presenti' : 'Non caricati' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="text-muted">PUK</div>
                        <div class="{{ filled($struttura->questura_puk) ? 'text-success' : 'text-body' }}">{{ filled($struttura->questura_puk) ? 'Presente' : 'Non caricato' }}</div>
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
                    <button class="nav-link active" id="questura-tab-operativita" data-bs-toggle="pill" data-bs-target="#questura-pane-operativita" type="button" role="tab" aria-controls="questura-pane-operativita" aria-selected="true">Operatività</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="questura-tab-storico-export" data-bs-toggle="pill" data-bs-target="#questura-pane-storico-export" type="button" role="tab" aria-controls="questura-pane-storico-export" aria-selected="false">Storico TXT</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="questura-tab-storico-elettronico" data-bs-toggle="pill" data-bs-target="#questura-pane-storico-elettronico" type="button" role="tab" aria-controls="questura-pane-storico-elettronico" aria-selected="false">Storico invio diretto</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="questura-pane-operativita" role="tabpanel" aria-labelledby="questura-tab-operativita">
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
                                <div class="text-muted small mb-1">Schedine nel periodo</div>
                                <div class="fs-4">{{ $totaleSchedine }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 mb-0">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Schedine pronte</div>
                                <div class="fs-4 text-success">{{ $totaleValide }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 mb-0">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Schedine da correggere</div>
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
                                            <button type="submit" class="btn btn-soft-success" title="Scarica TXT">
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
                                <button type="submit" class="btn btn-success">
                                Scarica TXT Questura
                            </button></form>
                            <form method="POST" action="{{ route('questura.ws.tables') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-soft-secondary">Archivia tabelle di riferimento</button>
                            </form>
                            <form method="POST" action="{{ route('questura.ws.verify') }}" class="d-inline" data-confirm-kind="questura-verify">
                                @csrf
                                <input type="hidden" name="dal" value="{{ $dal->format('Y-m-d') }}">
                                <input type="hidden" name="al" value="{{ $al->format('Y-m-d') }}">
                                <button type="submit" class="btn btn-info text-white" @disabled(!empty($credStatus['blocked']))>Verifica invio diretto</button>
                            </form>
                            <form method="POST" action="{{ route('questura.ws.send') }}" class="d-inline" data-confirm-kind="questura-send">
                                @csrf
                                <input type="hidden" name="dal" value="{{ $dal->format('Y-m-d') }}">
                                <input type="hidden" name="al" value="{{ $al->format('Y-m-d') }}">
                                <button type="submit" class="btn btn-primary" @disabled(!empty($credStatus['blocked']))>Invia direttamente</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="questura-pane-storico-export" role="tabpanel" aria-labelledby="questura-tab-storico-export">
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
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="fw-semibold">{{ $export->filename }}</div>
                                                <div class="small text-muted">{{ $export->dal?->format('d/m/Y') }}@if(!$export->dal?->isSameDay($export->al)) - {{ $export->al?->format('d/m/Y') }}@endif</div>
                                                <div class="small text-muted">{{ $export->schedine_count }} schedine · {{ $export->righe_count }} righe</div>
                                                <div class="small text-muted">{{ $export->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                            <div class="d-flex flex-column gap-1">
                                                @if($export->finalized_at)
                                                    <span class="badge bg-success-subtle text-body">TXT eliminato dopo ricevuta</span>
                                                    <a class="btn btn-soft-secondary" href="{{ route('questura.receipts.download', ['id' => $export->questura_receipt_id]) }}">Scarica ricevuta</a>
                                                @else
                                                    <a href="{{ route('questura.download.storico', ['id' => $export->id]) }}" class="btn btn-soft-secondary">Scarica TXT temporaneo</a>
                                                    <form method="POST" action="{{ route('questura.txt.receipt', ['id' => $export->id]) }}">
                                                        @csrf
                                                        <label class="small">Data comunicazione manuale <input class="form-control" type="date" name="communication_date" required max="{{ today()->subDay()->toDateString() }}"></label>
                                                        <label class="small d-block"><input type="checkbox" name="communication_confirmed" value="1" required> Confermo che questo elenco esatto è stato comunicato e riconciliato per la giornata indicata.</label>
                                                        <div class="small text-muted">Il download non prova l'invio. Una ricevuta valida comporta la cancellazione delle copie Questura; le Schedine PMS restano disponibili.</div>
                                                        <button type="submit" class="btn btn-soft-secondary">Acquisisci ricevuta e finalizza TXT</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{ $storico->withQueryString()->links() }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="questura-pane-storico-elettronico" role="tabpanel" aria-labelledby="questura-tab-storico-elettronico">
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
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-semibold">{{ $tx->mode === 'verify' ? 'Verifica invio diretto' : 'Invio diretto' }}</span>
                                                    <span class="badge bg-light text-body">{{ match ($tx->esitoSicuro()['state']) { 'simulation' => 'Prova interna', 'sent' => 'Richiesta inviata', 'rejected' => 'Rifiutato', 'technical_error' => 'Errore tecnico', 'uncertain' => 'Esito incerto', 'partial' => 'Acquisizione parziale', 'in_progress' => 'In corso', default => 'Esito non verificato' } }}</span>
                                                </div>
                                                <div class="small text-muted">{{ $tx->dal?->format('d/m/Y') }}@if($tx->al && !$tx->dal?->isSameDay($tx->al)) - {{ $tx->al?->format('d/m/Y') }}@endif</div>
                                                <div class="small text-muted">{{ $tx->schedine_count }} schedine · {{ $tx->righe_count }} righe</div>
                                                <div class="small text-muted">{{ $tx->esitoSicuro()['message'] }}</div>
                                                @foreach($tx->esitoSicuro()['row_errors'] ?? [] as $rowError)
                                                    <div class="small text-danger">Riga {{ $rowError['row'] }}: {{ match ($rowError['code']) { '11' => 'formato non corretto; verificare il record', '12' => 'campo non corretto; verificare i dati', default => 'errore remoto non classificato' } }}.</div>
                                                @endforeach
                                                @if($tx->esitoSicuro()['simulated'])
                                                    <div class="small text-info">Prova interna: nessun dato è stato inviato al portale reale.</div>
                                                @endif
                                                <div class="small text-muted">{{ $tx->executed_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                            <div class="d-flex flex-column gap-1">
                                                @if($tx->finalized_at)
                                                    <span class="badge bg-success-subtle text-body">Payload eliminato dopo ricevuta</span>
                                                    <a class="btn btn-soft-secondary" href="{{ route('questura.receipts.download', ['id' => $tx->questura_receipt_id]) }}">Scarica ricevuta</a>
                                                @else
                                                    <a href="{{ route('questura.ws.payload', ['id' => $tx->id]) }}" class="btn btn-soft-secondary">Scarica payload temporaneo</a>
                                                @endif
                                                @if(!$tx->finalized_at && $tx->mode === 'send' && in_array($tx->status, ['sent', 'uncertain', 'partial', 'in_progress'], true))
                                                    <form method="POST" action="{{ route('questura.ws.receipt.acquire', ['id' => $tx->id]) }}">
                                                        @csrf
                                                        <button class="btn btn-soft-secondary" type="submit">Acquisisci ricevuta giornaliera</button>
                                                    </form>
                                                    <a href="{{ route('questura.ws.receipt', ['id' => $tx->id]) }}" class="btn btn-soft-secondary" title="Scarica ricevuta archiviata">
                                                        <i class="ri-file-pdf-line fs-16 align-middle"></i>
                                                    </a>
                                                    @if(in_array($tx->status, ['uncertain', 'partial', 'in_progress'], true))
                                                        <form method="POST" action="{{ route('questura.ws.finalize', ['id' => $tx->id]) }}">
                                                            @csrf
                                                            <label class="small d-block"><input type="checkbox" name="reconciled" value="1" required> Ho riconciliato l'intero elenco con la comunicazione della giornata. La sola ricevuta giornaliera non prova ogni riga.</label>
                                                            <button class="btn btn-soft-secondary" type="submit">Finalizza dopo riconciliazione</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{ $trasmissioni->withQueryString()->links() }}
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
