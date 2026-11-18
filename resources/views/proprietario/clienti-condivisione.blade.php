@extends('layouts.master')
@section('title', 'Condivisione clienti')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Proprietario @endslot
    @slot('title') Condivisione clienti @endslot
@endcomponent
@if(!$available)
    <div class="alert alert-info">La condivisione clienti non è ancora attiva. Gli archivi attuali restano separati.</div>
@else
    <div class="card"><div class="card-body">
        <h4 class="card-title">Crea un gruppo di condivisione</h4>
        <p class="text-muted">Scegli almeno due delle tue strutture. Gli utenti già abilitati in ciascun hotel utilizzeranno la stessa anagrafica clienti.</p>
        <form method="POST" action="{{ route('proprietario.clienti-condivisione.preview') }}">
            @csrf
            <label class="form-label" for="nome-gruppo">Nome del gruppo</label>
            <input class="form-control mb-3" id="nome-gruppo" name="nome" maxlength="120" value="{{ old('nome', 'Clienti condivisi') }}" required>
            @foreach($structures as $structure)
                <div class="form-check mb-2"><input class="form-check-input" id="nuova-struttura-{{ $structure->id }}" type="checkbox" name="strutture[]" value="{{ $structure->id }}"><label class="form-check-label" for="nuova-struttura-{{ $structure->id }}">{{ $structure->nome_struttura }}</label></div>
            @endforeach
            <button class="btn btn-primary mt-2" type="submit">Verifica le scelte</button>
        </form>
    </div></div>
    @foreach($groups as $group)
        <div class="card"><div class="card-body">
            <h4 class="card-title">{{ $group->nome }}</h4>
            <div class="alert alert-success" data-sharing-status="attivo" role="status">
                <strong>Condivisione attiva</strong>
                <p class="mb-1">Questi hotel utilizzano la stessa anagrafica clienti:</p>
                <ul class="mb-2">@foreach($group->structures as $structure)<li>{{ $structure->nome_struttura }}</li>@endforeach</ul>
                <p class="mb-0">Clienti condivisi: <strong>{{ $group->people_count }}</strong>.</p>
            </div>
            @if($group->people_count === 0)
                <p>La condivisione è configurata. L’archivio condiviso è ancora vuoto: i clienti aggiunti o importati negli hotel del gruppo saranno condivisi.</p>
            @endif
            <p>Soggiorni, provenienze e consensi locali rimangono distinti.</p>
            <a class="btn btn-light btn-sm mb-3" href="{{ route('proprietario.clienti-condivisione.sources', $group->id) }}">Scarica provenienze e dati originali</a>
            <a class="btn btn-light btn-sm mb-3" href="{{ route('proprietario.clienti-condivisione.index', ['revisionati' => request()->boolean('revisionati') ? 0 : 1]) }}">{{ request()->boolean('revisionati') ? 'Mostra solo coppie da revisionare' : 'Includi coppie già revisionate' }}</a>
            <details class="mb-3"><summary>Aggiungi strutture</summary>
                <form class="mt-3" method="POST" action="{{ route('proprietario.clienti-condivisione.preview') }}">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ $group->id }}"><input type="hidden" name="version" value="{{ $group->version }}"><input type="hidden" name="nome" value="{{ $group->nome }}">
                    @foreach($structures as $structure)
                        <div class="form-check mb-2"><input class="form-check-input" id="gruppo-{{ $group->id }}-{{ $structure->id }}" type="checkbox" name="strutture[]" value="{{ $structure->id }}" @checked($group->structures->contains('id', $structure->id))><label class="form-check-label" for="gruppo-{{ $group->id }}-{{ $structure->id }}">{{ $structure->nome_struttura }}</label></div>
                    @endforeach
                    <button class="btn btn-primary mt-2" type="submit">Verifica le scelte</button>
                </form>
            </details>
            <details class="mb-3"><summary>Separa una struttura</summary>
                <p class="mt-3">La struttura conserverà i clienti del proprio archivio e quelli collegati a registrazioni locali. Perderà l’accesso agli altri clienti del gruppo. Negli altri hotel i dati restano conservati; gli aggiornamenti successivi saranno indipendenti.</p>
                <form method="POST" action="{{ route('proprietario.clienti-condivisione.separate', $group->id) }}">
                    @csrf
                    <input type="hidden" name="version" value="{{ $group->version }}">
                    <label class="form-label" for="separa-{{ $group->id }}">Struttura da separare</label>
                    <select class="form-select mb-3" id="separa-{{ $group->id }}" name="struttura_id" required>@foreach($group->structures as $structure)<option value="{{ $structure->id }}">{{ $structure->nome_struttura }}</option>@endforeach</select>
                    <label class="form-label" for="consegna-{{ $group->id }}">Pacchetto alla separazione</label>
                    <select class="form-select mb-3" id="consegna-{{ $group->id }}" name="consegna" required>
                        <option value="0">Non creare un pacchetto: conserva solo l’archivio e lo storico locali</option>
                        <option value="1">Autorizzo anche la creazione del pacchetto locale scaricabile</option>
                    </select>
                    <div class="form-check mb-3"><input class="form-check-input" id="conferma-separa-{{ $group->id }}" name="conferma" type="checkbox" value="1" required><label class="form-check-label" for="conferma-separa-{{ $group->id }}">Confermo la separazione e la cessazione degli aggiornamenti condivisi.</label></div>
                    <button class="btn btn-outline-danger" type="submit">Conferma separazione</button>
                </form>
            </details>
            <details class="mb-3"><summary>Verifica possibili duplicati ({{ count($group->candidates) }}, fino a 100 coppie)</summary>
                <p class="mt-3">Il nome uguale non dimostra che sia la stessa persona. Confronta le informazioni; scegli come principale la scheda da conservare. I dati alternativi rimangono nel registro riservato.</p>
                @forelse($group->candidates as $pair)
                    <div class="border rounded p-3 mb-3">
                        <div class="row">
                            @foreach(['prima', 'seconda'] as $side)
                                @php($data = $pair['dati_'.$side])
                                <div class="col-md-6"><strong>{{ $data['surname'] ?? '' }} {{ $data['name'] ?? '' }} — P{{ $pair[$side] }}</strong><p class="mb-2">Nascita: {{ $data['nac_reg'] ?? 'Da completare' }}, {{ $data['city_reg'] ?? '' }} {{ $data['country_reg'] ?? '' }}<br>Documento: {{ $data['type_doc_reg'] ?? '' }} {{ $data['num_doc_reg'] ?? '' }}<br>Residenza: {{ $data['city'] ?? '' }} {{ $data['cap'] ?? '' }}</p></div>
                            @endforeach
                        </div>
                        <form method="POST" action="{{ route('proprietario.clienti-condivisione.merge', $group->id) }}">
                            @csrf
                            <input type="hidden" name="principale" value="{{ $pair['prima'] }}"><input type="hidden" name="secondaria" value="{{ $pair['seconda'] }}">
                            <input type="hidden" name="versione_principale" value="{{ $pair['versione_prima'] }}"><input type="hidden" name="versione_secondaria" value="{{ $pair['versione_seconda'] }}">
                            <label class="form-label" for="principale-{{ $group->id }}-{{ $pair['prima'] }}-{{ $pair['seconda'] }}">Scheda principale</label>
                            <select class="form-select mb-2" name="principale_scelta" id="principale-{{ $group->id }}-{{ $pair['prima'] }}-{{ $pair['seconda'] }}"><option value="{{ $pair['prima'] }}">P{{ $pair['prima'] }}</option><option value="{{ $pair['seconda'] }}">P{{ $pair['seconda'] }}</option></select>
                            <div class="form-check mb-2"><input class="form-check-input" id="fusione-{{ $group->id }}-{{ $pair['prima'] }}-{{ $pair['seconda'] }}" type="checkbox" name="conferma" value="1" required><label class="form-check-label" for="fusione-{{ $group->id }}-{{ $pair['prima'] }}-{{ $pair['seconda'] }}">Ho verificato che è la stessa persona e confermo la scheda principale scelta.</label></div>
                            <button class="btn btn-outline-primary btn-sm" type="submit">Riunisci conservando le provenienze</button>
                        </form>
                        <form class="mt-3" method="POST" action="{{ route('proprietario.clienti-condivisione.keep-separate', $group->id) }}">
                            @csrf
                            <input type="hidden" name="prima" value="{{ $pair['prima'] }}"><input type="hidden" name="seconda" value="{{ $pair['seconda'] }}">
                            <input type="hidden" name="versione_prima" value="{{ $pair['versione_prima'] }}"><input type="hidden" name="versione_seconda" value="{{ $pair['versione_seconda'] }}">
                            <label><input name="conferma" type="checkbox" value="1" required> Mantengo queste schede separate per la revisione attuale.</label>
                            <button class="btn btn-light btn-sm" type="submit">Mantieni separate</button>
                        </form>
                    </div>
                @empty<p>Nessuna coppia omonima da proporre. Non equivale all’assenza di duplicati con nomi diversi.</p>@endforelse
            </details>
            <details class="mb-3"><summary>Residenze da confermare ({{ count($group->residences) }}, fino a 30 persone)</summary>
                @foreach($group->residences as $residence)
                    <form class="border rounded p-3 mt-3" method="POST" action="{{ route('proprietario.clienti-condivisione.residence', $group->id) }}">
                        @csrf
                        <strong>{{ $residence['data']['surname'] ?? '' }} {{ $residence['data']['name'] ?? '' }} — P{{ $residence['id'] }}</strong>
                        <input type="hidden" name="persona" value="{{ $residence['id'] }}"><input type="hidden" name="version" value="{{ $residence['version'] }}">
                        <label class="form-label d-block mt-2" for="residenza-{{ $residence['id'] }}">Versione verificata con il cliente</label>
                        <select class="form-select mb-2" id="residenza-{{ $residence['id'] }}" name="residenza" required>
                            @foreach($residence['alternative'] as $alternative)
                                <option value="{{ $alternative['id'] }}">{{ $alternative['hotel'] }}: {{ $alternative['data']['country'] ?? '' }}, {{ $alternative['data']['city'] ?? '' }}, {{ $alternative['data']['province'] ?? '' }}, {{ $alternative['data']['cap'] ?? '' }}, {{ $alternative['data']['address'] ?? '' }} {{ $alternative['data']['number'] ?? '' }}</option>
                            @endforeach
                        </select>
                        <div class="form-check mb-2"><input class="form-check-input" id="conferma-residenza-{{ $residence['id'] }}" name="conferma" type="checkbox" value="1" required><label class="form-check-label" for="conferma-residenza-{{ $residence['id'] }}">Confermo che la versione scelta è stata verificata come residenza attuale.</label></div>
                        <button class="btn btn-outline-primary btn-sm" type="submit">Conferma residenza</button>
                    </form>
                @endforeach
            </details>
            @if($group->merges->isNotEmpty())
                <details><summary>Fusioni recenti</summary>
                    <p class="mt-3">L’annullamento automatico è consentito solo se non sono intervenuti aggiornamenti o nuove registrazioni.</p>
                    @foreach($group->merges as $merge)
                        <form class="mb-3" method="POST" action="{{ route('proprietario.clienti-condivisione.undo', $group->id) }}">@csrf
                            <input type="hidden" name="operazione" value="{{ $merge->id }}">
                            <label><input type="checkbox" name="conferma" value="1" required> Confermo l’annullamento della fusione {{ $merge->id }} del {{ $merge->created_at }}</label>
                            <button class="btn btn-light btn-sm" type="submit">Annulla fusione</button>
                        </form>
                    @endforeach
                </details>
            @endif
        </div></div>
    @endforeach
@endif
@if($packages->isNotEmpty())
    <div class="card"><div class="card-body"><h4 class="card-title">Pacchetti delle strutture separate</h4><p>Copie dei dati e delle residenze alternative conservati al momento della separazione. Non ricevono gli aggiornamenti successivi del gruppo.</p>
        @foreach($packages as $package)<a class="btn btn-light mb-2" href="{{ route('proprietario.clienti-condivisione.package', $package->id) }}">Scarica pacchetto {{ $package->nome_struttura }}</a>@endforeach
    </div></div>
@endif
@endsection
