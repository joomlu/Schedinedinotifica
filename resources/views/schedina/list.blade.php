@extends('layouts.master')
@section('title') @lang('translation.schedina') @endsection
@section('css')
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Tables @endslot
@slot('title')Schedina @endslot
@endcomponent


<div class="row justify-content-end">
                                                <div class="col-sm-2">
                                                   
                                                    <a type="button" class="btn btn-primary" href="{{url('/newschedina')}}"><i class="ri-add-circle-line align-middle me-1"></i>
                                                    @lang('translation.new')</a>
                                                    
                                                </div>
                                            </div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('translation.tickets')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Nome</th>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>Quantitá</th>
                                <th>Camera</th>
                                <th>Letti</th>
                                
                                <th>Nazione</th>
                                <th>Citta</th>
                                <th>Link</th>
                                <th>Componenti</th>
                                <th>Actions.</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($schedinas as $schedina)
                            <tr>
                                <td>{{$schedina->scheda}}</td>
                                <td>{{$schedina->surname}}, {{$schedina->name}}</td>
                                <td>{{$schedina->arrive}}</td>
                                <td>{{$schedina->departure}}</td>
                                <td>{{$schedina->cant_people}}</td>
                                <td>{{$schedina->room}}</td>
                                <td>{{$schedina->beds}}</td>
                                <td>{{$schedina->oa_country}}</td>
                                <td>{{$schedina->oa_city}}</td>
                                
                                
                                <td><a href="#" class="link-success">Link <i class="ri-arrow-right-line align-middle"></i></a></td>
                                <td><a href="{{url('/newcomponenti')}}/{{$schedina->id}}/{{$schedina->customer_id}}" class="btn btn-success">ADD</a></td>
                                <td> <a href="{{url('/editschedina')}}/{{$schedina->id}}" type="button" class="btn btn-success btn-icon waves-effect waves-light"><i class="ri-search-line"></i></a> 
                                <a  href="{{ route('schedina.destroy',['id' => $schedina->id] )}}" onclick="
return confirm('Seguro deseas eliminar esta schedina definitivamente?')" type="button"  class="btn btn-danger btn-icon waves-effect waves-light">
                      <i class="ri-delete-bin-5-line"></i><a></td> 
                            </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

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