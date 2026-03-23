@extends('layouts.master')
@section('title', 'Clienti')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Tabelle
    @endslot
    @slot('title')
        Clienti
    @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <x-crud-table
            title="Clienti"
            subtitle="Elenco clienti della struttura"
            searchPlaceholder="Cerca per codice, nome, cognome, tipo, stato, nazione, città o gruppo..."
            searchId="clientiSearch"
            createText="Nuovo Cliente"
            createHref="{{ route('newcustomer') }}"
            createTarget="#"
            tableId="clientiTable"
            :paginator="$customers"
        >
            <x-slot name="topbarLeft">
                <form method="GET" action="{{ route('customers') }}" class="d-flex align-items-center gap-2">
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <div style="min-width: 250px;">
                        <x-ui.select
                            name="tipo_cliente"
                            id="clientiTipoFiltro"
                            onchange="this.form.submit()"
                            placeholder="Filtra per tipo cliente"
                            data-min-search="0"
                        >
                            <option value="">Tutti i tipi cliente</option>
                            @foreach($tipiClienteDisponibili as $tipoOption)
                                <option value="{{ $tipoOption }}" @selected($tipoClienteSelezionato === $tipoOption)>{{ $tipoOption }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div style="min-width: 220px;">
                        <x-ui.select
                            name="stato"
                            id="clientiStatoFiltro"
                            onchange="this.form.submit()"
                            placeholder="Filtra per stato"
                            data-min-search="0"
                        >
                            <option value="">Tutti gli stati</option>
                            <option value="completo" @selected(($statoSelezionato ?? '') === 'completo')>Completati</option>
                            <option value="bozza" @selected(($statoSelezionato ?? '') === 'bozza')>Bozze / incompleti</option>
                        </x-ui.select>
                    </div>
                    @if($tipoClienteSelezionato !== '' || ($statoSelezionato ?? '') !== '')
                        <a href="{{ route('customers', request()->except('tipo_cliente', 'stato', 'page')) }}" class="btn btn-light btn-sm">
                            <i class="ri-close-line align-middle"></i>
                        </a>
                    @endif
                </form>
            </x-slot>

            <x-slot name="columns">
                <tr>
                    <th style="width: 10%;">Codice</th>
                    <th style="width: 14%;">Nome</th>
                    <th style="width: 14%;">Cognome</th>
                    <th style="width: 15%;">Tipo Cliente</th>
                    <th style="width: 12%;">Stato</th>
                    <th style="width: 14%;">Nazione</th>
                    <th style="width: 14%;">Città</th>
                    <th style="width: 13%;">Gruppo</th>
                    <th class="text-end" style="width: 13%; min-width: 180px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($customers as $customer)
                    @php
                        $tipoCliente = trim((string) ($customer->type_housed ?? ''));
                        $tipoNorm = mb_strtolower($tipoCliente);
                        $badgeClass = 'bg-secondary-subtle text-secondary';
                        $isBozzaIncompleta = (bool) ($customer->is_bozza_incompleta ?? false);

                        if ($tipoNorm === 'ospite') {
                            $badgeClass = 'bg-primary-subtle text-primary';
                        } elseif ($tipoNorm === 'componente') {
                            $badgeClass = 'bg-info-subtle text-info';
                        } elseif ($tipoNorm === 'richiesta') {
                            $badgeClass = 'bg-warning-subtle text-warning';
                        }
                    @endphp
                    <tr
                        class="data-row"
                        data-id="{{ $customer->id }}"
                        data-codice="{{ $customer->numero_cliente }}"
                        data-nome="{{ $customer->name }}"
                        data-cognome="{{ $customer->surname }}"
                        data-tipo="{{ $tipoCliente }}"
                        data-nazione="{{ $customer->country }}"
                        data-citta="{{ $customer->city }}"
                        data-gruppo="{{ $customer->group }}"
                        data-email="{{ $customer->email }}"
                        data-telefono="{{ $customer->phone }}"
                    >
                        <td class="align-middle">
                            @if($customer->numero_cliente)
                                <span class="badge bg-dark-subtle text-dark">{{ $customer->numero_cliente }}</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Da generare</span>
                            @endif
                        </td>
                        <td class="align-middle">{{ $customer->name }}</td>
                        <td class="align-middle">{{ $customer->surname }}</td>
                        <td class="align-middle">
                            @if($tipoCliente !== '')
                                <span class="badge {{ $badgeClass }}">{{ $tipoCliente }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($isBozzaIncompleta)
                                <span class="badge bg-warning-subtle text-warning">Bozza / incompleto</span>
                            @else
                                <span class="badge bg-success-subtle text-success">Completo</span>
                            @endif
                        </td>
                        <td class="align-middle">{{ $customer->display_country ?: '-' }}</td>
                        <td class="align-middle">{{ $customer->display_city ?: '-' }}</td>
                        <td class="align-middle">{{ $customer->group ?: '-' }}</td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('customer.storico', $customer->id) }}" class="btn btn-soft-dark btn-sm" title="Storico cliente">
                                    <i class="ri-history-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ route('customer.print', $customer->id) }}" class="btn btn-soft-secondary btn-sm js-customer-print" title="Stampa scheda cliente" data-popup-title="Scheda cliente">
                                    <i class="ri-printer-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ route('newschedina', ['customer_id' => $customer->id]) }}" class="btn btn-soft-primary btn-sm" title="Apri schedina">
                                    <i class="ri-file-add-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-soft-info btn-sm">
                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                </a>
                                <form action="{{ route('customer.destroy', $customer->id) }}" method="POST" class="d-inline" data-confirm="off">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm js-customer-delete" data-customer-name="{{ trim($customer->name . ' ' . $customer->surname) }}">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-customer-delete').forEach((button) => {
            button.addEventListener('click', async function (event) {
                event.preventDefault();
                const form = button.closest('form');
                if (!form || !window.Swal) return;

                const customerName = (button.dataset.customerName || '').trim();
                const label = customerName || 'questo cliente';

                const first = await window.Swal.fire({
                    title: 'Conferma eliminazione',
                    text: `Stai per eliminare ${label}. Vuoi continuare?`,
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
                    text: 'Premi OK per procedere con l\'eliminazione.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                });

                if (!second.isConfirmed) return;
                form.submit();
            });
        });

        document.querySelectorAll('.js-customer-print').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const href = link.getAttribute('href');
                if (!href) return;

                const width = 980;
                const height = 860;
                const left = Math.max(0, Math.round((window.screen.width - width) / 2));
                const top = Math.max(0, Math.round((window.screen.height - height) / 2));
                const popup = window.open(
                    href,
                    'customer-print-popup',
                    `popup=yes,width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
                );

                if (popup) {
                    popup.focus();
                }
            });
        });
    });
</script>
@endpush
