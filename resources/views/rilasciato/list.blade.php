@extends('layouts.master')
@section('title', 'Rilasciato da')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Configurazioni @endslot
    @slot('title') Rilasciato da @endslot
@endcomponent

<div class="row config-page catalogo-indirizzi">
    <div class="col-lg-12">
        <x-crud-table
            title="Rilasciato da"
            subtitle="Gestione autorità di rilascio"
            searchPlaceholder="Cerca per nome..."
            searchId="rilasciatoSearch"
            createText="Nuovo Rilasciato da"
            createTarget="#modalRilasciatoCreate"
            tableId="rilasciatoTable"
            :paginator="$rilasciati"
        >
            <x-slot name="columns">
                <tr>
                    <th>Nome</th>
                    <th class="text-end" style="width: 18%; min-width: 160px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($rilasciati as $item)
                    <tr class="data-row" data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                        <td class="align-middle">{{ $item->name }}</td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalRilasciatoEdit{{ $item->id }}">
                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                </button>

                                <form action="{{ route('rilasciato.destroy', $item->id, false) }}" method="POST" class="d-inline" data-confirm-label="{{ 'il rilascio documento ' . $item->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                @endforeach
            </x-slot>

        </x-crud-table>

        @foreach($rilasciati as $item)
                    <div id="modalRilasciatoEdit{{ $item->id }}" class="modal fade" tabindex="-1" aria-labelledby="rilasciatoEditLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rilasciatoEditLabel{{ $item->id }}">Modifica Rilasciato da</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('rilasciato.update', $item->id, false) }}" data-confirm-label="{{ 'il rilascio documento ' . $item->name }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_catalogo_modal" value="modalRilasciatoEdit{{ $item->id }}">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="rilasciatoNameEdit{{ $item->id }}" class="form-label">Nome</label>
                                            <input type="text" name="name" value="{{ $item->name }}" class="form-control" id="rilasciatoNameEdit{{ $item->id }}" maxlength="191" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Salva</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
        @endforeach
    </div>
</div>

<div id="modalRilasciatoCreate" class="modal fade" tabindex="-1" aria-labelledby="rilasciatoCreateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rilasciatoCreateLabel">Nuovo Rilasciato da</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('rilasciato.store', [], false) }}">
                <input type="hidden" name="_catalogo_modal" value="modalRilasciatoCreate">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rilasciatoNameCreate" class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" id="rilasciatoNameCreate" maxlength="191" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary btn-sm">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any() && old('_catalogo_modal'))
    const target = document.getElementById(@json(old('_catalogo_modal')));
    if (target) {
        const values = {{ \Illuminate\Support\Js::from(old()) }};
        target.querySelectorAll('input[name]').forEach(input => {
            if (!Object.hasOwn(values, input.name) || input.name.startsWith('_')) return;
            if (input.type === 'checkbox') input.checked = String(values[input.name]) === '1';
            else input.value = typeof values[input.name] === 'string' ? values[input.name] : '';
        });
        bootstrap.Modal.getOrCreateInstance(target).show();
    }
    @endif
});
document.addEventListener('hidden.bs.modal', event => {
    if (event.target.id.startsWith('modalRilasciato')) {
        event.target.querySelectorAll('form').forEach(form => form.reset());
    }
});
</script>
@endsection

@push('css')
<style>
@media (max-width: 575.98px) {
    .catalogo-indirizzi .card-header .flex-grow-1 { min-width: 0; max-width: 100%; }
}
</style>
@endpush
