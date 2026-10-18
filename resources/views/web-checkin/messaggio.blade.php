@extends('layouts.master')
@section('title', 'Anteprima invito Web Check-in')
@section('content')
@php($testo = $comunicazione->contenuto)
<div class="card"><div class="card-body">
    <h1 class="h4">Anteprima della comunicazione preparata</h1>
    <p class="text-muted">Nessun invio automatico. Nessuna consegna confermata. Il contenuto è conservato cifrato nello storico della richiesta.</p>
    <h2 class="h5">{{ $testo['oggetto'] }}</h2>
    <button class="btn btn-soft-secondary mb-3" id="copia-invito" type="button">Copia oggetto e testo</button>
    <textarea class="form-control mb-3" id="testo-invito" rows="8" readonly aria-label="Testo della comunicazione">{{ $testo['oggetto']."\n\n".$testo['corpo'] }}</textarea>
    <div class="border rounded p-3 mb-3" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $testo['corpo'] }}</div>
    <p>Link e dati si riferiscono alla preparazione del {{ $comunicazione->created_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }}. Revoca, rigenerazione o scadenza possono aver invalidato il link.</p>
    @if(in_array($comunicazione->canale, ['email', 'whatsapp'], true))
        <form method="post" action="{{ route('web_checkin.comunicazione.apri', [$richiesta->id, $comunicazione->id]) }}" class="mb-3">
            @csrf
            <button class="btn btn-primary" type="submit">{{ $comunicazione->canale === 'email' ? 'Apri programma di posta esterno' : 'Apri WhatsApp esterno' }}</button>
        </form>
        <p class="small text-muted">Lo storico registra la richiesta di apertura. Il programma esterno può essere assente o rifiutare l’azione: non disponiamo di una conferma di invio o consegna.</p>
    @endif
    <a class="btn btn-light" href="{{ route('web_checkin.comunicazioni', $richiesta->id) }}">Prepara una nuova comunicazione</a>
</div></div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const text = document.getElementById('testo-invito');
    document.getElementById('copia-invito').addEventListener('click', async (event) => {
        const button = event.currentTarget;
        try { await navigator.clipboard.writeText(text.value); button.textContent = 'Testo copiato'; }
        catch (_) { text.focus(); text.select(); }
    });
});
</script>
@endpush
