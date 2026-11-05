@extends('layouts.master')
@section('title') Nuova struttura @endsection
@section('content')
<h4>Registra una nuova struttura</h4>
<p>L’anagrafica sarà collegata al tuo proprietario. Dopo il salvataggio i dati maestri potranno essere modificati dall’amministratore. L’abilitazione del servizio resta separata.</p>
<form method="POST" action="{{ route('struttura.store') }}" enctype="multipart/form-data">@csrf
@include('shared.strutture.campi-maestri', ['completo' => true])
<button type="submit" class="btn btn-primary">Registra struttura</button><a href="{{ route('strutture.seleziona.index') }}" class="btn btn-light">Annulla</a>
</form>
@endsection
