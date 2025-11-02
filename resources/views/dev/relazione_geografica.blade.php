<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Relazione Geografica — Demo</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="{{ asset('build/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/css/icons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/css/app.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('libs/select2/css/select2.min.css') }}" rel="stylesheet" />
  <style> body{ background:#f8f9fa; padding:20px; } .container{ max-width:1000px; margin:0 auto; }</style>
</head>
<body>
  <div class="container">
    <h1 class="h5 mb-3">Relazione Geografica</h1>
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
    <p class="text-muted">Demo: questa pagina carica solamente le dipendenze minime (jQuery, Select2, axios/http, GeoSelect) senza il layout completo.</p>
  </div>
  <script src="{{ asset('libs/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('libs/select2/js/select2.full.min.js') }}"></script>
  <script src="{{ asset('libs/select2/js/i18n/it.js') }}"></script>
  <script src="{{ asset('libs/axios/axios.min.js') }}"></script>
  <script src="{{ asset('build/js/utils/http.js') }}"></script>
  <script src="{{ asset('js/components/geo-select.js') }}?v={{ @filemtime(public_path('js/components/geo-select.js')) }}"></script>
</body>
</html>
