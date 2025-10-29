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
                    <h4 class="card-title mb-0">Clienti - Edit</h4>
                </div><!-- end card header -->
                <div class="card-body">
                
                    <form  method="POST" action="{{route('customer.update', $customer->id)}}" class="form-steps" autocomplete="off">
                    @csrf 
                    @method('PUT') 
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
                                                <option value="{{$customer->group}}">{{$customer->group}}</option>
                                                    @foreach($groups as $group)
                                                    <option value="{{$group->name}}">{{$group->name}}</option>
                                                    @endforeach
                                                </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-4">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">SubGruppo</label>
                                                <select class="form-control" name="subgroup">
                                                <option value="{{$customer->subgroup}}">{{$customer->subgroup}}</option>
                                                @foreach($subgroups as $subgroup)
                                                    <option value="{{$subgroup->name}}">{{$subgroup->name}}</option>
                                                    @endforeach
                                                </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">SubGruppo 1</label>
                                                    <select class="form-control" name="subgroup1">
                                                    <option value="{{$customer->subgroup1}}">{{$customer->subgroup1}}</option>    
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
                                                    <option value="{{$customer->type_housed}}" selected>{{$customer->type_housed}}</option> 
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
                                                    <option value="{{$customer->type}}" selected>{{$customer->type}}</option> 
                                                        <option value="M">Dott.</option>
                                                        <option value="F">Famiglia</option>
                                                    </select>
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nome</label>
                                                <input type="text" class="form-control" name="name" value="{{$customer->name}}">
                                                   
                                                <div class="invalid-feedback">Please enter an Subgroup</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cognome</label>
                                                <input type="text" class="form-control" name="surname" value="{{$customer->surname}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Sesso</label>
                                                    <select class="form-control" name="grupo">
                                                        <option value="{{$customer->grupo}}" selected>{{$customer->grupo}}</option>
                                                        <option value="M">M</option>
                                                        <option value="F">F</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please enter an group</div>
                                                </div>
                                        </div>
                                    </div>
                                    

                                    <div class="row">
                                    <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                <input type="text" class="form-control" name="country" value="{{$customer->country}}">
                                                   
                                            <div class="invalid-feedback">Please enter an Subgroup</div>
                                    </div>
                                    </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                <input type="text" class="form-control" name="city" value="{{$customer->city}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                                <input type="text" class="form-control" name="region" value="{{$customer->region}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <input type="text" class="form-control" name="province" value="{{$customer->province}}">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="row">
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cap</label>
                                                <input type="text" class="form-control" name="cap" value="{{$customer->cap}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                <input type="text" class="form-control" name="typeaway" value="{{$customer->typeaway}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada</label>
                                                <input type="text" class="form-control" name="address" value="{{$customer->address}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num</label>
                                                <input type="text" class="form-control" name="number" value="{{$customer->number}}">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div> 
                                    
                                    
                                    

                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Email</label>
                                                <input type="text" class="form-control" name="email" value="{{$customer->email}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Telefono</label>
                                                <input type="text" class="form-control" name="phone" value="{{$customer->phone}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Fax</label>
                                                <input type="text" class="form-control" name="fax" value="{{$customer->fax}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Celular</label>
                                                <input type="text" class="form-control" name="cellphone" value="{{$customer->cellphone}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione </label>
                                                <textarea type="text" class="form-control" name="observation">{{$customer->observation}}</textarea>
                                                   
                                                
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
                                                <input type="text" class="form-control" name="country_reg"  value="{{$customer->country_reg}}">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta (di Nascita) :</label>
                                                <input type="text" class="form-control" name="city_reg"  value="{{$customer->city_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia (di Nascita) </label>
                                                <input type="text" class="form-control" name="prov_reg"  value="{{$customer->prov_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza</label>
                                                <input type="text" class="form-control" name="ciudadania_reg"  value="{{$customer->ciudadania_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="text" class="form-control" name="nac_reg"  value="{{$customer->nac_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Doc. Tipo </label>
                                                <input type="text" class="form-control" name="type_doc_reg"  value="{{$customer->type_doc_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="text" class="form-control" name="num_doc_reg"  value="{{$customer->num_doc_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                <input type="text" class="form-control" name="date_pub_reg" value="{{$customer->date_pub_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato il</label>
                                                <input type="date" class="form-control" name="expire_reg" value="{{$customer->expire_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input"> Scade il</label>
                                                <input type="date" class="form-control" name="released_reg" value="{{$customer->released_reg}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione anagrafica</label>
                                                <textarea type="text" class="form-control" name="observation_reg">{{$customer->observation_reg}}</textarea>
                                                   
                                                
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
                                                <input type="text" class="form-control" name="azienda" value="{{$customer->azienda}}">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">C.F. </label>
                                                <input type="text" class="form-control" name="cf_az" value="{{$customer->cf_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">P.I.</label>
                                                <input type="text" class="form-control" name="pi_az" value="{{$customer->pi_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                   
                                    <div class="row">
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                <input type="text" class="form-control" name="typeaway_az" value="{{$customer->typeaway_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada </label>address_az
                                                <input type="text" class="form-control" name="address_az"  value="{{$customer->address_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="text" class="form-control" name="number_az" value="{{$customer->number_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">CAP :</label>
                                                <input type="text" class="form-control" name="cap_az" value="{{$customer->cap_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Email </label>
                                                <input type="text" class="form-control" name="email_az" value="{{$customer->email_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Telefono</label>
                                                <input type="text" class="form-control" name="phone_az" value="{{$customer->phone_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Fax</label>
                                                <input type="text" class="form-control" name="fax_az" value="{{$customer->fax_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Celular</label>
                                                <input type="text" class="form-control" name="cellphone_az" value="{{$customer->cellphone_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                <input type="text" class="form-control" name="country_az" value="{{$customer->country_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                <input type="text" class="form-control" name="city_az" value="{{$customer->city_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                                <input type="text" class="form-control" name="region_az" value="{{$customer->region_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <input type="text" class="form-control" name="province_az" value="{{$customer->province_az}}">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                    
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Descrizione</label>
                                                <textarea type="text" class="form-control" name="desc_az" value="{{$customer->desc_az}}"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nota</label>
                                                <textarea type="text" class="form-control" name="nota">{{$customer->nota}}</textarea>
                                                   
                                                
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
@endsection
