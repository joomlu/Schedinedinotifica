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
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h4 class="mb-1">Dashboard</h4>
                        <p class="text-muted mb-0">Accedi rapidamente alle aree principali.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ route('newschedina') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-primary-subtle text-primary rounded"><i class="ri-file-add-line"></i></div>
                        <div>
                            <h6 class="mb-1">Nuova Schedina</h6>
                            <p class="text-muted mb-0">Crea una nuova schedina</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ route('schedina') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-info-subtle text-info rounded"><i class="ri-list-check"></i></div>
                        <div>
                            <h6 class="mb-1">Schedine</h6>
                            <p class="text-muted mb-0">Vedi tutte le schedine</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ route('arrivals') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-success-subtle text-success rounded"><i class="ri-plane-line"></i></div>
                        <div>
                            <h6 class="mb-1">Schedine Arrivi</h6>
                            <p class="text-muted mb-0">Gestisci arrivi in attesa</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ route('newcustomer') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-warning-subtle text-warning rounded"><i class="ri-user-add-line"></i></div>
                        <div>
                            <h6 class="mb-1">Nuovo Cliente</h6>
                            <p class="text-muted mb-0">Registra un cliente</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ route('customers') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-danger-subtle text-danger rounded"><i class="ri-group-line"></i></div>
                        <div>
                            <h6 class="mb-1">Clienti</h6>
                            <p class="text-muted mb-0">Lista clienti</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
@section('script')
@endsection
