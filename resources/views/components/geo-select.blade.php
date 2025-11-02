@php
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
@endphp

<div id="{{ $containerId }}" class="geo-select-component">
  <div class="row g-3">
    <div class="col-md-6 select-group">
      <label for="{{ $prefix }}_country" class="form-label">Stato</label>
      <select id="{{ $prefix }}_country" name="{{ $nameNation }}" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Nazione"></select>
    </div>
    <div class="col-md-6 select-group">
      <label for="{{ $prefix }}_region" class="form-label">Regione</label>
      <select id="{{ $prefix }}_region" name="{{ $nameRegion }}" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Regione" disabled></select>
    </div>
    <div class="col-md-4 select-group">
      <label for="{{ $prefix }}_prov" class="form-label">Provincia</label>
      <select id="{{ $prefix }}_prov" name="{{ $nameProvince }}" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Provincia" disabled></select>
    </div>
    <div class="col-md-4 select-group">
      <label for="{{ $prefix }}_city" class="form-label">Città</label>
      <select id="{{ $prefix }}_city" name="{{ $nameCity }}" class="form-select" data-no-select2="1" data-placeholder="Seleziona una Città" disabled></select>
    </div>
    <div class="col-md-4 select-group">
      <label for="{{ $prefix }}_cap" class="form-label">CAP</label>
      <select id="{{ $prefix }}_cap" name="{{ $nameCap }}" class="form-select" data-no-select2="1" data-placeholder="Seleziona un CAP" disabled></select>
      @if($showAddressInline)
        <div id="{{ $inlineId }}" class="form-text text-muted small mt-1"></div>
      @endif
    </div>

    <!-- Manual fields for non-Italy -->
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="{{ $prefix }}_region_manual" class="form-label">Regione / Stato federato</label>
      <input id="{{ $prefix }}_region_manual" type="text" class="form-control" name="{{ $nameRegion }}" placeholder="Inserisci regione" autocomplete="address-level1">
    </div>
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="{{ $prefix }}_prov_manual" class="form-label">Provincia / Contea</label>
      <input id="{{ $prefix }}_prov_manual" type="text" class="form-control" name="{{ $nameProvince }}" placeholder="Inserisci provincia" autocomplete="address-level2">
    </div>
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="{{ $prefix }}_city_manual" class="form-label">Città / Località</label>
      <input id="{{ $prefix }}_city_manual" type="text" class="form-control" name="{{ $nameCity }}" placeholder="Inserisci città" autocomplete="address-level3">
    </div>
    <div class="col-md-6 mb-3 d-none manual-group">
      <label for="{{ $prefix }}_cap_manual" class="form-label">CAP / ZIP</label>
      <input id="{{ $prefix }}_cap_manual" type="text" class="form-control" name="{{ $nameCap }}" placeholder="Inserisci CAP o ZIP" inputmode="numeric" autocomplete="postal-code" maxlength="10" pattern="^[0-9]{4,5}$" title="Inserisci un CAP valido (4-5 cifre)">
    </div>
  </div>
</div>

<script>
  (function(){
    function ready(){ return !!(window.GeoSelect && window.jQuery && jQuery.fn && jQuery.fn.select2); }
    function boot(){
      var cfg = {
        nation: '#{{ $prefix }}_country',
        region: '#{{ $prefix }}_region',
        province: '#{{ $prefix }}_prov',
        city: '#{{ $prefix }}_city',
        cap: '#{{ $prefix }}_cap',
        regionManual: '#{{ $prefix }}_region_manual',
        provinceManual: '#{{ $prefix }}_prov_manual',
        cityManual: '#{{ $prefix }}_city_manual',
        capManual: '#{{ $prefix }}_cap_manual',
        preselectItaly: {{ $preselectItaly ? 'true' : 'false' }},
        manualForNonItaly: {{ $manualForNonItaly ? 'true' : 'false' }},
        filterCapByCity: {{ $filterCapByCity ? 'true' : 'false' }},
        autoSelectUniqueCap: {{ $autoSelectUniqueCap ? 'true' : 'false' }},
        backfillRegionFromProvince: {{ $backfillRegionFromProvince ? 'true' : 'false' }},
        container: '#{{ $containerId }}'
      };
      try { new GeoSelect(cfg); } catch(e) { console.error('GeoSelect init error', e); }
    }
    (function wait(){ if (ready()) boot(); else setTimeout(wait, 50); })();
  })();
</script>
