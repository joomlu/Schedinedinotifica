
@extends('layouts.master')
@section('title', 'Tipo Documento')
@section('content')
<div class="row config-page">
    <div class="col-lg-12">
        <x-crud-table
            title="Tipo Documento"
            subtitle="Catalogo ufficiale Questura (sola lettura)"
            searchPlaceholder="Cerca per descrizione..."
            searchId="tipoDocumentoSearch"
            createText="Nuovo Documento"
            createTarget="#"
            tableId="tipoDocumentoTable"
            :paginator="$tipoDocumenti"
            :createDisabled="true"
            createTooltip="Catalogo Questura: non modificabile"
        >
            <x-slot name="columns">
                <tr>
                    <th>Descrizione</th>
                    <th style="width: 16%;">Stato</th>
                    <th class="text-end" style="width: 18%; min-width: 180px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($tipoDocumenti as $tipo)
                    <tr class="data-row"
                        data-id="{{ $tipo->id }}"
                        data-codice="{{ $tipo->codice }}"
                        data-descrizione="{{ $tipo->descrizione }}">
                        <td class="align-middle">{{ $tipo->descrizione }}</td>
                        <td class="align-middle">
                            <span class="badge bg-warning-subtle text-warning">Questura</span>
                        </td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Catalogo Questura: non modificabile">
                                    <button type="button" class="btn btn-soft-info btn-sm" disabled aria-disabled="true">
                                        <i class="ri-edit-line fs-16 align-middle"></i>
                                    </button>
                                </span>
                                <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Catalogo Questura: non eliminabile">
                                    <button type="button" class="btn btn-soft-danger btn-sm" disabled aria-disabled="true">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot>

        </x-crud-table>
    </div>
</div>
@endsection
