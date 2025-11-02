@extends('layouts.master')
@section('title')
    @lang('translation.componenti')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
        Componenti
        @endslot
        @slot('title')
        Nuovo 
        @endslot
    @endcomponent

    

      
        <div class="row">

        
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Nuovo Componenti</h4>
                        
                    </div><!-- end card header -->
                    <form  method="POST" action="{{route('componenti.store')}}">
                    @csrf 
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="valueInput" class="form-label">Tipo Allogiatto</label>
                                        <select type="text" class="form-control" name="relationship">
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
                                        <input type="text" class="form-control" name="name">
                                        <input type="hidden" class="form-control" name="schedina_id" value="{{$schedina_id}}">
                                        <input type="hidden" class="form-control" name="customer_id" value="{{$customer_id}}">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Cognome</label>
                                        <input type="text" class="form-control" name="surname">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Sesso</label>
                                        <select type="text" class="form-control" name="sex">
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                
                                
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Nazione</label>
                                       
                                        <select type="text" class="form-control autofill-select" data-autofill="countries" name="country">
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation['denominazione_cittadinanza'] }}">{{ $nation['denominazione_cittadinanza'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Cittadinanza</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_nac">
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
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_nac">
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Regione</label>
                                        
                                        <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="regione">
                                                    <option value="">Seleccione una Región</option>
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
                                        <label for="readonlyInput" class="form-label">Citta</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Tipo via</label>
                                        <select type="text" class="form-control" name="tipeaway">
                                            @foreach($typeaway as $typestreet)
                                            <option value="{{$typestreet->name}}">{{$typestreet->name}}</option>
                                            @endforeach
                                        </select> 
                                        
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Strada</label>
                                        <input type="text" class="form-control" name="address">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Num</label>
                                        <input type="text" class="form-control" name="number">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Cap</label>
                                        <input type="text" class="form-control" name="cap">
                                    </div>
                                    </div>
                               
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Data di nascita</label>
                                        <input type="date" class="form-control" name="date_nac" data-provider="flatpickr" data-date-format="{{ config('app.date.backend_format') }}" data-altFormat="{{ config('app.date.display_format') }}">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-4">
                                <div>
                                        <label for="basiInput" class="form-label">Essento</label>
                                        <select type="text" class="form-control" name="exent">
                                            <option value="Si">SI</option>
                                            <option value="NO">NO</option>
                                        </select> 
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
       
    </div> <!-- container-fluid -->
</div><!-- End Page-content -->



@endsection

