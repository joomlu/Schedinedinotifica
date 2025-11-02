<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Relazione Geografica</title>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    body{ font-family: system-ui,-apple-system,Segoe UI,Roboto,sans-serif; padding:20px; }
    .container{ max-width:1000px; margin:0 auto; }
    /* Nasconde i campi manuali quando non c'è Bootstrap */
    .d-none{ display:none !important; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Relazione Geografica</h1>

    <x-geo-select
      prefix="geo"
      :preselectItaly="true"
      :filterCapByCity="true"
      :autoSelectUniqueCap="true"
      :endpoints="[
        'nations' => '/nations',
        'regions' => '/regions',
        'provincesAll' => '/provinces-all',
        'citiesByProvince' => '/cities-by-province',
        'capByProvince' => '/cap-by-province',
      ]"
    />

    <hr/>
    <p class="text-muted">Demo: assicurati che jQuery, Select2 e la classe GeoSelect siano caricati.</p>
  </div>

  <!-- Dipendenze -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/it.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
  <!-- Libreria UMD (servita da route) -->
  <script src="/libreria/GeoSelect.js"></script>
  <!-- Bridge: espone la classe anche come window.GeoSelect per il componente Blade -->
  <script>
    window.GeoSelect = window.GeoSelect || (window.Libreria && window.Libreria.GeoSelect);
  </script>
</body>
</html>
