@extends('layouts.master')
@section('title') Configurazione Tassa di soggiorno @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Superadmin @endslot
@slot('title') Configurazione Tassa di soggiorno @endslot
@endcomponent
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $errore)<li>{{ $errore }}</li>@endforeach</ul></div>@endif
@php
$contesto = ['comune' => $comune, 'anno' => $anno, 'categoria' => $categoria];
$nomi = ['valida_dal' => 'Validità dal', 'valida_al' => 'Validità al', 'inizio' => 'Periodo dell’imposta dal', 'fine' => 'Periodo dell’imposta al', 'giorni_massimo' => 'Massimo notti consecutive', 'max_age_children' => 'Età massima esenzione', 'min_age_adult' => 'Età minima imponibile'];
$documenti = array_reverse($scheda['documenti']);
$haPropostaCompleta = collect($documenti)->contains(fn ($d) => $d['ufficiale'] && !$d['analisi']['problemi']);
$stato = isset($scheda['applicazione']) ? 'Completo' : ($haPropostaCompleta ? 'Aggiornamento disponibile' : 'In attesa di informazioni');
@endphp
<div class="card tassa-configurazione"><div class="card-body">
<h4>Configurazione tassa di soggiorno</h4>
<p class="text-muted">Profili comunali per anno e categoria. Il contesto territoriale qui selezionato è indipendente dalla struttura nel menu superiore.</p>
<h5>Contesto territoriale</h5>
<form method="GET" action="{{ route('superadmin.tassa.configurazione') }}" class="row g-3 mb-4">
<div class="col-md-5"><label for="comune" class="form-label">Comune</label><input id="comune" name="comune" class="form-control" list="comuni-tassa" value="{{ $comune }}" required maxlength="120"><datalist id="comuni-tassa">@foreach($comuni as $nome)<option value="{{ $nome }}">@endforeach</datalist></div>
<div class="col-md-2"><label for="anno" class="form-label">Anno fiscale</label><input id="anno" name="anno" class="form-control" type="number" min="2015" max="2100" required value="{{ $anno }}"></div>
<div class="col-md-3"><label for="categoria" class="form-label">Categoria del profilo disponibile</label><select id="categoria" name="categoria" class="form-select">@foreach(range(1,5) as $c)<option value="{{ $c }}" @selected($c === $categoria)>Alberghi {{ $c }} stelle</option>@endforeach</select></div>
<div class="col-md-2 align-self-end d-grid"><button class="btn btn-light">Apri scheda</button></div>
</form>
<div class="alert alert-light border text-body" role="status"><h5>{{ $comune }} — {{ $anno }} — Alberghi {{ $categoria }} stelle</h5>
<p><strong>Stato:</strong> {{ $stato }}<br><strong>Ultima verifica:</strong> {{ $scheda['ultima_verifica'] ?? 'Non ancora eseguita' }}</p></div>
<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Sezioni configurazione tassa"><a class="btn btn-outline-primary" href="#profilo-territoriale">Profilo territoriale</a><a class="btn btn-outline-primary" href="#verifiche-fonti">Verifiche delle fonti</a><a class="btn btn-outline-primary" href="#documentazione-tassa">Documentazione</a><a class="btn btn-outline-primary" href="#note-esenzioni">Note sulle esenzioni</a></nav>
<section id="profilo-territoriale" class="border rounded p-3 mb-4" aria-labelledby="titolo-profilo"><h5 id="titolo-profilo">Profilo territoriale applicabile</h5>
@if($profilo)
<dl class="row">
<dt class="col-sm-5">Tariffa per notte</dt><dd class="col-sm-7">€ {{ number_format((float)$profilo['tariffe'][$categoria], 2, ',', '.') }}</dd>
@foreach($nomi as $campo => $nome)<dt class="col-sm-5">{{ $nome }}</dt><dd class="col-sm-7">{{ in_array($campo, ['valida_dal', 'valida_al', 'inizio', 'fine'], true) ? \Carbon\Carbon::parse($profilo[$campo])->format('d/m/Y') : $profilo[$campo] }}</dd>@endforeach
<dt class="col-sm-5">Fonte del profilo applicato</dt><dd class="col-sm-7">{{ $profilo['regola_fonte'] }}</dd>
</dl>
@else
<div class="alert alert-warning">Non è disponibile un profilo comunale certificato per questo anno e categoria. Non vengono copiati importi o periodi dagli anni precedenti. La continuità richiede evidenza della vigenza: una disposizione limitata a un anno non viene prolungata.</div>
@endif
@if($comune !== 'Bellaria-Igea Marina')<p class="text-warning">Il motore attuale usa configurazioni legacy per struttura in questo Comune. La documentazione può essere conservata, ma l’applicazione di un nuovo profilo comunale richiede un’estensione del calcolo fuori da questo intervento.</p>@endif
</section>
<section id="verifiche-fonti" class="border rounded p-3 mb-4" aria-labelledby="titolo-verifiche"><h5 id="titolo-verifiche">Verifiche delle fonti</h5>
<form method="POST" action="{{ route('superadmin.tassa.verifica') }}" class="mb-3">@csrf @foreach($contesto as $campo => $v)<input type="hidden" name="{{ $campo }}" value="{{ $v }}">@endforeach<button class="btn btn-primary">Verifica aggiornamenti</button></form>
@foreach($scheda['verifica'] ?? [] as $avviso)<p class="text-warning">{{ $avviso }}</p>@endforeach
<p class="text-muted">Controllo automatico settimanale delle fonti note per anno corrente e successivo. L’esito distingue documenti trovati, dati mancanti ed errori; una ricerca negativa non prova l’assenza di nuove regole.</p>
@if($inCoda)<div class="alert alert-info" id="verifica-in-coda">Verifica in coda o in corso. I risultati appariranno in questa scheda appena disponibili.</div>@endif
</section>
<section id="documentazione-tassa" class="border rounded p-3 mb-4" aria-labelledby="titolo-documentazione"><h5 id="titolo-documentazione">Documentazione di supporto</h5>
<form method="POST" action="{{ route('superadmin.tassa.documento') }}" enctype="multipart/form-data" class="mb-4">@csrf @foreach($contesto as $campo => $v)<input type="hidden" name="{{ $campo }}" value="{{ $v }}">@endforeach
<label for="fonte" class="form-label">Collegamento ufficiale</label><input id="fonte" name="fonte" type="url" class="form-control mb-2" value="{{ old('fonte') }}" placeholder="https://…">
<label for="pdf" class="form-label">Documento PDF (massimo 8 MB)</label><input id="pdf" name="pdf" type="file" accept="application/pdf" class="form-control mb-2">
<label for="testo" class="form-label">Testo del documento con pagina o articolo</label><textarea id="testo" name="testo" class="form-control mb-2" rows="4" maxlength="100000">{{ old('testo') }}</textarea>
<p class="text-muted">Originali conservati in un archivio privato. Un upload non certifica provenienza o validità; OCR e servizi di analisi esterni non disponibili.</p>
<button class="btn btn-outline-primary">Conserva e analizza</button></form>
@foreach($documenti as $d)
<details class="mb-3"><summary>{{ $d['titolo'] ?? 'Documento' }} — {{ \Carbon\Carbon::parse($d['verificata_il'])->format('d/m/Y H:i') }} — {{ isset($d['attestazione_superadmin']) ? 'Provenienza e vigore verificati dal Superadmin su riscontro documentato' : ($d['ufficiale'] ? 'Provenienza istituzionale online verificata; vigore da controllare' : 'Provenienza da verificare') }}</summary>
<p>{{ $d['fonte'] ?? 'Documento fornito dal Superadmin' }} <a href="{{ route('superadmin.tassa.download', array_merge($contesto, ['id' => $d['id']])) }}">Scarica originale</a></p>
@if($d['limite'])<p class="text-warning">{{ $d['limite'] }}</p>@endif
@foreach($d['analisi']['problemi'] as $problema)<p>{{ $problema }}</p>@endforeach
@foreach($d['analisi']['valori'] as $campo => $valore)
@php $attuale = str_starts_with($campo, 'tariffa_') ? ($profilo['tariffe'][(int)substr($campo,-1)] ?? null) : ($profilo[$campo] ?? null); @endphp
@if((string)$attuale !== (string)$valore)<p><strong>{{ $nomi[$campo] ?? 'Tariffa alberghi '.substr($campo,-1).' stelle' }}</strong>: {{ $attuale ?? 'Non disponibile' }} → {{ $valore }}. {{ $d['analisi']['riferimenti'][$campo]['riferimento'] }}: {{ $d['analisi']['riferimenti'][$campo]['passaggio'] }}</p>@endif
@endforeach
@if(!$profilo || $profilo['valida_dal'] > now()->toDateString())
<details><summary>Completa soltanto i punti da chiarire e conferma il profilo futuro</summary>
<form method="POST" action="{{ route('superadmin.tassa.conferma') }}" class="row g-2">@csrf @foreach($contesto as $campo => $v)<input type="hidden" name="{{ $campo }}" value="{{ $v }}">@endforeach<input type="hidden" name="documento" value="{{ $d['id'] }}">
@foreach($nomi as $campo => $nome)<div class="col-md-6"><label class="form-label">{{ $nome }}<input class="form-control" name="{{ $campo }}" type="{{ in_array($campo,['valida_dal','valida_al','inizio','fine']) ? 'date' : 'number' }}" required value="{{ old($campo, $d['analisi']['valori'][$campo] ?? '') }}"></label></div>@endforeach
@foreach(range(1,5) as $c)<div class="col-md-4"><label class="form-label">Tariffa alberghi {{ $c }} stelle (€)<input class="form-control" name="tariffa_{{ $c }}" type="number" min="0" step="0.01" required value="{{ old('tariffa_'.$c, $d['analisi']['valori']['tariffa_'.$c] ?? '') }}"></label></div>@endforeach
@if(!$d['ufficiale'])
<label class="form-label">Protocollo comunale, firma digitale già verificata o riscontro dell’Ufficio Tributi<textarea name="riscontro_comunale" class="form-control" required minlength="30" maxlength="2000"></textarea></label>
<label class="form-label">Articolo o comunicazione che attesta la vigenza nell’anno {{ $anno }}<textarea name="riferimento_vigore" class="form-control" required minlength="20" maxlength="2000"></textarea></label>
<label><input type="checkbox" name="verifica_provenienza" value="1" required> Ho verificato il riscontro presso il Comune o la firma, separatamente dal caricamento. La registrazione è una verifica documentata del Superadmin, non una verifica automatica della firma o del protocollo.</label>
@endif
<label class="form-label">Passaggi ufficiali, pagine/articoli e motivazione che risolvono i dubbi<textarea name="motivazione" class="form-control" required minlength="20" maxlength="2000"></textarea></label>
<label><input name="conferma" type="checkbox" value="1" required> Ho verificato provenienza, vigore nell’anno indicato, completezza e assenza di modifiche alle esenzioni e al comportamento del calcolo.</label><button class="btn btn-primary">Conferma profilo futuro</button></form></details>
@endif
</details>
@endforeach
</section>
<section id="note-esenzioni" class="border rounded p-3 mb-4" aria-label="Note sulle esenzioni">
@if($comune === 'Bellaria-Igea Marina')
<h5>Familiari del gestore se anagraficamente conviventi</h5>
<p>La voce è stata inserita dal seeder Bellaria come codice 450. Se il record è attivo e selezionato, il calcolo attuale lo riconosce come esenzione; non viene applicata automaticamente in base alla parentela. L’etichetta «Legacy — non certificato» indica che la voce non appartiene al catalogo certificato incorporato, non che sia assente dalla normativa. La specifica StayTour fornita in precedenza elenca i codici 400–440 e non il 450: il raccordo del codice locale con le esportazioni resta non certificato.</p>
<p>Il regolamento comunale del 2025 pubblicato e collegato dal sito istituzionale contiene questa disposizione nell’articolo 5, comma 1, lettera i), pagine 4–5. L’audit documentale del 7/10/2026 aveva recuperato il coordinato CC29/2026: l’articolo 5 risultava invariato. Nella nuova consultazione del 13/10/2026 la pagina comunale richiama ancora gli atti del 2025 e alcuni allegati dell’albo non sono accessibili. Occorre verificare il coordinato vigente e la sua efficacia per l’anno {{ $anno }}. Non vengono certificati, eliminati o modificati record o calcoli. Eventuali rettifiche del calcolo restano fuori ambito.</p>
<a href="https://www.comune.bellaria-igea-marina.rn.it/it/documenti_pubblici/regolamento-imposta-di-soggiorno-ids" rel="noopener" target="_blank">Fonte comunale consultata il 13/10/2026</a>
@else
<p class="mb-0 text-muted">Nessuna nota territoriale aggiuntiva presente per il Comune selezionato.</p>
@endif
</section>
</div></div>
@endsection

