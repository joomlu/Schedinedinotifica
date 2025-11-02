@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <div class="row">
        <div class="col">

            <div class="h-100">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">@lang('translation.good_morning')</h4>
                                <p class="text-muted mb-0">@lang('translation.dashboard_intro')</p>
                            </div>
                            
                        </div><!-- end card header -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->

                <!-- Quick Links -->
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">@lang('translation.common.quick_actions')</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('customers') }}">Clienti</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('groups') }}">Gruppi</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('subgroups') }}">Sottogruppi</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('subgroups1') }}">Sottogruppi 1</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('title') }}">Titoli</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('typedoc') }}">Tipo Documento</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('typestreet') }}">Tipo Via</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('schedina') }}">Schedine</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('componenti') }}">Componenti</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('arrivals') }}">Arrivi</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('estructura') }}">Struttura</a></div>
                                    <div class="col-6 col-md-3"><a class="btn btn-soft-primary w-100" href="{{ route('released') }}">Rilasci</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end Quick Links -->

            </div> <!-- end .h-100-->

        </div> <!-- end col -->

       
    </div>
@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
