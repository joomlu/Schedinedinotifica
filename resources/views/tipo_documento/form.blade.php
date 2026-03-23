
<div class="mb-3">
    <label for="codice" class="form-label">Codice</label>
    <input type="text" name="codice" id="codice"
           class="form-control @error('codice') is-invalid @enderror"
           value="{{ old('codice', $tipo_documento->codice ?? '') }}">
    @error('codice')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="descrizione" class="form-label">Descrizione</label>
    <input type="text" name="descrizione" id="descrizione"
           class="form-control @error('descrizione') is-invalid @enderror"
           value="{{ old('descrizione', $tipo_documento->descrizione ?? '') }}">
    @error('descrizione')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
