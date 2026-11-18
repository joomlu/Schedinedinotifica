@extends('layouts.master')
@section('title', 'Conferma condivisione clienti')
@section('content')
<div class="card"><div class="card-body">
    <h4 class="card-title">Conferma condivisione clienti</h4>
    <p>Le strutture selezionate utilizzeranno la stessa anagrafica. Ogni soggiorno e codice precedente rimarrà collegato al proprio hotel.</p>
    <ul>@foreach($structures as $structure)<li>{{ $structure->nome_struttura }}</li>@endforeach</ul>
    <p>Schede attualmente presenti negli archivi selezionati: <strong>{{ $count }}</strong>. Le corrispondenze certe saranno collegate; i casi dubbi resteranno separati e consultabili nella revisione duplicati.</p>
    <p>I consensi e le classificazioni locali non vengono estesi agli altri hotel. La provenienza da un archivio non crea un soggiorno.</p>
    <form method="POST" action="{{ route('proprietario.clienti-condivisione.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-check mb-3"><input class="form-check-input" id="conferma-gruppo" name="conferma" type="checkbox" value="1" required><label class="form-check-label" for="conferma-gruppo">Confermo che queste strutture devono condividere l’anagrafica clienti.</label></div>
        <button class="btn btn-primary" type="submit">Conferma condivisione</button>
        <a class="btn btn-light" href="{{ route('proprietario.clienti-condivisione.index') }}">Torna alle scelte</a>
    </form>
</div></div>
@endsection
