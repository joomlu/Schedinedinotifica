<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Indirizzo</title>
  <style> body{ font-family: system-ui,-apple-system,Segoe UI,Roboto,sans-serif; padding:20px; } .container{ max-width:1000px; margin:0 auto; } </style>
</head>
<body>
  <div class="container">
    <h1>Indirizzo</h1>

    <x-address-fields prefix="addr" />

    <hr/>
    <pre id="log" style="background:#f8f9fa; padding:12px; border-radius:8px"></pre>
  </div>

  <script src="/libreria/AddressFields.js"></script>
  <script>
    (function(){
      var root = document.getElementById('addr_addr_wrap') || document.getElementById('addr_addr') || document.querySelector('[id$="_addr_wrap"]') || document;
      root.addEventListener('address:change', function(e){
        document.getElementById('log').textContent = JSON.stringify(e.detail, null, 2);
      });
    })();
  </script>
</body>
</html>
