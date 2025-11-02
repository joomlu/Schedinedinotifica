@extends('layouts.master')

@section('title', 'Nac_Reg_Prov_Citt — Demo Select2')

@section('content')
  <div class="container-xxl py-4">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
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
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script-bottom')
  <!-- Axios + HTTP wrapper for GeoSelect -->
  <script src="{{ asset('build/libs/axios/axios.min.js') }}"></script>
  <script src="{{ asset('build/js/utils/http.js') }}"></script>
  <!-- GeoSelect component (public) -->
  <script src="{{ asset('js/components/geo-select.js') }}?v={{ @filemtime(public_path('js/components/geo-select.js')) }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      function readyWhenSelect2(){
        if (window.jQuery && jQuery.fn && jQuery.fn.select2){
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
          ['typeaway','address','num','internal'].forEach(function(id){
            const el = document.getElementById(id);
            if (el){ el.addEventListener('input', function(){
              try{ if (window.geo && typeof geo.getValues === 'function'){ renderSummary(geo.getValues()); } }catch(_){ }
            }); }
          });
        } else {
          setTimeout(readyWhenSelect2, 50);
        }
      }
      readyWhenSelect2();
    });
  </script>
@endsection
