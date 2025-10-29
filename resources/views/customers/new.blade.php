@extends('layouts.master')
@section('title')
    @lang('translation.wizard')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Forms
        @endslot
        @slot('title')
            Wizard
        @endslot
    @endcomponent

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ospite (Cliente) - aggiungere</h4>
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
                                        aria-controls="steparrow-gen-info" aria-selected="true">Ospite (Cliente) Residenza</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab"
                                        data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button"
                                        role="tab" aria-controls="steparrow-description-info"
                                        aria-selected="false">Anagrafica</button>
                                </li>
                                <button class="nav-link" id="steparrow-azienda-info-tab"
                                    data-bs-toggle="pill" data-bs-target="#steparrow-azienda-info" 
                                    type="button" role="tab" aria-controls="steparrow-azienda-info" 
                                    aria-selected="false">
                                    Azienda
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
                                                <select class="form-control" name="group">
                                                    @foreach($groups as $group)
                                                    <option value="{{$group->name}}">{{$group->name}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">Please enter an group</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-4">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">SubGruppo</label>
                                                <select class="form-control" name="subgroup">
                                                @foreach($subgroups as $subgroup)
                                                    <option value="{{$subgroup->name}}">{{$subgroup->name}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">Please enter an Subgroup</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">SubGruppo 1</label>
                                                    <select class="form-control" name="subgroup1">
                                                    @foreach($subgroups1 as $subgroup1)
                                                    <option value="{{$subgroup1->name}}">{{$subgroup1->name}}</option>
                                                    @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">Please enter an Subgroup</div>
                                                </div>
                                            </div>
                                            
                                    </div>
                                    <div class="row">
                                    <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Tipo Alloggiato</label>
                                                    <select class="form-control" name="type_housed">
                                                        <option value="Hotel K2">Hotel K2</option>
                                                        <option value="Hotel K2">Sub gruppo 1x</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please enter an Subgroup</div>
                                                </div>
                                            </div>
                                        
                                        
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Tipo</label>
                                                    <select class="form-control" name="type">
                                                        <option value="M">Dott.</option>
                                                        <option value="F">Famiglia</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please enter an group</div>
                                                </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nome</label>
                                                <input type="text" class="form-control" name="name">
                                                   
                                                <div class="invalid-feedback">Please enter an Subgroup</div>
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
                                                    <select class="form-control" name="grupo">
                                                        <option value="M">M</option>
                                                        <option value="F">F</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please enter an group</div>
                                                </div>
                                        </div>
                                    </div>
                                    

                                    <div class="row">
    <!-- Nación -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="nation-select">Nación</label>
            <select id="nation-select" class="form-control" name="country">
                <option value="">Seleccione una Nación</option>
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
            <label class="form-label" for="region-select">Región</label>
            <select id="region-select" class="form-control" name="region">
                <option value="">Seleccione una Región</option>
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
            <select id="province-select" class="form-control" name="province">
                <option value="">Seleccione una Provincia</option>
                <!-- Se llenará mediante AJAX -->
            </select>
        </div>
    </div>

    <!-- CAP -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="cap-select">CAP</label>
            <select id="cap-select" class="form-control" name="cap">
                <option value="">Seleccione un CAP</option>
                <!-- Se llenará mediante AJAX -->
            </select>
        </div>
    </div>
</div>

<div class="row">
    <!-- Ciudad -->
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="form-label" for="city-select">Ciudad</label>
            <select id="city-select" class="form-control" name="city">
                <option value="">Seleccione una ciudad</option>
                <!-- Se llenará mediante AJAX -->
            </select>
        </div>
    </div>




                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control" name="typeaway">
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
                                                    for="steparrow-gen-info-email-input">Num</label>
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
                                    Next
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
                                               
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="country_reg">
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
                                                    <select type="text" class="form-control autofill-select" data-autofill="provinces" name="prov_reg">
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
                                                    for="steparrow-gen-info-email-input">Citta (di Nascita) :</label>
                                                    <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_reg">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="ciudadania_reg">
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
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="text" class="form-control" name="nac_reg" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data di nascita">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Doc. Tipo </label>
                                                    <select class="form-control" name="type_doc_reg">
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
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="text" class="form-control number" name="num_doc_reg">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                <input type="text" class="form-control" name="date_pub_reg">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato il</label>
                                                <input type="text" class="form-control" name="expire_reg" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data scadenza">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input"> Scade il</label>
                                                <input type="text" class="form-control" name="released_reg" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data emissione">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione anagrafica</label>
                                                <textarea type="text" class="form-control" name="observation_reg"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="button" class="btn btn-success btn-label right ms-auto nexttab" 
    data-nexttab="steparrow-azienda-info">
    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
    Next
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
                                                    for="steparrow-gen-info-email-input">Azienda </label>
                                                <input type="text" class="form-control" name="azienda">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">C.F. </label>
                                                <input type="text" class="form-control" name="cf_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">P.I.</label>
                                                <input type="text" class="form-control" name="pi_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                   
                                    <div class="row">
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control" name="typeaway_az">
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
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="number" class="form-control number" name="number_az" min="0" step="10000000">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">CAP :</label>
                                                <input type="text" class="form-control" name="cap_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Email </label>
                                                <input type="text" class="form-control" name="email_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Telefono</label>
                                                <input type="text" class="form-control phone" name="phone_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Fax</label>
                                                <input type="text" class="form-control" name="fax_az">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Celular</label>
                                                <input type="text" class="form-control" name="cellphone_az">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                    <select type="text" class="form-control" name="country_az">
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation['denominazione_cittadinanza'] }}">{{ $nation['denominazione_cittadinanza'] }}</option>
                                                        @endforeach
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_az">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                               
                                                <select id="region-select" class="form-control" name="region_az">
                                                    <option value="">Seleccione una Región</option>
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
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_az">
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
                                                    for="steparrow-gen-info-email-input">Descrizione</label>
                                                <textarea type="text" class="form-control" name="desc_az"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nota</label>
                                                <textarea type="text" class="form-control" name="nota"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="submit" class="btn btn-success btn-label right ms-auto nexttab nexttab"
                                        data-nexttab="steparrow-description-info-tab"><i
                                            class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Salva</button>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
                alert('Por favor, introduce un correo electrónico válido.');
                input.focus();
            }
        });
    });


    </script>
