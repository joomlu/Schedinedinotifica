@extends('layouts.master')
@section('title') Componenti (accompagnatori) @endsection
@section('css')
<!-- DataTables CSS -->
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Gestione @endslot
@slot('title')Componenti (accompagnatori) @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Componenti (accompagnatori)</h5>
                <div class="flex-shrink-0">
                    <a href="{{ url('/componenti_new') }}" class="btn btn-soft-success">
                        <i class="ri-add-circle-line align-middle me-1"></i> @lang('translation.new')
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="componenti-table" class="table dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Nazione</th>
                                <th>Città</th>
                                <th>Tipo Alloggiato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($componenti as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->surname }}</td>
                                <td>{{ $customer->country }}</td>
                                <td>{{ $customer->city }}</td>
                                <td>{{ $customer->relationship }}</td>
                                <td>
                                    <div class="hstack gap-3 flex-wrap">
                                        <a href="{{ url('/editcomponenti') }}/{{ $customer->id }}" class="link-success fs-15">
                                            <i class="ri-edit-2-line"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete({{ $customer->id }})" class="link-danger fs-15">
                                            <i class="ri-delete-bin-line"></i>
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
<script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/pdfmake/build/pdfmake.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/pdfmake/build/vfs_fonts.js') }}"></script>
<script src="{{ URL::asset('build/libs/jszip/jszip.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    new DataTable('#componenti-table', {
        responsive: true,
        order: [[0, 'desc']], // Ordina per ID decrescente
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'csv',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-secondary'
            }
        ],
        language: {
            search: "Cerca:",
            lengthMenu: "Mostra _MENU_ componenti",
            info: "Mostrando _START_ a _END_ di _TOTAL_ componenti",
            infoEmpty: "Nessun componente disponibile",
            infoFiltered: "(filtrato da _MAX_ componenti totali)",
            paginate: {
                first: "Primo",
                last: "Ultimo",
                next: "Successivo",
                previous: "Precedente"
            }
        }
    });
});

function confirmDelete(id) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Vuoi eliminare definitivamente questo componente?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sì, elimina!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ url('/componenti_destroy') }}/" + id;
        }
    });
}
</script>

<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection