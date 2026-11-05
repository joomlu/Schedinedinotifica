@extends('layouts.master')
@section('title') @lang('translation.Title') @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Tables @endslot
@slot('title')Released @endslot
@endcomponent



<div class="row justify-content-end">
                                                <div class="col-sm-2">
                                                   
                                                    <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal"><i class="ri-add-circle-line align-middle me-1"></i>
                                                    @lang('translation.new')</a>
                                                    

<!-- Default Modals -->

<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">@lang('translation.new') @lang('translation.Released')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <div class="modal-body">
<form method="POST" action="{{route('released.store')}}">
@csrf 
<div>
    <label for="basiInput" class="form-label">@lang('translation.Name')</label>
    <input type="text" name="name" class="form-control" id="basiInput">
</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary ">Salvar</button>
</form>
            </div>
      
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


                                                </div>
                                            </div>
<div class="row">

    <div class="col-lg-12">
           
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('translation.Released')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <x-ui.datatable id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <x-slot:head>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th></th>
                            </tr>
                        </x-slot:head>
                        <x-slot:body>
                            @foreach($released as $release)
                            <tr>
                                <td>{{$release->id}}</td>
                                <td>{{$release->name}}</td>
                                
                                <td> <button type="button" class="btn btn-success btn-icon waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#ModalEdit{{$release->id}}"><i class=" ri-pencil-line"></i></button> 
                                <a href="{{ route('released.destroy',['id' => $release->id] )}}" type="button" class="btn btn-danger btn-icon waves-effect waves-light" data-confirm-label="{{ 'il rilascio documento ' . $release->name }}">
                      <i class="ri-delete-bin-5-line"></i><a>     
                                </td>
                            </tr>
                                <div id="ModalEdit{{$release->id}}" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="myModalEditLabel">@lang('translation.edit') @lang('translation.Released')</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                                            </div>
                                            <div class="modal-body">
                                                    <form method="POST" action="{{route('released.update', $release->id)}}">
                                                    @csrf 
                                                    @method('PUT') 
                                                                <div>
                                                                    <label for="basiInput" class="form-label">@lang('translation.Name')</label>
                                                                    <input type="text" name="name" value="{{$release->name}}" class="form-control" id="basiInput">
                                                                </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary ">Salvar</button>
                                                                </div>
                                                    </form>
                                            
                                    
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->
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
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
@endsection
