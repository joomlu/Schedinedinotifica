@extends('layouts.master')
@section('title')
    @lang('translation.wizard')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            @lang('translation.forms')
        @endslot
        @slot('title')
            @lang('translation.wizard')
        @endslot
    @endcomponent

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('translation.titles.customer-add')</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form  method="POST" action="{{route('customer.store')}}" class="form-steps" autocomplete="off">
                    @csrf 
                        <div class="text-center pt-3 pb-4 mb-1 d-flex justify-content-center">
                            
                        </div>
                        <div class="step-arrow-nav mb-4">

                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill"
                                        data-bs-target="#steparrow-gen-info" type="button" role="tab"
                                        aria-controls="steparrow-gen-info" aria-selected="true">@lang('translation.steps.customer_residence')</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab"
                                        data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button"
                                        role="tab" aria-controls="steparrow-description-info"
                                        aria-selected="false">@lang('translation.steps.anagraphic')</button>
                                </li>
                                <button class="nav-link" id="steparrow-azienda-info-tab"
                                    data-bs-toggle="pill" data-bs-target="#steparrow-azienda-info" 
                                    type="button" role="tab" aria-controls="steparrow-azienda-info" 
                                    aria-selected="false">
                                    @lang('translation.steps.company')
                                </button>

                                
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="steparrow-gen-info" role="tabpanel"
                                aria-labelledby="steparrow-gen-info-tab">
                                <div>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Gruppo</label>
                                                <select class="form-control js-example-basic-single" name="group" data-placeholder="Seleziona gruppo">
                                                    @foreach($groups as $group)
                                                    <option value="{{$group->name}}">{{$group->name}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">@lang('translation.labels.group') obbligatorio</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-4">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">SubGruppo</label>
                                                <select class="form-control js-example-basic-single" name="subgroup" data-placeholder="Seleziona sottogruppo">
                                                @foreach($subgroups as $subgroup)
                                                    <option value="{{$subgroup->name}}">{{$subgroup->name}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">@lang('translation.labels.subgroup') obbligatorio</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">SubGruppo 1</label>
                                                    <select class="form-control js-example-basic-single" name="subgroup1" data-placeholder="Seleziona sottogruppo 2">
                                                    @foreach($subgroups1 as $subgroup1)
                                                    <option value="{{$subgroup1->name}}">{{$subgroup1->name}}</option>
                                                    @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">@lang('translation.labels.subgroup1') obbligatorio</div>
                                                </div>
                                            </div>
                                            
                                    </div>
                                    <div class="row">
                                    <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Tipo Alloggiato</label>
                                                    <select class="form-control js-example-basic-single" name="type_housed" data-placeholder="Seleziona tipologia alloggiato">
                                                        <option value="Hotel K2">Hotel K2</option>
                                                        <option value="Hotel K2">Sub gruppo 1x</option>
                                                    </select>
                                                    <div class="invalid-feedback">@lang('translation.labels.type_housed') obbligatorio</div>
                                                </div>
                                            </div>
                                        
                                        
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Tipo</label>
                                                    <select class="form-control js-example-basic-single" name="type" data-placeholder="Seleziona tipo">
                                                        <option value="M">Dott.</option>
                                                        <option value="F">Famiglia</option>
                                                    </select>
                                                    <div class="invalid-feedback">@lang('translation.labels.type') obbligatorio</div>
                                                </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nome</label>
                                                <input type="text" class="form-control" name="name">
                                                   
                                                <div class="invalid-feedback">@lang('translation.labels.name') obbligatorio</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cognome</label>
                                                <input type="text" class="form-control" name="surname">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Sesso</label>
                                                    <select class="form-control js-example-basic-single" name="grupo" data-placeholder="Seleziona gruppo">
                                                        <option value="M">M</option>
                                                        <option value="F">F</option>
                                                    </select>
                                                    <div class="invalid-feedback">@lang('translation.labels.sex') obbligatorio</div>
                                                </div>
                                        </div>
                                    </div>
                                    

                                    <div class="row">
    <!-- Nación -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="nation-select">Nazione</label>
            <select id="nation-select" class="form-control js-example-basic-single" name="country" data-placeholder="Seleziona una Nazione">
                <option value="">Seleziona una Nazione</option>
                @foreach($nations as $nation)
                    <option value="{{ $nation['denominazione_cittadinanza'] }}">
                        {{ $nation['denominazione_cittadinanza'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Región -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="region-select">Regione</label>
            <select id="region-select" class="form-control js-example-basic-single" name="region" data-placeholder="Seleziona una Regione">
                <option value="">Seleziona una Regione</option>
                @foreach($regions as $region)
                    <option value="{{ $region['codice_regione'] }}">
                        {{ $region['denominazione_regione'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Provincia -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="province-select">Provincia</label>
            <select id="province-select" class="form-control js-example-basic-single" name="province" data-placeholder="Seleziona una Provincia">
                <option value="">Seleziona una Provincia</option>
                <!-- Se llenará mediante AJAX -->
            </select>
        </div>
    </div>

    <!-- CAP -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="cap-select">CAP</label>
            <select id="cap-select" class="form-control js-example-basic-single" name="cap" data-placeholder="Seleziona un CAP">
                <option value="">Seleziona un CAP</option>
                <!-- Se llenará mediante AJAX -->
            </select>
        </div>
    </div>
</div>

<div class="row">
    <!-- Ciudad -->
    <div class="col-lg-3">
        <div class="mb-3">
                                                <label class="form-label" for="city-select">@lang('translation.labels.city')</label>
            <select id="city-select" class="form-control js-example-basic-single" name="city" data-placeholder="Seleziona una città">
                <option value="">Seleziona una città</option>
                <!-- Se llenará mediante AJAX -->
            </select>
        </div>
    </div>




                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control js-example-basic-single" name="typeaway" data-placeholder="Seleziona tipo via">
                                                    @foreach($typestreets as $typestreet)
                                                    <option value="{{$typestreet->name}}">{{$typestreet->name}}</option>
                                                    @endforeach
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada</label>
                                                <input type="text" class="form-control" name="address">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.number')</label>
                                                <input type="text" class="form-control number" name="number">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div> 
                                    
                                    
                                    

                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Email</label>
                                                <input type="text" class="form-control email" name="email">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Telefono</label>
                                                <input type="text" class="form-control phone" name="phone">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Fax</label>
                                                <input type="text" class="form-control" name="fax">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Celular</label>
                                                <input type="text" class="form-control phone" name="cellphone">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione </label>
                                                <textarea type="text" class="form-control" name="observation"></textarea>
                                                   
                                                
                                            </div>
                                        </div>


                                        
                                    
                                    
                                </div>
                                <div class="d-flex align-items-start gap-3 mt-4">
                                <button type="button" class="btn btn-success btn-label right ms-auto nexttab"
                                    data-nexttab="steparrow-description-info">
                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                    @lang('translation.buttons.next')
                                </button>
                                </div>
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane fade" id="steparrow-description-info" role="tabpanel"
    aria-labelledby="steparrow-description-info-tab">

                                <div>
                                <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione (di Nascita) </label>
                                               
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="country_reg" data-placeholder="Seleziona una Nazione">
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation['denominazione_cittadinanza'] }}">{{ $nation['denominazione_cittadinanza'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia (di Nascita) </label>
                                                    <select type="text" class="form-control autofill-select" data-autofill="provinces" name="prov_reg" data-placeholder="Seleziona una Provincia">
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                                   
                                                
                                            </div>
                                        </div>

                                        
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.birth_city')</label>
                                                    <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_reg" data-placeholder="Seleziona una Città">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.citizenship')</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="ciudadania_reg" data-placeholder="Seleziona cittadinanza">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.birth_date')</label>
                                                <input type="text" class="form-control" name="nac_reg" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.doc_type')</label>
                                                    <select class="form-control js-example-basic-single" name="type_doc_reg" data-placeholder="Seleziona tipo documento">
                                                    @foreach($TypeDocs as $TypeDoc)
                                                    <option value="{{$TypeDoc->name}}">{{$TypeDoc->name}}</option>
                                                    @endforeach
                                                    </select>
                                               
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.doc_number')</label>
                                                <input type="text" class="form-control number" name="num_doc_reg">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.issued')</label>
                                                <input type="text" class="form-control" name="date_pub_reg">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.issued_at')</label>
                                                <input type="text" class="form-control" name="expire_reg" data-provider="flatpickr" data-date-format="d M, Y" data-date-pair="doc-reg" data-date-pair-role="start" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.expires_at')</label>
                                                <input type="text" class="form-control" name="released_reg" data-provider="flatpickr" data-date-format="d M, Y" data-date-pair="doc-reg" data-date-pair-role="end" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.observation_reg')</label>
                                                <textarea type="text" class="form-control" name="observation_reg"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="button" class="btn btn-success btn-label right ms-auto nexttab" 
    data-nexttab="steparrow-azienda-info">
    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
    @lang('translation.buttons.next')
</button>

                                </div>
                            </div>
</div>
                           

                            
                        </div>
                        <!-- end tab content -->


                        <div class="tab-pane fade" id="steparrow-azienda-info" role="tabpanel"
    aria-labelledby="steparrow-azienda-info-tab">

                                <div>
                                <div class="row">
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.company')</label>
                                                <input type="text" class="form-control" name="azienda">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.cf')</label>
                                                <input type="text" class="form-control" name="cf_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.pi')</label>
                                                <input type="text" class="form-control" name="pi_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                   
                                    <div class="row">
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control js-example-basic-single" name="typeaway_az" data-placeholder="Seleziona tipo via">
                                                    @foreach($typestreets as $typestreet)
                                                    <option value="{{$typestreet->name}}">{{$typestreet->name}}</option>
                                                    @endforeach
                                                    </select>
                                                
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada </label>
                                                <input type="text" class="form-control" name="address_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.number')</label>
                                                <input type="number" class="form-control number" name="number_az" min="0" step="10000000">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.cap')</label>
                                                <input type="text" class="form-control" name="cap_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.email')</label>
                                                <input type="text" class="form-control" name="email_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.phone')</label>
                                                <input type="text" class="form-control phone" name="phone_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.fax')</label>
                                                <input type="text" class="form-control" name="fax_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.cellphone')</label>
                                                <input type="text" class="form-control" name="cellphone_az">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.country')</label>
                                                    <select type="text" class="form-control js-example-basic-single" name="country_az" data-placeholder="Seleziona una Nazione">
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation['denominazione_cittadinanza'] }}">{{ $nation['denominazione_cittadinanza'] }}</option>
                                                        @endforeach
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.city')</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_az" data-placeholder="Seleziona una Città">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.region')</label>
                                               
                                                <select id="region-select" class="form-control js-example-basic-single" name="region_az" data-placeholder="Seleziona una Regione">
                                                    <option value="">Seleziona una Regione</option>
                                                    @foreach($regions as $region)
                                                        <option value="{{ $region['codice_regione'] }}">
                                                            {{ $region['denominazione_regione'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.province')</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_az" data-placeholder="Seleziona una Provincia">
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                    
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.description')</label>
                                                <textarea type="text" class="form-control" name="desc_az"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">@lang('translation.labels.note')</label>
                                                <textarea type="text" class="form-control" name="nota"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="submit" class="btn btn-success btn-label right ms-auto nexttab nexttab"
                                        data-nexttab="steparrow-description-info-tab"><i
                                            class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>@lang('translation.buttons.save')</button>
                                </div>
                            </div>
</div>
                           

                            
                        </div>
                        <!-- end tab content -->
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
    <script>
    // Selecciona todos los inputs con la clase "number"
    const numberInputs = document.querySelectorAll('input.number');
    const phoneInputs = document.querySelectorAll('input.phone');
    const emailInputs = document.querySelectorAll('input.email');


    numberInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            // Remueve cualquier carácter que no sea numérico
            input.value = input.value.replace(/[^0-9]/g, '');
        });
    });
     // Validación para los teléfonos (permitir números, espacios, paréntesis, guiones)
     phoneInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            // Permitir solo números, espacios, paréntesis, guiones
            input.value = input.value.replace(/[^0-9\-\(\)\s]/g, '');
        });
    });

    // Validación para los emails (chequear formato correcto)
    emailInputs.forEach(input => {
        input.addEventListener('blur', function (e) {
            // Expresión regular para validar email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(input.value)) {
                alert('Per favore, inserisci un indirizzo email valido.');
                input.focus();
            }
        });
    });


    </script>
