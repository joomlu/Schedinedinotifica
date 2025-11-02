@extends('layouts.master')
@section('title')
    @lang('translation.wizard')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Schedina
        @endslot
        @slot('title')
            edit
        @endslot
    @endcomponent
    
    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Schedina</h4>
                </div><!-- end card header -->
                <div class="card-body">
                <form method="POST" action="{{route('schedina.update', $schedina->id)}}">
                                                    @csrf 
                                                    @method('PUT') 
                        <div class="text-center pt-3 pb-4 mb-1 d-flex justify-content-center">
                            
                        </div>
                        <div class="step-arrow-nav mb-4">

                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill"
                                        data-bs-target="#steparrow-gen-info" type="button" role="tab"
                                        aria-controls="steparrow-gen-info" aria-selected="true">Schedina</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab"
                                        data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button"
                                        role="tab" aria-controls="steparrow-description-info"
                                        aria-selected="false">Ospite - Anagrafica</button>
                                </li>
                                <button class="nav-link" id="steparrow-azienda-info-tab"
                                    data-bs-toggle="pill" data-bs-target="#steparrow-azienda-info" 
                                    type="button" role="tab" aria-controls="steparrow-azienda-info" 
                                    aria-selected="false">
                                    Ospite - Residenza
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
                                                        <select class="form-control" name="type">
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
                                                        <select class="form-control" name="sex">
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
                                                    <select class="form-control" name="relationship">
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
                                                    <select class="form-control" name="exent">
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
                                        
                                        
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Arrivo</label>
                                                <input type="date" class="form-control" value="{{$schedina->arrive}}" name="arrive" data-provider="flatpickr" data-date-format="{{ config('app.date.backend_format') }}" data-altFormat="{{ config('app.date.display_format') }}">
                                                   
                                               
                                            </div>
                                        </div>
                                       
                                        <div class="col-lg-3">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Partenza</label>
                                                        <input type="date" class="form-control" value="{{$schedina->departure}}" name="departure" data-provider="flatpickr" data-date-format="{{ config('app.date.backend_format') }}" data-altFormat="{{ config('app.date.display_format') }}">
                                                    
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
                                    Next
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
                                                    for="steparrow-gen-info-email-input">Nazione  </label>
                                                <input type="text" class="form-control" name="oa_country" value="{{$schedina->oa_country}}">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta </label>
                                                <input type="text" class="form-control" name="oa_city"  value="{{$schedina->oa_city}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione  </label>
                                                <input type="text" class="form-control" name="oa_region" value="{{$schedina->oa_region}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <input type="text" class="form-control" name="oa_prov" value="{{$schedina->oa_prov}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza </label>
                                                <input type="text" class="form-control" name="oa_city_nac" value="{{$schedina->oa_city_nac}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="date" class="form-control" name="oa_date_nac"  value="{{$schedina->oa_date_nac}}" data-provider="flatpickr" data-date-format="{{ config('app.date.backend_format') }}" data-altFormat="{{ config('app.date.display_format') }}">
                                                   
                                                
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
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione  </label>
                                                <input type="text" class="form-control" name="or_country" value="{{$schedina->or_country}}">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta </label>
                                                <input type="text" class="form-control" name="or_city" value="{{$schedina->or_city}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione  </label>
                                                <input type="text" class="form-control" name="or_region" value="{{$schedina->or_region}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <input type="text" class="form-control" name="or_prov" value="{{$schedina->or_prov}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">CAP :</label>
                                                <input type="text" class="form-control" name="or_cap" value="{{$schedina->or_cap}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control" name="or_typeaway">
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
                                                <input type="text" class="form-control" name="or_address" value="{{$schedina->or_address}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="number" class="form-control" name="or_num" value="{{$schedina->or_num}}">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        </div>
                                    <div class="row">
                                        
                                       
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Documento</label>
                                                    <select class="form-control" name="or_doctype">
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
                                                <input type="date" class="form-control" name="or_published_date" value="{{$schedina->or_published_date}}" data-provider="flatpickr" data-date-format="{{ config('app.date.backend_format') }}" data-altFormat="{{ config('app.date.display_format') }}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Scade il</label>
                                                <input type="date" class="form-control" name="or_expire" value="{{$schedina->or_expire}}" data-provider="flatpickr" data-date-format="{{ config('app.date.backend_format') }}" data-altFormat="{{ config('app.date.display_format') }}">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                    <select class="form-control" name="or_published">
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
    <script>
       document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search");
    const surnameInput = document.getElementById("surname");
    const resultsContainer = document.getElementById("results");

    searchInput.addEventListener("input", function () {
        const query = searchInput.value;

        if (query.length > 2) { // Buscar después de 3 caracteres
            fetch(`/search_customers?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = "";
                    if (data.length > 0) {
                        resultsContainer.style.display = "block";
                        data.forEach(item => {
                            const li = document.createElement("li");
                            li.classList.add("list-group-item");
                            li.textContent = item.name + ' ' + item.surname; // Cambiar a lo que quieras mostrar
                            li.addEventListener("mousedown", () => { // Usar mousedown para evitar el blur prematuro
                                searchInput.value = item.name;
                                surnameInput.value = item.surname || ""; // Agrega el surname
                                resultsContainer.style.display = "none";
                            });
                            resultsContainer.appendChild(li);
                        });
                    } else {
                        resultsContainer.style.display = "none";
                    }
                })
                .catch(error => console.error("Error en la búsqueda:", error));
        } else {
            resultsContainer.style.display = "none";
        }
    });

    // Ocultar la lista cuando se pierde el foco del input
    searchInput.addEventListener("blur", function () {
        // Usar un pequeño retraso para que el evento mousedown se registre primero
        setTimeout(() => {
            resultsContainer.style.display = "none";
        }, 100);
    });

    // Opcional: evitar que la lista desaparezca si el mouse está sobre ella
    resultsContainer.addEventListener("mousedown", (e) => {
        e.preventDefault(); // Previene que el blur se active al hacer clic en la lista
    });
});


</script>
@endsection