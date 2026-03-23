@extends('layouts.master')
@section('title')
    @lang('translation.edit')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Forms
        @endslot
        @slot('title')
            Edit Clienti
        @endslot
    @endcomponent

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Componenti - Edit </h4>
                </div><!-- end card header -->
                <div class="card-body">
                
                    <form  method="POST" action="{{route('componenti.update', $componenti->id)}}" class="form-steps" autocomplete="off">
                    @csrf 
                    @method('PUT') 
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="valueInput" class="form-label">Tipo Alloggiato</label>
                                        <x-ui.select name="relationship">
                                            <option value="CAPOFAMIGLIA" {{ $componenti->relationship === 'CAPOFAMIGLIA' ? 'selected' : '' }}>CAPOFAMIGLIA</option>
                                            <option value="CAPOGRUPPO" {{ $componenti->relationship === 'CAPOGRUPPO' ? 'selected' : '' }}>CAPOGRUPPO</option>
                                            <option value="FAMILIARE" {{ $componenti->relationship === 'FAMILIARE' ? 'selected' : '' }}>FAMILIARE</option>
                                            <option value="MEMBRO GRUPPO" {{ $componenti->relationship === 'MEMBRO GRUPPO' ? 'selected' : '' }}>MEMBRO GRUPPO</option>
                                            <option value="OSPITE SINGOLO" {{ $componenti->relationship === 'OSPITE SINGOLO' ? 'selected' : '' }}>OSPITE SINGOLO</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Nome</label>
                                        <input type="text" class="form-control" name="name" value="{{$componenti->name}}">
                                        <input type="hidden" class="form-control" name="schedina_id" value="{{$componenti->schedina_id}}">
                                        <input type="hidden" class="form-control" name="customer_id" value="{{$componenti->customer_id}}">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Cognome</label>
                                        <input type="text" class="form-control" name="surname" value="{{$componenti->surname}}">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Sesso</label>
                                        <x-ui.select name="sex">
                                            <option value="M" {{ $componenti->sex === 'M' ? 'selected' : '' }}>M</option>
                                            <option value="F" {{ $componenti->sex === 'F' ? 'selected' : '' }}>F</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <!--end col-->
                                
                                
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Nazione</label>
                                       
                                        <x-ui.select class="autofill-select" data-autofill="countries" name="country">
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation['denominazione_cittadinanza'] }}" {{ $componenti->country === $nation['denominazione_cittadinanza'] ? 'selected' : '' }}>{{ $nation['denominazione_cittadinanza'] }}</option>
                                                        @endforeach
                                                    </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Cittadinanza</label>
                                        
                                        <x-ui.select class="autofill-select" data-autofill="cities" name="city_nac">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}" {{ $componenti->city_nac === $ciudad['denominazione_ita'] ? 'selected' : '' }}>{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </x-ui.select>
                                                
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Provincia</label>
                                        
                                        <x-ui.select class="autofill-select" data-autofill="provinces" name="province_nac">
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}" {{ $componenti->province_nac === $province['denominazione_provincia'] ? 'selected' : '' }}>{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Regione</label>
                                        
                                        <x-ui.select id="region-select" class="autofill-select" data-autofill="regions" name="regione">
                                                    @foreach($regions as $region)
                                                        <option value="{{ $region['codice_regione'] }}" {{ (string) $componenti->regione === (string) $region['codice_regione'] ? 'selected' : '' }}>
                                                            {{ $region['denominazione_regione'] }}
                                                        </option>
                                                    @endforeach
                                                </x-ui.select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Città</label>
                                        
                                        <x-ui.select class="autofill-select" data-autofill="cities" name="city">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}" {{ $componenti->city === $ciudad['denominazione_ita'] ? 'selected' : '' }}>{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </x-ui.select>
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Tipo Via</label>
                                        <x-ui.select name="tipeaway">
                                            @foreach($typeaway as $typestreet)
                                            <option value="{{$typestreet->name}}" {{ $componenti->typeaway === $typestreet->name ? 'selected' : '' }}>{{$typestreet->name}}</option>
                                            @endforeach
                                        </x-ui.select>
                                        
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Strada</label>
                                        <input type="text" class="form-control" name="address" value="{{$componenti->address}}">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Num</label>
                                        <input type="text" class="form-control" name="number" value="{{$componenti->number}}">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">CAP</label>
                                        <input type="text" class="form-control" name="cap" value="{{$componenti->cap}}">
                                    </div>
                                    </div>
                               
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Data di nascita</label>
                                        <x-calendario name="date_nac" variant="birth" :value="$componenti->date_nac" />
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-4">
                                <div>
                                        <label for="basiInput" class="form-label">Esente</label>
                                        <x-ui.select name="exent">
                                            <option value="Si" {{ $componenti->exent === 'Si' ? 'selected' : '' }}>SI</option>
                                            <option value="NO" {{ $componenti->exent === 'NO' ? 'selected' : '' }}>NO</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <!--end col-->
                                
                                <div class="col-xxl-3 col-md-6">
                                <div>
                                        <button type="submit" class="btn btn-success">Salva</button>
                                    </div>
                                </div>
                               
                            </div>
                            <!--end row-->
                        </div>
                    </form>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div><!-- end row -->
    
@endsection
@section('script')
    <script src="{{ URL::asset('js/autofill-select.js') }}"></script>
@endsection
