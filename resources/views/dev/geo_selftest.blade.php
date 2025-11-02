<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>GeoSelect SelfTest</title>
  <link href="{{ asset('build/css/bootstrap.min.css') }}" rel="stylesheet" />
  <style> body{ background:#f8f9fa; padding:20px; } .container{ max-width:900px; margin:0 auto; }</style>
</head>
<body>
  <div class="container">
    <h1 class="h5 mb-3">GeoSelect SelfTest</h1>
    <p class="text-muted">Esegue verifiche logiche non-UI sul componente per garantire mapping e parametri coerenti prima dell'integrazione grafica.</p>
    <pre id="out" class="bg-light p-3 rounded" style="white-space: pre-wrap"></pre>
  </div>
  <script src="{{ URL::asset('js/components/geo-select.js') }}"></script>
  <script>
    (function(){
      var out = document.getElementById('out');
      try {
        if (!window.GeoSelect || !GeoSelect.selfTest) {
          out.textContent = 'GeoSelect.selfTest non disponibile';
          return;
        }
        var res = GeoSelect.selfTest();
        var lines = [];
        lines.push('Risultato globale: ' + (res.pass ? 'PASS' : 'FAIL'));
        res.results.forEach(function(r){ lines.push((r.pass ? '✔' : '✖') + ' ' + r.name); });
        out.textContent = lines.join('\n');
      } catch (e) {
        out.textContent = 'Errore nel self-test: ' + (e && e.message ? e.message : e);
      }
    })();
  </script>
</body>
</html>
