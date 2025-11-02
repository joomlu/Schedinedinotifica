<script src="<?php echo e(URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/simplebar/simplebar.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/node-waves/waves.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/feather-icons/feather.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js')); ?>"></script>
<script>
	window.AppDateConfig = {
		displayFormat: "<?php echo e(config('app.date.display_format', 'd/m/Y')); ?>",
		backendFormat: "<?php echo e(config('app.date.backend_format', 'Y-m-d')); ?>"
	};
</script>
<script src="<?php echo e(URL::asset('build/js/plugins.js')); ?>"></script>
<?php echo $__env->yieldContent('script'); ?>
<?php echo $__env->yieldContent('script-bottom'); ?>
<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/layouts/vendor-scripts.blade.php ENDPATH**/ ?>