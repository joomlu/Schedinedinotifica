@extends('layouts.master-without-nav')
@section('title', 'Invito Web Check-in')
@section('body')<body>@endsection
@section('content')
<main class="container py-5" style="max-width:740px"><div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center">
@if($identitaHotel['logo_url'])<img src="{{ $identitaHotel['logo_url'] }}" width="{{ $identitaHotel['logo_width'] }}" height="{{ $identitaHotel['logo_height'] }}" alt="{{ $identitaHotel['nome'] }}" style="max-height:90px;max-width:220px;object-fit:contain">@endif
<h1 class="h3 my-4">{{ $identitaHotel['nome'] }}</h1><h2 class="h4">Benvenuto, prepariamo il tuo arrivo</h2>
<p>Completa il check-in online prima dell’arrivo. Puoi salvare e continuare più tardi.</p>
<div class="rounded border p-3 my-4 text-start" style="background:#f4f7f7"><strong>Prenotazione:</strong> {{ $richiesta->numero_prenotazione }}<br><strong>Arrivo:</strong> {{ $richiesta->arrivo->format('d/m/Y') }}<br><strong>Partenza:</strong> {{ $richiesta->partenza->format('d/m/Y') }}<br><strong>Ospiti:</strong> {{ $richiesta->quantita_persone }}</div>
<a href="{{ $checkinUrl }}" class="btn btn-lg px-4 py-3" style="background:{{ $identitaHotel['colore'] }};color:{{ $identitaHotel['testo_colore'] }}">Apri il tuo Web Check-in</a>
<p class="small text-muted mt-4">Link disponibile fino al {{ $richiesta->link_expires_at?->timezone('Europe/Rome')->format('d/m/Y H:i:s T') }}. La reception verificherà i dati prima della registrazione definitiva.</p>
</div></div></main><style>body{background:#f3f5f4}</style>
@endsection
