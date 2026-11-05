@extends('layouts.master')

@section('title', $module['title'])

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Aiuto @endslot
    @slot('title') {{ $module['title'] }} @endslot
@endcomponent

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="card-title mb-1">{{ $module['title'] }}</h4>
                <p class="text-muted mb-0">{{ $module['summary'] }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('help.general') }}" class="btn btn-light">Torna al centro assistenza</a>
                <a href="{{ route('help.print', ['section' => 'modules', 'module' => $module['slug']]) }}" target="_blank" class="btn btn-primary">Stampa questo modulo</a>
                @if(!empty($module['route']) && \Illuminate\Support\Facades\Route::has($module['route']))
                    <a href="{{ route($module['route']) }}" class="btn btn-light">{{ $module['cta'] ?? 'Apri modulo' }}</a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="card border mb-4">
            <div class="card-body">
                <h6 class="card-title mb-2">Indice dei moduli</h6>
                <div class="d-flex flex-nowrap overflow-auto gap-2 pb-1">
                    @foreach($modules as $item)
                        <a href="{{ route('help.module', ['slug' => $item['slug']]) }}" class="btn {{ $item['slug'] === $module['slug'] ? 'btn-primary' : 'btn-light' }}">
                            {{ $item['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-5">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center gap-2">
                        <i class="{{ $module['icon'] }} text-primary fs-20"></i>
                        <h5 class="card-title mb-0 fs-6">Panoramica</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="fw-semibold mb-1">Quando lo usi</div>
                            <p class="mb-0">{{ $module['when'] }}</p>
                        </div>
                        <div class="mb-3">
                            <div class="fw-semibold mb-1">Cosa trovi qui</div>
                            <ul class="mb-0 ps-3">
                                @foreach($module['items'] as $item)
                                    <li class="mb-2">{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1">Risultato</div>
                            <p class="mb-0">{{ $module['result'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-0 fs-6">Spiegazione del modulo</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($module['details']))
                            <div class="mb-3">
                                <div class="fw-semibold mb-2">Punti importanti</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($module['details'] as $detail)
                                        <li class="mb-2">{{ $detail }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($module['field_groups']))
                            <div class="row g-3">
                                @foreach($module['field_groups'] as $group)
                                    <div class="col-12">
                                        <div class="border rounded-3 p-3">
                                            <div class="fw-semibold mb-2">{{ $group['title'] }}</div>
                                            <ul class="mb-0 ps-3">
                                                @foreach($group['items'] as $item)
                                                    <li class="mb-2">{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
