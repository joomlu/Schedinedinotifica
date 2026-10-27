@extends('layouts.master')
@section('title') Dati struttura @endsection
@section('content')
<h4>Dati struttura — {{ $struttura->nome_struttura }}</h4>
<p class="text-muted">Anagrafica unica #{{ $struttura->id }}. Per modificare i dati maestri rivolgersi all’amministratore.</p>
@php
    $dominio = app(\App\Services\AnagraficaStruttura::class);
    $vista = $dominio->display($struttura);
    $campi = array_diff($dominio::MAESTRI, ['logo', 'logo_citta', 'logo_citta_upload', 'tipologia_generale_id', 'tipologia_struttura_id', 'classificazione_id']);
@endphp
@foreach($vista['conflitti'] as $campo => $messaggio)<div class="alert alert-warning">{{ $campo }}: {{ $messaggio }}</div>@endforeach
<div class="row g-3">
@foreach($vista['valori'] + collect($campi)->mapWithKeys(fn ($campo) => [$campo => $struttura->$campo])->all() as $campo => $valore)
    <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 text-break"><div class="text-muted">{{ ucfirst(str_replace('_', ' ', $campo)) }}</div><div data-campo="{{ $campo }}">{{ $valore instanceof \DateTimeInterface ? $valore->format('d/m/Y') : ($valore ?? 'Non indicato') }}</div></div></div>
@endforeach
@foreach(['logo' => 'Logo struttura', 'logo_citta' => 'Logo città'] as $campo => $etichetta)
    @if($struttura->$campo)<div class="col-12 col-md-6"><p>{{ $etichetta }}</p><img src="{{ asset($struttura->$campo) }}" alt="{{ $etichetta }}" class="img-fluid" style="max-height:100px"></div>@endif
@endforeach
</div>
@if($puoGestireOperativo)
@if(auth()->user()->isProprietario() || (auth()->user()->isStrutturaUser() && auth()->user()->ruolo_operativo === 'proprietario'))
<a class="btn btn-outline-primary mt-3" href="{{ route('struttura.create') }}">Registra struttura</a>
@endif
<div class="card mt-4"><div class="card-body"><h5>Configurazione operativa</h5>
<form method="POST" action="{{ route('struttura.update', ['sid' => $struttura->id]) }}">@csrf @method('PUT')
<input type="hidden" name="camere_reali_enabled" value="0">
<div class="form-check form-switch my-3"><input id="camereRealiSwitch" type="checkbox" class="form-check-input" name="camere_reali_enabled" value="1" @checked(old('camere_reali_enabled', $struttura->camere_reali_enabled))><label for="camereRealiSwitch">Gestione camere reali</label></div>
@foreach(['questura_username' => 'Username Questura', 'questura_password' => 'Nuova password Questura', 'questura_wskey' => 'Nuovo WSKEY Questura'] as $campo => $etichetta)
<div class="mb-3">
    <label for="{{ $campo }}" class="form-label">{{ $etichetta }}</label>
    @if($campo === 'questura_password')<div class="input-group">@endif
    <input id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'questura_username' ? 'text' : 'password' }}" autocomplete="{{ $campo === 'questura_username' ? 'username' : 'new-password' }}" class="form-control" value="{{ $campo === 'questura_username' ? old($campo, $struttura->$campo) : '' }}">
    @if($campo === 'questura_password')
        <button type="button" class="btn btn-outline-secondary" data-password-toggle="questura_password" aria-label="Mostra password" aria-pressed="false"><i class="ri-eye-line align-middle"></i></button>
        </div>
    @endif
    @if($campo !== 'questura_username')<small class="text-muted d-block mt-1">{{ filled($struttura->getRawOriginal($campo)) ? 'Configurata' : 'Non configurata' }}</small>@endif
    @error($campo)<div class="text-danger">{{ $message }}</div>@enderror
</div>
@endforeach
<p class="text-muted">Lascia vuoti password e WSKEY per conservare quelli configurati.</p><button class="btn btn-primary" type="submit">Salva configurazione operativa</button>
</form></div></div>
@endif
@if($struttura->proprietario && $puoGestireOperativo)
<div class="card mt-4"><div class="card-body"><h5>Anagrafica proprietario #{{ $struttura->proprietario->id }}</h5>
@foreach(['nome', 'email', 'telefono', 'cellulare', 'ragione_sociale', 'codice_fiscale', 'partita_iva', 'codice_destinatario', 'pec', 'indirizzo', 'numero_civico', 'cap', 'citta', 'provincia', 'regione', 'nazione'] as $campo)
<p><strong>{{ ucfirst(str_replace('_', ' ', $campo)) }}:</strong> {{ $struttura->proprietario->$campo ?? 'Non indicato' }}</p>
@endforeach
</div></div>
@endif
@endsection

@section('script')
<script>
document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.dataset.passwordToggle);
        var visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-label', visible ? 'Nascondi password' : 'Mostra password');
        button.setAttribute('aria-pressed', visible ? 'true' : 'false');
        button.querySelector('i').className = visible ? 'ri-eye-off-line align-middle' : 'ri-eye-line align-middle';
    });
});
</script>
@endsection
