<div id="configurazione-questura" class="row g-3 mb-3">
    <div class="col-12 col-md-6">
        <label for="questura_profilo" class="form-label">Profilo Alloggiati Web</label>
        <select id="questura_profilo" name="questura_profilo" class="form-select">
            <option value="standard" @selected(old('questura_profilo', $struttura->questura_profilo ?? 'standard') === 'standard')>Standard</option>
            <option value="appartamenti" @selected(old('questura_profilo', $struttura->questura_profilo) === 'appartamenti')>Gestione Appartamenti</option>
        </select>
        <small class="text-muted">Scegli il profilo effettivamente abilitato dalla Questura sulla tua utenza, indipendentemente dalla tipologia della struttura.</small>
        @error('questura_profilo')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="questura_appartamento_id" class="form-label">ID appartamento Alloggiati Web</label>
        <input id="questura_appartamento_id" name="questura_appartamento_id" type="text" inputmode="numeric" pattern="[0-9]+" maxlength="10" class="form-control" value="{{ old('questura_appartamento_id', $struttura->questura_appartamento_id) }}" aria-describedby="questura_appartamento_help">
        <small id="questura_appartamento_help" class="text-muted">ID dell’appartamento attivo nell’elenco Alloggiati Web, associato a questa struttura interna. Obbligatorio solo per Gestione Appartamenti; anche 0 è ammesso. Gli zeri iniziali non cambiano l’ID. È distinto da CIR, CIN e codice Ross1000.</small>
        @error('questura_appartamento_id')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
</div>
