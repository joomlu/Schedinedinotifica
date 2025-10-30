@extends('layouts.master')
@section('title') Strutture @endsection
@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Gestione @endslot
@slot('title') Strutture @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista Strutture</h5>
                <div class="flex-shrink-0">
                    @can('create structures')
                    <a href="{{ route('estructura.new') }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> Aggiungi Struttura
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <table id="estructuras-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Città</th>
                            <th>Tipologia</th>
                            <th>Telefono</th>
                            <th>Email</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estructuras as $estructura)
                        <tr>
                            <td>{{ $estructura->id }}</td>
                            <td>{{ $estructura->name }}</td>
                            <td>{{ $estructura->city }}</td>
                            <td>{{ $estructura->typology }}</td>
                            <td>{{ $estructura->phone }}</td>
                            <td>{{ $estructura->email }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('estructura.show', $estructura->id) }}" class="btn btn-sm btn-info">
                                        <i class="ri-eye-line"></i> Visualizza
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<!-- DataTables JS -->
<script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#estructuras-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        language: {
            url: "{{ URL::asset('build/libs/datatables.net/i18n/it-IT.json') }}",
            emptyTable: "Nessuna struttura disponibile",
            zeroRecords: "Nessuna struttura trovata"
        }
    });
});
</script>
@endsection
