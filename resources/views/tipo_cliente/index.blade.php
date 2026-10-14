@extends('layouts.master')
@section('title', 'Tipo Cliente')
@section('content')
<div class="row config-page">
    <div class="col-lg-12">

        <x-crud-table
            title="Tipo Cliente"
            subtitle="Classificazioni predefinite del sistema, non modificabili."
            searchPlaceholder="Cerca per ID, codice o descrizione..."
            searchId="tipoClienteSearch"
            tableId="tipoClienteTable"
            :paginator="$tipiClienti"
        >

            <x-slot name="columns">
                <tr>
                    <th style="width: 16%;">Codice</th>
                    <th>Descrizione</th>
                    <th style="width: 14%;">Stato</th>
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
                    </tr>
                @endforeach
            </x-slot>

        </x-crud-table>

    </div>
</div>

@endsection
