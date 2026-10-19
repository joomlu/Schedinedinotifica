<header class="mb-4">
<div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3"><div><a class="small" href="{{ route('schedina.web') }}">← Elenco Web Check-in</a><h1 class="h4 mt-2 mb-1">{{ $richiesta->nome_referente }}</h1><p class="text-muted mb-2">{{ $richiesta->numero_prenotazione }} · {{ $richiesta->arrivo?->format('d/m/Y') }} – {{ $richiesta->partenza?->format('d/m/Y') }} · {{ $richiesta->quantita_persone }} ospiti</p><span class="badge {{ $richiesta->processata() ? 'bg-primary' : 'bg-secondary' }}">{{ $richiesta->processata() ? 'Check-in effettuato' : ($richiesta->compilato_at ? 'Compilazione completata · Check-in da effettuare' : 'In attesa dei dati ospiti') }}</span></div>@include('web-checkin.partials.azioni')</div>
<nav class="nav nav-tabs" aria-label="Sezioni della richiesta">
@foreach(['Richiesta'=>route('web_checkin.edit',$richiesta->id),'Email'=>route('web_checkin.comunicazioni',$richiesta->id),'Ospiti'=>route('web_checkin.attivita',[$richiesta->id,'ospiti'])] as $label=>$url)
@php($active=($sezione ?? '')===$label)
<a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $url }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav></header>
