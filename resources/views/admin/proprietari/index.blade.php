@extends('layouts.master')
@section('title') Proprietari @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('title') Proprietari @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">I miei proprietari</h4>
        <a href="{{ route('admin.proprietari.create') }}" class="btn btn-primary">Nuovo proprietario</a>
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
                                <td>{{ $proprietario->attivo ? 'Sì' : 'No' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.proprietari.edit', $proprietario->id) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                                    <form action="{{ route('admin.proprietari.disable', $proprietario->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Disabilita</button>
                                    </form>
                                    <form action="{{ route('admin.proprietari.destroy', $proprietario->id) }}" method="POST" class="d-inline" data-confirm-label="{{ 'il proprietario ' . $proprietario->nome }}">
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
