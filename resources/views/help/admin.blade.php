@extends('layouts.master')

@section('title', 'Guida admin e superadmin')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Aiuto @endslot
    @slot('title') Guida admin e superadmin @endslot
@endcomponent

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="card-title mb-1">Guida admin e superadmin</h4>
                <p class="text-muted mb-0">Questa guida e separata dal centro assistenza generale. Serve per capire il perimetro amministrativo del software, le differenze tra admin e superadmin e come correggere gli errori della catena gestionale.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('help.index') }}" class="btn btn-light">Torna alla scelta aiuto</a>
                <a href="{{ route('help.general') }}" class="btn btn-light">Apri centro assistenza generale</a>
                <a href="{{ route('help.print', ['section' => 'admin-index']) }}" target="_blank" class="btn btn-primary">Stampa guida admin</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="card border mb-4">
            <div class="card-body">
                <h6 class="card-title mb-2">Indice della guida amministrativa</h6>
                <p class="text-muted mb-3">Qui dentro restano solo i temi dedicati ad admin e superadmin, senza mescolarli alla guida quotidiana di proprietario e struttura.</p>
                <div class="d-flex flex-nowrap overflow-auto gap-2 pb-1">
                    @foreach($adminTopics as $topic)
                        <a href="{{ route('help.management', ['slug' => $topic['slug']]) }}" class="btn btn-light">
                            {{ $topic['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row g-3">
            @foreach($adminTopics as $topic)
                <div class="col-xl-6">
                    <div class="card border h-100 shadow-sm mb-0">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center gap-2">
                            <i class="{{ $topic['icon'] }} text-primary fs-20"></i>
                            <h5 class="card-title mb-0 fs-6">{{ $topic['title'] }}</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">{{ $topic['summary'] }}</p>
                            <div class="mb-3">
                                <div class="fw-semibold mb-1">Quando la usi</div>
                                <p class="mb-0">{{ $topic['when'] }}</p>
                            </div>
                            @if(!empty($topic['details']))
                                <div class="mb-3">
                                    <div class="fw-semibold mb-1">Cosa significa</div>
                                    <ul class="mb-0 ps-3">
                                        @foreach($topic['details'] as $detail)
                                            <li class="mb-2">{{ $detail }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold mb-1">Risultato</div>
                                <p class="mb-0">{{ $topic['result'] }}</p>
                            </div>
                            @if(!empty($topic['quick_links']))
                                <div class="mt-3">
                                    <div class="fw-semibold mb-2">Collegamenti diretti</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($topic['quick_links'] as $link)
                                            @if(\Illuminate\Support\Facades\Route::has($link['route']))
                                                <a href="{{ route($link['route']) }}" class="btn btn-light btn-sm">{{ $link['title'] }}</a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <a href="{{ route('help.management', ['slug' => $topic['slug']]) }}" class="btn btn-primary">Apri guida dedicata</a>
                                <a href="{{ route('help.print', ['section' => 'admin', 'topic' => $topic['slug']]) }}" target="_blank" class="btn btn-light">Stampa questo argomento</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
