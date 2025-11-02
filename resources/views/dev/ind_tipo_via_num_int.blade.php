<!doctype html>
<html lang="it" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ind_Tipo_Via_Num_Int — Demo Indirizzo</title>
  @include('layouts.head-css')
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
    @include('components.address-fields', [
      'prefix' => 'demoaddr',
      'containerId' => 'demoaddr_wrap',
      'showInline' => false,
      'types' => [
        'Via','Viale','Vicolo','Piazza','Piazzale','Piazzetta','Corso','Largo',
        'Borgo','Contrada','Traversa','Rotonda','Salita','Discesa','Rampa',
        'Passaggio','Galleria','Cortile','Calle','Campo','Fondamenta','Stradone'
      ]
    ])
  </div>

  @include('layouts.vendor-scripts')
</body>
</html>
