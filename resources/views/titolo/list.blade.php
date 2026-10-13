@extends('layouts.master')
@section('title', 'Titoli')
@section('content')
@php
    $failed = is_string(old('_titolo_form')) ? old('_titolo_form') : null;
    $oldValue = static fn ($field, $fallback = '') => is_scalar(old($field)) ? old($field) : $fallback;
@endphp
<div class="row config-page"><div class="col-lg-12">
    <x-crud-table title="Titoli" subtitle="Gestione titoli (Sig., Dott., Ing., ...) — struttura corrente"
        searchPlaceholder="Cerca per ID, nome, descrizione..." searchId="titoliSearch" createText="Nuovo Titolo"
        createTarget="#modalTitoloCreate" tableId="titoliTable" :paginator="$titoli">
        <x-slot name="topbarRight">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalTitoloBase">Recupera base originale</button>
        </x-slot>
        <x-slot name="columns"><tr><th>Nome</th><th>Descrizione</th><th>Stato</th><th class="text-end">Azioni</th></tr></x-slot>
        <x-slot name="rows">
            @foreach($titoli as $titolo)
                <tr data-id="{{ $titolo->id }}" data-nome="{{ $titolo->nome }}" data-descrizione="{{ $titolo->descrizione }}">
                    <td>{{ $titolo->nome }}</td><td>{{ $titolo->descrizione }}</td>
                    <td><span class="badge {{ $titolo->attivo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $titolo->attivo ? 'Attivo' : 'Disattivo' }}</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalTitoloEdit{{ $titolo->id }}">Modifica</button>
                        <form action="{{ route('titolo.destroy', $titolo->id) }}" method="POST" class="d-inline" data-confirm-label="{{ 'il titolo ' . $titolo->nome }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-soft-danger btn-sm">Elimina</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-slot>
    </x-crud-table>
</div></div>
@foreach($titoli as $titolo)
    @include('titolo.modal', ['formKey' => 'edit-'.$titolo->id, 'modalId' => 'modalTitoloEdit'.$titolo->id, 'record' => $titolo])
@endforeach
@include('titolo.modal', ['formKey' => 'create', 'modalId' => 'modalTitoloCreate', 'record' => null])
<div id="modalTitoloBase" class="modal fade" tabindex="-1" aria-labelledby="modalTitoloBaseLabel" aria-hidden="true" data-titolo-modal>
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="modalTitoloBaseLabel">Recupera base originale</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button></div>
        <form method="POST" action="{{ route('titolo.recover') }}" data-confirm-title="Conferma recupero della base" data-confirm-text="Aggiungere i titoli mancanti della base originale alla struttura corrente? Personalizzazioni, stato e relazioni saranno conservati." data-confirm-button="Sì, recupera">
            @csrf <input type="hidden" name="conferma" value="1">
            <div class="modal-body">
                <p>La base originale contiene 80 titoli. Il recupero aggiunge soltanto quelli mancanti nella struttura corrente.</p>
                <p>I titoli rinominati, le descrizioni personalizzate, gli stati e le relazioni vengono conservati. I nomi già occupati sono segnalati senza sovrascrittura. Nessun titolo personale viene eliminato.</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button><button type="submit" class="btn btn-primary">Recupera titoli mancanti</button></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const Modal = window.bootstrap.Modal;
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-bs-toggle="modal"], [data-bs-dismiss="modal"]');
        if (!trigger) return;
        const selector = trigger.getAttribute('data-bs-target');
        const modal = selector && selector.startsWith('#modalTitolo') ? document.getElementById(selector.slice(1)) : trigger.closest('[data-titolo-modal]');
        if (!modal || !modal.hasAttribute('data-titolo-modal')) return;
        event.preventDefault(); event.stopImmediatePropagation();
        const instance = Modal.getOrCreateInstance(modal);
        trigger.hasAttribute('data-bs-dismiss') ? instance.hide() : instance.show();
    }, true);
    document.querySelectorAll('[data-titolo-modal]').forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            modal.querySelector('form').reset();
            if (modal.id === 'modalTitoloBase') return;
            modal.querySelector('[name="nome"]').value = modal.dataset.nome || '';
            modal.querySelector('[name="descrizione"]').value = modal.dataset.descrizione || '';
            modal.querySelector('[type="checkbox"][name="attivo"]').checked = modal.dataset.attivo === '1';
        });
    });
    const failed = @json($failed);
    const id = failed === 'create' ? 'modalTitoloCreate' : (failed && failed.startsWith('edit-') ? 'modalTitoloEdit'+failed.slice(5) : null);
    if (id && document.getElementById(id)) Modal.getOrCreateInstance(document.getElementById(id)).show();
});
</script>
@endpush
