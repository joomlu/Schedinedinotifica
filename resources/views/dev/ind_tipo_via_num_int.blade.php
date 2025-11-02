<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Ind_Tipo_Via_Num_Int — Demo Indirizzo</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="{{ asset('build/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/css/icons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/css/app.min.css') }}" rel="stylesheet" />
  <style> body{ background:#f8f9fa; padding:20px; } .container{ max-width:1000px; margin:0 auto; }</style>
</head>
<body>
  <div class="container">
    <h1 class="h5 mb-3">Indirizzo (Tipo, Strada, Num, Int)</h1>
    @include('components.address-fields', [
      'prefix' => 'demoaddr',
      'containerId' => 'demoaddr_wrap',
      'showInline' => true,
      'types' => [
        'Via','Viale','Vicolo','Piazza','Piazzale','Piazzetta','Corso','Largo',
        'Borgo','Contrada','Traversa','Rotonda','Salita','Discesa','Rampa',
        'Passaggio','Galleria','Cortile','Calle','Campo','Fondamenta','Stradone'
      ]
    ])
  </div>
  <script src="{{ asset('libs/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('libs/select2/js/select2.full.min.js') }}"></script>
  <script src="{{ asset('libs/select2/js/i18n/it.js') }}"></script>
</body>
</html>