<script>
$(document).ready(function() {
    // Al cambiar la región, se cargan las provincias correspondientes
    $('#region-select').on('change', function() {
        var regionCode = $(this).val();
        if (regionCode) {
            $.ajax({
                url: '/provinces-by-region',
                type: 'GET',
                data: { codice_regione: regionCode },
                success: function(data) {
                    $('#province-select').empty().append('<option value="">Seleccione una Provincia</option>');
                    $.each(data, function(index, province) {
                        $('#province-select').append(
                            $('<option>', { 
                                value: province.sigla_provincia,
                                text: province.denominazione_provincia
                            })
                        );
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error en la petición AJAX de provincias:", status, error);
                }
            });
        } else {
            $('#province-select').empty().append('<option value="">Seleccione una Provincia</option>');
        }
    });

    // Al cambiar la provincia, se cargan los CAP correspondientes
    $('#province-select').on('change', function() {
        var provinceCode = $(this).val();
        if (provinceCode) {
            $.ajax({
                url: '/cap-by-province',
                type: 'GET',
                data: { sigla_provincia: provinceCode },
                success: function(data) {
                    $('#cap-select').empty().append('<option value="">Seleccione un CAP</option>');
                    $.each(data, function(index, cap) {
                        $('#cap-select').append(
                            $('<option>', { 
                                value: cap.cap,
                                text: cap.cap
                            })
                        );
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error en la petición AJAX de CAP:", status, error);
                }
            });
        } else {
            $('#cap-select').empty().append('<option value="">Seleccione un CAP</option>');
        }
    });
});
</script>
<script>
$(document).ready(function() {
    // Inicializar Select2 en cada campo
    $('#nation-select').select2({
        placeholder: "Seleccione una Nación",
        allowClear: true
    });
    $('#region-select').select2({
        placeholder: "Seleccione una Región",
        allowClear: true
    });
    $('#province-select').select2({
        placeholder: "Seleccione una Provincia",
        allowClear: true
    });
    $('#cap-select').select2({
        placeholder: "Seleccione un CAP",
        allowClear: true
    });
    $('#city-select').select2({
        placeholder: "Seleccione una ciudad",
        allowClear: true
    });

    // Al cambiar la región, se cargan las provincias correspondientes
    $('#region-select').on('change', function() {
        var regionCode = $(this).val();
        if (regionCode) {
            $.ajax({
                url: '{{ route("provincesByRegion") }}',
                type: 'GET',
                data: { codice_regione: regionCode },
                success: function(data) {
                    var provinceSelect = $('#province-select');
                    provinceSelect.empty().append('<option value="">Seleccione una Provincia</option>');
                    $.each(data, function(index, province) {
                        provinceSelect.append(
                            $('<option>', { 
                                value: province.sigla_provincia,
                                text: province.denominazione_provincia
                            })
                        );
                    });
                    provinceSelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error("Error en la petición AJAX de provincias:", status, error);
                }
            });
        } else {
            $('#province-select').empty().append('<option value="">Seleccione una Provincia</option>').trigger('change');
        }
    });

    // Al cambiar la provincia, se cargan CAP y ciudades correspondientes
    $('#province-select').on('change', function() {
        var provinceCode = $(this).val();
        if (provinceCode) {
            // Petición para CAP
            $.ajax({
                url: '{{ route("capByProvince") }}',
                type: 'GET',
                data: { sigla_provincia: provinceCode },
                success: function(data) {
                    var capSelect = $('#cap-select');
                    capSelect.empty().append('<option value="">Seleccione un CAP</option>');
                    $.each(data, function(index, cap) {
                        capSelect.append(
                            $('<option>', { 
                                value: cap.cap,
                                text: cap.cap
                            })
                        );
                    });
                    capSelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error("Error en la petición AJAX de CAP:", status, error);
                }
            });

            // Petición para Ciudades
            $.ajax({
                url: '{{ route("citiesByProvince") }}',
                type: 'GET',
                data: { sigla_provincia: provinceCode },
                success: function(data) {
                    var citySelect = $('#city-select');
                    citySelect.empty().append('<option value="">Seleccione una ciudad</option>');
                    $.each(data, function(index, city) {
                        // Puedes usar 'codice_istat' o el nombre de la ciudad según tus necesidades
                        citySelect.append(
                            $('<option>', { 
                                value: city.codice_istat,
                                text: city.denominazione_ita
                            })
                        );
                    });
                    citySelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error("Error en la petición AJAX de ciudades:", status, error);
                }
            });
        } else {
            $('#cap-select').empty().append('<option value="">Seleccione un CAP</option>').trigger('change');
            $('#city-select').empty().append('<option value="">Seleccione una ciudad</option>').trigger('change');
        }
    });
});
</script>



<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/it.js"></script>
<script src="{{ asset('js/flatpickr-init.js') }}"></script>
@endsection
