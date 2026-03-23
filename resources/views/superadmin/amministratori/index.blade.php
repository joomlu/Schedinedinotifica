@extends('layouts.master')
@section('title') Amministratori @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Amministratori @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Amministratori</h4>
        <a href="{{ route('superadmin.amministratori.create') }}" class="btn btn-primary">Nuovo amministratore</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-light border mb-3">
                Doppio clic su un amministratore per aprire subito la modifica e gestire i proprietari assegnati.
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Attivo</th>
                            <th>Proprietari</th>
                            <th>Strutture</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                            <tr class="js-admin-row" data-edit-url="{{ route('superadmin.amministratori.edit', $admin->id) }}" style="cursor: pointer;">
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->ruolo }}</td>
                                <td>{{ $admin->attivo ? 'Sì' : 'No' }}</td>
                                <td>{{ $admin->proprietari_count ?? 0 }}</td>
                                <td>{{ $admin->strutture_count ?? 0 }}</td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.amministratori.edit', $admin->id) }}" class="btn btn-sm btn-outline-secondary">Accedi</a>
                                    <a href="{{ route('superadmin.amministratori.edit', ['id' => $admin->id, 'tab' => 'proprietari']) }}" class="btn btn-sm btn-outline-primary">Assegna proprietario</a>
                                    @if($admin->attivo)
                                        <form action="{{ route('superadmin.amministratori.disable', $admin->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Disabilita</button>
                                        </form>
                                    @else
                                        <form action="{{ route('superadmin.amministratori.enable', $admin->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Riattiva</button>
                                        </form>
                                    @endif
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
    document.querySelectorAll('.js-admin-row').forEach(function (row) {
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
