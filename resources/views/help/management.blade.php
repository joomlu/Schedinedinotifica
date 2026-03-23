@extends('layouts.master')

@section('title', $topic['title'])

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Aiuto @endslot
    @slot('title') {{ $topic['title'] }} @endslot
@endcomponent

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="card-title mb-1">{{ $topic['title'] }}</h4>
                <p class="text-muted mb-0">{{ $topic['summary'] }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route($isAdminTopic ? 'help.admin' : 'help.general') }}" class="btn btn-light">Torna al centro assistenza</a>
                <a href="{{ route('help.print', ['section' => $isAdminTopic ? 'admin' : 'personas', 'topic' => $topic['slug']]) }}" target="_blank" class="btn btn-primary">Stampa questo argomento</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="card border mb-4">
            <div class="card-body">
                <h6 class="card-title mb-2">{{ $isAdminTopic ? 'Guida amministrativa' : 'Indice di impostazioni e gestione' }}</h6>
                @if($isAdminTopic)
                    <p class="text-muted mb-3">Questa guida e riservata alla catena amministrativa del software: superadmin e admin. Non riguarda l operativita quotidiana della struttura.</p>
                @endif
                <div class="d-flex flex-nowrap overflow-auto gap-2 pb-1">
                    @foreach($managementTopics as $item)
                        <a href="{{ route('help.management', ['slug' => $item['slug']]) }}" class="btn {{ $item['slug'] === $topic['slug'] ? 'btn-primary' : 'btn-light' }}">
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
                        <i class="{{ $topic['icon'] }} text-primary fs-20"></i>
                        <h5 class="card-title mb-0 fs-6">Panoramica</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="fw-semibold mb-1">Quando lo usi</div>
                            <p class="mb-0">{{ $topic['when'] }}</p>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1">Risultato</div>
                            <p class="mb-0">{{ $topic['result'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header border-0 bg-light-subtle">
                        <h5 class="card-title mb-0 fs-6">{{ $isAdminTopic ? 'Guida del ruolo' : 'Spiegazione del settore' }}</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($topic['details']))
                            <div class="mb-3">
                                <div class="fw-semibold mb-2">Punti importanti</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($topic['details'] as $detail)
                                        <li class="mb-2">{{ $detail }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($topic['field_groups']))
                            <div class="row g-3">
                                @foreach($topic['field_groups'] as $group)
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
