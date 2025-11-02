@extends('layouts.master')
@section('title')
    @lang('translation.componenti')
@endsection
@section('content')
    @component('components.breadcrumb')
    @slot('li_1')
    @lang('translation.components')
        @endslot
        @slot('title')
    @lang('translation.new') 
        @endslot
    @endcomponent

    

      
        <div class="row">

        
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">@lang('translation.titles.components-add')</h4>
                        
                    </div><!-- end card header -->
                    <form  method="POST" action="{{route('componenti.store')}}">
                    @csrf 
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="valueInput" class="form-label">@lang('translation.labels.type_housed')</label>
                                        <select type="text" class="form-control js-example-basic-single" name="relationship" data-placeholder="Seleziona tipo alloggiato">
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
                                        <select type="text" class="form-control js-example-basic-single" name="sex" data-placeholder="Seleziona sesso">
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                
                                
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.country')</label>
                                       
                                        <select type="text" class="form-control autofill-select" data-autofill="countries" name="country" data-placeholder="Seleziona una Nazione">
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
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.province')</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_nac" data-placeholder="Seleziona una Provincia">
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.region')</label>
                                        
                                        <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="regione" data-placeholder="Seleziona una Regione">
                                                    <option value="">Seleziona una Regione</option>
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
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.city')</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city" data-placeholder="Seleziona una Città">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.typeaway')</label>
                                        <select type="text" class="form-control js-example-basic-single" name="tipeaway" data-placeholder="Seleziona tipo via">
                                            @foreach($typeaway as $typestreet)
                                            <option value="{{$typestreet->name}}">{{$typestreet->name}}</option>
                                            @endforeach
                                        </select> 
                                        
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.address')</label>
                                        <input type="text" class="form-control" name="address">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.number')</label>
                                        <input type="text" class="form-control" name="number">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.cap')</label>
                                        <input type="text" class="form-control" name="cap">
                                    </div>
                                    </div>
                               
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">@lang('translation.labels.birth_date')</label>
                                        <input type="text" class="form-control" name="date_nac" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-4">
                                <div>
                                        <label for="basiInput" class="form-label">Esente</label>
                                        <select type="text" class="form-control" name="exent">
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
       
    </div> <!-- container-fluid -->
</div><!-- End Page-content -->



@endsection

