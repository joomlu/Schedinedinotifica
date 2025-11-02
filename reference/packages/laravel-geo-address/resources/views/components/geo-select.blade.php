@php
  $prefix = $prefix ?? 'geo';
  $nameNation = $nameNation ?? ($prefix . '_country');
  $nameRegion = $nameRegion ?? ($prefix . '_region');
  $nameProvince = $nameProvince ?? ($prefix . '_prov');
  $nameCity = $nameCity ?? ($prefix . '_city');
  $nameCap = $nameCap ?? ($prefix . '_cap');
  $inlineId = $inlineId ?? ($prefix . '_address_inline');
  $preselectItaly = isset($preselectItaly) ? (bool)$preselectItaly : false;
  $filterCapByCity = isset($filterCapByCity) ? (bool)$filterCapByCity : true;
  $autoSelectUniqueCap = isset($autoSelectUniqueCap) ? (bool)$autoSelectUniqueCap : true;
  $backfillRegionFromProvince = isset($backfillRegionFromProvince) ? (bool)$backfillRegionFromProvince : true;
  $containerId = $containerId ?? ($prefix . '_geo_wrap');
  $endpoints = $endpoints ?? [];
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
      <div id="{{ $inlineId }}" class="form-text text-muted small mt-1"></div>
    </div>
  </div>
</div>

<script>
  (function(){
    function ready(){
      var hasJq = !!(window.jQuery && jQuery.fn && jQuery.fn.select2);
      var Geo = window.GeoSelect || (window.Libreria && window.Libreria.GeoSelect);
      return !!(hasJq && Geo);
    }
    function boot(){
      var Geo = window.GeoSelect || (window.Libreria && window.Libreria.GeoSelect);
      var cfg = {
        nation: '#{{ $prefix }}_country',
        region: '#{{ $prefix }}_region',
        province: '#{{ $prefix }}_prov',
        city: '#{{ $prefix }}_city',
        cap: '#{{ $prefix }}_cap',
        preselectItaly: {{ $preselectItaly ? 'true' : 'false' }},
        filterCapByCity: {{ $filterCapByCity ? 'true' : 'false' }},
        autoSelectUniqueCap: {{ $autoSelectUniqueCap ? 'true' : 'false' }},
        backfillRegionFromProvince: {{ $backfillRegionFromProvince ? 'true' : 'false' }},
        container: '#{{ $containerId }}',
        endpoints: @json($endpoints)
      };
      try { new Geo(cfg); } catch(e) { console.error('GeoSelect init error', e); }
    }
    (function wait(){ if (ready()) boot(); else setTimeout(wait, 50); })();
  })();
</script>
