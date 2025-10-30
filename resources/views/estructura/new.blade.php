@extends('layouts.master')
@section('title') Nuova Struttura @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Gestione @endslot
        @slot('title') Nuova Struttura @endslot
    @endcomponent

    <div class="row">
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aggiungi Nuova Struttura</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('estructura.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-6">
                                <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" id="name" required>
                            </div>
                            
                            <div class="col-xxl-3 col-md-6">
                                <label for="city" class="form-label">Città</label>
                                <input type="text" name="city" class="form-control" id="city">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="address" class="form-label">Indirizzo</label>
                                <input type="text" name="address" class="form-control" id="address">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="cp" class="form-label">CAP</label>
                                <input type="text" name="cp" class="form-control" id="cp">
                            </div>
                        </div>

                        <div class="row gy-4 mt-2">
                            <div class="col-xxl-3 col-md-6">
                                <label for="phone" class="form-label">Telefono</label>
                                <input type="text" name="phone" class="form-control" id="phone">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="fax" class="form-label">FAX</label>
                                <input type="text" name="fax" class="form-control" id="fax">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" id="email">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="web" class="form-label">Web</label>
                                <input type="url" name="web" class="form-control" id="web">
                            </div>
                        </div>

                        <div class="row gy-4 mt-2">
                            <div class="col-xxl-3 col-md-6">
                                <label for="cf" class="form-label">Codice Fiscale</label>
                                <input type="text" name="cf" class="form-control" id="cf">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="piva" class="form-label">Partita IVA</label>
                                <input type="text" name="piva" class="form-control" id="piva">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="typology" class="form-label">Tipologia</label>
                                <input type="text" name="typology" class="form-control" id="typology">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="clasification" class="form-label">Classificazione</label>
                                <input type="text" name="clasification" class="form-control" id="clasification">
                            </div>
                        </div>

                        <div class="row gy-4 mt-2">
                            <div class="col-xxl-3 col-md-6">
                                <label for="startact" class="form-label">Inizio Attività</label>
                                <input type="date" name="startact" class="form-control" id="startact">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="closeact" class="form-label">Fine Attività</label>
                                <input type="date" name="closeact" class="form-control" id="closeact">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="roomdisp" class="form-label">Camere Disponibili</label>
                                <input type="number" name="roomdisp" class="form-control" id="roomdisp">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="beddisp" class="form-label">Letti Disponibili</label>
                                <input type="number" name="beddisp" class="form-control" id="beddisp">
                            </div>
                        </div>

                        <div class="row gy-4 mt-2">
                            <div class="col-xxl-3 col-md-6">
                                <label for="numshedine" class="form-label">Num. Schedine</label>
                                <input type="number" name="numshedine" class="form-control" id="numshedine">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="ref" class="form-label">Riferimento</label>
                                <input type="text" name="ref" class="form-control" id="ref">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="refpass" class="form-label">Password Riferimento</label>
                                <input type="password" name="refpass" class="form-control" id="refpass">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="logo" class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control" id="logo" accept="image/*">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="ri-save-line me-1"></i> Salva Struttura
                                </button>
                                <a href="{{ route('estructura') }}" class="btn btn-secondary">
                                    <i class="ri-arrow-go-back-line me-1"></i> Annulla
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
