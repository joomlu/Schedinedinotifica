@extends('layouts.master')
@section('title')
    @lang('translation.edit')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            @lang('translation.forms')
        @endslot
        @slot('title')
            @lang('translation.titles.components-edit')
        @endslot
    @endcomponent

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('translation.titles.components-edit')</h4>
                </div><!-- end card header -->
                <div class="card-body">
                
                    <form  method="POST" action="{{route('componenti.update', $componenti->id)}}" class="form-steps" autocomplete="off" data-sa-confirm="update">
                    @csrf 
                    @method('PUT') 
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="valueInput" class="form-label">Tipo Alloggiato</label>
                                        <select type="text" class="form-control js-example-basic-single" name="relationship" data-placeholder="Seleziona tipo alloggiato">
                                        <option value="{{$componenti->relationship}}" active>{{$componenti->relationship}}</option>
                                            <option value="CAPOFAMIGLIA">CAPOFAMIGLIA</option>
                                            <option value="CAPOGRUPPO">CAPOGRUPPO</option>
                                            <option value="FAMILIARE">FAMILIARE</option>
                                            <option value="MEMBRO GRUPPO">MEMBRO GRUPPO</option>
                                            <option value="OSPITE SINGOLO">OSPITE SINGOLO</option>
                                        </select> 
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
                                        <select type="text" class="form-control js-example-basic-single" name="sex" data-placeholder="Seleziona sesso">
                                            <option value="{{$componenti->sex}}">{{$componenti->sex}}</option>
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                
                                
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Nazione</label>
                                       
                                        <select type="text" class="form-control autofill-select" data-autofill="countries" name="country" data-placeholder="Seleziona una Nazione">
                                                        <option value="{{$componenti->country}}">{{$componenti->country}}</option>
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation['denominazione_cittadinanza'] }}">{{ $nation['denominazione_cittadinanza'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Cittadinanza</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_nac" data-placeholder="Seleziona una Città">
                                        <option value="{{$componenti->city_nac}}">{{$componenti->city_nac}}</option>
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Provincia</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_nac" data-placeholder="Seleziona una Provincia">
                                        <option value="{{$componenti->province_nac}}">{{$componenti->province_nac}}</option>
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Regione</label>
                                        
                                        <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="regione" data-placeholder="Seleziona una Regione">
                                        <option value="{{$componenti->regione}}">{{$componenti->regione}}</option>
                                                    @foreach($regions as $region)
                                                        <option value="{{ $region['codice_regione'] }}">
                                                            {{ $region['denominazione_regione'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Città</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city" data-placeholder="Seleziona una Città">
                                        <option value="{{$componenti->regione}}">{{$componenti->city}}</option>
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Tipo via</label>
                                        <select type="text" class="form-control js-example-basic-single" name="tipeaway" data-placeholder="Seleziona tipo via">
                                        <option value="{{$componenti->typeaway}}">{{$componenti->typeaway}}</option>
                                            @foreach($typeaway as $typestreet)
                                            <option value="{{$typestreet->name}}">{{$typestreet->name}}</option>
                                            @endforeach
                                        </select> 
                                        
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
                                        <label for="readonlyInput" class="form-label">Num.</label>
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
                                        <input type="text" class="form-control" name="date_nac" value="{{$componenti->date_nac}}" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-4">
                                <div>
                                        <label for="basiInput" class="form-label">Esente</label>
                                        <select type="text" class="form-control" name="exent">
                                        <option value="{{$componenti->exent}}">{{$componenti->exent}}</option>
                                            <option value="Si">SI</option>
                                            <option value="NO">NO</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                
                                <div class="col-xxl-3 col-md-6">
                                <div>
                                        <button type="submit" class="btn btn-success">@lang('translation.buttons.save')</button>
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
    <script src="{{ URL::asset('build/js/pages/form-wizard.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('js/autofill-select.js') }}"></script>
@endsection
