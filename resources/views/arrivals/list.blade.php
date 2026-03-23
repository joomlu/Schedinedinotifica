@extends('layouts.master')
@section('title', 'Schedine Arrivi')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Schedine Arrivi
        @endslot
        @slot('title')
            Schedine Arrivi
        @endslot
    @endcomponent

    @php
        $stats = $arriviStats ?? [];
        $inArrivoOggi = $stats['in_arrivo_oggi'] ?? 0;
        $totPersone = $stats['tot_persone'] ?? 0;
        $totCamere = $stats['tot_camere'] ?? 0;
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

    <div class="row g-3 mb-3">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3"><div class="text-muted small">Schedine arrivi aperte</div><div class="fs-4">{{ $arrivals->total() }}</div></div></div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3"><div class="text-muted small">In arrivo oggi</div><div class="fs-4">{{ $inArrivoOggi }}</div></div></div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3"><div class="text-muted small">Persone</div><div class="fs-4">{{ $totPersone }}</div></div></div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3"><div class="text-muted small">Camere</div><div class="fs-4">{{ $totCamere }}</div></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <x-crud-table
                title="Schedine Arrivi"
                subtitle="Elenco operativo delle schedine arrivi ancora aperte per la struttura corrente"
                searchPlaceholder="Cerca per codice, nome, cognome, arrivo, partenza, nazione, città o camera..."
                searchId="arriviSearch"
                createText="Nuovo arrivo"
                :createHref="route('newarrival')"
                tableId="arriviTable"
                :paginator="$arrivals"
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
                        <th class="text-end" style="width: 13%; min-width: 190px;">Azioni</th>
                    </tr>
                </x-slot>
                <x-slot name="rows">
                    @foreach($arrivals as $arrival)
                        <tr
                            class="data-row"
                            data-codice="{{ $arrival->scheda }}"
                            data-nome="{{ $arrival->name }}"
                            data-cognome="{{ $arrival->surname }}"
                            data-arrivo="{{ $formatShortDate($arrival->arrive) }}"
                            data-partenza="{{ $formatShortDate($arrival->departure) }}"
                            data-nazione="{{ $arrival->oa_country }}"
                            data-citta="{{ $arrival->oa_city }}"
                            data-persone="{{ $arrival->cant_people }}"
                            data-camera="{{ $arrival->room }}"
                        >
                            <td class="align-middle text-nowrap">
                                @if($arrival->scheda)
                                    <span class="badge bg-dark-subtle text-dark">{{ $arrival->scheda }}</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Arrivo aperto</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $arrival->name ?: '-' }}</td>
                            <td class="align-middle">{{ $arrival->surname ?: '-' }}</td>
                            <td class="align-middle text-nowrap">{{ $formatShortDate($arrival->arrive) }}</td>
                            <td class="align-middle text-nowrap">{{ $formatShortDate($arrival->departure) }}</td>
                            <td class="align-middle">{{ $arrival->oa_country ?: '-' }}</td>
                            <td class="align-middle">{{ $arrival->oa_city ?: '-' }}</td>
                            <td class="align-middle text-center">{{ $arrival->cant_people ?: '-' }}</td>
                            <td class="align-middle text-center">{{ $arrival->room ?: '-' }}</td>
                            <td class="text-end align-middle text-nowrap">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('schedina.edit', ['id' => $arrival->id]) }}" class="btn btn-soft-info btn-sm" title="Apri arrivo nella scheda">
                                        <i class="ri-edit-line fs-16 align-middle"></i>
                                    </a>
                                    <form action="{{ route('a_schedina', $arrival->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-soft-success btn-sm" type="submit" title="Converti in schedina">
                                            <i class="ri-check-line fs-16 align-middle"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('arrivals.destroy', $arrival->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-soft-danger btn-sm" type="submit" title="Elimina arrivo">
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
