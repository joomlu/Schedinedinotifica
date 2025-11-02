<!doctype html>
<html lang="it" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GeoSelect SelfTest</title>
  @include('layouts.head-css')
</head>
<body>
  <div class="container py-4">
    <h1 class="h4 mb-3">GeoSelect SelfTest</h1>
    <p class="text-muted">Esegue verifiche logiche non-UI sul componente per garantire mapping e parametri coerenti prima dell'integrazione grafica.</p>
    <pre id="out" class="bg-light p-3 rounded" style="white-space: pre-wrap"></pre>
  </div>
  @include('layouts.vendor-scripts')
  <script src="{{ URL::asset('js/components/geo-select.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      let out = document.getElementById('out');
      try {
        if (!window.GeoSelect || !GeoSelect.selfTest) {
          out.textContent = 'GeoSelect.selfTest non disponibile';
          return;
        }
        const res = GeoSelect.selfTest();
        const lines = [];
        lines.push('Risultato globale: ' + (res.pass ? 'PASS' : 'FAIL'));
        res.results.forEach(r => lines.push((r.pass ? '✔' : '✖') + ' ' + r.name));
        out.textContent = lines.join('\n');
      } catch (e) {
        out.textContent = 'Errore nel self-test: ' + (e && e.message ? e.message : e);
      }
    });
  </script>
</body>
</html>
