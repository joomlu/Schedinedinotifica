@extends('layouts.master')
@section('title', 'Cestino')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Sistema
    @endslot
    @slot('title')
        Cestino
    @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <x-crud-table
            title="Cestino"
            subtitle="Archivio centrale di tutti gli elementi eliminati dal sistema, organizzato per sezione"
            searchPlaceholder="Cerca per tipo, codice, titolo o origine..."
            searchId="cestinoSearch"
            createText=""
            createTarget="#"
            tableId="cestinoTable"
            :paginator="$items"
        >
            <x-slot name="topbarLeft">
                <form method="GET" action="{{ route('cestino.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <div style="min-width: 240px;">
                        <x-ui.select name="tipo" id="cestinoTipoFilter">
                            <option value="">Tutte le sezioni</option>
                            @foreach($tipi as $tipo)
                                <option value="{{ $tipo }}" @selected($tipoSelezionato === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @if($tipoSelezionato !== '')
                        <a href="{{ route('cestino.index', request()->filled('q') ? ['q' => request('q')] : []) }}" class="btn btn-light">
                            Reset sezione
                        </a>
                    @endif
                </form>
            </x-slot>

            <x-slot name="columns">
                <tr>
                    <th style="width: 12%;">Eliminato il</th>
                    <th style="width: 14%;">Sezione</th>
                    <th style="width: 14%;">Codice</th>
                    <th>Titolo</th>
                    <th style="width: 14%;">Origine</th>
                    <th style="width: 10%;">Circuito</th>
                    <th class="text-end" style="width: 16%; min-width: 180px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($items as $item)
                    <tr class="data-row">
                        <td class="align-middle text-nowrap">{{ optional($item->deleted_at)->format('d/m/Y H:i') }}</td>
                        <td class="align-middle">{{ $item->sezione }}</td>
                        <td class="align-middle">{{ $item->code ?: '-' }}</td>
                        <td class="align-middle">{{ $item->title ?: '-' }}</td>
                        <td class="align-middle">{{ $item->source ?: '-' }}</td>
                        <td class="align-middle">{{ $item->circuito ?: '-' }}</td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <button
                                    type="button"
                                    class="btn btn-soft-info"
                                    title="Dettaglio"
                                    data-bs-toggle="modal"
                                    data-bs-target="#cestinoDettaglio{{ $item->id }}"
                                >
                                    <i class="ri-eye-line fs-16 align-middle"></i>
                                </button>
                                <form action="{{ route('cestino.restore', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-soft-success" title="Ripristina">
                                        <i class="ri-arrow-go-back-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('cestino.destroy', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger" title="Elimina definitivamente">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div id="cestinoDettaglio{{ $item->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Dettaglio cestino</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Sezione</div>
                                                <div>{{ $item->sezione }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Tipo record</div>
                                                <div>{{ $item->entity_type }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Codice</div>
                                                <div>{{ $item->code ?: '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Titolo</div>
                                                <div>{{ $item->title ?: '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Origine</div>
                                                <div>{{ $item->source ?: '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Eliminato il</div>
                                                <div>{{ optional($item->deleted_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border rounded p-3">
                                        <div class="small text-muted mb-2">Snapshot archivio</div>
                                        <pre class="mb-0 small text-wrap">{{ json_encode($item->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                    <form action="{{ route('cestino.restore', ['id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">Ripristina</button>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoFilter = document.getElementById('cestinoTipoFilter');
        if (!tipoFilter || !tipoFilter.form) {
            return;
        }

        tipoFilter.addEventListener('change', function () {
            tipoFilter.form.submit();
        });
    });
</script>
@endpush
