
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
                    <h4 class="card-title mb-0">Componenti - Edit </h4>
                </div><!-- end card header -->
                <div class="card-body">
                
                    <form  method="POST" action="<?php echo e(route('componenti.update', $componenti->id)); ?>" class="form-steps" autocomplete="off">
                    <?php echo csrf_field(); ?> 
                    <?php echo method_field('PUT'); ?> 
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="valueInput" class="form-label">Tipo Allogiatto</label>
                                        <select type="text" class="form-control" name="relationship">
                                        <option value="<?php echo e($componenti->relationship); ?>" active><?php echo e($componenti->relationship); ?></option>
                                            <option value="CAPOFAMIGLIA">CAPOFAMIGLIA</option>
                                            <option value="CAPOGRUPPO">CAPOGRUPPO</option>
                                            <option value="FAMILIARE">FAMILIARE</option>
                                            <option value="MEMBRO GRUPPO">MEMBRO GRUPPO</option>
                                            <option value="OSPITE SINGOLO">OSPITE SINGOLO</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Nome</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo e($componenti->name); ?>">
                                        <input type="hidden" class="form-control" name="schedina_id" value="<?php echo e($componenti->schedina_id); ?>">
                                        <input type="hidden" class="form-control" name="customer_id" value="<?php echo e($componenti->customer_id); ?>">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Cognome</label>
                                        <input type="text" class="form-control" name="surname" value="<?php echo e($componenti->surname); ?>">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Sesso</label>
                                        <select type="text" class="form-control" name="sex">
                                            <option value="<?php echo e($componenti->sex); ?>"><?php echo e($componenti->sex); ?></option>
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                
                                
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Nazione</label>
                                       
                                        <select type="text" class="form-control autofill-select" data-autofill="countries" name="country">
                                                        <option value="<?php echo e($componenti->country); ?>"><?php echo e($componenti->country); ?></option>
                                                        <?php $__currentLoopData = $nations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($nation['denominazione_cittadinanza']); ?>"><?php echo e($nation['denominazione_cittadinanza']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="readonlyInput" class="form-label">Cittadinanza</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city_nac">
                                        <option value="<?php echo e($componenti->city_nac); ?>"><?php echo e($componenti->city_nac); ?></option>
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad['denominazione_ita']); ?>"><?php echo e($ciudad['denominazione_ita']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Provincia</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="provinces" name="province_nac">
                                        <option value="<?php echo e($componenti->province_nac); ?>"><?php echo e($componenti->province_nac); ?></option>
                                                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($province['denominazione_provincia']); ?>"><?php echo e($province['denominazione_provincia']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Regione</label>
                                        
                                        <select id="region-select" class="form-control autofill-select" data-autofill="regions" name="regione">
                                        <option value="<?php echo e($componenti->regione); ?>"><?php echo e($componenti->regione); ?></option>
                                                    <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($region['codice_regione']); ?>">
                                                            <?php echo e($region['denominazione_regione']); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Citta</label>
                                        
                                        <select type="text" class="form-control autofill-select" data-autofill="cities" name="city">
                                        <option value="<?php echo e($componenti->regione); ?>"><?php echo e($componenti->city); ?></option>
                                                    <?php $__currentLoopData = $ciudades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ciudad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ciudad['denominazione_ita']); ?>"><?php echo e($ciudad['denominazione_ita']); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Tipo via</label>
                                        <select type="text" class="form-control" name="tipeaway">
                                        <option value="<?php echo e($componenti->typeaway); ?>"><?php echo e($componenti->typeaway); ?></option>
                                            <?php $__currentLoopData = $typeaway; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typestreet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($typestreet->name); ?>"><?php echo e($typestreet->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select> 
                                        
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Strada</label>
                                        <input type="text" class="form-control" name="address" value="<?php echo e($componenti->address); ?>">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Num</label>
                                        <input type="text" class="form-control" name="number" value="<?php echo e($componenti->number); ?>">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Cap</label>
                                        <input type="text" class="form-control" name="cap" value="<?php echo e($componenti->cap); ?>">
                                    </div>
                                    </div>
                               
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Data di nascita</label>
                                        <input type="date" class="form-control" name="date_nac" value="<?php echo e($componenti->date_nac); ?>">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-4">
                                <div>
                                        <label for="basiInput" class="form-label">Essento</label>
                                        <select type="text" class="form-control" name="exent">
                                        <option value="<?php echo e($componenti->exent); ?>"><?php echo e($componenti->exent); ?></option>
                                            <option value="Si">SI</option>
                                            <option value="NO">NO</option>
                                        </select> 
                                    </div>
                                </div>
                                <!--end col-->
                                
                                <div class="col-xxl-3 col-md-6">
                                <div>
                                        <button type="submit" class="btn btn-success">Salva</button>
                                    </div>
                                </div>
                               
                            </div>
                            <!--end row-->
                        </div>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/componenti/edit.blade.php ENDPATH**/ ?>