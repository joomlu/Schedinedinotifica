<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nac_Reg_Prov_Citt — Demo Select2</title>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <link href="<?php echo e(asset('build/css/bootstrap.min.css')); ?>" rel="stylesheet" />
  <link href="<?php echo e(asset('libs/select2/css/select2.min.css')); ?>" rel="stylesheet" />
  <style>
    body { background: #f8f9fa; }
    .container-demo { max-width: 1040px; margin: 32px auto; }
  </style>
  </head>
<body>
  <div class="container-demo">
    <?php if (isset($component)) { $__componentOriginal76c493f567900989cba13e025934da34 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76c493f567900989cba13e025934da34 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.geo-select','data' => ['prefix' => 'demo','showAddressInline' => false,'preselectItaly' => true,'manualForNonItaly' => true,'filterCapByCity' => true,'autoSelectUniqueCap' => true,'backfillRegionFromProvince' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('geo-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['prefix' => 'demo','showAddressInline' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'preselectItaly' => true,'manualForNonItaly' => true,'filterCapByCity' => true,'autoSelectUniqueCap' => true,'backfillRegionFromProvince' => true]); ?>
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

    <div class="row mt-3">
      <div class="col-12">
        <div id="geo-summary" class="small text-muted">
          <ul id="geo-summary-geo"></ul>
          <ul id="geo-summary-address"></ul>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo e(asset('libs/jquery/jquery.min.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/select2/js/select2.full.min.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/select2/js/i18n/it.js')); ?>"></script>
  <script src="<?php echo e(asset('libs/axios/axios.min.js')); ?>"></script>
  <script src="<?php echo e(asset('build/js/utils/http.js')); ?>"></script>
  <script src="<?php echo e(asset('js/components/geo-select.js')); ?>?v=<?php echo e(@filemtime(public_path('js/components/geo-select.js'))); ?>"></script>
  <script>
    (function(){
      function ready(){ return window.jQuery && jQuery.fn && jQuery.fn.select2; }
      function boot(){
        window.__GEO_READY__ = true;
        document.body.setAttribute('data-geo-ready', '1');
        var summaryEl = document.getElementById('geo-summary');
        var summaryGeoEl = document.getElementById('geo-summary-geo');
        var summaryAddrEl = document.getElementById('geo-summary-address');
        function renderSummary(v){
          if (!summaryEl) return;
          if (summaryGeoEl){
            summaryGeoEl.innerHTML = '<li>Stato: ' + (v.nation.label || '—') + '</li>'
              + '<li>Regione: ' + (v.region.label || '—') + '</li>'
              + '<li>Provincia, Città, CAP: ' + (v.province.label || '—') + ', ' + (v.city.label || '—') + ', ' + (v.cap.label || '—') + '</li>'
              + '<li>Modalità: ' + (v.manualMode ? 'Inserimento manuale (non Italia)' : 'Selettori Italia') + '</li>';
          }
          if (summaryAddrEl){
            summaryAddrEl.innerHTML = '<li>Tipo Via: —</li>'
              + '<li>Strada: —</li>'
              + '<li>Num.: —</li>'
              + '<li>Int.: —</li>';
          }
        }
        var container = document.getElementById('demo_geo_wrap');
        if (container) {
          container.addEventListener('geoselect:change', function(ev){ renderSummary(ev.detail); });
        }
        // Render iniziale
        try {
          var get = function(sel){ return { value: (sel && sel.value)||'', label: (sel && sel.options && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '') }; };
          renderSummary({
            nation: get(document.getElementById('demo_country')),
            region: get(document.getElementById('demo_region')),
            province: get(document.getElementById('demo_prov')),
            city: get(document.getElementById('demo_city')),
            cap: get(document.getElementById('demo_cap')),
            manualMode: false
          });
        } catch(_){}
      }
      (function wait(){ if (ready()) boot(); else setTimeout(wait, 50); })();
    })();
  </script>
</body>
</html>
<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/dev/nac_reg_prov_citt.blade.php ENDPATH**/ ?>