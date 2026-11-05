@php
    $geoCatalogo = app(\App\Services\AnagraficaGeografia::class)->form($entity);
    // Dopo un errore si conserva l'intero input dell'operatore, senza risolvere il vecchio record.
    $geoHaInput = collect(['nazione', 'regione', 'provincia', 'citta', 'cap'])->contains(fn ($campo) => session()->hasOldInput($campo));
@endphp
@if($geoCatalogo && ! $geoHaInput)
    <x-geo.italia :prefix="$geoPrefix" title="Ubicazione" :required="false" :value="$geoCatalogo"
        :names="['nazione_id' => 'nazione', 'regione_id' => 'regione', 'provincia_id' => 'provincia', 'comune_id' => 'citta', 'cap' => 'cap',
            'regione_text' => 'regione', 'provincia_text' => 'provincia', 'citta_text' => 'citta', 'cap_text' => 'cap', 'manual_flag' => 'geo_manual']" />
@else
    <div class="border rounded p-3">
        <h6>Ubicazione</h6>
        @if($entity->exists && ! $geoCatalogo)<p class="text-muted small">Dati geografici conservati in forma testuale: il catalogo non offre un riscontro univoco completo. Verifica i dati prima di modificarli.</p>@endif
        <div class="row g-3">
        @foreach(['nazione' => 'Nazione', 'regione' => 'Regione', 'provincia' => 'Provincia', 'citta' => 'Comune', 'cap' => 'CAP'] as $campo => $etichetta)
            <div class="col-12 col-md-6"><label for="{{ $geoPrefix }}_{{ $campo }}" class="form-label">{{ $etichetta }}</label>
            <input type="text" id="{{ $geoPrefix }}_{{ $campo }}" name="{{ $campo }}" class="form-control @error($campo) is-invalid @enderror" value="{{ old($campo, $entity->$campo) }}">
            @error($campo)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        @endforeach
        </div>
    </div>
@endif
