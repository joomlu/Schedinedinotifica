@php
    $retry = $failed === $formKey;
    $nome = $record?->nome ?? '';
    $descrizione = $record?->descrizione ?? '';
    $attivo = $record?->attivo ?? true;
@endphp
<div id="{{ $modalId }}" class="modal fade" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true" data-titolo-modal
    data-nome="{{ $nome }}" data-descrizione="{{ $descrizione }}" data-attivo="{{ $attivo ? '1' : '0' }}">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="{{ $modalId }}Label">{{ $record ? 'Modifica Titolo' : 'Nuovo Titolo' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button></div>
        <form method="POST" action="{{ $record ? route('titolo.update', [$record->id], false) : route('titolo.store', [], false) }}" data-confirm-label="{{ $record ? 'il titolo '.$nome : 'il nuovo titolo' }}">
            @csrf @if($record) @method('PUT') @endif
            <input type="hidden" name="_titolo_form" value="{{ $formKey }}">
            <div class="modal-body">
                <div class="mb-3"><label for="{{ $modalId }}Nome" class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control" id="{{ $modalId }}Nome" value="{{ $retry ? $oldValue('nome') : $nome }}" maxlength="50" required>
                    @if($retry) @error('nome') <div class="text-danger">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3"><label for="{{ $modalId }}Descrizione" class="form-label">Descrizione (opzionale)</label>
                    <input type="text" name="descrizione" class="form-control" id="{{ $modalId }}Descrizione" value="{{ $retry ? $oldValue('descrizione') : $descrizione }}" maxlength="150">
                    @if($retry) @error('descrizione') <div class="text-danger">{{ $message }}</div> @enderror @endif
                </div>
                <div class="form-check form-switch"><input type="hidden" name="attivo" value="0">
                    <input type="checkbox" class="form-check-input" role="switch" name="attivo" value="1" id="{{ $modalId }}Attivo" @checked($retry ? $oldValue('attivo', 0) == 1 : $attivo)>
                    <label class="form-check-label" for="{{ $modalId }}Attivo">Attivo</label>
                    @if($retry) @error('attivo') <div class="text-danger">{{ $message }}</div> @enderror @endif
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button><button type="submit" class="btn btn-primary">Salva</button></div>
        </form>
    </div></div>
</div>
