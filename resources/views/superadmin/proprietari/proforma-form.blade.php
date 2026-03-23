@extends('layouts.master')
@section('title') {{ $proforma ? 'Modifica proforma' : 'Nuova proforma' }} @endsection

@php
    $areaLabel = $areaLabel ?? 'SuperAdmin';
    $ownerRoutePrefix = $ownerRoutePrefix ?? 'superadmin.proprietari';
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') {{ $areaLabel }} @endslot
        @slot('title') {{ $proforma ? 'Modifica proforma' : 'Nuova proforma' }} @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Controlla i dati della proforma.</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $proforma ? route($ownerRoutePrefix . '.proforme.update', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) : route($ownerRoutePrefix . '.proforme.store', $proprietario->id) }}">
                @csrf
                @if($proforma)
                    @method('PUT')
                @endif

                @php
                    $customRows = old('custom_righe', $customRighe ?? []);
                    $modalitaStrutture = old('filtro_strutture_modalita', 'all');
                    $strutturaSingola = old('filtro_struttura_id');
                    $struttureMultiple = collect(old('filtro_strutture_ids', []))->map(fn ($id) => (string) $id)->all();
                @endphp

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small text-uppercase mb-1">Proprietario</div>
                            <div class="fw-semibold">{{ $proprietario->nome }}</div>
                            <div class="small text-muted">{{ $proprietario->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small text-uppercase mb-1">Intestazione</div>
                            <div class="fw-semibold">{{ $proprietario->ragione_sociale ?: $proprietario->nome }}</div>
                            <div class="small text-muted">P.IVA {{ $proprietario->partita_iva ?: 'non impostata' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small text-uppercase mb-1">Base servizi</div>
                            <div class="fw-semibold">{{ $fatturazione['righe']->count() }}</div>
                            <div class="small text-muted">Servizi già assegnati alle strutture del proprietario.</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">Imponibile</div>
                                    <div class="fw-semibold" id="owner-proforma-imponibile">0,00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">Sconto totale</div>
                                    <div class="fw-semibold" id="owner-proforma-sconto">0,00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">IVA totale</div>
                                    <div class="fw-semibold" id="owner-proforma-iva">0,00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">Totale documento</div>
                                    <div class="fw-bold fs-4" id="owner-proforma-totale">0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-light-subtle">
                        <div class="fw-semibold">Dati documento</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Numero proforma</label>
                                <input type="text" name="proforma_numero" class="form-control" value="{{ old('proforma_numero', $proforma?->numero) }}" placeholder="Es. PRO-00001">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data documento</label>
                                <x-calendario
                                    name="proforma_data"
                                    variant="single"
                                    :value="old('proforma_data', optional($proforma?->data_documento)->toDateString() ?: now()->toDateString())"
                                    placeholder="gg/mm/aaaa"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Note documento</label>
                                <textarea name="proforma_note" class="form-control" rows="3" placeholder="Note interne o testo libero della proforma">{{ old('proforma_note', $proforma?->note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-light-subtle">
                        <div class="fw-semibold">Ambito della proforma</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-xl-4">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <input class="form-check-input mt-0 js-owner-scope-mode" type="radio" name="filtro_strutture_modalita" value="all" @checked($modalitaStrutture === 'all')>
                                    <span class="fw-semibold">Tutto il proprietario</span>
                                </label>
                                <div class="small text-muted">Include tutte le strutture e lascia disponibili anche i servizi generali del proprietario.</div>
                            </div>
                            <div class="col-xl-4">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <input class="form-check-input mt-0 js-owner-scope-mode" type="radio" name="filtro_strutture_modalita" value="single" @checked($modalitaStrutture === 'single')>
                                    <span class="fw-semibold">Una sola struttura</span>
                                </label>
                                <select name="filtro_struttura_id" class="form-select js-owner-scope-single">
                                    <option value="">Seleziona struttura</option>
                                    @foreach(($fatturazione['strutture'] ?? collect()) as $struttura)
                                        <option value="{{ $struttura->id }}" @selected((string) $strutturaSingola === (string) $struttura->id)>{{ $struttura->nome_struttura }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-4">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <input class="form-check-input mt-0 js-owner-scope-mode" type="radio" name="filtro_strutture_modalita" value="multiple" @checked($modalitaStrutture === 'multiple')>
                                    <span class="fw-semibold">Più strutture selezionate</span>
                                </label>
                                <div class="border rounded-3 p-2 js-owner-scope-multiple" style="max-height: 170px; overflow: auto;">
                                    @forelse(($fatturazione['strutture'] ?? collect()) as $struttura)
                                        <label class="form-check d-flex align-items-center gap-2 mb-2">
                                            <input class="form-check-input js-owner-scope-multiple-check" type="checkbox" name="filtro_strutture_ids[]" value="{{ $struttura->id }}" @checked(in_array((string) $struttura->id, $struttureMultiple, true))>
                                            <span>{{ $struttura->nome_struttura }}</span>
                                        </label>
                                    @empty
                                        <div class="small text-muted">Nessuna struttura collegata.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    I servizi generali del proprietario restano gestibili a parte. Per i servizi legati alle strutture puoi scegliere una sola struttura, più strutture oppure tutto il portafoglio insieme.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-light-subtle">
                        <div class="fw-semibold">Servizi da fatturare al proprietario</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Includi</th>
                                        <th>Struttura</th>
                                        <th>Servizio</th>
                                        <th style="width: 120px;">Quantità</th>
                                        <th style="width: 160px;">Prezzo unitario</th>
                                        <th style="width: 100px;">Sconto</th>
                                        <th style="width: 90px;">IVA %</th>
                                        <th class="text-end">Totale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fatturazione['righe'] as $riga)
                                        @php
                                            $checked = old("proforma_righe.{$riga['key']}.selected", $riga['selected'] ?? true);
                                        @endphp
                                        <tr
                                            data-owner-proforma-row
                                            data-struttura-id="{{ $riga['struttura_id'] }}"
                                            data-is-generale="{{ !empty($riga['is_generale']) ? '1' : '0' }}"
                                        >
                                            <td>
                                                <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][selected]" value="0">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input js-owner-row-selected" name="proforma_righe[{{ $riga['key'] }}][selected]" value="1" @checked((bool) $checked)>
                                                </div>
                                            </td>
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][struttura_id]" value="{{ $riga['struttura_id'] }}">
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][admin_servizio_id]" value="{{ $riga['admin_servizio_id'] }}">
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][descrizione]" value="{{ $riga['descrizione'] }}">
                                            <td>
                                                <div class="fw-semibold">{{ $riga['struttura_nome'] }}</div>
                                                @if(!empty($riga['is_generale']))
                                                    <div class="small text-muted">Servizio generale del proprietario</div>
                                                @endif
                                            </td>
                                            <td>{{ $riga['descrizione'] }}</td>
                                            <td>
                                                <input type="number" min="1" max="99999" name="proforma_righe[{{ $riga['key'] }}][quantita]" class="form-control form-control-sm" value="{{ old("proforma_righe.{$riga['key']}.quantita", $riga['quantita']) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="proforma_righe[{{ $riga['key'] }}][prezzo_unitario]" class="form-control form-control-sm" value="{{ old("proforma_righe.{$riga['key']}.prezzo_unitario", $riga['prezzo_unitario']) }}">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <select name="proforma_righe[{{ $riga['key'] }}][sconto_tipo]" class="form-select">
                                                        <option value="percentuale" @selected(old("proforma_righe.{$riga['key']}.sconto_tipo", $riga['sconto_tipo'] ?? 'percentuale') === 'percentuale')>%</option>
                                                        <option value="importo" @selected(old("proforma_righe.{$riga['key']}.sconto_tipo", $riga['sconto_tipo'] ?? 'percentuale') === 'importo')>€</option>
                                                    </select>
                                                    <input type="number" step="0.01" min="0" name="proforma_righe[{{ $riga['key'] }}][sconto_valore]" class="form-control" value="{{ old("proforma_righe.{$riga['key']}.sconto_valore", $riga['sconto_valore'] ?? 0) }}">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="100" name="proforma_righe[{{ $riga['key'] }}][aliquota_iva]" class="form-control form-control-sm" value="{{ old("proforma_righe.{$riga['key']}.aliquota_iva", $riga['aliquota_iva'] ?? 22) }}">
                                                <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][note]" value="{{ $riga['note'] }}">
                                            </td>
                                            <td class="text-end fw-semibold js-owner-proforma-row-total">{{ number_format((float) $riga['totale'], 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr data-owner-proforma-custom-row>
                                            <td colspan="8" class="text-muted">Non ci sono ancora servizi assegnati alle strutture del proprietario.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">Servizi personalizzati</div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addCustomRiga">Aggiungi servizio personalizzato</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" id="customRigheTable">
                                <thead>
                                    <tr>
                                        <th style="width: 220px;">Servizio da catalogo</th>
                                        <th>Descrizione</th>
                                        <th style="width: 120px;">Quantità</th>
                                        <th style="width: 160px;">Prezzo unitario</th>
                                        <th style="width: 100px;">Sconto</th>
                                        <th style="width: 90px;">IVA %</th>
                                        <th style="min-width: 280px;">Note</th>
                                        <th style="width: 90px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customRows as $index => $riga)
                                        <tr>
                                            <td>
                                                <select name="custom_righe[{{ $index }}][catalogo_servizio_id]" class="form-select form-select-sm js-catalogo-servizio">
                                                    <option value="">Servizio libero</option>
                                                    @foreach($catalogoServizi as $servizio)
                                                        <option value="{{ $servizio->id }}" data-nome="{{ $servizio->nome }}" data-prezzo="{{ $servizio->importo }}" @selected(($riga['catalogo_servizio_id'] ?? null) == $servizio->id)>{{ $servizio->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="custom_righe[{{ $index }}][descrizione]" class="form-control form-control-sm" value="{{ $riga['descrizione'] ?? '' }}"></td>
                                            <td><input type="number" min="1" max="99999" name="custom_righe[{{ $index }}][quantita]" class="form-control form-control-sm" value="{{ $riga['quantita'] ?? 1 }}"></td>
                                            <td><input type="number" step="0.01" min="0" name="custom_righe[{{ $index }}][prezzo_unitario]" class="form-control form-control-sm" value="{{ $riga['prezzo_unitario'] ?? '' }}"></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <select name="custom_righe[{{ $index }}][sconto_tipo]" class="form-select">
                                                        <option value="percentuale" @selected(($riga['sconto_tipo'] ?? 'percentuale') === 'percentuale')>%</option>
                                                        <option value="importo" @selected(($riga['sconto_tipo'] ?? 'percentuale') === 'importo')>€</option>
                                                    </select>
                                                    <input type="number" step="0.01" min="0" name="custom_righe[{{ $index }}][sconto_valore]" class="form-control" value="{{ $riga['sconto_valore'] ?? 0 }}">
                                                </div>
                                            </td>
                                            <td><input type="number" step="0.01" min="0" max="100" name="custom_righe[{{ $index }}][aliquota_iva]" class="form-control form-control-sm" value="{{ $riga['aliquota_iva'] ?? 22 }}"></td>
                                            <td><input type="text" name="custom_righe[{{ $index }}][note]" class="form-control form-control-sm" value="{{ $riga['note'] ?? '' }}"></td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger js-remove-custom">Rimuovi</button></td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ $proforma ? route($ownerRoutePrefix . '.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) : route($ownerRoutePrefix . '.edit', ['id' => $proprietario->id, 'tab' => 'fatturazione']) }}" class="btn btn-outline-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">{{ $proforma ? 'Aggiorna proforma' : 'Salva proforma' }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var addButton = document.getElementById('addCustomRiga');
    var tableBody = document.querySelector('#customRigheTable tbody');
    var index = tableBody ? tableBody.querySelectorAll('tr').length : 0;

    function buildRow(rowIndex) {
        return `
            <tr data-owner-proforma-custom-row>
                <td>
                    <select name="custom_righe[${rowIndex}][catalogo_servizio_id]" class="form-select form-select-sm js-catalogo-servizio">
                        <option value="">Servizio libero</option>
                        @foreach($catalogoServizi as $servizio)
                            <option value="{{ $servizio->id }}" data-nome="{{ $servizio->nome }}" data-prezzo="{{ $servizio->importo }}">{{ $servizio->nome }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="custom_righe[${rowIndex}][descrizione]" class="form-control form-control-sm"></td>
                <td><input type="number" min="1" max="99999" name="custom_righe[${rowIndex}][quantita]" class="form-control form-control-sm" value="1"></td>
                <td><input type="number" step="0.01" min="0" name="custom_righe[${rowIndex}][prezzo_unitario]" class="form-control form-control-sm"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <select name="custom_righe[${rowIndex}][sconto_tipo]" class="form-select">
                            <option value="percentuale">%</option>
                            <option value="importo">€</option>
                        </select>
                        <input type="number" step="0.01" min="0" name="custom_righe[${rowIndex}][sconto_valore]" class="form-control" value="0">
                    </div>
                </td>
                <td><input type="number" step="0.01" min="0" max="100" name="custom_righe[${rowIndex}][aliquota_iva]" class="form-control form-control-sm" value="22"></td>
                <td><input type="text" name="custom_righe[${rowIndex}][note]" class="form-control form-control-sm"></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger js-remove-custom">Rimuovi</button></td>
            </tr>
        `;
    }

    addButton?.addEventListener('click', function () {
        if (!tableBody) return;
        tableBody.insertAdjacentHTML('beforeend', buildRow(index));
        index += 1;
    });

    document.addEventListener('click', function (event) {
        if (!event.target.classList.contains('js-remove-custom')) return;
        var row = event.target.closest('tr');
        if (row) {
            row.remove();
        }
    });

    document.addEventListener('change', function (event) {
        if (!event.target.classList.contains('js-catalogo-servizio')) return;
        var select = event.target;
        var option = select.options[select.selectedIndex];
        var row = select.closest('tr');
        if (!row || !option) return;
        var descrizione = row.querySelector('input[name*="[descrizione]"]');
        var prezzo = row.querySelector('input[name*="[prezzo_unitario]"]');
        if (select.value) {
            if (descrizione && !descrizione.value) descrizione.value = option.dataset.nome || '';
            if (prezzo && !prezzo.value) prezzo.value = option.dataset.prezzo || '';
        }
    });

    function parseDecimal(value) {
        if (value === null || value === undefined) return 0;
        var normalized = String(value).trim().replace(/\s/g, '');
        if (!normalized) return 0;
        if (normalized.includes(',') && normalized.includes('.')) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        } else if (normalized.includes(',')) {
            normalized = normalized.replace(',', '.');
        }
        var parsed = parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatMoney(value) {
        return new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value || 0);
    }

    function computeRow(row) {
        var qtyInput = row.querySelector('input[name*="[quantita]"]');
        var priceInput = row.querySelector('input[name*="[prezzo_unitario]"]');
        var ivaInput = row.querySelector('input[name*="[aliquota_iva]"]');
        var scontoType = row.querySelector('select[name*="[sconto_tipo]"]');
        var scontoValue = row.querySelector('input[name*="[sconto_valore]"]');
        if (!qtyInput || !priceInput || !ivaInput || !scontoType || !scontoValue) return null;

        var qty = Math.max(1, parseInt(qtyInput.value || '1', 10) || 1);
        var unit = parseDecimal(priceInput.value);
        var iva = parseDecimal(ivaInput.value);
        var sconto = parseDecimal(scontoValue.value);
        var subtotale = qty * unit;
        var discountAmount = scontoType.value === 'importo'
            ? Math.min(subtotale, sconto)
            : (subtotale * sconto / 100);
        var imponibile = Math.max(0, subtotale - discountAmount);
        var totaleIva = imponibile * iva / 100;
        var totale = imponibile + totaleIva;

        var totalCell = row.querySelector('.js-owner-proforma-row-total');
        if (totalCell) {
            totalCell.textContent = formatMoney(totale);
        }

        return {
            imponibile: imponibile,
            sconto: discountAmount,
            iva: totaleIva,
            totale: totale
        };
    }

    function recalcOwnerProforma() {
        var totals = {
            imponibile: 0,
            sconto: 0,
            iva: 0,
            totale: 0
        };

        document.querySelectorAll('[data-owner-proforma-row], [data-owner-proforma-custom-row]').forEach(function (row) {
            if (row.matches('[data-owner-proforma-row]')) {
                var selected = row.querySelector('.js-owner-row-selected');
                if (selected && !selected.checked) return;
            }
            var result = computeRow(row);
            if (!result) return;
            totals.imponibile += result.imponibile;
            totals.sconto += result.sconto;
            totals.iva += result.iva;
            totals.totale += result.totale;
        });

        var fields = {
            'owner-proforma-imponibile': totals.imponibile,
            'owner-proforma-sconto': totals.sconto,
            'owner-proforma-iva': totals.iva,
            'owner-proforma-totale': totals.totale
        };

        Object.keys(fields).forEach(function (id) {
            var node = document.getElementById(id);
            if (node) node.textContent = formatMoney(fields[id]);
        });
    }

    function syncScopeSelection() {
        var mode = document.querySelector('.js-owner-scope-mode:checked')?.value || 'all';
        var selectedSingle = document.querySelector('.js-owner-scope-single')?.value || '';
        var selectedMultiple = Array.from(document.querySelectorAll('.js-owner-scope-multiple-check:checked')).map(function (input) {
            return input.value;
        });

        document.querySelectorAll('[data-owner-proforma-row]').forEach(function (row) {
            var isGenerale = row.dataset.isGenerale === '1';
            var strutturaId = row.dataset.strutturaId || '';
            var checkbox = row.querySelector('.js-owner-row-selected');
            if (!checkbox) return;

            if (isGenerale) {
                row.classList.remove('d-none');
                return;
            }

            var include = true;
            if (mode === 'single') {
                include = !!selectedSingle && strutturaId === selectedSingle;
            } else if (mode === 'multiple') {
                include = selectedMultiple.includes(strutturaId);
            }

            checkbox.checked = include;
            row.classList.toggle('d-none', !include);
        });

        recalcOwnerProforma();
    }

    document.addEventListener('input', function (event) {
        if (event.target.closest('[data-owner-proforma-row], [data-owner-proforma-custom-row]')) {
            recalcOwnerProforma();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.closest('[data-owner-proforma-row], [data-owner-proforma-custom-row]')) {
            recalcOwnerProforma();
        }
        if (event.target.matches('.js-owner-scope-mode, .js-owner-scope-single, .js-owner-scope-multiple-check')) {
            syncScopeSelection();
        }
    });

    syncScopeSelection();
    recalcOwnerProforma();
});
</script>
@endpush
