
<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.Title'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?> <?php echo app('translator')->get('translation.tables'); ?> <?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?> <?php echo app('translator')->get('translation.Title'); ?> <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>



<div class="row justify-content-end">
                                                <div class="col-sm-2">
                                                   
                                                    <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal"><i class="ri-add-circle-line align-middle me-1"></i>
                                                    <?php echo app('translator')->get('translation.new'); ?></a>
                                                    

<!-- Default Modals -->

<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel"><?php echo app('translator')->get('translation.new'); ?> <?php echo app('translator')->get('translation.Title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <div class="modal-body">
<form method="POST" action="<?php echo e(route('title.store')); ?>">
<?php echo csrf_field(); ?> 
<div>
    <label for="basiInput" class="form-label"><?php echo app('translator')->get('translation.Name'); ?></label>
    <input type="text" name="name" class="form-control" id="basiInput">
</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo app('translator')->get('translation.close'); ?></button>
                <button type="submit" class="btn btn-primary "><?php echo app('translator')->get('translation.save'); ?></button>
</form>
            </div>
      
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


                                                </div>
                                            </div>
<div class="row">

    <div class="col-lg-12">
           
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo app('translator')->get('translation.Title'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th></th>
                            
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $title; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $titles): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($titles->id); ?></td>
                                <td><?php echo e($titles->name); ?></td>
                                
                                
                                <td> <button type="button" class="btn btn-success btn-icon waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#ModalEdit<?php echo e($titles->id); ?>"><i class=" ri-pencil-line"></i></button> 
                                <a  href="<?php echo e(route('title.destroy',['id' => $titles->id] )); ?>" onclick="
return confirm('Sei sicuro di eliminare definitivamente questo elemento?')" type="button"  class="btn btn-danger btn-icon waves-effect waves-light">
                      <i class="ri-delete-bin-5-line"></i><a>     
                                </td>
                            </tr>
                                <div id="ModalEdit<?php echo e($titles->id); ?>" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="myModalEditLabel"><?php echo app('translator')->get('translation.edit'); ?> <?php echo app('translator')->get('translation.Title'); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                                            </div>
                                            <div class="modal-body">
                                                    <form method="POST" action="<?php echo e(route('title.update', $titles->id)); ?>">
                                                    <?php echo csrf_field(); ?> 
                                                    <?php echo method_field('PUT'); ?> 
                                                                <div>
                                                                    <label for="basiInput" class="form-label"><?php echo app('translator')->get('translation.Name'); ?></label>
                                                                    <input type="text" name="name" value="<?php echo e($titles->name); ?>" class="form-control" id="basiInput">
                                                                </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo app('translator')->get('translation.close'); ?></button>
                                                                    <button type="submit" class="btn btn-primary "><?php echo app('translator')->get('translation.save'); ?></button>
                                                                </div>
                                                    </form>
                                            
                                    
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="<?php echo e(URL::asset('build/js/pages/datatables.init.js')); ?>"></script>

<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/title/list.blade.php ENDPATH**/ ?>