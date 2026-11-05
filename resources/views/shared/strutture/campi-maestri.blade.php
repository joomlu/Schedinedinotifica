@php
    $completo = $completo ?? false;
    $campi = [
        'cir' => ['CIR', 'text'], 'cin' => ['CIN', 'text'],
        'ragione_sociale' => ['Ragione sociale della struttura', 'text'], 'partita_iva' => ['Partita IVA', 'text'],
        'codice_fiscale' => ['Codice fiscale', 'text'], 'codice_unico' => ['Codice destinatario', 'text'],
        'camere_disponibili' => ['Camere disponibili', 'number'], 'letti_disponibili' => ['Letti disponibili', 'number'], 'letti_agg' => ['Letti aggiuntivi', 'number'],
        'telefono' => ['Telefono commerciale', 'tel'], 'telefono_secondario' => ['Telefono secondario', 'tel'],
        'email' => ['Email commerciale', 'email'], 'fax' => ['Fax', 'tel'], 'sito_web' => ['Sito web', 'url'],
    ];
    if ($completo) $campi = [
        'nome_struttura' => ['Nome struttura', 'text'], 'nazione' => ['Nazione', 'text'], 'regione' => ['Regione', 'text'],
        'provincia' => ['Provincia', 'text'], 'citta' => ['Comune', 'text'], 'indirizzo' => ['Indirizzo', 'text'],
        'numero_civico' => ['Numero civico', 'text'], 'cap' => ['CAP', 'text'], 'zona' => ['Zona', 'text'], 'localita' => ['Località', 'text'],
        'latitudine' => ['Latitudine', 'text'], 'longitudine' => ['Longitudine', 'text'],
    ] + $campi;
@endphp
<div class="card my-3"><div class="card-header"><h5 class="mb-0">Anagrafica della struttura</h5></div><div class="card-body">
    @include('shared.strutture.conflitti-anagrafici')
    <x-xx.struttura-tipologia :tipologie-generali="$tipologieGenerali" :tipologie-struttura="$tipologieStruttura" :classificazioni="$classificazioni" :entity="$struttura" />
    <div class="row g-3 mt-1">
        @foreach($campi as $campo => [$etichetta, $tipo])
            <div class="col-12 col-md-6 col-xl-4">
                <label class="form-label" for="anagrafica_{{ $campo }}">{{ $etichetta }}</label>
                <input class="form-control @error($campo) is-invalid @enderror" id="anagrafica_{{ $campo }}" name="{{ $campo }}" type="{{ $tipo }}" value="{{ old($campo, $struttura->$campo) }}" @if($tipo === 'number') min="0" step="1" @endif>
                @error($campo)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endforeach
        <div class="col-12 col-md-4">
            <label for="anagrafica_apertura" class="form-label">Tipo apertura</label>
            <select id="anagrafica_apertura" name="tipo_apertura" class="form-select"><option value="">Seleziona</option>
                @foreach(['Annuale', 'Stagionale'] as $tipo)<option @selected(old('tipo_apertura', $struttura->tipo_apertura) === $tipo)>{{ $tipo }}</option>@endforeach
            </select>
            @error('tipo_apertura')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        @foreach(['data_apertura' => 'Data apertura', 'data_chiusura' => 'Data chiusura'] as $campo => $etichetta)
            <div class="col-12 col-md-4"><label class="form-label" for="anagrafica_{{ $campo }}">{{ $etichetta }}</label>
            <input id="anagrafica_{{ $campo }}" name="{{ $campo }}" type="date" class="form-control" value="{{ old($campo, $struttura->$campo?->format('Y-m-d')) }}">
            @error($campo)<div class="text-danger">{{ $message }}</div>@enderror</div>
        @endforeach
        @if($completo)
            <div class="col-12"><label for="anagrafica_logo" class="form-label">Logo struttura</label><input id="anagrafica_logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-control">@error('logo')<div class="text-danger">{{ $message }}</div>@enderror</div>
        @endif
    </div>
</div></div>
