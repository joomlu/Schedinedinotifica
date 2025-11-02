<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Ind_Tipo_Via_Num_Int — Demo Indirizzo</title>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <link href="<?php echo e(asset('build/css/bootstrap.min.css')); ?>" rel="stylesheet" />
  <style> body{ background:#f8f9fa; padding:20px; } .container{ max-width:1000px; margin:0 auto; }</style>
</head>
<body>
  <div class="container">
    <h1 class="h5 mb-3">Indirizzo (Tipo, Strada, Num, Int)</h1>
    <?php if (isset($component)) { $__componentOriginaldc016df668a233eaec677248355ed6f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc016df668a233eaec677248355ed6f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.address-fields','data' => ['prefix' => 'demoaddr','containerId' => 'demoaddr_wrap','showInline' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('address-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['prefix' => 'demoaddr','containerId' => 'demoaddr_wrap','showInline' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc016df668a233eaec677248355ed6f3)): ?>
<?php $attributes = $__attributesOriginaldc016df668a233eaec677248355ed6f3; ?>
<?php unset($__attributesOriginaldc016df668a233eaec677248355ed6f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc016df668a233eaec677248355ed6f3)): ?>
<?php $component = $__componentOriginaldc016df668a233eaec677248355ed6f3; ?>
<?php unset($__componentOriginaldc016df668a233eaec677248355ed6f3); ?>
<?php endif; ?>
  </div>
  <script src="<?php echo e(asset('libs/jquery/jquery.min.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/select2/js/select2.full.min.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/select2/js/i18n/it.js')); ?>"></script>
</body>
</html>

<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/dev/ind_tipo_via_num_int.blade.php ENDPATH**/ ?>