
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.componenti'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
        Componenti
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
        Nuovo 
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    

      
        <div class="row">

        
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Nuovo Componenti</h4>
                        
                    </div><!-- end card header -->
                    <form  method="POST" action="<?php echo e(route('componenti.store')); ?>">
                    <?php echo csrf_field(); ?> 
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="valueInput" class="form-label">Tipo Allogiatto</label>
                                        <select type="text" class="form-control" name="relationship">
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
                                        <input type="text" class="form-control" name="name">
                                        <input type="hidden" class="form-control" name="schedina_id" value="<?php echo e($schedina_id); ?>">
                                        <input type="hidden" class="form-control" name="customer_id" value="<?php echo e($customer_id); ?>">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Cognome</label>
                                        <input type="text" class="form-control" name="surname">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                    <div>
                                        <label for="basiInput" class="form-label">Sesso</label>
                                        <select type="text" class="form-control" name="sex">
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
                                                    <option value="">Seleccione una Región</option>
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
                                            <?php $__currentLoopData = $typeaway; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typestreet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($typestreet->name); ?>"><?php echo e($typestreet->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select> 
                                        
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-3">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Strada</label>
                                        <input type="text" class="form-control" name="address">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Num</label>
                                        <input type="text" class="form-control" name="number">
                                    </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-2">
                                        <div>
                                        <label for="readonlyInput" class="form-label">Cap</label>
                                        <input type="text" class="form-control" name="cap">
                                    </div>
                                    </div>
                               
                                <!--end col-->
                                <div class="col-xxl-3 col-md-3">
                                <div>
                                        <label for="readonlyInput" class="form-label">Data di nascita</label>
                                        <input type="date" class="form-control" name="date_nac">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-md-4">
                                <div>
                                        <label for="basiInput" class="form-label">Essento</label>
                                        <select type="text" class="form-control" name="exent">
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
       
    </div> <!-- container-fluid -->
</div><!-- End Page-content -->



<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/componenti/new.blade.php ENDPATH**/ ?>