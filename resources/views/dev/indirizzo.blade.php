<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Indirizzo — Demo</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="{{ asset('build/css/bootstrap.min.css') }}" rel="stylesheet" />
  <style> body{ background:#f8f9fa; padding:20px; } .container{ max-width:1000px; margin:0 auto; }</style>
</head>
<body>
  <div class="container">
    <h1 class="h5 mb-3">Indirizzo</h1>
    <x-address-fields prefix="addr" />
    <hr/>
    <pre id="log" class="bg-light p-3 rounded" style="white-space: pre-wrap"></pre>
  </div>
  <script>
    (function(){
      var root = document.getElementById('addr_addr_wrap') || document.getElementById('addr_addr') || document.querySelector('[id$="_addr_wrap"]') || document;
      root.addEventListener('address:change', function(e){
        var out = document.getElementById('log');
        if (out) out.textContent = JSON.stringify(e.detail, null, 2);
      });
    })();
  </script>
</body>
</html>
