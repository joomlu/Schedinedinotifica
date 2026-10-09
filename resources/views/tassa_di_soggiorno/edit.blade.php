@extends('layouts.master')

@section('title') Tassa di soggiorno @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Configurazioni @endslot
        @slot('title') Tassa di soggiorno @endslot
    @endcomponent

@if($automaticaBellaria)
<div class="row config-page"><div class="col-12"><div class="card"><div class="card-body">
    <x-table-topbar title="Tassa di soggiorno" subtitle="Regole fiscali e preferenze della struttura corrente" :showSearch="false" />
    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
        @foreach(['configurazione' => 'Configurazione', 'esenzioni' => 'Esenzioni', 'stampa' => 'Stampa ricevuta'] as $id => $titolo)
            <li class="nav-item" role="presentation"><button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $id }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $id }}" type="button" role="tab" aria-controls="pane-{{ $id }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $titolo }}</button></li>
        @endforeach
    </ul>
    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="pane-configurazione" role="tabpanel" aria-labelledby="tab-configurazione">
            <div class="row g-3">
                <div class="col-lg-6"><div class="card border h-100"><div class="card-body">
                    <h5 class="card-title">Identificazione struttura</h5>
                    <dl class="row mb-3">
                        @foreach(['comune' => 'Comune', 'tipologia_generale' => 'Tipologia generale', 'tipologia_struttura' => 'Tipologia struttura', 'classificazione' => 'Classificazione'] as $campo => $nome)
                            <dt class="col-sm-6">{{ $nome }}</dt><dd class="col-sm-6">{{ $datiStruttura[$campo] ?: 'Non configurato' }}</dd>
                        @endforeach
                    </dl>
                    <a class="btn btn-outline-primary" href="{{ route('struttura.edit') }}">Modifica nei Dati struttura</a>
                </div></div></div>
                <div class="col-lg-6"><div class="card border h-100"><div class="card-body">
                    <h5 class="card-title">Configurazione fiscale applicata</h5>
                    <div class="alert {{ $diagnosi['stato'] === 'automatica' ? 'alert-success' : 'alert-warning' }}" data-tassa-stato="{{ $diagnosi['stato'] }}">{{ $diagnosi['messaggio'] }}</div>
                    @if($diagnosi['profilo'])
                        @if($diagnosi['stato'] !== 'automatica')<p>Regola certificata di riferimento, non applicabile finché la discordanza segnalata non è risolta.</p>@endif
                        <dl class="row">
                            <dt class="col-sm-6">Tariffa per notte</dt><dd class="col-sm-6">€ {{ number_format($diagnosi['profilo']['tassa_soggiorno'], 2, ',', '.') }}</dd>
                            <dt class="col-sm-6">Massimo pernottamenti</dt><dd class="col-sm-6">{{ $diagnosi['profilo']['giorni_massimo'] }}</dd>
                            <dt class="col-sm-6">Periodo fiscale</dt><dd class="col-sm-6">{{ \Carbon\Carbon::parse($diagnosi['profilo']['inizio'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($diagnosi['profilo']['fine'])->format('d/m/Y') }}</dd>
                            <dt class="col-sm-6">Versione</dt><dd class="col-sm-6">{{ $diagnosi['profilo']['regola_versione'] }}</dd>
                            <dt class="col-sm-6">Validità della regola</dt><dd class="col-sm-6">{{ \Carbon\Carbon::parse($diagnosi['profilo']['valida_dal'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($diagnosi['profilo']['valida_al'])->format('d/m/Y') }}</dd>
                        </dl>
                        <p class="text-muted small">{{ $diagnosi['profilo']['regola_fonte'] }}</p>
                        <p class="mb-0">Esenzione fino al giorno del 18° compleanno incluso. Il codice 777 è derivato dalle notti oltre il limite, con tariffa zero.</p>
                    @endif
                </div></div></div>
                <div class="col-12">
                    <h5>Struttura {{ $struttura->nome_struttura }} (ID {{ $struttura->id }}) — anno fiscale {{ $annoFiscale }}</h5>
                    <form method="GET" action="{{ route('tassa_di_soggiorno.edit') }}" class="d-flex gap-2 mb-3">
                        <label class="form-label" for="anno-fiscale">Anno fiscale</label><input class="form-control w-auto" id="anno-fiscale" name="anno_fiscale" type="number" min="2015" max="2100" value="{{ $annoFiscale }}"><button class="btn btn-outline-primary">Verifica profilo</button>
                    </form>
                    @if($diagnosi['stato'] === 'configurazione_legacy_discordante')<p class="text-warning"><strong>Configurazione legacy conservata.</strong> Nessuna modifica automatica ai valori salvati.</p>@endif
                    @if($confronto)
                        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Campo</th><th>Valore salvato</th><th>Profilo certificato</th><th>Stato</th></tr></thead><tbody>
                        @foreach(['tassa_soggiorno' => 'Tariffa per notte', 'giorni_massimo' => 'Limite notti', 'inizio' => 'Inizio periodo', 'fine' => 'Fine periodo', 'max_age_children' => 'Età minori', 'min_age_adult' => 'Età adulti'] as $campo => $nome)
                            <tr><td>{{ $nome }}</td><td>{{ $confronto['attuali'][$campo] ?? 'Non salvato' }}</td><td>{{ $confronto['richiesti'][$campo] }}</td><td>{{ isset($confronto['differenze'][$campo]) ? 'Differente / derivato dal profilo' : 'Coincidente' }}</td></tr>
                        @endforeach
                        </tbody></table></div>
                    @endif
                    @if($riallineamentoToken)
                        <form method="POST" action="{{ route('tassa_di_soggiorno.riallinea') }}">
                            @csrf<input type="hidden" name="token" value="{{ $riallineamentoToken }}">
                            <div class="alert alert-warning">Riallineamento esplicito dei soli sei parametri sopra indicati. Nessuna ricevuta salvata, export consolidato o Schedina sarà riscritta. Le elaborazioni correnti saranno ricalcolate usando il profilo certificato.</div>
                            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="consenso-riallineamento" name="consenso" value="1" required><label class="form-check-label" for="consenso-riallineamento">Ho verificato le differenze e autorizzo il riallineamento della struttura e dell’anno indicati.</label></div>
                            <button type="submit" class="btn btn-primary">Riallinea al profilo certificato</button>
                        </form>
                    @elseif($diagnosi['stato'] === 'configurazione_legacy_discordante')
                        <p>Richiedere il riallineamento a un amministratore o al proprietario autorizzato.</p>
                    @endif
                </div>
                <div class="col-12"><p class="text-muted">Il periodo fiscale è indipendente dall’apertura della struttura. Una Schedina può essere registrata anche fuori stagione o quando il calcolo Tassa non è disponibile.</p><a class="btn btn-light" href="{{ route('tassa_di_soggiorno.rapporto') }}">Vai al rapporto Tassa</a></div>
            </div>
        </div>
        <div class="tab-pane fade" id="pane-esenzioni" role="tabpanel" aria-labelledby="tab-esenzioni">
            <div class="card border"><div class="card-body"><h5 class="card-title">Catalogo esenzioni</h5>
                <p>Le cause pertinenti si applicano ai singoli ospiti nella Schedina. Codici e significati ufficiali sono regole del sistema.</p>
                <div class="table-responsive"><table class="table align-middle" id="esenzioni-table"><thead><tr><th>Codice</th><th>Descrizione</th><th>Stato</th></tr></thead><tbody>
                    @foreach($esenzioni as $esenzione)
                        <tr><td>{{ $esenzione->codice }}</td><td>{{ $esenzione->descrizione }}</td><td>{{ in_array((string) $esenzione->codice, ['400','405','410','415','420','425','430','440'], true) ? 'Catalogo ufficiale' : 'Legacy — non certificato' }}</td></tr>
                    @endforeach
                </tbody></table></div>
                <p class="mb-0">777: pernottamenti oltre le 6 notti, calcolati automaticamente. Non è un’esenzione personale e non è selezionabile.</p>
            </div></div>
        </div>
        <div class="tab-pane fade" id="pane-stampa" role="tabpanel" aria-labelledby="tab-stampa">
            <div class="row g-3">
                <div class="col-lg-6"><div class="card border h-100"><div class="card-body"><h5 class="card-title">Identità ricevuta</h5>
                    <p>Logo struttura: utilizzato automaticamente dai Dati struttura.</p>
                    @if($struttura->logo)<img src="{{ asset($struttura->logo) }}" alt="Logo struttura" class="img-thumbnail mb-3" style="max-height:80px">@else<p class="text-muted">Logo non presente: la ricevuta mostra il nome della struttura.</p>@endif
                    <p>Stemma Comune: automatico dalla fonte geografica esistente.</p>
                    @if($logoComune)<img src="{{ asset($logoComune) }}" alt="Stemma Comune" class="img-thumbnail" style="max-height:80px">@else<p class="text-muted">Stemma non presente: la ricevuta mostra il nome del Comune.</p>@endif
                </div></div></div>
                <div class="col-lg-6"><div class="card border h-100"><div class="card-body" data-configurazione-immagine>
                    <h5 class="card-title">Immagine turistica della ricevuta</h5>
                    <p>Immagine facoltativa visualizzata nel piè di pagina della ricevuta.</p>
                    <form method="POST" enctype="multipart/form-data" action="{{ route('tassa_di_soggiorno.update') }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="ricevuta_senza_immagine" id="ricevuta-senza-immagine" value="0">
                        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" id="ricevuta-mostra" {{ $tassa->ricevuta_immagine ? 'checked' : '' }}><label class="form-check-label" for="ricevuta-mostra">Mostra immagine turistica nella ricevuta</label></div>
                        <div id="ricevuta-opzioni">
                            @if($tassa->ricevuta_immagine)<img src="{{ str_starts_with($tassa->ricevuta_immagine, 'tassa-ricevute/') ? route('tassa_di_soggiorno.immagine') : asset($tassa->ricevuta_immagine) }}" alt="Immagine turistica attuale" class="img-fluid rounded mb-3" id="ricevuta-preview">@else<img alt="Anteprima immagine" class="img-fluid rounded mb-3 d-none" id="ricevuta-preview">@endif
                            <label class="form-label" for="ricevuta-foto">Carica o sostituisci immagine</label><input id="ricevuta-foto" class="form-control" type="file" name="ricevuta_foto" accept="image/jpeg,image/png,image/webp">
                            <p class="text-muted small mt-2">JPG, PNG o WebP, massimo 2 MB.</p>
                        </div>
                        <p id="ricevuta-off" class="text-muted">Ricevuta senza immagine. Il footer resterà compatto.</p>
                        <p class="text-muted small">Disattivando e salvando, l’immagine viene rimossa dalla ricevuta corrente. Per mostrarla nuovamente, caricala di nuovo.</p>
                        <button class="btn btn-primary" type="submit">Salva preferenze stampa</button>
                        @if($tassa->ricevuta_immagine)<button class="btn btn-outline-secondary" type="submit" name="ricevuta_senza_immagine" value="1">Rimuovi immagine</button>@endif
                    </form>
                </div></div></div>
                <div class="col-lg-6"><div class="card border h-100"><div class="card-body"><h5 class="card-title">Messaggio di cortesia</h5><p class="fw-semibold">Grazie per aver scelto {{ $struttura->citta ?: 'la nostra città' }}!</p><p>Il vostro soggiorno contribuisce a sostenere il territorio, la qualità dei servizi turistici e la valorizzazione della nostra città.</p><p class="text-muted mb-0">Messaggio automatico della ricevuta, separato dal calcolo fiscale. Footer istituzionale centralizzato e non configurabile.</p></div></div></div>
                <div class="col-lg-6"><div class="card border h-100"><div class="card-body"><h5 class="card-title">Anteprima ricevuta</h5>
                    @if($previewSchedina)
                        <label class="form-label" for="anteprima-schedina">Scegli una Schedina della struttura corrente</label>
                        <select class="form-select mb-3" id="anteprima-schedina">
                            @foreach($previewSchedine as $opzione)<option value="{{ route('schedina.tassa.anteprima', $opzione->id) }}">Schedina {{ $opzione->id }} — {{ $opzione->arrive }} / {{ $opzione->departure }}</option>@endforeach
                        </select>
                        <a id="anteprima-ricevuta" class="btn btn-outline-primary" target="_blank" rel="noopener" href="{{ route('schedina.tassa.anteprima', $previewSchedina->id) }}">Anteprima ricevuta</a>
                        <p class="text-muted mt-2">Ultime 20 Schedine. Per le altre usa l’elenco Schedine. Il calcolo viene verificato per le date del soggiorno, indipendentemente dall’anno selezionato in questa configurazione. Nessun documento viene registrato durante l’anteprima.</p>
                    @else<p class="text-muted">L’anteprima richiede una Schedina della struttura corrente.</p>@endif
                </div></div></div>
            </div>
        </div>
    </div>
</div></div></div></div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const scelta = document.getElementById('anteprima-schedina');
    scelta?.addEventListener('change', () => { document.getElementById('anteprima-ricevuta').href = scelta.value; });
    const mostra = document.getElementById('ricevuta-mostra');
    const foto = document.getElementById('ricevuta-foto');
    const preview = document.getElementById('ricevuta-preview');
    let url;
    const aggiorna = () => {
        document.getElementById('ricevuta-senza-immagine').value = mostra.checked ? '0' : '1';
        document.getElementById('ricevuta-opzioni').hidden = !mostra.checked;
        document.getElementById('ricevuta-off').hidden = mostra.checked;
        foto.disabled = !mostra.checked;
    };
    mostra.addEventListener('change', aggiorna);
    foto.addEventListener('change', () => {
        if (!foto.files[0]) return;
        if (url) URL.revokeObjectURL(url);
        url = URL.createObjectURL(foto.files[0]); preview.src = url; preview.classList.remove('d-none');
    });
    aggiorna();
});
</script>

@else
    <div class="row config-page">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <x-table-topbar
                        title="Tassa di soggiorno"
                        subtitle="{{ $struttura->nome_struttura ?? 'Struttura' }} — {{ $struttura->citta }}"
                        :showSearch="false"
                    />

                    <div class="d-flex align-items-center gap-3 mb-3">
                        @php $logoComune = $struttura->logo_citta ?? null; @endphp
                        @if($logoComune)
                            <img src="{{ asset($logoComune) }}" alt="Logo città" style="max-height:60px;" class="rounded shadow-sm">
                        @endif
                        <div>
                            <div>{{ $struttura->nome_struttura }}</div>
                            <div class="text-muted">{{ $struttura->citta }} ({{ $struttura->provincia }})</div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-dati" data-bs-toggle="tab" data-bs-target="#pane-dati" type="button" role="tab" aria-controls="pane-dati" aria-selected="true">Dati tassa</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-regole" data-bs-toggle="tab" data-bs-target="#pane-regole" type="button" role="tab" aria-controls="pane-regole" aria-selected="false">Regole calcolo</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-esenzioni" data-bs-toggle="tab" data-bs-target="#pane-esenzioni" type="button" role="tab" aria-controls="pane-esenzioni" aria-selected="false">Esenzioni</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-export" data-bs-toggle="tab" data-bs-target="#pane-export" type="button" role="tab" aria-controls="pane-export" aria-selected="false">Anteprima export</button>
                        </li>
                    </ul>

                    <div class="alert alert-warning">Le configurazioni esistenti non sono riscritte automaticamente. Verificare aliquota, classificazione e stagione: per alberghi Bellaria documentati 1–2 stelle €1,00; 3 stelle €1,50; 4–5 stelle €2,50, massimo 6 notti. RTA/villaggi e raccordo CSV per limiti diversi da 6 restano da verificare.</div>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="pane-dati" role="tabpanel" aria-labelledby="tab-dati">
                            <form method="POST" enctype="multipart/form-data" action="{{ route('tassa_di_soggiorno.update') }}" class="row g-4">
                                @csrf
                                @method('PUT')
                                <div class="col-12">
                                    <label class="form-label" for="ricevuta-foto">Immagine turistica della ricevuta (facoltativa)</label>
                                    <input id="ricevuta-foto" class="form-control" type="file" name="ricevuta_foto" accept="image/jpeg,image/png,image/webp">
                                    <label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="ricevuta_senza_immagine" value="1"> Ricevuta senza immagine</label>
                                    <small class="text-muted">JPG, PNG o WebP, massimo 2 MB. Senza immagine il footer resta compatto.</small>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Aliquota (€ per notte)</label>
                                            <input type="number" step="0.01" min="0" max="9999" name="tassa_soggiorno" class="form-control" value="{{ old('tassa_soggiorno', $tassa->tassa_soggiorno) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Giorni massimo imponibili</label>
                                            <input type="number" min="0" max="365" name="giorni_massimo" class="form-control" value="{{ old('giorni_massimo', $tassa->giorni_massimo) }}">
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Data inizio periodo tassa</label>
                                            <x-calendario name="inizio" variant="period-start" group="tassa-periodo" :value="old('inizio', optional($tassa->inizio)->format('Y-m-d'))" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Data fine periodo tassa</label>
                                            <x-calendario name="fine" variant="period-end" group="tassa-periodo" :value="old('fine', optional($tassa->fine)->format('Y-m-d'))" />
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Età massima bambini</label>
                                            <input type="number" min="0" max="120" name="max_age_children" class="form-control" value="{{ old('max_age_children', $tassa->max_age_children) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Età minima adulti</label>
                                            <input type="number" min="0" max="120" name="min_age_adult" class="form-control" value="{{ old('min_age_adult', $tassa->min_age_adult) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card border shadow-sm h-100 mb-0">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="card-title mb-0">
                                                    <span class="section-title-help">Periodo e regole
                                                        <x-ui.help title="Periodo tassa">
                                                            Il periodo di applicazione limita le notti tassabili alle sole date comprese tra inizio e fine. Se il soggiorno supera i giorni massimo, le notti eccedenti vengono conteggiate con codice 777 senza generare importo.
                                                        </x-ui.help>
                                                    </span>
                                                </h5>
                                            </div>
                                            <div class="text-muted small mb-3">Configurazione operativa della struttura corrente.</div>
                                            <div class="border rounded-3 p-3 bg-light-subtle mb-3">
                                                <div class="text-muted small">Comune / località</div>
                                                <div class="fw-semibold">{{ $struttura->citta }}{{ !empty($struttura->localita) ? ' - ' . $struttura->localita : '' }}</div>
                                            </div>
                                            <div class="border rounded-3 p-3 bg-warning-subtle text-warning-emphasis">
                                                <div class="fw-semibold mb-1">Codice 777</div>
                                                <div class="small mb-0">Non è un'esenzione. Il sistema lo usa automaticamente per segnalare i pernottamenti oltre il limite massimo imponibile.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Note</label>
                                    <textarea name="note" class="form-control" rows="4" placeholder="Note operative o specifiche del Comune">{{ old('note', $tassa->note) }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Salva configurazione</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="pane-regole" role="tabpanel" aria-labelledby="tab-regole">
                            <div class="alert alert-info mb-0">
                                <ul class="mb-0">
                                    <li>Le notti imponibili sono limitate da "Giorni massimo imponibili".</li>
                                    <li>Le notti eccedenti sono conteggiate come "oltre giorni max" con codice 777 senza generare tassa.</li>
                                    <li>Le esenzioni impostate nella tab dedicata identificano solo soggetti non paganti.</li>
                                    <li>Aliquota, età e periodo di applicazione vengono applicati alla struttura corrente.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-esenzioni" role="tabpanel" aria-labelledby="tab-esenzioni">
                            @if(!isset($esenzioni))
                                <div class="alert alert-warning mb-0">Tabella esenzioni non presente: eseguire le migrazioni.</div>
                            @else
                                <div class="alert alert-secondary d-flex align-items-start gap-2">
                                    <i class="ri-information-line fs-5 mt-1"></i>
                                    <div>
                                        Qui vanno solo le esenzioni reali, cioè i soggetti non paganti. Il codice <strong>777</strong> non è configurabile qui perché viene gestito automaticamente dal sistema come pernottamenti oltre il limite massimo imponibile.
                                    </div>
                                </div>
                                @if($canManageEsenzioni)
                                    <div class="card border mb-3">
                                        <div class="card-header bg-light">Nuova esenzione</div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('tassa_esenzioni.store') }}" class="row g-3 align-items-end">
                                                @csrf
                                                <div class="col-md-2">
                                                    <label class="form-label">Codice</label>
                                                    <input type="text" name="codice" class="form-control" maxlength="50" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Descrizione</label>
                                                    <input type="text" name="descrizione" class="form-control" maxlength="255" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Ordine</label>
                                                    <input type="number" name="ordine" class="form-control" min="0" max="10000" value="100">
                                                </div>
                                                <div class="col-md-3 d-flex gap-3">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="attivo" value="1" id="new-attivo" checked>
                                                        <label class="form-check-label" for="new-attivo">Attivo</label>
                                                    </div>
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="richiede_nota" value="1" id="new-nota">
                                                        <label class="form-check-label" for="new-nota">Richiede nota</label>
                                                    </div>
                                                    <button type="submit" class="btn btn-success ms-auto mt-3">Aggiungi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-light border d-flex align-items-start gap-2">
                                        <i class="ri-eye-line fs-5 mt-1 text-primary"></i>
                                        <div>
                                            Le voci non paganti e informative sono gestite da admin e super admin. La struttura può solo consultarle.
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table align-middle" id="esenzioni-table">
                                        <thead>
                                            <tr>
                                                <th>Codice</th>
                                                <th>Descrizione</th>
                                                <th>Ordine</th>
                                                <th>Attivo</th>
                                                <th>Richiede nota</th>
                                                @if($canManageEsenzioni)
                                                    <th class="text-end">Azioni</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($esenzioni as $esenzione)
                                                @if($canManageEsenzioni)
                                                    <form id="update-esenzione-{{ $esenzione->id }}" method="POST" action="{{ route('tassa_esenzioni.update', $esenzione->id) }}" class="d-none">
                                                        @csrf
                                                        @method('PUT')
                                                    </form>
                                                    <form id="delete-esenzione-{{ $esenzione->id }}" method="POST" action="{{ route('tassa_esenzioni.destroy', $esenzione->id) }}" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                                <tr>
                                                    @if($canManageEsenzioni)
                                                        <td style="width: 120px"><input type="text" name="codice" value="{{ $esenzione->codice }}" class="form-control form-control-sm" form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td><input type="text" name="descrizione" value="{{ $esenzione->descrizione }}" class="form-control form-control-sm" form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td style="width: 110px"><input type="number" name="ordine" value="{{ $esenzione->ordine }}" class="form-control form-control-sm" min="0" max="10000" form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td class="text-center"><input type="checkbox" name="attivo" value="1" {{ $esenzione->attivo ? 'checked' : '' }} form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td class="text-center"><input type="checkbox" name="richiede_nota" value="1" {{ $esenzione->richiede_nota ? 'checked' : '' }} form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td class="text-end">
                                                            <div class="d-inline-flex gap-2">
                                                                <button type="submit" class="btn btn-outline-primary btn-sm" form="update-esenzione-{{ $esenzione->id }}">Salva</button>
                                                                <button type="submit" class="btn btn-outline-danger btn-sm" form="delete-esenzione-{{ $esenzione->id }}">Elimina</button>
                                                            </div>
                                                        </td>
                                                    @else
                                                        <td class="fw-semibold">{{ $esenzione->codice }}</td>
                                                        <td>{{ $esenzione->descrizione }}</td>
                                                        <td>{{ $esenzione->ordine }}</td>
                                                        <td class="text-center">{{ $esenzione->attivo ? 'Sì' : 'No' }}</td>
                                                        <td class="text-center">{{ $esenzione->richiede_nota ? 'Sì' : 'No' }}</td>
                                                    @endif
                                                </tr>
                                            @empty
                                            @endforelse
                                            <tr id="esenzioni-empty-row" class="{{ $esenzioni->count() ? 'd-none' : '' }}" data-empty-state="1">
                                                <td colspan="{{ $canManageEsenzioni ? 6 : 5 }}" class="text-muted text-center">Nessuna esenzione configurata</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted small" id="esenzioni-counter" data-base-text="{{ $esenzioni->count() ? 'Mostrando '.$esenzioni->firstItem().'–'.$esenzioni->lastItem().' di '.$esenzioni->total().' risultati' : 'Nessun risultato' }}">
                                        @if($esenzioni->count())
                                            Mostrando {{ $esenzioni->firstItem() }}–{{ $esenzioni->lastItem() }} di {{ $esenzioni->total() }} risultati
                                        @else
                                            Nessun risultato
                                        @endif
                                    </div>
                                    <div>
                                        {{ $esenzioni->links('pagination::bootstrap-5-clean') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="pane-export" role="tabpanel" aria-labelledby="tab-export">
                            <div class="alert alert-secondary">Anteprima CSV (separatore ";")</div>
                            <pre class="bg-light p-3 border rounded small mb-3">tipo;data_reg;arrivo;partenza;nominativo;soggetti;pernottamenti_imponibili;tariffa
0;2026-03-05;2026-03-05;2026-03-10;Mario Rossi;1;5;{{ $tassa->tassa_soggiorno }}
777;2026-03-05;2026-03-05;2026-03-10;Mario Rossi;1;{{ max(0, (int)($tassa->giorni_massimo ?? 6) ? 10 - (int)$tassa->giorni_massimo : 0) }};0
</pre>
                            <p class="text-muted mb-0">Le righe con tipo 777 indicano solo le notti oltre il limite massimo imponibile. Non sono esenzioni e non generano importo da pagare.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Se arrivo da paginazione, apri automaticamente la tab Esenzioni
        const url = new URL(window.location.href);
        if (url.searchParams.has('page')) {
            const tabButton = document.getElementById('tab-esenzioni');
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
    });
</script>
@endsection
