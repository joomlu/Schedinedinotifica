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

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Admin</th>
                            <th>Attivo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proprietari as $proprietario)
                            <tr>
                                <td>{{ $proprietario->nome }}</td>
                                <td>{{ $proprietario->email }}</td>
                                <td>{{ $proprietario->telefono }}</td>
                                <td>{{ optional($proprietario->admin)->name }}</td>
                                <td>{{ $proprietario->attivo ? 'Sì' : 'No' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.proprietari.edit', $proprietario->id) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                                    <form action="{{ route('superadmin.proprietari.disable', $proprietario->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Disabilita</button>
                                    </form>
                                    <form action="{{ route('superadmin.proprietari.destroy', $proprietario->id) }}" method="POST" class="d-inline" data-confirm-label="{{ 'il proprietario ' . $proprietario->nome }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Cestino</button>
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
