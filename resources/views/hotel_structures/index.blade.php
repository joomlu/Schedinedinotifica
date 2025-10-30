@extends('layouts.master')
@section('title') Strutture Hotel @endsection
@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Gestione @endslot
@slot('title') Strutture Hotel @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Successo!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Le Mie Strutture Hotel</h5>
                <div class="flex-shrink-0">
                    @can('create structures')
                    <a href="{{ route('hotel-structures.create') }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> Aggiungi Struttura Hotel
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <table id="hotel-structures-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Città</th>
                            <th>Tipologia</th>
                            <th>Stato</th>
                            <th>Autorizzazione</th>
                            <th>Username</th>
                            <th>Scadenza</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($structures as $structure)
                        <tr>
                            <td>{{ $structure->id }}</td>
                            <td>
                                @if($structure->logo)
                                <img src="{{ Storage::url($structure->logo) }}" alt="logo" class="avatar-xs rounded-circle me-2">
                                @endif
                                {{ $structure->name }}
                            </td>
                            <td>{{ $structure->city }}</td>
                            <td>{{ $structure->typology }}</td>
                            <td>
                                @if($structure->online)
                                <span class="badge bg-success">On-line</span>
                                @else
                                <span class="badge bg-danger">Off-line</span>
                                @endif
                            </td>
                            <td>
                                @if($structure->activo)
                                <span class="badge bg-success">Attivo</span>
                                @else
                                <span class="badge bg-warning">Disattivato</span>
                                @endif
                            </td>
                            <td>
                                <code>{{ $structure->username_hotel }}</code>
                            </td>
                            <td>
                                @if($structure->fecha_vencimiento)
                                {{ $structure->fecha_vencimiento->format('d/m/Y') }}
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if($structure->schedina_url)
                                    <a href="{{ $structure->schedina_url }}" target="_blank" class="btn btn-sm btn-primary" title="Accedi al Software">
                                        <i class="ri-external-link-line"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('hotel-structures.edit', $structure->id) }}" class="btn btn-sm btn-info">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    @can('create structures')
                                    <form action="{{ route('hotel-structures.destroy', $structure->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questa struttura?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                    @endcan
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
    $('#hotel-structures-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        language: {
            "sEmptyTable": "Nessun dato disponibile nella tabella",
            "sInfo": "Mostra da _START_ a _END_ di _TOTAL_ elementi",
            "sInfoEmpty": "Mostra da 0 a 0 di 0 elementi",
            "sInfoFiltered": "(filtrato da _MAX_ elementi totali)",
            "sInfoPostFix": "",
            "sInfoThousands": ",",
            "sLengthMenu": "Mostra _MENU_ elementi",
            "sLoadingRecords": "Caricamento...",
            "sProcessing": "Elaborazione...",
            "sSearch": "Cerca:",
            "sZeroRecords": "Nessun elemento corrispondente trovato",
            "oPaginate": {
                "sFirst": "Primo",
                "sLast": "Ultimo",
                "sNext": "Successivo",
                "sPrevious": "Precedente"
            }
        }
    });
});
</script>
@endsection
