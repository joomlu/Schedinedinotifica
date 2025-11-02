@extends('layouts.master')
@section('title') @lang('translation.arrivals') @endsection
@section('css')
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.tables') @endslot
@slot('title') @lang('translation.arrivals') @endslot
@endcomponent

<div class="alert alert-danger" role="alert">
    <strong>@lang('translation.arrivals')</strong>
 </div>

<div class="row justify-content-end">
    <div class="col-sm-2">
        <a type="button" class="btn btn-primary" href="{{url('/new_arrival')}}" title="@lang('translation.actions.add')" aria-label="@lang('translation.actions.add')">
            <i class="ri-add-circle-line align-middle me-1"></i>
            @lang('translation.new')
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('translation.arrivals')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Nome</th>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>Nazione</th>
                                <th>Città</th>
                                <th>@lang('translation.actions.action')</th>
                                <th>@lang('translation.actions.delete')</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($arrivals as $arrival)
                            <tr>
                                <td>{{$arrival->id}}</td>
                                <td>{{$arrival->name}}</td>
                                <td>{{$arrival->arrive}}</td>
                                <td>{{$arrival->departure}}</td>
                                <td>{{$arrival->oa_country}}</td>
                                <td>{{$arrival->oa_city}}</td>
                                <td><form action="{{ route('a_schedina', $arrival->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning" title="@lang('translation.actions.to_schedina')" aria-label="@lang('translation.actions.to_schedina')">
                                            @lang('translation.actions.to_schedina')
                                        </button>
                                    </form></td>
                                <td>
                                    <form action="{{ route('arrivals.destroy', $arrival->id) }}" method="POST" data-sa-confirm="delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon waves-effect waves-light" title="@lang('translation.actions.delete')" aria-label="@lang('translation.actions.delete')">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
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

@section('script')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
