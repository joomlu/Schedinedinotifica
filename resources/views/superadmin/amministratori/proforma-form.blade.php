@extends('layouts.master')
@section('title') {{ $proforma ? 'Modifica proforma' : 'Nuova proforma' }} @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
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
            <form method="POST" action="{{ $proforma ? route('superadmin.amministratori.proforme.update', ['id' => $admin->id, 'fatturazione' => $proforma->id]) : route('superadmin.amministratori.proforme.store', $admin->id) }}">
                @csrf
                @if($proforma)
                    @method('PUT')
                @endif

                @php
                    $customRows = old('custom_righe', $customRighe ?? []);
                @endphp

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small text-uppercase mb-1">Amministratore</div>
                            <div class="fw-semibold">{{ $admin->name }}</div>
                            <div class="small text-muted">{{ $admin->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small text-uppercase mb-1">Intestazione</div>
                            <div class="fw-semibold">{{ $admin->ragione_sociale ?: $admin->name }}</div>
                            <div class="small text-muted">P.IVA {{ $admin->partita_iva ?: 'non impostata' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                            <div class="text-muted small text-uppercase mb-1">Catalogo servizi</div>
                            <div class="fw-semibold">{{ $fatturazione['righe']->count() }}</div>
                            <div class="small text-muted">Servizi disponibili da includere liberamente nella proforma.</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">Imponibile</div>
                                    <div class="fw-semibold" id="admin-proforma-imponibile">0,00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">Sconto totale</div>
                                    <div class="fw-semibold" id="admin-proforma-sconto">0,00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">IVA totale</div>
                                    <div class="fw-semibold" id="admin-proforma-iva">0,00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small text-uppercase mb-1">Totale documento</div>
                                    <div class="fw-bold fs-4" id="admin-proforma-totale">0,00</div>
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
                        <div class="fw-semibold">Servizi disponibili per la proforma</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">Includi</th>
                                        <th>Destinazione</th>
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
                                        <tr data-admin-proforma-row>
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][selected]" value="0">
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][proprietario_id]" value="{{ $riga['proprietario_id'] ?? '' }}">
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][admin_servizio_id]" value="{{ $riga['admin_servizio_id'] }}">
                                            <input type="hidden" name="proforma_righe[{{ $riga['key'] }}][descrizione]" value="{{ $riga['descrizione'] }}">
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input
                                                        class="form-check-input js-admin-proforma-selected"
                                                        type="checkbox"
                                                        name="proforma_righe[{{ $riga['key'] }}][selected]"
                                                        value="1"
                                                        @checked(old("proforma_righe.{$riga['key']}.selected", $riga['selected'] ?? false))
                                                    >
                                                </div>
                                            </td>
                                            <td>{{ $riga['destinazione'] ?? 'Amministratore' }}</td>
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
                                            <td class="text-end fw-semibold js-admin-proforma-row-total">{{ number_format((float) $riga['totale'], 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-muted">Non ci sono ancora servizi base configurati per questo amministratore.</td>
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
                                        <tr data-admin-proforma-custom-row>
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
                                            <td class="text-end">
                                                <div class="fw-semibold small js-admin-proforma-row-total mb-2">0,00</div>
                                                <button type="button" class="btn btn-sm btn-outline-danger js-remove-custom">Rimuovi</button>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ $proforma ? route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id]) : route('superadmin.amministratori.edit', ['id' => $admin->id, 'tab' => 'fatturazione']) }}" class="btn btn-outline-secondary">Annulla</a>
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
            <tr data-admin-proforma-custom-row>
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
                <td class="text-end">
                    <div class="fw-semibold small js-admin-proforma-row-total mb-2">0,00</div>
                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-custom">Rimuovi</button>
                </td>
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
            recalcAdminProforma();
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
        recalcAdminProforma();
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
        var selectedInput = row.querySelector('.js-admin-proforma-selected');
        var qtyInput = row.querySelector('input[name*="[quantita]"]');
        var priceInput = row.querySelector('input[name*="[prezzo_unitario]"]');
        var ivaInput = row.querySelector('input[name*="[aliquota_iva]"]');
        var scontoType = row.querySelector('select[name*="[sconto_tipo]"]');
        var scontoValue = row.querySelector('input[name*="[sconto_valore]"]');
        if (!qtyInput || !priceInput || !ivaInput || !scontoType || !scontoValue) return null;

        if (selectedInput && !selectedInput.checked) {
            var unselectedTotalCell = row.querySelector('.js-admin-proforma-row-total');
            if (unselectedTotalCell) {
                unselectedTotalCell.textContent = formatMoney(0);
            }
            return {
                imponibile: 0,
                sconto: 0,
                iva: 0,
                totale: 0
            };
        }

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

        var totalCell = row.querySelector('.js-admin-proforma-row-total');
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

    function recalcAdminProforma() {
        var totals = {
            imponibile: 0,
            sconto: 0,
            iva: 0,
            totale: 0
        };

        document.querySelectorAll('[data-admin-proforma-row], [data-admin-proforma-custom-row]').forEach(function (row) {
            var result = computeRow(row);
            if (!result) return;
            totals.imponibile += result.imponibile;
            totals.sconto += result.sconto;
            totals.iva += result.iva;
            totals.totale += result.totale;
        });

        var fields = {
            'admin-proforma-imponibile': totals.imponibile,
            'admin-proforma-sconto': totals.sconto,
            'admin-proforma-iva': totals.iva,
            'admin-proforma-totale': totals.totale
        };

        Object.keys(fields).forEach(function (id) {
            var node = document.getElementById(id);
            if (node) node.textContent = formatMoney(fields[id]);
        });
    }

    document.addEventListener('input', function (event) {
        if (event.target.closest('[data-admin-proforma-row], [data-admin-proforma-custom-row]')) {
            recalcAdminProforma();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.closest('[data-admin-proforma-row], [data-admin-proforma-custom-row]')) {
            recalcAdminProforma();
        }
    });

    recalcAdminProforma();
});
</script>
@endpush
