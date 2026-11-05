@extends('layouts.master')
@section('title', 'Gruppi')
@section('content')
@php
    $failedForm = is_string(old('_group_form')) ? old('_group_form') : null;
    $oldGroupValue = static fn ($field, $fallback = '') => is_scalar(old($field)) ? old($field) : $fallback;
@endphp
<div class="row config-page">
    <div class="col-lg-12">

        <x-crud-table
            title="Gruppi"
            subtitle="Gruppi della struttura corrente — Gruppi I → Gruppi II → Gruppi III"
            searchPlaceholder="Cerca per ID, livello, nome, descrizione o padre..."
            searchId="gruppiSearch"
            createText="Nuovo Gruppo"
            createTarget="#modalGruppoCreate"
            tableId="gruppiTable"
            :paginator="$gruppi"
        >

            <x-slot name="columns">
                <tr>
                    <th style="width: 15%;">Livello</th>
                    <th style="width: 28%;">Nome</th>
                    <th style="width: 20%;">Padre</th>
                    <th>Descrizione</th>
                    <th class="text-end" style="width: 14%; min-width: 160px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($gruppi as $gruppo)
                    @php
                        $parentName = $gruppo->parent?->nome ?? '-';
                        $badge = $gruppo->tipo_label;
                    @endphp
                    <tr class="data-row"
                        data-id="{{ $gruppo->id }}"
                        data-livello="{{ $gruppo->livello }}"
                        data-nome="{{ $gruppo->nome }}"
                        data-descrizione="{{ $gruppo->descrizione }}"
                        data-padre="{{ $parentName }}"
                        data-tipo="{{ $badge }}">
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span class="badge bg-primary-subtle text-primary mb-1 fs-6">{{ $badge }}</span>
                            </div>
                        </td>
                        <td class="align-middle">{{ $gruppo->nome }}</td>
                        <td class="align-middle">{{ $parentName }}</td>
                        <td class="align-middle">{{ $gruppo->descrizione ?? '-' }}</td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalGruppoEdit{{ $gruppo->id }}">
                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                </button>

                                <form action="{{ route('gruppo.destroy', $gruppo->id) }}" method="POST" class="d-inline" data-confirm-label="{{ 'il gruppo ' . $gruppo->nome }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm" title="Elimina">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                @endforeach
            </x-slot>

        </x-crud-table>

        @foreach($gruppi as $gruppo)
            <div id="modalGruppoEdit{{ $gruppo->id }}" class="modal fade" tabindex="-1" aria-labelledby="gruppoEditLabel{{ $gruppo->id }}" aria-hidden="true" data-gruppo-modal data-original-name="{{ $gruppo->nome }}" data-original-description="{{ $gruppo->descrizione }}" data-original-level="{{ $gruppo->livello }}" data-original-parent="{{ $gruppo->parent_id ?? '' }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="gruppoEditLabel{{ $gruppo->id }}">Modifica Gruppo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                        </div>
                        <form method="POST" action="{{ route('gruppo.update', $gruppo->id) }}" data-confirm-label="{{ 'il gruppo ' . $gruppo->nome }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_group_form" value="edit-{{ $gruppo->id }}">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="livelloEdit{{ $gruppo->id }}" class="form-label">Livello</label>
                                    <select name="livello" id="livelloEdit{{ $gruppo->id }}" class="form-select" required data-field="livello">
                                        <option value="1" @selected(($failedForm === 'edit-'.$gruppo->id ? $oldGroupValue('livello') : $gruppo->livello) == 1)>Gruppi I (livello 1)</option>
                                        <option value="2" @selected(($failedForm === 'edit-'.$gruppo->id ? $oldGroupValue('livello') : $gruppo->livello) == 2)>Gruppi II (livello 2)</option>
                                        <option value="3" @selected(($failedForm === 'edit-'.$gruppo->id ? $oldGroupValue('livello') : $gruppo->livello) == 3)>Gruppi III (livello 3)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="parentEdit{{ $gruppo->id }}" class="form-label">Padre</label>
                                    <select name="parent_id" id="parentEdit{{ $gruppo->id }}" class="form-select" data-field="parent" data-selected="{{ $failedForm === 'edit-'.$gruppo->id ? $oldGroupValue('parent_id', '') : ($gruppo->parent_id ?? '') }}">
                                        <option value="">Nessun padre</option>
                                        @foreach($gruppiLivello1 as $g1)
                                            <option value="{{ $g1->id }}" data-parent-level="1">{{ $g1->nome }}</option>

        @endforeach
                                                @foreach($gruppiLivello2 as $g2)
                                                    <option value="{{ $g2->id }}" data-parent-level="2">{{ $g2->nome }} ({{ $g2->parent?->nome }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nomeEdit{{ $gruppo->id }}" class="form-label">Nome</label>
                                            <input type="text" name="nome" value="{{ $failedForm === 'edit-'.$gruppo->id ? $oldGroupValue('nome') : $gruppo->nome }}" class="form-control" id="nomeEdit{{ $gruppo->id }}" maxlength="100" placeholder="Inserisci il nome del gruppo" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="descrizioneEdit{{ $gruppo->id }}" class="form-label">Descrizione (opzionale)</label>
                                            <input type="text" name="descrizione" value="{{ $failedForm === 'edit-'.$gruppo->id ? $oldGroupValue('descrizione') : $gruppo->descrizione }}" class="form-control" id="descrizioneEdit{{ $gruppo->id }}" maxlength="191" placeholder="Inserisci una descrizione (opzionale)">
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

    </div>
</div>

<div id="modalGruppoCreate" class="modal fade" tabindex="-1" aria-labelledby="gruppoCreateLabel" aria-hidden="true" data-gruppo-modal>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gruppoCreateLabel">Nuovo Gruppo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="POST" action="{{ route('gruppo.store') }}">
                @csrf
                <input type="hidden" name="_group_form" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="livelloCreate" class="form-label">Livello</label>
                        <select name="livello" id="livelloCreate" class="form-select" required data-field="livello">
                            <option value="1" @selected(($failedForm === 'create' ? $oldGroupValue('livello') : 1) == 1)>Gruppi I (livello 1)</option>
                            <option value="2" @selected(($failedForm === 'create' ? $oldGroupValue('livello') : 1) == 2)>Gruppi II (livello 2)</option>
                            <option value="3" @selected(($failedForm === 'create' ? $oldGroupValue('livello') : 1) == 3)>Gruppi III (livello 3)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="parentCreate" class="form-label">Padre</label>
                        <select name="parent_id" id="parentCreate" class="form-select" data-field="parent" data-selected="{{ $failedForm === 'create' ? $oldGroupValue('parent_id', '') : '' }}">
                            <option value="">Nessun padre</option>
                            @foreach($gruppiLivello1 as $g1)
                                <option value="{{ $g1->id }}" data-parent-level="1">{{ $g1->nome }}</option>
                            @endforeach
                            @foreach($gruppiLivello2 as $g2)
                                <option value="{{ $g2->id }}" data-parent-level="2">{{ $g2->nome }} ({{ $g2->parent?->nome }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nomeCreate" class="form-label">Nome</label>
                        <input type="text" name="nome" value="{{ $failedForm === 'create' ? $oldGroupValue('nome') : '' }}" class="form-control" id="nomeCreate" maxlength="100" placeholder="Inserisci il nome del gruppo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descrizioneCreate" class="form-label">Descrizione (opzionale)</label>
                        <input type="text" name="descrizione" value="{{ $failedForm === 'create' ? $oldGroupValue('descrizione') : '' }}" class="form-control" id="descrizioneCreate" maxlength="191" placeholder="Inserisci una descrizione (opzionale)">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modals = document.querySelectorAll('[data-gruppo-modal]');
        const Modal = window.bootstrap.Modal;
        // Gestione univoca locale: evita toggle concorrenti dei gestori globali.
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-bs-toggle="modal"], [data-bs-dismiss="modal"]');
            if (!trigger) return;
            const selector = trigger.getAttribute('data-bs-target');
            const modal = selector && selector.startsWith('#modalGruppo')
                ? document.getElementById(selector.slice(1))
                : trigger.closest('[data-gruppo-modal]');
            if (!modal || !modal.hasAttribute('data-gruppo-modal')) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            const instance = Modal.getOrCreateInstance(modal);
            trigger.hasAttribute('data-bs-dismiss') ? instance.hide() : instance.show();
        }, true);

        modals.forEach(function (modal) {
            const levelSelect = modal.querySelector('[data-field="livello"]');
            const parentSelect = modal.querySelector('[data-field="parent"]');

            const updateParentSelect = function () {
                if (!parentSelect || !levelSelect) return;

                const level = levelSelect.value;
                const requiredParentLevel = level === '2' ? '1' : (level === '3' ? '2' : null);
                let selected = parentSelect.dataset.selected || '';

                Array.from(parentSelect.options).forEach(function (opt) {
                    const optLevel = opt.dataset.parentLevel;
                    if (!optLevel) {
                        opt.hidden = false;
                        return;
                    }
                    const visible = requiredParentLevel && optLevel === requiredParentLevel;
                    opt.hidden = !visible;
                    opt.disabled = !visible;
                    if (!visible && opt.value === selected) {
                        selected = '';
                    }
                });

                if (!requiredParentLevel) {
                    parentSelect.value = '';
                    parentSelect.disabled = true;
                    parentSelect.required = false;
                    return;
                }

                parentSelect.disabled = false;
                parentSelect.required = true;
                parentSelect.value = selected;

            };

            if (levelSelect) {
                levelSelect.addEventListener('change', updateParentSelect);
            }

            if (parentSelect) {
                parentSelect.addEventListener('change', function () {
                    parentSelect.dataset.selected = parentSelect.value || '';
                });
            }

            updateParentSelect();
            modal.addEventListener('hidden.bs.modal', function () {
                modal.querySelector('form').reset();
                const creating = modal.id === 'modalGruppoCreate';
                modal.querySelector('[name="nome"]').value = creating ? '' : modal.dataset.originalName;
                modal.querySelector('[name="descrizione"]').value = creating ? '' : modal.dataset.originalDescription;
                levelSelect.value = creating ? '1' : modal.dataset.originalLevel;
                parentSelect.dataset.selected = creating ? '' : modal.dataset.originalParent;
                updateParentSelect();
            });
        });
        const failed = @json($failedForm);
        const target = failed === 'create' ? 'modalGruppoCreate' : (failed && failed.startsWith('edit-') ? 'modalGruppoEdit' + failed.slice(5) : null);
        if (target && document.getElementById(target) && window.bootstrap) {
            Modal.getOrCreateInstance(document.getElementById(target)).show();
        }
    });
</script>
@endpush