<script>
$(document).ready(function() {
    // Quando cambia la Regione, carica le Province
    $('#region-select').on('change', function() {
        var regionCode = $(this).val();
        if (regionCode) {
            http.get('/provinces-by-region', { params: { codice_regione: regionCode } })
                .then(function(response){
                    var data = response.data;
                    $('#province-select').empty().append('<option value="">Seleziona una Provincia</option>');
                    $.each(data, function(index, province) {
                        $('#province-select').append(
                            $('<option>', { 
                                value: province.sigla_provincia,
                                text: province.denominazione_provincia
                            })
                        );
                    });
                })
                .catch(function(error){
                    console.error('Errore nel caricamento delle province:', error);
                });
        } else {
            $('#province-select').empty().append('<option value="">Seleziona una Provincia</option>');
        }
    });

    // Quando cambia la Provincia, carica i CAP
    $('#province-select').on('change', function() {
        var provinceCode = $(this).val();
        if (provinceCode) {
            http.get('/cap-by-province', { params: { sigla_provincia: provinceCode } })
                .then(function(response){
                    var data = response.data;
                    $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>');
                    $.each(data, function(index, cap) {
                        $('#cap-select').append(
                            $('<option>', { 
                                value: cap.cap,
                                text: cap.cap
                            })
                        );
                    });
                })
                .catch(function(error){
                    console.error('Errore nel caricamento dei CAP:', error);
                });
        } else {
            $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>');
        }
    });
});
</script>
<!-- Inizializzazione Select2 e cascading handled globalmente + handler AJAX sopra -->

<!-- Vincoli documento centralizzati in app.js tramite data-date-pair/doc-reg -->



@endsection
