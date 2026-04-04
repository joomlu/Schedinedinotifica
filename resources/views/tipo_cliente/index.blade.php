@extends('layouts.master')
@section('title', 'Tipo Cliente')
@section('content')
<div class="row config-page">
    <div class="col-lg-12">

        <x-crud-table
            title="Tipo Cliente"
            subtitle="Classificazione stato cliente: Ospite, Componente, Richiesta"
            searchPlaceholder="Cerca per ID, codice o descrizione..."
            searchId="tipoClienteSearch"
            createText="Nuovo Tipo Cliente"
            createTarget="#modalTipoClienteCreate"
            tableId="tipoClienteTable"
            :paginator="$tipiClienti"
        >

            <x-slot name="columns">
                <tr>
                    <th style="width: 16%;">Codice</th>
                    <th>Descrizione</th>
                    <th style="width: 14%;">Stato</th>
                    <th class="text-end" style="width: 18%; min-width: 180px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($tipiClienti as $tipoCliente)
                    <tr class="data-row"
                        data-id="{{ $tipoCliente->id }}"
                        data-codice="{{ $tipoCliente->codice }}"
                        data-descrizione="{{ $tipoCliente->descrizione }}">
                        <td class="align-middle">{{ $tipoCliente->codice }}</td>
                        <td class="align-middle">{{ $tipoCliente->descrizione }}</td>
                        <td class="align-middle">
                            @if($tipoCliente->attivo ?? true)
                                <span class="badge bg-success-subtle text-success">Attivo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Disattivo</span>
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalTipoClienteEdit{{ $tipoCliente->id }}">
                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                </button>

                                <form action="{{ route('tipo_cliente.destroy', $tipoCliente->id) }}" method="POST" class="d-inline" data-confirm-label="{{ 'il tipo cliente ' . $tipoCliente->descrizione }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div id="modalTipoClienteEdit{{ $tipoCliente->id }}" class="modal fade" tabindex="-1" aria-labelledby="tipoClienteEditLabel{{ $tipoCliente->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tipoClienteEditLabel{{ $tipoCliente->id }}">Modifica Tipo Cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('tipo_cliente.update', $tipoCliente->id) }}" data-confirm-label="{{ 'il tipo cliente ' . $tipoCliente->descrizione }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="codiceEdit{{ $tipoCliente->id }}" class="form-label">Codice</label>
                                            <input type="text" name="codice" value="{{ $tipoCliente->codice }}" class="form-control" id="codiceEdit{{ $tipoCliente->id }}" maxlength="50" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="descrizioneEdit{{ $tipoCliente->id }}" class="form-label">Descrizione</label>
                                            <input type="text" name="descrizione" value="{{ $tipoCliente->descrizione }}" class="form-control" id="descrizioneEdit{{ $tipoCliente->id }}" maxlength="191" required>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="attivoEdit{{ $tipoCliente->id }}" name="attivo" value="1" {{ ($tipoCliente->attivo ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="attivoEdit{{ $tipoCliente->id }}">Attivo</label>
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

<div id="modalTipoClienteCreate" class="modal fade" tabindex="-1" aria-labelledby="tipoClienteCreateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tipoClienteCreateLabel">Nuovo Tipo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tipo_cliente.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="codiceCreate" class="form-label">Codice</label>
                        <input type="text" name="codice" class="form-control" id="codiceCreate" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="descrizioneCreate" class="form-label">Descrizione</label>
                        <input type="text" name="descrizione" class="form-control" id="descrizioneCreate" maxlength="191" required>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="attivoCreate" name="attivo" value="1" checked>
                        <label class="form-check-label" for="attivoCreate">Attivo</label>
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
