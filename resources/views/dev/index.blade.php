<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Indice Dev</title>
  <link rel="stylesheet" href="{{ asset('build/css/app.min.css') }}">
</head>
<body>
  <div class="container py-4">
    <h1 class="h3 mb-3">Indice Dev</h1>
    <p class="text-muted">Pagine di sviluppo disponibili in questo ambiente.</p>
    <ul class="list-group">
      <li class="list-group-item"><a href="{{ url('/Ind_Tipo_Via_Num_Int') }}">Ind_Tipo_Via_Num_Int</a></li>
      <li class="list-group-item"><a href="{{ url('/Indirizzo') }}">Indirizzo</a></li>
      <li class="list-group-item"><a href="{{ url('/Nac_Reg_Prov_Citt') }}">Nac_Reg_Prov_Citt</a></li>
      <li class="list-group-item"><a href="{{ url('/Relazione_Geografica') }}">Relazione_Geografica</a></li>
      <li class="list-group-item"><a href="{{ url('/Geo_SelfTest') }}">Geo_SelfTest</a></li>
    </ul>
  </div>
  <script src="{{ asset('build/js/app.js') }}"></script>
</body>
</html>
