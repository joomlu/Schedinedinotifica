@extends('layouts.master')
@section('title')
    @lang('translation.wizard')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            @lang('translation.schedina')
        @endslot
        @slot('title')
            @lang('translation.edit')
        @endslot
    @endcomponent
    
    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('translation.schedina')</h4>
                </div><!-- end card header -->
                <div class="card-body">
                <form method="POST" action="{{route('schedina.update', $schedina->id)}}" data-sa-confirm="update">
                                                    @csrf 
                                                    @method('PUT') 
                        <div class="text-center pt-3 pb-4 mb-1 d-flex justify-content-center">
                            
                        </div>
                        <div class="step-arrow-nav mb-4">

                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill"
                                        data-bs-target="#steparrow-gen-info" type="button" role="tab"
                                        aria-controls="steparrow-gen-info" aria-selected="true">@lang('translation.schedina')</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab"
                                        data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button"
                                        role="tab" aria-controls="steparrow-description-info"
                                        aria-selected="false">@lang('translation.steps.guest_anagraphic')</button>
                                </li>
                                <button class="nav-link" id="steparrow-azienda-info-tab"
                                    data-bs-toggle="pill" data-bs-target="#steparrow-azienda-info" 
                                    type="button" role="tab" aria-controls="steparrow-azienda-info" 
                                    aria-selected="false">
                                    @lang('translation.steps.guest_residence')
                                </button>

                                
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="steparrow-gen-info" role="tabpanel"
                                aria-labelledby="steparrow-gen-info-tab">
                                <div>
                                    <div class="row">
                                    <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Tipo</label>
                                                        <select class="form-control js-example-basic-single" name="type" data-placeholder="Seleziona tipo">
                                                        <option value="{{$schedina->name}}">{{$schedina->type}}</option>
                                                        @foreach($titles as $title)
                                                        <option value="{{$title->name}}">{{$title->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    
                                                </div>
                                        </div>
                                    <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Clienti</label>
                                               <input type="text" id="search" value="{{$schedina->name}}" class="form-control" name="name">
                                               <ul id="results" class="list-group mt-2" style="display: none;"></ul>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cognome</label>
                                                <input type="text" class="form-control" value="{{$schedina->surname}}" id="surname" name="surname">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Sesso</label>
                                                        <select class="form-control js-example-basic-single" name="sex" data-placeholder="Seleziona sesso">
                                                        <option value="{{$schedina->sex}}">{{$schedina->sex}}</option>
                                                        <option value="M">M</option>
                                                        <option value="F">F</option>
                                                        </select>
                                                    
                                                </div>
                                        </div>
                                            
                                    </div>
                                    <div class="row">
                                    <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Tipo Alloggiato</label>
                                                    <select class="form-control js-example-basic-single" name="relationship" data-placeholder="Seleziona relazione">
                                                    <option value="{{$schedina->relationship}}">{{$schedina->relationship}}</option>
                                                        <option value="CAPO FAMIGLIA">CAPO FAMIGLIA</option>
                                                        <option value="CAPO GRUPPO">CAPO GRUPPO</option>
                                                        <option value="OSPITE SINGOLO">OSPITE SINGOLO</option>
                                                    </select>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-lg-5">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Esente</label>
                                                    <select class="form-control js-example-basic-single" name="exent" data-placeholder="Seleziona esenzione">
                                                        <option value="{{$schedina->exent}}">{{$schedina->exent}}</option>
                                                        <option value="NO">No</option>
                                                        <option value="Personale">Personale</option>
                                                        <option value="Acompagnatore Turistico">Acompagnatore Turistico</option>
                                                        <option value="Autista">Autista</option>
                                                        <option value="Forze armate in seervizio">Forze armate in seervizio</option>
                                                        <option value="Accompagnatori per pazienti">Accompagnatori per pazienti</option>
                                                        <option value="Residente in hotel">Residente in hotel</option>
                                                        <option value="Residente nel comune">Residente nel comune</option>
                                                    </select>
                                                   
                                                </div>
                                        </div>    
                                        
                                    </div>
                                    <div class="row">
                                        
                                        
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="stay-range-schedina-edit">Periodo soggiorno</label>
                                                <input type="text" class="form-control" id="stay-range-schedina-edit" data-provider="flatpickr" data-date-format="d M, Y" data-range-date placeholder="Seleziona il periodo">
                                                <input type="hidden" name="arrive" id="schedina-edit-arrive-hidden" value="{{$schedina->arrive}}">
                                                <input type="hidden" name="departure" id="schedina-edit-departure-hidden" value="{{$schedina->departure}}">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Quantità Persone</label>
                                                        <input type="number" class="form-control" value="{{$schedina->cant_people}}" name="cant_people">
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-1">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Camera</label>
                                                        <input type="number" class="form-control" name="room" value="{{$schedina->room}}">
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-1">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Letti</label>
                                                        <input type="number" class="form-control" name="beds" value="{{$schedina->beds}}">
                                                    
                                                </div>
                                        </div>
                                    </div>
                                    

                                    <div class="row">
                                    <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione</label>
                                                <textarea type="text" class="form-control" name="observation">{{$schedina->observation}}</textarea>
                                                   
                                            
                                    </div>
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
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="oa_country" data-placeholder="Seleziona una Nazione">
                                                    @if(!empty($schedina->oa_country))
                                                        <option value="{{$schedina->oa_country}}" selected>{{$schedina->oa_country}}</option>
                                                    @endif
                                                </select>
                                            </div> 
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Città</label>
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="oa_city" data-placeholder="Seleziona una Città">
                                                    @if(!empty($schedina->oa_city))
                                                        <option value="{{$schedina->oa_city}}" selected>{{$schedina->oa_city}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="mb-3 d-none">
                                                <label class="form-label">Città (manuale)</label>
                                                <input id="or_city_manual" type="text" class="form-control" name="or_city" placeholder="Inserisci Città">
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                                <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="oa_region" data-placeholder="Seleziona una Regione">
                                                    @if(!empty($schedina->oa_region))
                                                        <option value="{{$schedina->oa_region}}" selected>{{$schedina->oa_region}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="mb-3 d-none">
                                                <label class="form-label">Regione (manuale)</label>
                                                <input id="or_region_manual" type="text" class="form-control" name="or_region" placeholder="Inserisci Regione">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="oa_prov" data-placeholder="Seleziona una Provincia">
                                                    @if(!empty($schedina->oa_prov))
                                                        <option value="{{$schedina->oa_prov}}" selected>{{$schedina->oa_prov}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza </label>
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="oa_city_nac" data-placeholder="Seleziona cittadinanza">
                                                    @if(!empty($schedina->oa_city_nac))
                                                        <option value="{{$schedina->oa_city_nac}}" selected>{{$schedina->oa_city_nac}}</option>
                                                    @endif
                                                </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="text" class="form-control" name="oa_date_nac"  value="{{$schedina->oa_date_nac}}" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona la data">
                                                   
                                                
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
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label">Nazione</label>
                                                <select id="or_country" class="form-control" name="or_country" data-placeholder="Seleziona una Nazione">
                                                    @if(!empty($schedina->or_country))
                                                        <option value="{{$schedina->or_country}}" selected>{{$schedina->or_country}}</option>
                                                    @endif
                                                </select>
                                            </div> 
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label">Città</label>
                                                <select id="or_city" class="form-control" name="or_city" data-placeholder="Seleziona una Città">
                                                    @if(!empty($schedina->or_city))
                                                        <option value="{{$schedina->or_city}}" selected>{{$schedina->or_city}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label">Regione</label>
                                                <select id="or_region" class="form-control" name="or_region" data-placeholder="Seleziona una Regione">
                                                    @if(!empty($schedina->or_region))
                                                        <option value="{{$schedina->or_region}}" selected>{{$schedina->or_region}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label">Provincia</label>
                                                <select id="or_prov" class="form-control" name="or_prov" data-placeholder="Seleziona una Provincia">
                                                    @if(!empty($schedina->or_prov))
                                                        <option value="{{$schedina->or_prov}}" selected>{{$schedina->or_prov}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="mb-3 d-none">
                                                <label class="form-label">Provincia (manuale)</label>
                                                <input id="or_prov_manual" type="text" class="form-control" name="or_prov" placeholder="Inserisci Provincia">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label">CAP</label>
                                                <select id="or_cap" class="form-control" name="or_cap" data-placeholder="Seleziona CAP">
                                                    @if(!empty($schedina->or_cap))
                                                        <option value="{{$schedina->or_cap}}" selected>{{$schedina->or_cap}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="mb-3 d-none">
                                                <label class="form-label">CAP (manuale)</label>
                                                <input id="or_cap_manual" type="text" class="form-control" name="or_cap" placeholder="Inserisci CAP" maxlength="10" pattern="^[0-9]{4,5}$" title="Inserisci un CAP valido (4-5 cifre)">
                                            </div>
                                            <div id="or_address_inline" class="form-text text-muted small mt-1"></div>
                                        </div>
                                        
                                
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select id="or_typeaway" class="form-control js-example-basic-single" name="or_typeaway" data-placeholder="Seleziona tipo via">
                                                    <option value="{{$schedina->or_typeaway}}">{{$schedina->or_typeaway}}</option>
                                                        @foreach($typestreets as $typestreet)
                                                        <option value="{{$typestreet->name}}">{{$typestreet->name}}</option>
                                                        @endforeach
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada </label>
                                                <input id="or_address" type="text" class="form-control" name="or_address" value="{{$schedina->or_address}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label">Num.</label>
                                                <input id="or_num" type="text" class="form-control" name="or_num" value="{{$schedina->or_num}}">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label">Int.</label>
                                                <input id="or_internal" type="text" class="form-control" name="or_internal" value="{{$schedina->or_internal}}" placeholder="Interno, scala, ecc.">
                                            </div>
                                        </div>
                                        
                                        </div>
                                    <hr>
                                    <div class="note">Riepilogo selezione:</div>
                                    <ul id="or_geo_summary" class="mt-2 mb-3 small text-muted">
                                        <li>Stato: —</li>
                                        <li>Regione: —</li>
                                        <li>Provincia, Città, CAP: —, —, —</li>
                                        <li>Modalità: —</li>
                                        <li>Tipo Via: —</li>
                                        <li>Strada: —</li>
                                        <li>Num.: —</li>
                                        <li>Int.: —</li>
                                    </ul>
                                    <div class="row">
                                        
                                       
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Documento</label>
                                                    <select class="form-control js-example-basic-single" name="or_doctype" data-placeholder="Seleziona tipo documento">
                                                    <option value="{{$schedina->or_doctype}}">{{$schedina->or_doctype}}</option>
                                                        @foreach($TypeDocs as $TypeDoc)
                                                        <option value="{{$TypeDoc->name}}">{{$TypeDoc->name}}</option>
                                                        @endforeach
                                                    </select>
                                                
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Numero</label>
                                                <input type="text" class="form-control" name="or_doc" value="{{$schedina->or_doc}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato il</label>
                                                <input type="text" class="form-control" name="or_published_date" value="{{$schedina->or_published_date}}" data-provider="flatpickr" data-date-format="d M, Y" data-date-pair="doc" data-date-pair-role="start" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Scade il</label>
                                                <input type="text" class="form-control" name="or_expire" value="{{$schedina->or_expire}}" data-provider="flatpickr" data-date-format="d M, Y" data-date-pair="doc" data-date-pair-role="end" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                    <select class="form-control js-example-basic-single" name="or_published" data-placeholder="Ente rilasciante">
                                                    <option value="{{$schedina->or_published}}">{{$schedina->or_published}}</option>
                                                        <option value="Dal comune di">Dal comune di</option>
                                                        <option value="Dalla Motorizazione di">Dalla Motorizazione di</option>
                                                        <option value="Prefetto">Prefetto</option>
                                                        <option value="Rilasciato da">Rilasciato da</option>
                                                        </select>
                                                   
                                                
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
    <script src="{{ asset('js/components/geo-select.js') }}?v={{ @filemtime(public_path('js/components/geo-select.js')) }}"></script>
    <script src="{{ asset('js/pages/schedina-edit.js') }}?v={{ @filemtime(public_path('js/pages/schedina-edit.js')) }}"></script>
    <!-- Vincoli documento centralizzati in app.js tramite data-date-pair/doc -->
@endsection