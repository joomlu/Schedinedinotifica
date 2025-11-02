<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.structure'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Components <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Strutture <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-xxl-12">
            
            <div class="card">
                <div class="card-body">
                    
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#home" role="tab" aria-selected="false">
                                Generale
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#product1" role="tab" aria-selected="false">
                                Tasa Soggiorno
                            </a>
                        </li>
                        
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content  text-muted">
                        <div class="tab-pane active" id="home" role="tabpanel">
                            
                            <div class="card-body">
                    <div class="live-preview">
                    <form method="POST" action="<?php echo e(route('estructura.update', $estructura->id)); ?>">
                                                    <?php echo csrf_field(); ?> 
                                                    <?php echo method_field('PUT'); ?> 
                        <div class="input-group input-group-sm mb-3">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Nome</label>
                                    <input type="text" name="name" value="<?php echo e($estructura->name); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Citta</label>
                                    <input type="text" name="city" value="<?php echo e($estructura->city); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Indirizzo</label>
                                    <input type="text" name="address" value="<?php echo e($estructura->address); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">CAP</label>
                                    <input type="text" name="cp" value="<?php echo e($estructura->cp); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        </div>
                        <div class="row gy-4">
                            
                        <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Telefono</label>
                                    <input type="text" name="phone" value="<?php echo e($estructura->phone); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">FAX</label>
                                    <input type="text" name="fax" value="<?php echo e($estructura->fax); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">E-mail</label>
                                    <input type="text" name="email" value="<?php echo e($estructura->email); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Web</label>
                                    <input type="text" name="web" value="<?php echo e($estructura->web); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                         
                       


                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">C.F.</label>
                                    <input type="text" name="cf" value="<?php echo e($estructura->cf); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">P. Iva</label>
                                    <input type="text" name="piva" value="<?php echo e($estructura->piva); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->

                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Inizio attività</label>
                                    <input type="date" name="startact" value="<?php echo e($estructura->startact); ?>" class="form-control" id="basiInput" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Chiussura attività</label>
                                    <input type="date" name="closeact" value="<?php echo e($estructura->closeact); ?>" class="form-control" id="basiInput" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                </div>
                            </div>
                            <!--end col-->
                            
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                           
                        <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Tipologia Struttura</label>
                                    <input type="text" name="typology" value="<?php echo e($estructura->typology); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Clasificazione</label>
                                    <input type="text" name="clasification" value="<?php echo e($estructura->clasification); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Num Schedine</label>
                                    <input type="text" name="numshedine" value="<?php echo e($estructura->numshedine); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Camere disponibili</label>
                                    <input type="text" name="roomdisp" value="<?php echo e($estructura->roomdisp); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Letti disponibili</label>
                                    <input type="text" name="beddisp" value="<?php echo e($estructura->beddisp); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Letti Agg.</label>
                                    <input type="text" name="updatedbed" value="<?php echo e($estructura->updatedbed); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Referimento </label>
                                    <input type="text" name="ref" value="<?php echo e($estructura->ref); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Referimento Password </label>
                                    <input type="password" name="refpass" value="<?php echo e($estructura->refpass); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                       
                        <div class="row" style="margin-top: 20px">
                        <div class="col-xxl-3 col-md-3">
                            <button type="submit" class="btn btn-success">Salva</button>
                        </div>
                        </div>
</form>
                    </div>
</div>
                        </div>
                        <div class="tab-pane" id="product1" role="tabpanel">
                        <form method="POST" action="<?php echo e(route('tasa.update', $tasa->id)); ?>">
                                                    <?php echo csrf_field(); ?> 
                                                    <?php echo method_field('PUT'); ?> 
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Tassa Soggiorno</label>
                                    <input type="text" name="tassa_soggiorno" value="<?php echo e($tasa->tassa_soggiorno); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Giorni Massimo</label>
                                    <input type="text" name="giorni_massimo" value="<?php echo e($tasa->giorni_massimo); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Inizio</label>
                                    <input type="date" name="inizio" value="<?php echo e($tasa->inizio); ?>" class="form-control" id="basiInput" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Fine</label>
                                    <input type="date" name="fine" value="<?php echo e($tasa->fine); ?>" class="form-control" id="labelInput" data-provider="flatpickr" data-date-format="<?php echo e(config('app.date.backend_format')); ?>" data-altFormat="<?php echo e(config('app.date.display_format')); ?>">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">età mass. bimbi</label>
                                    <input type="number" name="max_age_children" value="<?php echo e($tasa->max_age_children); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">età min. Adulto</label>
                                    <input type="number" name="min_age_adult" value="<?php echo e($tasa->min_age_adult); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->

                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Provincia</label>
                                    <input type="text" name="province" value="<?php echo e($tasa->province); ?>" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Citta</label>
                                    <input type="text" name="city" value="<?php echo e($tasa->city); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Regione</label>
                                    <input type="text" name="region" value="<?php echo e($tasa->region); ?>" class="form-control" id="labelInput">
                                </div>
                            </div>
                            </div>
                        <!--end row-->
                        <div class="row" style="margin-top: 20px">
                        <div class="col-xxl-3 col-md-3">
                            <button type="submit" class="btn btn-success">Salva</button>
                        </div>
                        </div>
                        </form>
                        </div>
                       
                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div><!--end col-->

        
    </div><!--end row-->

   

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/prismjs/prism.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/estructura/index.blade.php ENDPATH**/ ?>