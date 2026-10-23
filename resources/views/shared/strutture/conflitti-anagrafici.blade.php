@if(!empty($anagraficaConflitti))
<div class="alert alert-warning" role="alert">
    @foreach($anagraficaConflitti as $campo => $messaggio)
        <p>{{ ucfirst(str_replace('_', ' ', $campo)) }}: {{ $messaggio }} Valore storico: {{ $struttura->getRawOriginal($campo) }}</p>
    @endforeach
    <div class="form-check"><input type="checkbox" id="confermaRisoluzioneAnagrafica" name="conferma_risoluzione_anagrafica" value="1" class="form-check-input" @checked(old('conferma_risoluzione_anagrafica'))>
    <label class="form-check-label" for="confermaRisoluzioneAnagrafica">Ho verificato i dati e confermo i valori selezionati per risolvere le discordanze.</label></div>
    @error('conferma_risoluzione_anagrafica')<div class="text-danger">{{ $message }}</div>@enderror
</div>
@endif
