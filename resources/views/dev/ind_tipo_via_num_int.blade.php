@extends('layouts.master')

@section('title', 'Ind_Tipo_Via_Num_Int — Demo Indirizzo')

@section('content')
  <div class="container-xxl py-4">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
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
        </div>
      </div>
    </div>
  </div>
@endsection

