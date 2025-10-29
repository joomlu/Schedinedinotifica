@extends('layouts.master')
@section('title') @lang('translation.structure') @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Components @endslot
        @slot('title') Strutture @endslot
    @endcomponent

    <div class="row">
        <div class="col-xxl-12">

            <div class="card">
                <div class="card-body">

                    <!-- Nav tabs -->
                    @php $active = session('active_tab', 'home'); @endphp
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $active == 'home' ? 'active' : '' }}" data-bs-toggle="tab" href="#home" role="tab" aria-selected="{{ $active == 'home' ? 'true' : 'false' }}">
                                Generale
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $active == 'product1' ? 'active' : '' }}" data-bs-toggle="tab" href="#product1" role="tab" aria-selected="{{ $active == 'product1' ? 'true' : 'false' }}">
                                Tasa Soggiorno
                            </a>
                        </li>

                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content  text-muted">
                        <div class="tab-pane {{ $active == 'home' ? 'active' : '' }}" id="home" role="tabpanel">

                            <div class="card-body">
                    <div class="live-preview">
                    <form method="POST" action="{{route('estructura.update', $estructura->id)}}">
                                                    @csrf
                                                    @method('PUT')
                        <div class="input-group input-group-sm mb-3">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Nome</label>
                                    <input type="text" name="name" value="{{$estructura->name}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Citta</label>
                                    <input type="text" name="city" value="{{$estructura->city}}" class="form-control" id="basiInput">
                                </div>
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Indirizzo</label>
                                    <input type="text" name="address" value="{{$estructura->address}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">CAP</label>
                                    <input type="text" name="cp" value="{{$estructura->cp}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        </div>
                        <div class="row gy-4">

                        <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Telefono</label>
                                    <input type="text" name="phone" value="{{$estructura->phone}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">FAX</label>
                                    <input type="text" name="fax" value="{{$estructura->fax}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">E-mail</label>
                                    <input type="text" name="email" value="{{$estructura->email}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Web</label>
                                    <input type="text" name="web" value="{{$estructura->web}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->




                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">C.F.</label>
                                    <input type="text" name="cf" value="{{$estructura->cf}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">P. Iva</label>
                                    <input type="text" name="piva" value="{{$estructura->piva}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->

                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Inizio attività</label>
                                    <input type="text" name="startact" value="{{$estructura->startact}}" class="form-control" id="startact-field" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data inizio attività">
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Chiussura attività</label>
                                    <input type="text" name="closeact" value="{{$estructura->closeact}}" class="form-control" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data fine attività">
                                </div>
                            </div>
                            <!--end col-->

                            </div>
                        <!--end row-->
                        <div class="row gy-4">

                        <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Tipologia Struttura</label>
                                    <input type="text" name="typology" value="{{$estructura->typology}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Clasificazione</label>
                                    <input type="text" name="clasification" value="{{$estructura->clasification}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Num Schedine</label>
                                    <input type="text" name="numshedine" value="{{$estructura->numshedine}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Camere disponibili</label>
                                    <input type="text" name="roomdisp" value="{{$estructura->roomdisp}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Letti disponibili</label>
                                    <input type="text" name="beddisp" value="{{$estructura->beddisp}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Letti Agg.</label>
                                    <input type="text" name="updatedbed" value="{{$estructura->updatedbed}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>
                        <!--end row-->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Referimento </label>
                                    <input type="text" name="ref" value="{{$estructura->ref}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Referimento Password </label>
                                    <input type="password" name="refpass" value="{{$estructura->refpass}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            </div>

                        <div class="row" style="margin-top: 20px">
                        <div class="col-xxl-3 col-md-3">
                            <button type="submit" class="btn btn-success">Salva</button>
                        </div>
                        </div>
</form>
                    </div>
</div>
                        </div>
                        <div class="tab-pane {{ $active == 'product1' ? 'active' : '' }}" id="product1" role="tabpanel">
                        <form method="POST" action="{{route('tasa.update', $tasa->id)}}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                        <!-- Logo row: logo placed above the location fields (left aligned) -->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    @if($tasa->logo)
                                        <img src="{{ asset('storage/logos/' . $tasa->logo) }}" alt="Logo" class="mb-2" style="height: 90px; width: auto; object-fit: contain;">
                                    @endif
                                </div>
                            </div>
                            <div class="col-xxl-9 col-md-9">
                                <!-- empty space to keep logo on the left -->
                            </div>
                        </div>

                        <!-- Primer row: Region, Provincia, Citta, File input -->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Regione</label>
                                    <input type="text" name="region" value="{{$tasa->region}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Provincia</label>
                                    <input type="text" name="province" value="{{$tasa->province}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Citta</label>
                                    <input type="text" name="city" value="{{$tasa->city}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="logoInput" class="form-label">Logo (carica)</label>
                                    <input type="file" name="logo" class="form-control" id="logoInput" accept="image/*">
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->

                        <!-- Segunda fila: Calendarios -->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Inizio</label>
                                    <input type="text" name="inizio" value="{{$tasa->inizio}}" class="form-control" id="inizio-field" data-provider="flatpickr" data-date-format="d M, Y" data-linked-to="#fine-field" placeholder="Seleziona data inizio">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Fine</label>
                                    <input type="text" name="fine" value="{{$tasa->fine}}" class="form-control" id="fine-field" data-provider="flatpickr" data-date-format="d M, Y" placeholder="Seleziona data fine">
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->

                        <!-- Tercera fila: Tassa y otros campos -->
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">Tassa Soggiorno</label>
                                    <input type="text" name="tassa_soggiorno" value="{{$tasa->tassa_soggiorno}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">Giorni Massimo</label>
                                    <input type="text" name="giorni_massimo" value="{{$tasa->giorni_massimo}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="basiInput" class="form-label">età mass. bimbi</label>
                                    <input type="number" name="max_age_children" value="{{$tasa->max_age_children}}" class="form-control" id="basiInput">
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-3 col-md-3">
                                <div>
                                    <label for="labelInput" class="form-label">età min. Adulto</label>
                                    <input type="number" name="min_age_adult" value="{{$tasa->min_age_adult}}" class="form-control" id="labelInput">
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                        <div class="row" style="margin-top: 20px">
                        <div class="col-xxl-3 col-md-3">
                            <button type="submit" class="btn btn-success">Salva</button>
                        </div>
                        </div>
                        </form>
                        </div>

                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div><!--end col-->


    </div><!--end row-->



@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/it.js"></script>
    <script src="{{ asset('js/flatpickr-init.js') }}"></script>
@endsection
