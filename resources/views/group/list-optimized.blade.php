@extends('layouts.master')
@section('title') @lang('translation.Group') @endsection

@section('css')
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.Configurations') @endslot
@slot('title')@lang('translation.Group')@endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <x-card :title="__('translation.Group')">
            <x-slot name="headerActions">
                <x-button 
                    variant="primary" 
                    icon="ri-add-circle-line"
                    data-bs-toggle="modal" 
                    data-bs-target="#createModal">
                    @lang('translation.new')
                </x-button>
            </x-slot>

            <x-datatable 
                id="groupsTable" 
                :columns="['ID', __('translation.Name'), __('translation.edit')]">
                @foreach($groups ?? [] as $group)
                <tr>
                    <td>{{ $group->id }}</td>
                    <td>{{ $group->name }}</td>
                    <td>
                        <div class="hstack gap-3 flex-wrap">
                            <a href="javascript:void(0);" 
                               class="link-success fs-15" 
                               data-bs-toggle="modal" 
                               data-bs-target="#editModal{{ $group->id }}">
                                <i class="ri-edit-2-line"></i>
                            </a>
                            <form action="{{ route('group.destroy', $group->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-danger fs-15 border-0 bg-transparent" onclick="return confirm('Sei sicuro?')">
                                    <i class="ri-delete-bin-5-line"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal per questo gruppo --}}
                <x-modal :id="'editModal' . $group->id" :title="__('translation.edit') . ' ' . __('translation.Group')">
                    <form method="POST" action="{{ route('group.update', $group->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <x-form.input 
                            name="name" 
                            :label="__('translation.Name')" 
                            :value="$group->name"
                            required />
                        
                        <x-slot name="footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('translation.close')</button>
                            <button type="submit" class="btn btn-primary">@lang('translation.save')</button>
                        </x-slot>
                    </form>
                </x-modal>
                @endforeach
            </x-datatable>
        </x-card>
    </div>
</div>

{{-- Create Modal --}}
<x-modal id="createModal" :title="__('translation.new') . ' ' . __('translation.Group')">
    <form method="POST" action="{{ route('group.store') }}">
        @csrf
        
        <x-form.input 
            name="name" 
            :label="__('translation.Name')" 
            placeholder="Inserisci nome gruppo..."
            required />
        
        <x-slot name="footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('translation.close')</button>
            <button type="submit" class="btn btn-primary">@lang('translation.save')</button>
        </x-slot>
    </form>
</x-modal>

@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
@endsection
