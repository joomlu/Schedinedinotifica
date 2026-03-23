@extends('layouts.master')
@section('title') @lang('translation.customers') @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Tables @endslot
@slot('title')Componenti @endslot
@endcomponent

<div class="row justify-content-end">
    <div class="col-sm-2">
                                                   
        
            <i class="ri-add-circle-line align-middle me-1"></i>
            @lang('translation.new')</a>
                                                    
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('translation.componenti')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <x-ui.datatable id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <x-slot:head>
                            <tr>
                                <th>id</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Nazione</th>
                                <th>Città</th>
                                <th>Tipo Allogiato</th>
                                <th>Link</th>
                                <th>Actions.</th>
                            </tr>
                        </x-slot:head> 
                        <x-slot:body>
                            @foreach($componenti as $customer)
                            <tr>
                                <td>{{$customer->id}}</td>
                                <td>{{$customer->name}}</td>
                                <td>{{$customer->surname}}</td>
                                <td>{{$customer->country}}</td>
                                <td>{{$customer->city}}</td>
                                <td>{{$customer->relationship}}</td>
                                <td><a href="#" class="link-success">Link <i class="ri-arrow-right-line align-middle"></i></a></td>
                                <td>
                                    <a href="{{url('/editcomponenti')}}/{{$customer->id}}" type="button" class="btn btn-success btn-icon waves-effect waves-light"><i class="ri-search-line"></i></a>
                                    <form action="{{ route('componenti.destroy', ['id' => $customer->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon waves-effect waves-light">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </x-slot:body>
                    </x-ui.datatable>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
@section('script')
@endsection
