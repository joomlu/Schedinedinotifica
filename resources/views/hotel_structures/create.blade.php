@extends('layouts.master')
@section('title') Nuova Struttura Hotel @endsection
@section('css')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}">
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Strutture Hotel @endslot
@slot('title') Nuova Struttura @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Crea Nuova Struttura Hotel</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('hotel-structures.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Sezione: Informazioni Generali -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="ri-information-line me-1"></i> Informazioni Generali</h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome Hotel <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="typology" class="form-label">Tipologia</label>
                            <select class="form-select @error('typology') is-invalid @enderror" id="typology" name="typology">
                                <option value="">Seleziona...</option>
                                <option value="Hotel" {{ old('typology') == 'Hotel' ? 'selected' : '' }}>Hotel</option>
                                <option value="B&B" {{ old('typology') == 'B&B' ? 'selected' : '' }}>B&B</option>
                                <option value="Resort" {{ old('typology') == 'Resort' ? 'selected' : '' }}>Resort</option>
                                <option value="Albergo" {{ old('typology') == 'Albergo' ? 'selected' : '' }}>Albergo</option>
                                <option value="Agriturismo" {{ old('typology') == 'Agriturismo' ? 'selected' : '' }}>Agriturismo</option>
                            </select>
                            @error('typology')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Descrizione</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Sezione: Credenziali di Accesso -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="ri-key-line me-1"></i> Credenziali di Accesso al Software</h6>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="username_hotel" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username_hotel') is-invalid @enderror" 
                                   id="username_hotel" name="username_hotel" value="{{ old('username_hotel') }}" required>
                            @error('username_hotel')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="password_hotel" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password_hotel') is-invalid @enderror" 
                                   id="password_hotel" name="password_hotel" required>
                            @error('password_hotel')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="schedina_url" class="form-label">URL Software Schedina</label>
                            <input type="url" class="form-control @error('schedina_url') is-invalid @enderror" 
                                   id="schedina_url" name="schedina_url" value="{{ old('schedina_url') }}" 
                                   placeholder="https://...">
                            @error('schedina_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Sezione: Autorizaciones y Fechas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="ri-shield-check-line me-1"></i> Autorizzazioni e Date</h6>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="fecha_registro" class="form-label">Data Registrazione</label>
                            <input type="text" class="form-control flatpickr @error('fecha_registro') is-invalid @enderror" 
                                   id="fecha_registro" name="fecha_registro" value="{{ old('fecha_registro') }}">
                            @error('fecha_registro')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="fecha_vencimiento" class="form-label">Data Scadenza</label>
                            <input type="text" class="form-control flatpickr @error('fecha_vencimiento') is-invalid @enderror" 
                                   id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}">
                            @error('fecha_vencimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">Stato On-line</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="online" name="online" value="1" 
                                       {{ old('online', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="online">Hotel On-line</label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">Stato Attivo</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" 
                                       {{ old('activo', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo">Hotel Attivo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Sezione: Ubicazione e Contatto -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="ri-map-pin-line me-1"></i> Ubicazione e Contatto</h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">Città</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city') }}">
                            @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Indirizzo</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                   id="address" name="address" value="{{ old('address') }}">
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="cp" class="form-label">CAP</label>
                            <input type="text" class="form-control @error('cp') is-invalid @enderror" 
                                   id="cp" name="cp" value="{{ old('cp') }}">
                            @error('cp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="phone" class="form-label">Telefono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="fax" class="form-label">Fax</label>
                            <input type="text" class="form-control @error('fax') is-invalid @enderror" 
                                   id="fax" name="fax" value="{{ old('fax') }}">
                            @error('fax')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="web" class="form-label">Sito Web</label>
                            <input type="url" class="form-control @error('web') is-invalid @enderror" 
                                   id="web" name="web" value="{{ old('web') }}" placeholder="https://...">
                            @error('web')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Sezione: Dati Fiscali e Operativi -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="ri-file-list-line me-1"></i> Dati Fiscali e Operativi</h6>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="cf" class="form-label">Codice Fiscale</label>
                            <input type="text" class="form-control @error('cf') is-invalid @enderror" 
                                   id="cf" name="cf" value="{{ old('cf') }}">
                            @error('cf')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="piva" class="form-label">Partita IVA</label>
                            <input type="text" class="form-control @error('piva') is-invalid @enderror" 
                                   id="piva" name="piva" value="{{ old('piva') }}">
                            @error('piva')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="clasification" class="form-label">Classificazione</label>
                            <select class="form-select @error('clasification') is-invalid @enderror" id="clasification" name="clasification">
                                <option value="">Seleziona...</option>
                                <option value="1 stella" {{ old('clasification') == '1 stella' ? 'selected' : '' }}>1 stella</option>
                                <option value="2 stelle" {{ old('clasification') == '2 stelle' ? 'selected' : '' }}>2 stelle</option>
                                <option value="3 stelle" {{ old('clasification') == '3 stelle' ? 'selected' : '' }}>3 stelle</option>
                                <option value="4 stelle" {{ old('clasification') == '4 stelle' ? 'selected' : '' }}>4 stelle</option>
                                <option value="5 stelle" {{ old('clasification') == '5 stelle' ? 'selected' : '' }}>5 stelle</option>
                            </select>
                            @error('clasification')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" name="logo" accept="image/*">
                            @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="roomdisp" class="form-label">Camere Disponibili</label>
                            <input type="number" class="form-control @error('roomdisp') is-invalid @enderror" 
                                   id="roomdisp" name="roomdisp" value="{{ old('roomdisp') }}" min="0">
                            @error('roomdisp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="beddisp" class="form-label">Posti Letto Disponibili</label>
                            <input type="number" class="form-control @error('beddisp') is-invalid @enderror" 
                                   id="beddisp" name="beddisp" value="{{ old('beddisp') }}" min="0">
                            @error('beddisp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('hotel-structures.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Annulla
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i> Salva Struttura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<!-- Flatpickr JS -->
<script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/flatpickr/l10n/it.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Flatpickr para las fechas
    flatpickr('.flatpickr', {
        locale: 'it',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y'
    });
});
</script>
@endsection
