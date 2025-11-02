<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.wizard'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Forms
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Wizard
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Cliente - aggiungere</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form  method="POST" action="<?php echo e(route('customer.store')); ?>" class="form-steps" autocomplete="off">
                    <?php echo csrf_field(); ?> 
                        <div class="text-center pt-3 pb-4 mb-1 d-flex justify-content-center">
                            
                        </div>
                        <div class="step-arrow-nav mb-4">

                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill"
                                        data-bs-target="#steparrow-gen-info" type="button" role="tab"
                                        aria-controls="steparrow-gen-info" aria-selected="true">Cliente Residenza</button>
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
                                                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($group->name); ?>"><?php echo e($group->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <div class="invalid-feedback">Per favore, inserisci un gruppo</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-4">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">SubGruppo</label>
                                                <select class="form-control" name="subgroup">
                                                <?php $__currentLoopData = $subgroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($subgroup->name); ?>"><?php echo e($subgroup->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <div class="invalid-feedback">Per favore, inserisci un sottogruppo</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">SubGruppo 1</label>
                                                    <select class="form-control" name="subgroup1">
                                                    <?php $__currentLoopData = $subgroups1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($subgroup1->name); ?>"><?php echo e($subgroup1->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <div class="invalid-feedback">Per favore, inserisci un sottogruppo</div>
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
                                                    <div class="invalid-feedback">Per favore, inserisci un sottogruppo</div>
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
                                                    <div class="invalid-feedback">Per favore, inserisci un gruppo</div>
                                                </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nome</label>
                                                <input type="text" class="form-control" name="name">
                                                   
                                                <div class="invalid-feedback">Per favore, inserisci un sottogruppo</div>
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
                                                    <div class="invalid-feedback">Per favore, inserisci un gruppo</div>
                                                </div>
                                        </div>
                                    </div>
                                    

                                    <div class="row">
                                      <div class="col-12 mb-2">
                                        <?php if (isset($component)) { $__componentOriginal76c493f567900989cba13e025934da34 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76c493f567900989cba13e025934da34 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.geo-select','data' => ['prefix' => 'cust','preselectItaly' => true,'manualForNonItaly' => true,'filterCapByCity' => true,'autoSelectUniqueCap' => true,'backfillRegionFromProvince' => true,'nameNation' => 'country','nameRegion' => 'region','nameProvince' => 'province','nameCity' => 'city','nameCap' => 'cap']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('geo-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['prefix' => 'cust','preselectItaly' => true,'manualForNonItaly' => true,'filterCapByCity' => true,'autoSelectUniqueCap' => true,'backfillRegionFromProvince' => true,'nameNation' => 'country','nameRegion' => 'region','nameProvince' => 'province','nameCity' => 'city','nameCap' => 'cap']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76c493f567900989cba13e025934da34)): ?>
<?php $attributes = $__attributesOriginal76c493f567900989cba13e025934da34; ?>
<?php unset($__attributesOriginal76c493f567900989cba13e025934da34); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76c493f567900989cba13e025934da34)): ?>
<?php $component = $__componentOriginal76c493f567900989cba13e025934da34; ?>
<?php unset($__componentOriginal76c493f567900989cba13e025934da34); ?>
<?php endif; ?>
                                      </div>
                                    </div>




                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                    <select class="form-control" name="typeaway">
                                                    <?php $__currentLoopData = $typestreets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typestreet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($typestreet->name); ?>"><?php echo e($typestreet->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                    for="steparrow-gen-info-email-input">Cellulare</label>
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
                                    Avanti
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
                                                        <?php $__currentLoopData = $nations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($nation['denominazione_cittadinanza']); ?>"><?php echo e($nation['denominazione_cittadinanza']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia (di Nascita) </label>
                                                    <select type="text" class="form-control autofill-select" data-autofill="provinces" name="prov_reg">
                                                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($province['denominazione_provincia']); ?>"><?php echo e($province['denominazione_provincia']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad['denominazione_ita']); ?>"><?php echo e($ciudad['denominazione_ita']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="ciudadania_reg">
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad['denominazione_ita']); ?>"><?php echo e($ciudad['denominazione_ita']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="date" class="form-control" name="nac_reg" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Doc. Tipo </label>
                                                    <select class="form-control" name="type_doc_reg">
                                                    <?php $__currentLoopData = $TypeDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $TypeDoc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($TypeDoc->name); ?>"><?php echo e($TypeDoc->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                <input type="date" class="form-control" name="expire_reg" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input"> Scade il</label>
                                                <input type="date" class="form-control" name="released_reg" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                                   
                                                
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
    Avanti
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
                                                    <?php $__currentLoopData = $typestreets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typestreet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($typestreet->name); ?>"><?php echo e($typestreet->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                        <?php $__currentLoopData = $nations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($nation['denominazione_cittadinanza']); ?>"><?php echo e($nation['denominazione_cittadinanza']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_az">
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad['denominazione_ita']); ?>"><?php echo e($ciudad['denominazione_ita']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                               
                                                <select id="region-select" class="form-control" name="region_az">
                                                    <option value="">Seleziona una Regione</option>
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
                                                
                                                <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_az">
                                                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($province['denominazione_provincia']); ?>"><?php echo e($province['denominazione_provincia']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/js/pages/form-wizard.init.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="<?php echo e(URL::asset('js/components/geo-select.js')); ?>?v=<?php echo e(@filemtime(public_path('js/components/geo-select.js'))); ?>"></script>
    <script src="<?php echo e(URL::asset('js/autofill-select.js')); ?>"></script>
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
                    $('#province-select').empty().append('<option value="">Seleziona una Provincia</option>');
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
            $('#province-select').empty().append('<option value="">Seleziona una Provincia</option>');
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
                    $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>');
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
            $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>');
        }
    });
});
</script>
<script>
$(document).ready(function() {
    // Inicializar Select2 en cada campo
    $('#nation-select').select2({
        placeholder: "Seleziona una Nazione",
        allowClear: true
    });
    $('#region-select').select2({
        placeholder: "Seleziona una Regione",
        allowClear: true
    });
    $('#province-select').select2({
        placeholder: "Seleziona una Provincia",
        allowClear: true
    });
    $('#cap-select').select2({
        placeholder: "Seleziona un CAP",
        allowClear: true
    });
    $('#city-select').select2({
        placeholder: "Seleziona una Città",
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
                    provinceSelect.empty().append('<option value="">Seleziona una Provincia</option>');
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
            $('#province-select').empty().append('<option value="">Seleziona una Provincia</option>').trigger('change');
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
                    capSelect.empty().append('<option value="">Seleziona un CAP</option>');
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
                    citySelect.empty().append('<option value="">Seleziona una Città</option>');
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
            $('#cap-select').empty().append('<option value="">Seleziona un CAP</option>').trigger('change');
            $('#city-select').empty().append('<option value="">Seleziona una Città</option>').trigger('change');
        }
    });
});
</script>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/customers/new.blade.php ENDPATH**/ ?>