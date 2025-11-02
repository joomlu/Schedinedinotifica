
<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.arrivals'); ?> <?php $__env->stopSection(); ?>
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
<?php $__env->slot('title'); ?>Arrivals <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>

<div class="alert alert-danger" role="alert">
    <strong>Arrivi</strong> 
</div>

<div class="row justify-content-end">
    <div class="col-sm-2">
        <a type="button" class="btn btn-primary" href="<?php echo e(url('/new_arrival')); ?>">
            <i class="ri-add-circle-line align-middle me-1"></i>
            <?php echo app('translator')->get('translation.new'); ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo app('translator')->get('translation.Arrivals'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Nome</th>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>Nazione</th>
                                <th>Città</th>
                                <th>Azione</th>
                                <th>Elimina</th>
                            </tr>
                        </thead> 
                        <tbody>
                            <?php $__currentLoopData = $arrivals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arrival): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($arrival->id); ?></td>
                                <td><?php echo e($arrival->name); ?></td>
                                <td><?php echo e($arrival->arrive); ?></td>
                                <td><?php echo e($arrival->departure); ?></td>
                                <td><?php echo e($arrival->oa_country); ?></td>
                                <td><?php echo e($arrival->oa_city); ?></td>
                                <td><form action="<?php echo e(route('a_schedina', $arrival->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-warning">
                                            A Schedina
                                        </button>
                                    </form></td>
                                <td>
                                    <form action="<?php echo e(route('arrivals.destroy', $arrival->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-icon waves-effect waves-light">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/arrivals/list.blade.php ENDPATH**/ ?>