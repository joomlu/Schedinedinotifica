@extends('layouts.master')
@section('title')
@lang('translation.arrivals')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            @lang('translation.new')
        @endslot
        @slot('title')
            @lang('translation.arrivals')
        @endslot
    @endcomponent
    
    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('translation.arrivals') - @lang('translation.new')</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form  method="POST" action="{{route('arrival.store')}}" class="form-steps" autocomplete="off" data-sa-confirm="create">
                    @csrf 
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
                                               <input type="text" id="search" class="form-control" name="name">
                                              <input type="hidden" id="customer_id" name="customer_id"> 
                                               <ul id="results" class="list-group mt-2" style="display: none;"></ul>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cognome</label>
                                                <input type="text" class="form-control" id="surname" name="surname">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Sesso</label>
                                                        <select class="form-control js-example-basic-single" name="sex" data-placeholder="Seleziona sesso">
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
                                                    for="steparrow-gen-info-email-input">Periodo soggiorno</label>
                                                <input type="text" class="form-control" id="stay-range" data-provider="flatpickr" data-date-format="d M, Y" data-range-date placeholder="Seleziona il periodo">
                                                <input type="hidden" name="arrive" id="arrive-hidden">
                                                <input type="hidden" name="departure" id="departure-hidden">
                                                   
                                               
                                            </div>
                                        </div>
                                       
                                        <div class="col-lg-3">
                                                <div class="mb-3">
                        <label class="form-label"
                            for="steparrow-gen-info-email-input">&nbsp;</label>
                            <input type="text" class="form-control" disabled value="Seleziona un periodo sopra">
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Quantità Persone</label>
                                                        <input type="number" class="form-control" name="cant_people">
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-1">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Camera</label>
                                                        <input type="number" class="form-control" name="room">
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-1">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Letti</label>
                                                        <input type="number" class="form-control" name="beds">
                                                    
                                                </div>
                                        </div>
                                    </div>
                                    

                                    <div class="row">
                                    <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione</label>
                                                <textarea type="text" class="form-control" name="observation"></textarea>
                                                   
                                            
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
                                                    for="steparrow-gen-info-email-input">Nazione  </label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="oa_country" data-placeholder="Seleziona una Nazione">
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
                                            
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="oa_city" data-placeholder="Seleziona una Città">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione  </label>
                                                
                                                <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="oa_region" data-placeholder="Seleziona una Regione">
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
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                          
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="oa_prov" data-placeholder="Seleziona una Provincia">
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
                                                    for="steparrow-gen-info-email-input">Cittadinanza </label>
                                               
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="oa_city_nac" data-placeholder="Seleziona una Città">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="text" class="form-control" name="oa_date_nac" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona la data">
                                                   
                                                
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
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione  </label>
                                               
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="or_country" data-placeholder="Seleziona una Nazione">
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
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="or_city" data-placeholder="Seleziona una Città">
                                                    @foreach($ciudades as $ciudad)
                                                        <option value="{{ $ciudad['denominazione_ita'] }}">{{ $ciudad['denominazione_ita'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione  </label>

                                                <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="or_region" data-placeholder="Seleziona una Regione">
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
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="or_prov" data-placeholder="Seleziona una Provincia">
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province['denominazione_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">CAP :</label>
                                                <input type="text" class="form-control" name="or_cap" maxlength="10" pattern="^[0-9]{4,5}$" title="Inserisci un CAP valido (4-5 cifre)">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control js-example-basic-single" name="or_typeaway" data-placeholder="Seleziona tipo via">
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
                                                <input type="text" class="form-control" name="or_address">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="number" class="form-control" name="or_num">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        </div>
                                    <div class="row">
                                        
                                       
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Documento</label>
                                                    <select class="form-control js-example-basic-single" name="or_doctype" data-placeholder="Seleziona tipo documento">
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
                                                <input type="text" class="form-control" name="or_doc">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato il</label>
                                                <input type="text" class="form-control" name="or_published_date" data-provider="flatpickr" data-date-format="d M, Y" data-date-pair="doc" data-date-pair-role="start" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Scade il</label>
                                                <input type="text" class="form-control" name="or_expire" data-provider="flatpickr" data-date-format="d M, Y" data-date-pair="doc" data-date-pair-role="end" placeholder="Seleziona la data">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                    <select class="form-control js-example-basic-single" name="or_published" data-placeholder="Ente rilasciante">
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
    <script>
// Popola hidden arrive/departure quando si seleziona un range con flatpickr
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('stay-range');
    if (input) {
        var fp = input._flatpickr;
        // se flatpickr non è ancora inizializzato dai plugin di app.js, inizializza qui
        if (!fp) {
            fp = flatpickr(input, {
                mode: 'range',
                dateFormat: input.getAttribute('data-date-format') || 'd M, Y',
                locale: 'it'
            });
        }
            function formatForBackend(date){
                // Backend atteso presumibilmente Y-m-d
                const y = date.getFullYear();
                const m = String(date.getMonth()+1).padStart(2,'0');
                const d = String(date.getDate()).padStart(2,'0');
                return `${y}-${m}-${d}`;
            }
        input.addEventListener('change', function(){
            var dates = (input.value || '').split(' to ');
            if (fp && fp.selectedDates && fp.selectedDates.length) {
                var start = fp.selectedDates[0] || null;
                var end = fp.selectedDates[1] || null;
                document.getElementById('arrive-hidden').value = start ? formatForBackend(start) : '';
                document.getElementById('departure-hidden').value = end ? formatForBackend(end) : '';
            } else if (dates.length === 2) {
                // fallback parsing testuale
                document.getElementById('arrive-hidden').value = dates[0].trim();
                document.getElementById('departure-hidden').value = dates[1].trim();
            }
        });
    }
});

       document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search");
    const surnameInput = document.getElementById("surname");
    const customeridInput = document.getElementById("customer_id");
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
                                customeridInput.value = item.id;
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
<!-- Vincoli documento centralizzati in app.js tramite data-date-pair/doc -->
@endsection
