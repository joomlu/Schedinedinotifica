<?php
  $prefix = $prefix ?? 'geo';
  $nameNation = $nameNation ?? ($prefix . '_country');
  $nameRegion = $nameRegion ?? ($prefix . '_region');
  $nameProvince = $nameProvince ?? ($prefix . '_prov');
  $nameCity = $nameCity ?? ($prefix . '_city');
  $nameCap = $nameCap ?? ($prefix . '_cap');
  $inlineId = $inlineId ?? ($prefix . '_address_inline');
  $preselectItaly = isset($preselectItaly) ? (bool)$preselectItaly : false;
  $manualForNonItaly = isset($manualForNonItaly) ? (bool)$manualForNonItaly : true;
  $filterCapByCity = isset($filterCapByCity) ? (bool)$filterCapByCity : true;
  $autoSelectUniqueCap = isset($autoSelectUniqueCap) ? (bool)$autoSelectUniqueCap : true;
  $backfillRegionFromProvince = isset($backfillRegionFromProvince) ? (bool)$backfillRegionFromProvince : true;
  $showAddressInline = isset($showAddressInline) ? (bool)$showAddressInline : true;
  $containerId = $containerId ?? ($prefix . '_geo_wrap');
?>

<div id="<?php echo e($containerId); ?>" class="geo-select-component">
  <div class="row g-3">
    <div class="col-md-6 select-group">
      <label for="<?php echo e($prefix); ?>_country" class="form-label">Stato</label>
      <select id="<?php echo e($prefix); ?>_country" name="<?php echo e($nameNation); ?>" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Nazione"></select>
    </div>
    <div class="col-md-6 select-group">
      <label for="<?php echo e($prefix); ?>_region" class="form-label">Regione</label>
      <select id="<?php echo e($prefix); ?>_region" name="<?php echo e($nameRegion); ?>" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Regione" disabled></select>
    </div>
    <div class="col-md-4 select-group">
      <label for="<?php echo e($prefix); ?>_prov" class="form-label">Provincia</label>
      <select id="<?php echo e($prefix); ?>_prov" name="<?php echo e($nameProvince); ?>" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Provincia" disabled></select>
    </div>
    <div class="col-md-4 select-group">
      <label for="<?php echo e($prefix); ?>_city" class="form-label">Città</label>
      <select id="<?php echo e($prefix); ?>_city" name="<?php echo e($nameCity); ?>" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Città" disabled></select>
    </div>
    <div class="col-md-4 select-group">
      <label for="<?php echo e($prefix); ?>_cap" class="form-label">CAP</label>
      <select id="<?php echo e($prefix); ?>_cap" name="<?php echo e($nameCap); ?>" class="form-select" data-no-select2="1" data-placeholder="Seleziona un CAP" disabled></select>
      <?php if($showAddressInline): ?>
        <div id="<?php echo e($inlineId); ?>" class="form-text text-muted small mt-1"></div>
      <?php endif; ?>
    </div>

    <!-- Manual fields for non-Italy -->
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="<?php echo e($prefix); ?>_region_manual" class="form-label">Regione / Stato federato</label>
      <input id="<?php echo e($prefix); ?>_region_manual" type="text" class="form-control" name="<?php echo e($nameRegion); ?>" placeholder="Inserisci regione" autocomplete="address-level1">
    </div>
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="<?php echo e($prefix); ?>_prov_manual" class="form-label">Provincia / Contea</label>
      <input id="<?php echo e($prefix); ?>_prov_manual" type="text" class="form-control" name="<?php echo e($nameProvince); ?>" placeholder="Inserisci provincia" autocomplete="address-level2">
    </div>
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="<?php echo e($prefix); ?>_city_manual" class="form-label">Città / Località</label>
      <input id="<?php echo e($prefix); ?>_city_manual" type="text" class="form-control" name="<?php echo e($nameCity); ?>" placeholder="Inserisci città" autocomplete="address-level3">
    </div>
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="<?php echo e($prefix); ?>_cap_manual" class="form-label">CAP / ZIP</label>
      <input id="<?php echo e($prefix); ?>_cap_manual" type="text" class="form-control" name="<?php echo e($nameCap); ?>" placeholder="Inserisci CAP o ZIP" inputmode="numeric" autocomplete="postal-code" maxlength="10" pattern="^[0-9]{4,5}$" title="Inserisci un CAP valido (4-5 cifre)">
    </div>
  </div>
</div>

<script>
  (function(){
    function ready(){ return !!(window.GeoSelect && window.jQuery && jQuery.fn && jQuery.fn.select2); }
    function boot(){
      var cfg = {
        nation: '#<?php echo e($prefix); ?>_country',
        region: '#<?php echo e($prefix); ?>_region',
        province: '#<?php echo e($prefix); ?>_prov',
        city: '#<?php echo e($prefix); ?>_city',
        cap: '#<?php echo e($prefix); ?>_cap',
        regionManual: '#<?php echo e($prefix); ?>_region_manual',
        provinceManual: '#<?php echo e($prefix); ?>_prov_manual',
        cityManual: '#<?php echo e($prefix); ?>_city_manual',
        capManual: '#<?php echo e($prefix); ?>_cap_manual',
        preselectItaly: <?php echo e($preselectItaly ? 'true' : 'false'); ?>,
        manualForNonItaly: <?php echo e($manualForNonItaly ? 'true' : 'false'); ?>,
        filterCapByCity: <?php echo e($filterCapByCity ? 'true' : 'false'); ?>,
        autoSelectUniqueCap: <?php echo e($autoSelectUniqueCap ? 'true' : 'false'); ?>,
        backfillRegionFromProvince: <?php echo e($backfillRegionFromProvince ? 'true' : 'false'); ?>,
        container: '#<?php echo e($containerId); ?>'
      };
      try { new GeoSelect(cfg); } catch(e) { console.error('GeoSelect init error', e); }
    }
    (function wait(){ if (ready()) boot(); else setTimeout(wait, 50); })();
  })();
</script>
<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/components/geo-select.blade.php ENDPATH**/ ?>