@push('styles')
<style>
.tassa-configurazione { min-width: 0; }
.tassa-configurazione section { scroll-margin-top: 100px; overflow-wrap: anywhere; }
.tassa-configurazione summary { cursor: pointer; padding: .75rem 0; overflow-wrap: anywhere; }
.tassa-configurazione label.form-label { width: 100%; }
.tassa-configurazione dd { overflow-wrap: anywhere; }
@media (max-width: 575.98px) {
    .tassa-configurazione nav .btn { flex: 1 1 100%; white-space: normal; }
    .tassa-configurazione form > .btn { width: 100%; }
    .tassa-configurazione section { padding: .75rem !important; }
}
</style>
@endpush
@section('script')
@if($inCoda)
<script>
(() => {
    let dirty = false;
    document.addEventListener('input', () => { dirty = true; });
    const poll = async () => {
        try {
            const response = await fetch(@json(route('superadmin.tassa.configurazione', $contesto)), {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) return;
            const state = await response.json();
            if (!state.in_coda) {
                if (!dirty) { location.reload(); return; }
                const box = document.getElementById('verifica-in-coda');
                box.textContent = 'Verifica conclusa. Conserva i dati che stai compilando prima di ricaricare la scheda.';
                return;
            }
        } catch (error) { /* Nessun errore tecnico esposto nell’interfaccia. */ }
        setTimeout(poll, 10000);
    };
    setTimeout(poll, 10000);
})();
</script>
@endif
@endsection
