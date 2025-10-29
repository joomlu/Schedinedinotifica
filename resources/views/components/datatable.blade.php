@props([
    'id' => 'datatable',
    'columns' => [],
    'data' => [],
    'buttons' => true,
    'responsive' => true,
])

<div class="table-responsive">
    <table id="{{ $id }}" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#{{ $id }}').DataTable({
            @if($responsive)
            responsive: true,
            @endif
            @if($buttons)
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'print', 'pdf']
            @endif
        });
    });
</script>
@endpush
