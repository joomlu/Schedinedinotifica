@extends('layouts.master')
@section('title', 'Schedine')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Tabelle
    @endslot
    @slot('title')
        Schedine
    @endslot
@endcomponent

@php
    $pageTitle = $pageTitle ?? 'Schedine';
    $pageSubtitle = $pageSubtitle ?? 'Elenco schedine della struttura';
    $createText = $createText ?? 'Nuova Schedina';
    $formatShortDate = function ($value) {
        if (!$value) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('j/n/y');
        } catch (\Throwable $e) {
            return $value;
        }
    };
@endphp

<div class="row">
    <div class="col-lg-12">
        <x-crud-table
            :title="$pageTitle"
            :subtitle="$pageSubtitle"
            searchPlaceholder="Cerca per codice, nome, cognome, arrivo, partenza, nazione, città o camera..."
            searchId="schedineSearch"
            :createText="$createText"
            createHref="{{ route('newschedina') }}"
            createTarget="#"
            tableId="schedineTable"
            :paginator="$schedinas"
        >
            <x-slot name="columns">
                <tr>
                    <th style="width: 10%;">Codice</th>
                    <th style="width: 13%;">Nome</th>
                    <th style="width: 13%;">Cogn.</th>
                    <th style="width: 8%;">Arr.</th>
                    <th style="width: 8%;">Part.</th>
                    <th style="width: 12%;">Naz.</th>
                    <th style="width: 11%;">Città</th>
                    <th style="width: 6%;">Per.</th>
                    <th style="width: 6%;">Cam.</th>
                    <th style="width: 13%;">Tassa</th>
                    <th class="text-end" style="width: 14%; min-width: 190px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($schedinas as $schedina)
                    <tr
                        class="data-row"
                        data-id="{{ $schedina->id }}"
                        data-codice="{{ $schedina->scheda }}"
                        data-nome="{{ $schedina->name }}"
                        data-cognome="{{ $schedina->surname }}"
                        data-arrivo="{{ $schedina->arrive }}"
                        data-partenza="{{ $schedina->departure }}"
                        data-nazione="{{ $schedina->oa_country }}"
                        data-citta="{{ $schedina->oa_city }}"
                        data-persone="{{ $schedina->cant_people }}"
                        data-camera="{{ $schedina->room }}"
                        data-tassa="{{ number_format((float) ($schedina->tassa_totale ?? 0), 2, ',', '.') }}"
                    >
                        <td class="align-middle">
                            @if($schedina->scheda)
                                <span class="badge bg-dark-subtle text-dark">{{ $schedina->scheda }}</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Da generare</span>
                            @endif
                        </td>
                        <td class="align-middle">{{ $schedina->name ?: '-' }}</td>
                        <td class="align-middle">{{ $schedina->surname ?: '-' }}</td>
                        <td class="align-middle text-nowrap">{{ $formatShortDate($schedina->arrive) }}</td>
                        <td class="align-middle text-nowrap">{{ $formatShortDate($schedina->departure) }}</td>
                        <td class="align-middle">{{ $schedina->oa_country ?: '-' }}</td>
                        <td class="align-middle">{{ $schedina->oa_city ?: '-' }}</td>
                        <td class="align-middle">{{ $schedina->cant_people ?: '-' }}</td>
                        <td class="align-middle">{{ $schedina->room ?: '-' }}</td>
                        <td class="align-middle">
                            @if($schedina->tassa_configurata)
                                <span class="fw-semibold">{{ number_format((float) ($schedina->tassa_totale ?? 0), 2, ',', '.') }} €</span>
                                @if(!empty($schedina->tassa_warning))
                                    <div class="small text-muted mt-1">{{ $schedina->tassa_warning }}</div>
                                @endif
                            @else
                                <span class="text-muted">Non configurata</span>
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('schedina.copy', ['id' => $schedina->id]) }}" class="btn btn-soft-primary btn-sm" title="Copia schedina">
                                    <i class="ri-file-copy-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ route('schedina.tassa.print', ['id' => $schedina->id]) }}" class="btn btn-soft-secondary btn-sm" title="Stampa tassa" target="_blank">
                                    <i class="ri-printer-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ route('schedina.edit', ['id' => $schedina->id]) }}" class="btn btn-soft-info btn-sm" title="Modifica">
                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                </a>
                                <form action="{{ route('schedina.destroy', ['id' => $schedina->id]) }}" method="POST" class="d-inline js-schedina-delete-form" data-confirm="off">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-soft-danger btn-sm js-schedina-delete-btn" title="Elimina" data-confirm-ignore>
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot>
        </x-crud-table>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-schedina-delete-form').forEach(function (form) {
            form.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        document.querySelectorAll('.js-schedina-delete-btn').forEach(function (button) {
            button.addEventListener('click', async function (event) {
                event.preventDefault();
                event.stopPropagation();

                const form = button.closest('form');
                if (!form) return;

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    const first = await window.Swal.fire({
                        title: 'Conferma eliminazione',
                        text: 'Stai per eliminare una schedina. Vuoi continuare?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sì, elimina',
                        cancelButtonText: 'Annulla',
                        reverseButtons: true,
                        focusCancel: true,
                    });

                    if (!first.isConfirmed) return;

                    const second = await window.Swal.fire({
                        title: 'Eliminazione confermata',
                        text: 'Premi OK per procedere con l eliminazione.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                    });

                    if (!second.isConfirmed) return;
                } else if (!window.confirm('Stai per eliminare una schedina. Vuoi continuare?')) {
                    return;
                }

                form.submit();
            });
        });
    });
</script>
@endsection
