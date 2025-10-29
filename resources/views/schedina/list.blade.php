@extends('layouts.master')
@section('title') @lang('translation.schedina') @endsection
@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Gestione @endslot
@slot('title') Schedina @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">@lang('translation.tickets')</h5>
                <div class="flex-shrink-0">
                    <a href="{{ url('/newschedina') }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> @lang('translation.new')
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="schedina-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Nome</th>
                            <th>Arrivo</th>
                            <th>Partenza</th>
                            <th>Quantità</th>
                            <th>Camera</th>
                            <th>Letti</th>
                            <th>Nazione</th>
                            <th>Città</th>
                            <th>Componenti</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedinas as $schedina)
                        <tr>
                            <td>{{ $schedina->scheda }}</td>
                            <td>{{ $schedina->surname }}, {{ $schedina->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedina->arrive)->format('d M, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedina->departure)->format('d M, Y') }}</td>
                            <td>{{ $schedina->cant_people }}</td>
                            <td>{{ $schedina->room }}</td>
                            <td>{{ $schedina->beds }}</td>
                            <td>{{ $schedina->oa_country }}</td>
                            <td>{{ $schedina->oa_city }}</td>
                            <td>
                                <a href="{{ url('/newcomponenti/' . $schedina->id . '/' . $schedina->customer_id) }}" class="btn btn-sm btn-soft-success">
                                    <i class="ri-user-add-line align-bottom me-1"></i> Aggiungi
                                </a>
                            </td>
                            <td>
                                <div class="hstack gap-3 flex-wrap">
                                    <a href="{{ url('/editschedina/' . $schedina->id) }}" class="link-success fs-15">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete({{ $schedina->id }})" class="link-danger fs-15">
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
    new DataTable('#schedina-table', {
        responsive: true,
        order: [[0, 'desc']], // Ordina per numero schedina decrescente
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
            lengthMenu: "Mostra _MENU_ schede",
            info: "Mostrando _START_ a _END_ di _TOTAL_ schede",
            infoEmpty: "Nessuna scheda disponibile",
            infoFiltered: "(filtrato da _MAX_ schede totali)",
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
        text: "Vuoi eliminare definitivamente questa schedina?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sì, elimina!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ url('/scheduledestroy') }}/" + id;
        }
    });
}
</script>

<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection