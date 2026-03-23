@extends('layouts.master')
@section('title', 'Rilasciato da')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Configurazioni @endslot
    @slot('title') Rilasciato da @endslot
@endcomponent

<div class="row config-page">
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

                                <form action="{{ route('rilasciato.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div id="modalRilasciatoEdit{{ $item->id }}" class="modal fade" tabindex="-1" aria-labelledby="rilasciatoEditLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rilasciatoEditLabel{{ $item->id }}">Modifica Rilasciato da</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('rilasciato.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
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
            </x-slot>

        </x-crud-table>
    </div>
</div>

<div id="modalRilasciatoCreate" class="modal fade" tabindex="-1" aria-labelledby="rilasciatoCreateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rilasciatoCreateLabel">Nuovo Rilasciato da</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('rilasciato.store') }}">
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
