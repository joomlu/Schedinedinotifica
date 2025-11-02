
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.edit'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Forms
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Edit Clienti
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Clienti - Edit</h4>
                </div><!-- end card header -->
                <div class="card-body">
                
                    <form  method="POST" action="<?php echo e(route('customer.update', $customer->id)); ?>" class="form-steps" autocomplete="off">
                    <?php echo csrf_field(); ?> 
                    <?php echo method_field('PUT'); ?> 
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
                                                <option value="<?php echo e($customer->group); ?>"><?php echo e($customer->group); ?></option>
                                                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($group->name); ?>"><?php echo e($group->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-4">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">SubGruppo</label>
                                                <select class="form-control" name="subgroup">
                                                <option value="<?php echo e($customer->subgroup); ?>"><?php echo e($customer->subgroup); ?></option>
                                                <?php $__currentLoopData = $subgroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($subgroup->name); ?>"><?php echo e($subgroup->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="steparrow-gen-info-email-input">SubGruppo 1</label>
                                                    <select class="form-control" name="subgroup1">
                                                    <option value="<?php echo e($customer->subgroup1); ?>"><?php echo e($customer->subgroup1); ?></option>    
                                                    <?php $__currentLoopData = $subgroups1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($subgroup1->name); ?>"><?php echo e($subgroup1->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                    <option value="<?php echo e($customer->type_housed); ?>" selected><?php echo e($customer->type_housed); ?></option> 
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
                                                    <option value="<?php echo e($customer->type); ?>" selected><?php echo e($customer->type); ?></option> 
                                                        <option value="M">Dott.</option>
                                                        <option value="F">Famiglia</option>
                                                    </select>
                                                    
                                                </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nome</label>
                                                <input type="text" class="form-control" name="name" value="<?php echo e($customer->name); ?>">
                                                   
                                                <div class="invalid-feedback">Please enter an Subgroup</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cognome</label>
                                                <input type="text" class="form-control" name="surname" value="<?php echo e($customer->surname); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                                <div class="mb-3">
                                                <label class="form-label"
                                                        for="steparrow-gen-info-email-input">Sesso</label>
                                                    <select class="form-control" name="grupo">
                                                        <option value="<?php echo e($customer->grupo); ?>" selected><?php echo e($customer->grupo); ?></option>
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
                                                <input type="text" class="form-control" name="country" value="<?php echo e($customer->country); ?>">
                                                   
                                            <div class="invalid-feedback">Please enter an Subgroup</div>
                                    </div>
                                    </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                <input type="text" class="form-control" name="city" value="<?php echo e($customer->city); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                                <input type="text" class="form-control" name="region" value="<?php echo e($customer->region); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <input type="text" class="form-control" name="province" value="<?php echo e($customer->province); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="row">
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cap</label>
                                                <input type="text" class="form-control" name="cap" value="<?php echo e($customer->cap); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                <input type="text" class="form-control" name="typeaway" value="<?php echo e($customer->typeaway); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada</label>
                                                <input type="text" class="form-control" name="address" value="<?php echo e($customer->address); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num</label>
                                                <input type="text" class="form-control" name="number" value="<?php echo e($customer->number); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div> 
                                    
                                    
                                    

                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Email</label>
                                                <input type="text" class="form-control" name="email" value="<?php echo e($customer->email); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Telefono</label>
                                                <input type="text" class="form-control" name="phone" value="<?php echo e($customer->phone); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Fax</label>
                                                <input type="text" class="form-control" name="fax" value="<?php echo e($customer->fax); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Celular</label>
                                                <input type="text" class="form-control" name="cellphone" value="<?php echo e($customer->cellphone); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione </label>
                                                <textarea type="text" class="form-control" name="observation"><?php echo e($customer->observation); ?></textarea>
                                                   
                                                
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
                                                <input type="text" class="form-control" name="country_reg"  value="<?php echo e($customer->country_reg); ?>">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta (di Nascita) :</label>
                                                <input type="text" class="form-control" name="city_reg"  value="<?php echo e($customer->city_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia (di Nascita) </label>
                                                <input type="text" class="form-control" name="prov_reg"  value="<?php echo e($customer->prov_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Cittadinanza</label>
                                                <input type="text" class="form-control" name="ciudadania_reg"  value="<?php echo e($customer->ciudadania_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Data di nascita</label>
                                                <input type="text" class="form-control" name="nac_reg"  value="<?php echo e($customer->nac_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Doc. Tipo </label>
                                                <input type="text" class="form-control" name="type_doc_reg"  value="<?php echo e($customer->type_doc_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="text" class="form-control" name="num_doc_reg"  value="<?php echo e($customer->num_doc_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato </label>
                                                <input type="text" class="form-control" name="date_pub_reg" value="<?php echo e($customer->date_pub_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Rilasciato il</label>
                                                <input type="date" class="form-control" name="expire_reg" value="<?php echo e($customer->expire_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input"> Scade il</label>
                                                <input type="date" class="form-control" name="released_reg" value="<?php echo e($customer->released_reg); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Osservazione anagrafica</label>
                                                <textarea type="text" class="form-control" name="observation_reg"><?php echo e($customer->observation_reg); ?></textarea>
                                                   
                                                
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
                                                <input type="text" class="form-control" name="azienda" value="<?php echo e($customer->azienda); ?>">
                                                   
                                                
                                            </div> 
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">C.F. </label>
                                                <input type="text" class="form-control" name="cf_az" value="<?php echo e($customer->cf_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">P.I.</label>
                                                <input type="text" class="form-control" name="pi_az" value="<?php echo e($customer->pi_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                   
                                    <div class="row">
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Tipo Via</label>
                                                <input type="text" class="form-control" name="typeaway_az" value="<?php echo e($customer->typeaway_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Strada </label>address_az
                                                <input type="text" class="form-control" name="address_az"  value="<?php echo e($customer->address_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                    
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Num.</label>
                                                <input type="text" class="form-control" name="number_az" value="<?php echo e($customer->number_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">CAP :</label>
                                                <input type="text" class="form-control" name="cap_az" value="<?php echo e($customer->cap_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        </div>
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Email </label>
                                                <input type="text" class="form-control" name="email_az" value="<?php echo e($customer->email_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Telefono</label>
                                                <input type="text" class="form-control" name="phone_az" value="<?php echo e($customer->phone_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Fax</label>
                                                <input type="text" class="form-control" name="fax_az" value="<?php echo e($customer->fax_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Celular</label>
                                                <input type="text" class="form-control" name="cellphone_az" value="<?php echo e($customer->cellphone_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nazione</label>
                                                <input type="text" class="form-control" name="country_az" value="<?php echo e($customer->country_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Citta</label>
                                                <input type="text" class="form-control" name="city_az" value="<?php echo e($customer->city_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Regione</label>
                                                <input type="text" class="form-control" name="region_az" value="<?php echo e($customer->region_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Provincia</label>
                                                <input type="text" class="form-control" name="province_az" value="<?php echo e($customer->province_az); ?>">
                                                   
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                    
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Descrizione</label>
                                                <textarea type="text" class="form-control" name="desc_az" value="<?php echo e($customer->desc_az); ?>"></textarea>
                                                   
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                        <div class="mb-3">
                                                <label class="form-label"
                                                    for="steparrow-gen-info-email-input">Nota</label>
                                                <textarea type="text" class="form-control" name="nota"><?php echo e($customer->nota); ?></textarea>
                                                   
                                                
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/customers/edit.blade.php ENDPATH**/ ?>