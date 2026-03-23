@extends('layouts.master')
@section('title') Proprietari @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Proprietari @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Proprietari</h4>
        <a href="{{ route('superadmin.proprietari.create') }}" class="btn btn-primary">Nuovo proprietario</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="card-title mb-2">Assegnazione amministratori</h6>
            <p class="text-muted mb-0">Ogni proprietario puo essere assegnato a un amministratore. Un admin puo gestire molti proprietari, e ogni proprietario puo poi avere molte strutture.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-light border mb-3">
                Doppio clic su un proprietario per aprire subito la sua scheda completa, con strutture, servizi e fatturazione.
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Amministratore di riferimento</th>
                            <th>Strutture</th>
                            <th>Attivo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proprietari as $proprietario)
                            <tr class="js-owner-row" data-edit-url="{{ route('superadmin.proprietari.edit', $proprietario->id) }}" style="cursor: pointer;">
                                <td>{{ $proprietario->nome }}</td>
                                <td>{{ $proprietario->email }}</td>
                                <td>{{ $proprietario->telefono }}</td>
                                <td>
                                    @if($proprietario->admin)
                                        <div class="fw-semibold">{{ $proprietario->admin->name }}</div>
                                        <div class="small text-muted">{{ $proprietario->admin->email }}</div>
                                        <div class="small text-muted">{{ $proprietario->admin->telefono ?: 'Telefono non impostato' }}</div>
                                    @else
                                        <span class="text-muted">Nessuno</span>
                                    @endif
                                </td>
                                <td>{{ $proprietario->strutture_count }}</td>
                                <td>{{ $proprietario->attivo ? 'Sì' : 'No' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.proprietari.edit', $proprietario->id) }}" class="btn btn-sm btn-outline-secondary">Accedi</a>
                                    <form action="{{ route('superadmin.proprietari.disable', $proprietario->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Disabilita</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-owner-row').forEach(function (row) {
        row.addEventListener('dblclick', function (event) {
            if (event.target.closest('a, button, form, input, select, textarea, label')) {
                return;
            }

            const url = row.dataset.editUrl;
            if (url) {
                window.location.href = url;
            }
        });
    });
});
</script>
@endpush
