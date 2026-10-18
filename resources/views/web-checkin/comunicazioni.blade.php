@extends('layouts.master')
@section('title', 'Comunicazioni Web Check-in')
@section('content')
<div class="card"><div class="card-body">
    <h1 class="h4">Invito Web Check-in — {{ $richiesta->numero_prenotazione }}</h1>
    <p>Scegli un modello e personalizza questa comunicazione. Il modello originale cambia soltanto con «Aggiorna modello». Il contenuto riguarda l’invito e non assegna il tipo alloggiato.</p>
    <a class="btn btn-light mb-3" href="{{ route('web_checkin.edit', $richiesta->id) }}">Torna alla richiesta</a>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form method="post" action="{{ route('web_checkin.prepara', $richiesta->id) }}" id="invito-form">
        @csrf
        <input type="hidden" name="idempotenza" value="{{ (string) Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="azione" id="modello-azione" value="">
        <input type="hidden" name="versione" id="modello-versione" value="1">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="invito-modello">Modello</label>
                <select class="form-select" name="modello_id" id="invito-modello">
                    @foreach($modelli as $m)<option value="{{ $m->id }}" @selected((string)old('modello_id') === (string)$m->id) data-oggetto="{{ $m->oggetto }}" data-corpo="{{ $m->corpo }}" data-versione="{{ $m->versione }}" data-nome="{{ $m->nome }}">{{ $m->nome }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label" for="invito-nome">Nome del modello riutilizzabile</label><input class="form-control" id="invito-nome" name="nome" maxlength="191"></div>
            <div class="col-12"><label class="form-label" for="invito-oggetto">Oggetto personalizzato</label><input class="form-control" id="invito-oggetto" name="oggetto" maxlength="255" required></div>
            <div class="col-12"><label class="form-label" for="invito-corpo">Testo personalizzato</label><textarea class="form-control" id="invito-corpo" name="corpo" rows="12" maxlength="12000" required></textarea></div>
            <div class="col-12"><div class="small text-muted">Variabili disponibili (testo semplice): @foreach($variabili as $v)<code>{{ '{'.'{'.$v.'}'.'}' }}</code> @endforeach</div></div>
            <div class="col-md-6"><label class="form-label" for="invito-canale">Canale da preparare</label><select class="form-select" id="invito-canale" name="canale"><option value="testo">Testo da copiare</option><option value="email">Programma di posta esterno</option><option value="whatsapp">WhatsApp esterno</option></select></div>
            <div class="col-12 d-flex flex-wrap gap-2">
                @if($richiesta->stato !== 'convertito' && app(\App\Services\WebCheckinLink::class)->valid($richiesta))<button class="btn btn-primary" type="submit">Prepara e verifica anteprima</button>@else<p class="text-muted">Invito non disponibile: richiesta presa in carico o link scaduto/revocato. Lo storico e i modelli restano consultabili.</p>@endif
                <button class="btn btn-light" type="submit" name="azione" value="nuovo" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Salva nuovo modello</button>
                <button class="btn btn-light" type="submit" name="azione" value="duplica" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Duplica modello</button>
                <button class="btn btn-warning" type="submit" name="azione" value="aggiorna" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Aggiorna modello</button>
            </div>
        </div>
    </form>
    <p class="small text-muted mt-3">La preparazione non invia messaggi. Posta e WhatsApp sono applicazioni esterne: la loro apertura non prova invio o consegna. Per un nuovo invito torna qui e prepara esplicitamente una nuova comunicazione.</p>
    <h2 class="h5 mt-4">Storico delle preparazioni</h2>
    @forelse($storico as $c)<p><a href="{{ route('web_checkin.comunicazione', [$richiesta->id, $c->id]) }}">{{ $c->created_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }} — {{ $c->canale }} — {{ $c->stato === 'apertura_richiesta' ? 'Apertura esterna richiesta' : 'Preparato' }}</a></p>@empty<p>Nessuna comunicazione preparata.</p>@endforelse
    {{ $storico->links() }}
</div></div>
@endsection
@push('scripts')
@php($previousInput = \Illuminate\Support\Arr::only(session()->getOldInput(), ['nome', 'oggetto', 'corpo', 'canale']))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('invito-modello');
    const load = () => {
        const o = select.selectedOptions[0];
        if (!o) return;
        for (const key of ['nome', 'oggetto', 'corpo']) document.getElementById('invito-' + key).value = o.dataset[key];
        document.getElementById('modello-versione').value = o.dataset.versione;
    };
    select.addEventListener('change', load); load();
    document.querySelectorAll('#invito-form button[name="azione"]').forEach(button => {
        button.addEventListener('click', () => { document.getElementById('modello-azione').value = button.value; });
    });
    const previous = @json($previousInput);
    for (const key of ['nome', 'oggetto', 'corpo', 'canale']) {
        if (Object.prototype.hasOwnProperty.call(previous, key)) document.getElementById('invito-' + key).value = previous[key] ?? '';
    }
});
</script>
@endpush
