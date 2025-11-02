@extends('layouts.master')

@section('title', 'Relazione Geografica — Demo')

@section('content')
  <div class="container-xxl py-4">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">Relazione Geografica</h5></div>
          <div class="card-body">
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
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script-bottom')
  <script src="{{ asset('build/libs/axios/axios.min.js') }}"></script>
  <script src="{{ asset('build/js/utils/http.js') }}"></script>
  <script src="{{ asset('js/components/geo-select.js') }}?v={{ @filemtime(public_path('js/components/geo-select.js')) }}"></script>
@endsection
