@extends('layouts.master')
@section('title')
    Tassa di soggiorno
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Anagrafica
        @endslot
        @slot('title')
            Tassa di soggiorno
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-body">
                                        @if($struttura && $struttura->logo_citta)
                                            <div class="mb-3">
                                                <img src="{{ asset($struttura->logo_citta) }}" alt="Logo città" class="img-fluid mb-3" style="max-height: 75px; display: block; float: left;">
                                            </div>
                                        @endif
                    <form method="POST" action="{{ isset($tassa) ? route('tassa_di_soggiorno.update', $tassa->id) : route('tassa_di_soggiorno.store') }}">
                        @csrf
                        @if(isset($tassa)) @method('PUT') @endif
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-money-euro-circle-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Dati Tassa di soggiorno</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Località (come da struttura)</label>
                                            <input type="text" class="form-control" value="{{ $struttura->localita ?? '' }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Aliquota</label>
                                            <input type="text" name="tassa_soggiorno" class="form-control" value="{{ old('tassa_soggiorno', $tassa->tassa_soggiorno ?? '') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Giorni massimo</label>
                                            <input type="text" name="giorni_massimo" class="form-control" value="{{ old('giorni_massimo', $tassa->giorni_massimo ?? '') }}">
                                        </div>
                                        <div class="mb-3 row">
                                            <div class="col-md-6">
                                                <label class="form-label small">Data inizio pagamento tassa</label>
                                                <x-calendario
                                                    name="inizio"
                                                    variant="single"
                                                    :value="old('inizio', $tassa->inizio ?? '')"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Data fine pagamento tassa</label>
                                                <x-calendario
                                                    name="fine"
                                                    variant="single"
                                                    :value="old('fine', $tassa->fine ?? '')"
                                                />
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <div class="col-md-6">
                                                <label class="form-label">Età max bambini</label>
                                                <input type="text" name="max_age_children" class="form-control" value="{{ old('max_age_children', $tassa->max_age_children ?? '') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Età min adulti</label>
                                                <input type="text" name="min_age_adult" class="form-control" value="{{ old('min_age_adult', $tassa->min_age_adult ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Note</label>
                                            <textarea name="note" class="form-control">{{ old('note', $tassa->note ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success btn-label">
                                <i class="ri-save-line label-icon align-middle fs-16 me-2"></i>Salva configurazione
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
