@props([
    'id' => null,
    'pageLength' => null,
    'order' => null,
    'searching' => null,
    'responsive' => null,
    'head' => null,
    'body' => null,
])

<table
    data-ui="datatable"
    @if($id) id="{{ $id }}" @endif
    @if(!is_null($pageLength)) data-page-length="{{ $pageLength }}" @endif
    @if(!is_null($order)) data-order="{{ $order }}" @endif
    @if(!is_null($searching)) data-searching="{{ $searching ? '1' : '0' }}" @endif
    @if(!is_null($responsive)) data-responsive="{{ $responsive ? '1' : '0' }}" @endif
    {{ $attributes->merge(['class' => 'table table-bordered table-striped align-middle']) }}
>
    @if($head)
        <thead>{!! $head !!}</thead>
    @endif
    @if($body)
        <tbody>{!! $body !!}</tbody>
    @else
        {{ $slot }}
    @endif
</table>
