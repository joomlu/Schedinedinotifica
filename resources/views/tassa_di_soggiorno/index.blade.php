@extends('layouts.master')

@section('title', 'Tassa di soggiorno')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Configurazioni @endslot
        @slot('title') Tassa di soggiorno @endslot
    @endcomponent

    <div class="row config-page">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        La configurazione si gestisce dalla pagina principale.
                        <a href="{{ route('tassa_di_soggiorno.edit') }}" class="alert-link">Apri Tassa di soggiorno</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
