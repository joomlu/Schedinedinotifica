@extends('layouts.master')
@section('title') Gestione Utenti @endsection
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Amministrazione @endslot
    @slot('title')Utenti & Ruoli @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Utenti</h5>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table align-middle table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Ruolo attuale</th>
                            <th>Assegna ruolo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.updateRole', $user->id) }}" class="d-flex gap-2">
                                        @csrf
                                        <select name="role" class="form-select form-select-sm" style="max-width: 200px">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" @selected($user->roles->contains('name', $role->name))>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-success" type="submit">Salva</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection