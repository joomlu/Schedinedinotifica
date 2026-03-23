
@extends('layouts.master')
@section('title', 'Tipo Via')
@section('content')
<div class="row config-page">
    <div class="col-lg-12">

        <x-crud-table
            title="Tipo Via"
            subtitle="Gestione tipi di indirizzo"
            searchPlaceholder="Cerca per ID, abbreviazione o descrizione..."
            searchId="tipoviaSearch"
            createText="Nuovo Tipo Via"
            createTarget="#modalTipoviaCreate"
            tableId="tipoviaTable"
            :paginator="$tipovia"
        >

            <x-slot name="columns">
                <tr>
                    <th style="width: 20%;">Abbr</th>
                    <th>Descrizione</th>
                    <th style="width: 14%;">Stato</th>
                    <th class="text-end" style="width: 22%;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($tipovia as $via)
                    <tr class="data-row"
                        data-id="{{ $via->id }}"
                        data-abbr="{{ $via->abbr }}"
                        data-nome="{{ $via->nome }}"
                        data-descrizione="{{ $via->descrizione }}"
                        data-attivo="{{ $via->attivo ? '1' : '0' }}">
                        <td class="align-middle">{{ $via->abbr }}</td>
                        <td class="align-middle">{{ $via->descrizione }}</td>
                        <td class="align-middle">
                            @if($via->attivo)
                                <span class="badge bg-success-subtle text-success">Attivo</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Disattivo</span>
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalTipoviaEdit{{ $via->id }}">
                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                </button>

                                <form method="POST" action="{{ route('tipovia.update', $via->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="abbr" value="{{ $via->abbr }}">
                                    <input type="hidden" name="descrizione" value="{{ $via->descrizione }}">
                                    <input type="hidden" name="attivo" value="{{ $via->attivo ? 0 : 1 }}">
                                    <button type="submit" class="btn btn-soft-warning btn-sm" title="{{ $via->attivo ? 'Disattiva' : 'Riattiva' }}">
                                        <i class="{{ $via->attivo ? 'ri-pause-line' : 'ri-play-line' }} fs-16 align-middle"></i>
                                    </button>
                                </form>

                                <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#tipoviaDeleteModal{{ $via->id }}">
                                    <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Modifica -->
                    <div id="modalTipoviaEdit{{ $via->id }}" class="modal fade" tabindex="-1" aria-labelledby="tipoviaEditLabel{{ $via->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tipoviaEditLabel{{ $via->id }}">Modifica Tipo Via</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('tipovia.update', $via->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="abbrInputEdit{{ $via->id }}" class="form-label">Abbr</label>
                                            <input type="text" name="abbr" value="{{ $via->abbr }}" class="form-control" id="abbrInputEdit{{ $via->id }}" maxlength="20" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="descrizioneInputEdit{{ $via->id }}" class="form-label">Descrizione</label>
                                            <input type="text" name="descrizione" value="{{ $via->descrizione }}" class="form-control" id="descrizioneInputEdit{{ $via->id }}" maxlength="191">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="attivo" id="attivoEdit{{ $via->id }}" value="1" {{ $via->attivo ? 'checked' : '' }}>
                                            <label class="form-check-label" for="attivoEdit{{ $via->id }}">Attivo</label>
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

                    <!-- Modal Delete -->
                    <div id="tipoviaDeleteModal{{ $via->id }}" class="modal fade" tabindex="-1" aria-labelledby="tipoviaDeleteLabel{{ $via->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tipoviaDeleteLabel{{ $via->id }}">Conferma eliminazione</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-0">Eliminare definitivamente "{{ $via->descrizione }}"?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Annulla</button>
                                    <form action="{{ route('tipovia.destroy', $via->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-slot>

        </x-crud-table>

    </div>
</div>

<!-- Modal Nuovo Tipo Via -->
<div id="modalTipoviaCreate" class="modal fade" tabindex="-1" aria-labelledby="tipoviaCreateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tipoviaCreateLabel">Nuovo Tipo Via</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tipovia.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="abbrInput" class="form-label">Abbr</label>
                        <input type="text" name="abbr" class="form-control" id="abbrInput" maxlength="20" required>
                    </div>
                    <div class="mb-3">
                        <label for="descrizioneInput" class="form-label">Descrizione</label>
                        <input type="text" name="descrizione" class="form-control" id="descrizioneInput" maxlength="191">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="attivo" id="attivoCreate" value="1" checked>
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
