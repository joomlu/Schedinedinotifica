@php
    $editing = $evento !== null;
    $editorId = $editing ? 'modalEvento'.$evento->id : 'modalNuovoEvento';
    $failed = old('_editor') === $editorId;
    $value = fn ($field, $default = null) => $failed ? old($field, $default) : ($editing ? ($evento->{$field} ?? $default) : $default);
@endphp
<div id="{{ $editorId }}" class="modal fade" tabindex="-1" aria-labelledby="{{ $editorId }}Title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 id="{{ $editorId }}Title" class="modal-title">{{ $editing ? 'Modifica annotazione' : 'Aggiungi al calendario' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button></div>
        <form data-confirm="off" method="POST" action="{{ $editing ? route('calendario.update', $evento->id) : route('calendario.store') }}" class="d-flex flex-column overflow-hidden">
            @csrf
            @if($editing) @method('PUT') @endif
            <input type="hidden" name="_editor" value="{{ $editorId }}">
            <input type="hidden" name="contesto" value="{{ $contesto }}">
            <div class="modal-body">
                @if($failed && $errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <div class="row g-3">
                    <div class="col-sm-6"><label for="{{ $editorId }}Tipo" class="form-label">Cosa vuoi aggiungere?</label><x-ui.select id="{{ $editorId }}Tipo" name="tipo">@foreach(['manuale' => 'Da fare · attività o comunicazione', 'nota' => 'Informazioni · nota', 'evento' => 'Eventi · appuntamento', 'scadenza' => 'Scadenza · promemoria'] as $key => $label)<option value="{{ $key }}" @selected($value('tipo', 'manuale') === $key)>{{ $label }}</option>@endforeach</x-ui.select></div>
                    <div class="col-sm-6"><label class="form-label">Visibilità</label><div class="form-control-plaintext">{{ ($editing ? $evento->ambito === 'personale' : $contesto === 'personale') ? 'Solo personale' : 'Condivisa con la struttura' }}</div></div>
                    <div class="col-12"><label for="{{ $editorId }}Titolo" class="form-label">Titolo *</label><input id="{{ $editorId }}Titolo" name="titolo" type="text" class="form-control" value="{{ $value('titolo') }}" required maxlength="180" placeholder="Es. Contattare il cliente per l’orario di arrivo"></div>
                    <div class="col-sm-6"><label for="{{ $editing ? $editorId.'Data' : 'modalNuovoEventoData' }}" class="form-label">Giorno / scadenza *</label><x-ui.datepicker :id="$editorId.'Data'" name="data_evento" :value="$failed ? old('data_evento') : ($editing ? $evento->data_evento->toDateString() : $selectedDay->toDateString())" format="Y-m-d" :alt-input="true" alt-format="d/m/Y" min-date="1900-01-01" max-date="2100-12-31" required /></div>
                    <div class="col-sm-6"><label for="{{ $editorId }}Ora" class="form-label">Orario facoltativo</label><input id="{{ $editorId }}Ora" type="time" name="ora_evento" class="form-control" value="{{ substr($value('ora_evento') ?? '', 0, 5) }}"><div class="form-text">Senza orario: tutto il giorno.</div></div>
                    @if(!$editing && $contesto !== 'personale' && $canSelectStructure)
                        <div class="col-12"><label for="{{ $editorId }}Struttura" class="form-label">Struttura *</label><x-ui.select id="{{ $editorId }}Struttura" name="struttura_id" required><option value="">Seleziona una struttura</option>@foreach($struttureDisponibili as $item)<option value="{{ $item->id }}" @selected((string)($failed ? old('struttura_id') : $selectedStructureId) === (string)$item->id)>{{ $item->nome_struttura }}</option>@endforeach</x-ui.select></div>
                    @else
                        <input type="hidden" name="struttura_id" value="{{ $editing ? $evento->struttura_id : $selectedStructureId }}">
                    @endif
                    <div class="col-12"><label for="{{ $editorId }}Descrizione" class="form-label">Descrizione</label><textarea id="{{ $editorId }}Descrizione" name="descrizione" class="form-control" rows="3" maxlength="5000" placeholder="Note e informazioni utili">{{ $value('descrizione') }}</textarea></div>
                    <div class="col-12"><details open><summary class="text-muted">Avviso e stato</summary><div class="row g-3 mt-1">
                        <div class="col-sm-6"><label for="{{ $editorId }}Priorita" class="form-label">Priorità</label><x-ui.select id="{{ $editorId }}Priorita" name="priorita">@foreach(['bassa' => 'Verde · Bassa', 'normale' => 'Verde · Normale', 'alta' => 'Giallo · Attenzione', 'urgente' => 'Rosso · Urgente'] as $key => $label)<option value="{{ $key }}" @selected($value('priorita', 'normale') === $key)>{{ $label }}</option>@endforeach</x-ui.select></div>
                        <div class="col-sm-6"><label for="{{ $editorId }}Stato" class="form-label">Stato dell’annotazione</label><x-ui.select id="{{ $editorId }}Stato" name="stato">@foreach(['da_fare' => 'Da fare', 'vista' => 'Vista', 'completata' => 'Completata', 'chiusa' => 'Chiusa'] as $key => $label)<option value="{{ $key }}" @selected($value('stato', 'da_fare') === $key)>{{ $label }}</option>@endforeach</x-ui.select></div>
                    </div></details></div>
                    <div class="col-12"><div class="form-text">Le comunicazioni sono promemoria interni: il salvataggio non invia messaggi.</div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button><button type="submit" class="btn btn-primary">{{ $editing ? 'Salva modifiche' : 'Salva' }}</button></div>
        </form>
    </div></div>
</div>
