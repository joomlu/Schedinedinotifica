
@extends('layouts.master')
@section('title', 'Modifica Tipo Documento')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Modifica Tipo Documento</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tipo_documento.update', $tipo_documento) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('tipo_documento.form')
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Aggiorna</button>
                            <a href="{{ route('tipo_documento.index') }}" class="btn btn-secondary">Annulla</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
