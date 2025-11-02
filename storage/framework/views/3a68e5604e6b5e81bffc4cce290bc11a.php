
<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.schedina'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?> Tables <?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?>Schedina <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>


<div class="row justify-content-end">
                                                <div class="col-sm-2">
                                                   
                                                    <a type="button" class="btn btn-primary" href="<?php echo e(url('/newschedina')); ?>"><i class="ri-add-circle-line align-middle me-1"></i>
                                                    <?php echo app('translator')->get('translation.new'); ?></a>
                                                    
                                                </div>
                                            </div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo app('translator')->get('translation.tickets'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Nome</th>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>Quantitá</th>
                                <th>Camera</th>
                                <th>Letti</th>
                                
                                <th>Nazione</th>
                                <th>Citta</th>
                                <th>Link</th>
                                <th>Componenti</th>
                                <th>Actions.</th>
                            </tr>
                        </thead> 
                        <tbody>
                            <?php $__currentLoopData = $schedinas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedina): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($schedina->scheda); ?></td>
                                <td><?php echo e($schedina->surname); ?>, <?php echo e($schedina->name); ?></td>
                                <td><?php echo e($schedina->arrive); ?></td>
                                <td><?php echo e($schedina->departure); ?></td>
                                <td><?php echo e($schedina->cant_people); ?></td>
                                <td><?php echo e($schedina->room); ?></td>
                                <td><?php echo e($schedina->beds); ?></td>
                                <td><?php echo e($schedina->oa_country); ?></td>
                                <td><?php echo e($schedina->oa_city); ?></td>
                                
                                
                                <td><a href="#" class="link-success">Link <i class="ri-arrow-right-line align-middle"></i></a></td>
                                <td><a href="<?php echo e(url('/newcomponenti')); ?>/<?php echo e($schedina->id); ?>/<?php echo e($schedina->customer_id); ?>" class="btn btn-success">ADD</a></td>
                                <td> <a href="<?php echo e(url('/editschedina')); ?>/<?php echo e($schedina->id); ?>" type="button" class="btn btn-success btn-icon waves-effect waves-light"><i class="ri-search-line"></i></a> 
                                <a  href="<?php echo e(route('schedina.destroy',['id' => $schedina->id] )); ?>" onclick="
return confirm('Seguro deseas eliminar esta schedina definitivamente?')" type="button"  class="btn btn-danger btn-icon waves-effect waves-light">
                      <i class="ri-delete-bin-5-line"></i><a></td> 
                            </tr>
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
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/schedina/list.blade.php ENDPATH**/ ?>