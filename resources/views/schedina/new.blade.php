@extends('layouts.master')
@section('title')
    @lang('translation.wizard')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            @lang('translation.new')
        @endslot
        @slot('title')
            @lang('translation.schedina')
        @endslot
    @endcomponent
    
    <div class="row">
        
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('translation.schedina') - @lang('translation.new')</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form  method="POST" action="{{route('schedina.store')}}" class="form-steps" autocomplete="off" data-sa-confirm="create">
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
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-azienda-info-tab"
                                        data-bs-toggle="pill" data-bs-target="#steparrow-azienda-info" 
                                        type="button" role="tab" aria-controls="steparrow-azienda-info" 
                                        aria-selected="false">@lang('translation.steps.guest_residence')</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="steparrow-gen-info" role="tabpanel"
                                aria-labelledby="steparrow-gen-info-tab">
                                <div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="steparrow-gen-info-email-input">Tipo</label>
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
                                                    <select class="form-control js-example-basic-single" name="relationship" data-placeholder="Seleziona tipo alloggiato">
                                                        <option value="17">CAPO FAMIGLIA</option>
                                                        <option value="18">CAPO GRUPPO</option>
                                                        <option value="16">OSPITE SINGOLO</option>
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
                                        
                                        
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="stay-range-schedina">Periodo soggiorno</label>
                                                <input type="text" class="form-control" id="stay-range-schedina" data-provider="flatpickr" data-date-format="d M, Y" data-range-date placeholder="Seleziona il periodo">
                                                <input type="hidden" name="arrive" id="schedina-arrive-hidden">
                                                <input type="hidden" name="departure" id="schedina-departure-hidden">
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
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                    <select type="text" class="form-control autofill-select" data-autofill="countries" name="oa_country" data-placeholder="Seleziona una Nazione">
                                                        @foreach($nations as $nation)
                                                        <option value="{{ $nation->code }}">{{ $nation->name }}</option>
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
                                                        <option value="{{ $ciudad->code }}">{{ $ciudad->name }}</option>
                                                        @endforeach
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                               
                                                <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="region_az" data-placeholder="Seleziona una Regione">
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
                                                        <option value="{{ $province['sigla_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
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
                                                        <option value="{{ $ciudad->code }}">{{ $ciudad->name }}</option>
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
                                                        <option value="{{ $nation->code }}">{{ $nation->name }}</option>
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
                                                        <option value="{{ $ciudad->code }}">{{ $ciudad->name }}</option>
                                                        @endforeach
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione  </label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="regions" name="or_region" data-placeholder="Seleziona una Regione">
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
                                                        <option value="{{ $province['sigla_provincia'] }}">{{ $province['denominazione_provincia'] }}</option>
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
                                                    <select class="form-control" name="or_typeaway">
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
                                                <label class="form-label">Num.</label>
                                                <input type="text" class="form-control" name="or_num" placeholder="Es. 12, 12B">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label">Int.</label>
                                                <input type="text" class="form-control" name="or_internal" placeholder="Interno, scala, ecc.">
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
                                                        <option value="{{$TypeDoc->code}}">{{$TypeDoc->name}}</option>
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
       document.addEventListener("DOMContentLoaded", function () {
    // Mappa il range soggiorno nei campi hidden arrive/departure
    var inputRange = document.getElementById('stay-range-schedina');
    if (inputRange) {
        var fp = inputRange._flatpickr || flatpickr(inputRange, { mode: 'range', dateFormat: inputRange.getAttribute('data-date-format') || 'd M, Y', locale: 'it' });
        function formatYMD(date){
            const y = date.getFullYear();
            const m = String(date.getMonth()+1).padStart(2,'0');
            const d = String(date.getDate()).padStart(2,'0');
            return `${y}-${m}-${d}`;
        }
        inputRange.addEventListener('change', function(){
            var start = fp && fp.selectedDates[0] ? formatYMD(fp.selectedDates[0]) : '';
            var end = fp && fp.selectedDates[1] ? formatYMD(fp.selectedDates[1]) : '';
            document.getElementById('schedina-arrive-hidden').value = start;
            document.getElementById('schedina-departure-hidden').value = end;
        });
    }
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
    // Quando cambia la Regione, carica le Province
    $('#region-select').on('change', function() {
        var regionCode = $(this).val();
        if (regionCode) {
            http.get('/provinces-by-region', { params: { codice_regione: regionCode } })
              .then(function(response){
                $('#province-select').empty().append('<option value="">Seleziona una Provincia</option>');
                $.each(response.data, function(index, province) {
                    $('#province-select').append($('<option>', { value: province.sigla_provincia, text: province.denominazione_provincia }));
                });
              })
              .catch(function(err){ console.error('Errore nel caricamento delle province:', err); });
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
                $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>');
                $.each(response.data, function(index, cap) {
                    $('#cap-select').append($('<option>', { value: cap.cap, text: cap.cap }));
                });
              })
              .catch(function(err){ console.error('Errore nel caricamento dei CAP:', err); });
        } else {
            $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>');
        }
    });
});
</script>
<!-- Inizializzazione Select2 gestita globalmente; handler AJAX per cascata mantenuti sopra -->
<!-- Vincoli documento centralizzati in app.js tramite data-date-pair/doc -->
@endsection
