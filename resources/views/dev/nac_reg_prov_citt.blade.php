<!doctype html>
<html lang="it" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nac_Reg_Prov_Citt — Demo Select2</title>
  @include('layouts.head-css')
  <!-- Carico jQuery/Select2 locali direttamente nell'head per garantirne la disponibilità anticipata -->
  <script src="{{ asset('libs/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('libs/select2/js/select2.full.min.js') }}"></script>
  <script src="{{ asset('libs/select2/js/i18n/it.js') }}"></script>
  <style>
    body { background: #f8f9fa; }
    .container-demo { max-width: 1040px; margin: 32px auto; }
    .card { box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.05); }
    .form-label { font-weight: 600; }
    .note { color: #6c757d; font-size: .9rem; }
  </style>
</head>
<body>
  <div class="container-demo">
    @include('components.geo-select', [
      'prefix' => 'demo',
      'showAddressInline' => false,
      'preselectItaly' => true,
      'manualForNonItaly' => true,
      'filterCapByCity' => true,
      'autoSelectUniqueCap' => true,
      'backfillRegionFromProvince' => true,
    ])
  </div>
  <script src="{{ asset('js/components/geo-select.js') }}?v={{ @filemtime(public_path('js/components/geo-select.js')) }}"></script>

  @include('layouts.vendor-scripts')
  
  <script src="{{ asset('js/components/geo-select.js') }}?v={{ @filemtime(public_path('js/components/geo-select.js')) }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      // Attendi jQuery+Select2 prima di impostare la readiness e avviare il componente
      function readyWhenSelect2(){
        if (window.jQuery && jQuery.fn && jQuery.fn.select2){
          // Flag di readiness per Dusk
          window.__GEO_READY__ = true;
          document.body.setAttribute('data-geo-ready', '1');
          const summaryEl = document.getElementById('geo-summary');
          const summaryGeoEl = document.getElementById('geo-summary-geo');
          const summaryAddrEl = document.getElementById('geo-summary-address');
          function renderSummary(v){
            if (!summaryEl) return;
            if (summaryGeoEl){
              summaryGeoEl.innerHTML = `
                <li>Stato: ${v.nation.label || '—'}</li>
                <li>Regione: ${v.region.label || '—'}</li>
                <li>Provincia, Città, CAP: ${v.province.label || '—'}, ${v.city.label || '—'}, ${v.cap.label || '—'}</li>
                <li>Modalità: ${v.manualMode ? 'Inserimento manuale (non Italia)' : 'Selettori Italia'}</li>
              `;
            }
            if (summaryAddrEl){
              summaryAddrEl.innerHTML = `
                <li>Tipo Via: —</li>
                <li>Strada: —</li>
                <li>Num.: —</li>
                <li>Int.: —</li>
              `;
            }
          }
          const container = document.getElementById('demo_geo_wrap');
          if (container) {
            container.addEventListener('geoselect:change', function(ev){ renderSummary(ev.detail); });
          }
          // Render iniziale leggendo dallo stato corrente dei select
          (function initial(){
            try {
              const get = sel => ({ value: (sel && sel.value)||'', label: (sel && sel.options && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '') });
              renderSummary({
                nation: get(document.getElementById('demo_country')),
                region: get(document.getElementById('demo_region')),
                province: get(document.getElementById('demo_prov')),
                city: get(document.getElementById('demo_city')),
                cap: get(document.getElementById('demo_cap')),
                manualMode: false
              });
            } catch(_){}
          })();
          // Aggiorna riepilogo quando cambiano i campi indirizzo
          ['typeaway','address','num','internal'].forEach(function(id){
            const el = document.getElementById(id);
            if (el){ el.addEventListener('input', function(){ renderSummary(geo.getValues()); }); }
          });
        } else {
          setTimeout(readyWhenSelect2, 50);
        }
      }
      readyWhenSelect2();
    });
  </script>
</body>
</html>
