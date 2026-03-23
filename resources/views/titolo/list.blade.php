@extends('layouts.master')
@section('title', 'Titolo')
@section('content')
<div class="row config-page">
    <div class="col-lg-12">

        <x-crud-table
            title="Titoli"
            subtitle="Gestione titoli (Sig., Dott., Ing., ...)"
            searchPlaceholder="Cerca per ID, nome, descrizione..."
            searchId="titoliSearch"
            createText="Nuovo Titolo"
            createTarget="#myModal"
            tableId="titoliTable"
            :paginator="$titoli"
        >

            <x-slot name="columns">
                <tr>
                    <th style="width:20%">Nome</th>
                    <th>Descrizione</th>
                    <th style="width:12%">Stato</th>
                    <th class="text-end" style="width:20%; min-width:200px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($titoli as $titolo)
                    <tr data-id="{{ $titolo->id }}" data-nome="{{ $titolo->nome }}" data-descrizione="{{ $titolo->descrizione }}">
                        <td class="align-middle">{{ $titolo->nome }}</td>
                        <td class="align-middle">{{ $titolo->descrizione }}</td>
                        <td class="align-middle">
                            @if($titolo->attivo ?? true)
                                <span class="badge bg-success-subtle text-success">Attivo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Disattivo</span>
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#ModalEdit{{ $titolo->id }}">
                                <i class="ri-pencil-line align-middle me-1"></i> Modifica
                            </button>
                            <form action="{{ route('titolo.destroy', $titolo->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                    <i class="ri-delete-bin-line align-middle me-1"></i> Elimina
                                </button>
                            </form>
                        </td>
                    </tr>

                    <div id="ModalEdit{{ $titolo->id }}" class="modal fade" tabindex="-1" aria-labelledby="myModalEditLabel{{ $titolo->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myModalEditLabel{{ $titolo->id }}">Modifica Titolo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                </div>
                                <form method="POST" action="{{ route('titolo.update', $titolo->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="nomeInputEdit{{ $titolo->id }}" class="form-label">Nome</label>
                                            <input type="text" name="nome" value="{{ $titolo->nome }}" class="form-control" id="nomeInputEdit{{ $titolo->id }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="descrizioneInputEdit{{ $titolo->id }}" class="form-label">Descrizione</label>
                                            <input type="text" name="descrizione" value="{{ $titolo->descrizione }}" class="form-control" id="descrizioneInputEdit{{ $titolo->id }}">
                                        </div>
                                        <div class="form-check form-switch mb-1">
                                            <input class="form-check-input" type="checkbox" role="switch" id="attivoInputEdit{{ $titolo->id }}" name="attivo" value="1" {{ ($titolo->attivo ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="attivoInputEdit{{ $titolo->id }}">Attivo</label>
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

<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Nuovo Titolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="POST" action="{{ route('titolo.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nomeInput" class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" id="nomeInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="descrizioneInput" class="form-label">Descrizione</label>
                        <input type="text" name="descrizione" class="form-control" id="descrizioneInput">
                    </div>
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" role="switch" id="attivoInput" name="attivo" value="1" checked>
                        <label class="form-check-label" for="attivoInput">Attivo</label>
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
