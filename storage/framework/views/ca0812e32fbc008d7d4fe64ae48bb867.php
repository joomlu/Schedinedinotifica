<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Relazione Geografica — Demo</title>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <link href="<?php echo e(asset('build/css/bootstrap.min.css')); ?>" rel="stylesheet" />
  <link href="<?php echo e(asset('libs/select2/css/select2.min.css')); ?>" rel="stylesheet" />
  <style> body{ background:#f8f9fa; padding:20px; } .container{ max-width:1000px; margin:0 auto; }</style>
</head>
<body>
  <div class="container">
    <h1 class="h5 mb-3">Relazione Geografica</h1>
    <?php if (isset($component)) { $__componentOriginal76c493f567900989cba13e025934da34 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76c493f567900989cba13e025934da34 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.geo-select','data' => ['prefix' => 'geo','preselectItaly' => true,'filterCapByCity' => true,'autoSelectUniqueCap' => true,'endpoints' => [
        'nations' => '/nations',
        'regions' => '/regions',
        'provincesAll' => '/provinces-all',
        'citiesByProvince' => '/cities-by-province',
        'capByProvince' => '/cap-by-province',
      ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('geo-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['prefix' => 'geo','preselectItaly' => true,'filterCapByCity' => true,'autoSelectUniqueCap' => true,'endpoints' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        'nations' => '/nations',
        'regions' => '/regions',
        'provincesAll' => '/provinces-all',
        'citiesByProvince' => '/cities-by-province',
        'capByProvince' => '/cap-by-province',
      ])]); ?>
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
    <hr/>
    <p class="text-muted">Demo: questa pagina carica solamente le dipendenze minime (jQuery, Select2, axios/http, GeoSelect) senza il layout completo.</p>
  </div>
  <script src="<?php echo e(asset('libs/jquery/jquery.min.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/select2/js/select2.full.min.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/select2/js/i18n/it.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/axios/axios.min.js')); ?>"></script>
  <script src="<?php echo e(asset('build/js/utils/http.js')); ?>"></script>
  <script src="<?php echo e(asset('js/components/geo-select.js')); ?>?v=<?php echo e(@filemtime(public_path('js/components/geo-select.js'))); ?>"></script>
</body>
</html>
<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/dev/relazione_geografica.blade.php ENDPATH**/ ?>