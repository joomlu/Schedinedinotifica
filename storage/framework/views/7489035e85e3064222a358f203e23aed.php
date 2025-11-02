
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.wizard'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Nuova
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Schedina
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>
    
    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Schedina - aggiungere</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form  method="POST" action="<?php echo e(route('schedina.store')); ?>" class="form-steps" autocomplete="off">
                    <?php echo csrf_field(); ?> 
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
                                                        <?php $__currentLoopData = $titles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($title->name); ?>"><?php echo e($title->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                        <select class="form-control" name="sex">
                                                        <option value="1">M</option>
                                                        <option value="2">F</option>
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
                                                    <select class="form-control" name="exent">
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
                                                <input type="date" class="form-control" name="arrive">
                                                   
                                               
                                            </div>
                                        </div>
                                       
                                        <div class="col-lg-3">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Partenza</label>
                                                        <input type="date" class="form-control" name="departure">
                                                    
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
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                    <select type="text" class="form-control autofill-select" data-autofill="countries" name="oa_country">
                                                        <?php $__currentLoopData = $nations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($nation->code); ?>"><?php echo e($nation->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="oa_city"> 
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad->code); ?>"><?php echo e($ciudad->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                               
                                                <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="region_az">
                                                    <option value="">Seleccione una Región</option>
                                                    <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($region['codice_regione']); ?>">
                                                            <?php echo e($region['denominazione_regione']); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="oa_prov">
                                                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($province['sigla_provincia']); ?>"><?php echo e($province['denominazione_provincia']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza </label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="oa_city_nac">
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad->code); ?>"><?php echo e($ciudad->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div> 
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="date" class="form-control" name="oa_date_nac">
                                                   
                                                
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
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="countries" name="or_country">
                                                        <?php $__currentLoopData = $nations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($nation->code); ?>"><?php echo e($nation->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                                                    </select>
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta </label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="or_city">
                                                        <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad->code); ?>"><?php echo e($ciudad->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione  </label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="regions" name="or_region">
                                                <option value="">Seleccione una Región</option>
                                                    <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($region['codice_regione']); ?>">
                                                            <?php echo e($region['denominazione_regione']); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                               
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="or_prov"> 
                                                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($province['sigla_provincia']); ?>"><?php echo e($province['denominazione_provincia']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">CAP :</label>
                                                <input type="text" class="form-control" name="or_cap">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                
                                        <div class="col-lg-2">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control" name="or_typeaway">
                                                        <?php $__currentLoopData = $typestreets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typestreet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($typestreet->name); ?>"><?php echo e($typestreet->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                    <select class="form-control" name="or_doctype">
                                                        <?php $__currentLoopData = $TypeDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $TypeDoc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($TypeDoc->code); ?>"><?php echo e($TypeDoc->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                <input type="date" class="form-control" name="or_published_date">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Scade il</label>
                                                <input type="date" class="form-control" name="or_expire">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                    <select class="form-control" name="or_published">
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
    
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/js/pages/form-wizard.init.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <!-- Select2 CSS -->
     <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?php echo e(URL::asset('js/autofill-select.js')); ?>"></script>
    <script>
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
                url: '<?php echo e(route("provincesByRegion")); ?>',
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
                url: '<?php echo e(route("capByProvince")); ?>',
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
                url: '<?php echo e(route("citiesByProvince")); ?>',
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/schedina/new.blade.php ENDPATH**/ ?>