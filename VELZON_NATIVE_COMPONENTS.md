# Velzon Native Components Reference
## Guida ai Componenti Nativi Velzon per Laravel

Questa guida documenta tutti i componenti JavaScript e plugin già disponibili nel tuo progetto Velzon, con esempi pratici per l'integrazione nelle tue viste.

---

## 📦 Librerie JavaScript Disponibili

### In `/public/build/libs/`:
- **Bootstrap 5** - Framework CSS/JS base
- **DataTables** - Tabelle con ordinamento/filtri/paginazione
- **Choices.js** - Select avanzati con ricerca
- **Flatpickr** - Date picker moderno
- **SweetAlert2** - Alert/modali eleganti
- **GridJS** - Alternativa a DataTables (più moderna)
- **List.js** - Filtri/ordinamento liste senza backend
- **Dropzone** - Upload file drag & drop
- **ApexCharts** - Grafici interattivi
- **Quill** - Editor WYSIWYG
- **Feather Icons** - Icone vettoriali
- **SimpleMbar** - Scrollbar personalizzate
- **Node Waves** - Effetti ripple sui button

### JavaScript Helper in `/resources/js/pages/`:
- `datatables.init.js` - Configurazioni DataTables pronte
- `form-advanced.init.js` - Form components avanzati
- `listjs.init.js` - Liste filtrabili
- Molti altri helper specifici

---

## 🎯 Implementazione Pratica per le Tue Viste

### 1. **DataTables con AJAX** (per customers/list, componenti/list, schedina/list)

**Attualmente usi CDN esterni**, passiamo alle librerie Velzon locali con AJAX:

#### Template Vista Ottimizzata:
```blade
@extends('layouts.master')
@section('title') @lang('translation.customers') @endsection

@section('css')
<!-- Velzon DataTables CSS locale -->
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" />
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-user-3-line align-middle me-1"></i> @lang('translation.customers')
                </h4>
                <div class="flex-shrink-0">
                    <a href="{{ url('/newcustomer') }}" class="btn btn-success btn-sm">
                        <i class="ri-add-line align-bottom me-1"></i> Nuovo Cliente
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="customers-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Cognome</th>
                            <th>Nazione</th>
                            <th>Città</th>
                            <th>Gruppo</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX data -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- Velzon DataTables JS locale -->
<script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("customers.data") }}', // Nuovo endpoint AJAX
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'surname', name: 'surname' },
            { data: 'country', name: 'country' },
            { data: 'city', name: 'city' },
            { data: 'group', name: 'group' },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <a href="/editcustomer/${row.id}" class="btn btn-sm btn-success">
                            <i class="ri-pencil-fill"></i>
                        </a>
                        <button onclick="deleteCustomer(${row.id})" class="btn btn-sm btn-danger">
                            <i class="ri-delete-bin-5-line"></i>
                        </button>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ri-file-excel-2-line"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="ri-printer-line"></i> Stampa',
                className: 'btn btn-info btn-sm'
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/it-IT.json'
        }
    });
});

function deleteCustomer(id) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Non potrai annullare questa operazione!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sì, elimina!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/deletecustomer/${id}`;
        }
    });
}
</script>
@endsection
```

#### Controller con AJAX endpoint:
```php
// app/Http/Controllers/CustomerController.php

public function getData(Request $request)
{
    if ($request->ajax()) {
        $user = Auth::user();
        
        $query = Customers::query()
            ->where('structure_id', $user->structure_id);
        
        return DataTables::of($query)
            ->addColumn('action', function($row){
                return '<a href="/editcustomer/'.$row->id.'" class="btn btn-sm btn-success">Modifica</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
```

**Route da aggiungere:**
```php
Route::get('/customers/data', [CustomerController::class, 'getData'])->name('customers.data');
```

---

### 2. **Form Avanzati con Choices.js** (per select con ricerca)

#### Per customers/new.blade.php e edit.blade.php:

```blade
@section('css')
<link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

<div class="mb-3">
    <label for="country-select" class="form-label">Nazione</label>
    <select class="form-control" id="country-select" name="country" data-choices>
        <option value="">Seleziona...</option>
        @foreach($countries as $country)
            <option value="{{ $country->code }}">{{ $country->name }}</option>
        @endforeach
    </select>
</div>

@section('script')
<script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inizializza tutti i select con data-choices
    const choicesElements = document.querySelectorAll('[data-choices]');
    choicesElements.forEach(el => {
        new Choices(el, {
            searchEnabled: true,
            searchPlaceholderValue: 'Cerca...',
            noResultsText: 'Nessun risultato',
            itemSelectText: 'Premi per selezionare'
        });
    });
});
</script>
@endsection
```

---

### 3. **Date Picker con Flatpickr**

```blade
@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}">
@endsection

<div class="mb-3">
    <label for="birth-date" class="form-label">Data di Nascita</label>
    <input type="text" class="form-control flatpickr-input" id="birth-date" name="birth_date" 
           data-provider="flatpickr" data-date-format="d/m/Y">
</div>

@section('script')
<script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/flatpickr/l10n/it.js') }}"></script>
<script>
flatpickr("[data-provider='flatpickr']", {
    locale: 'it',
    dateFormat: 'd/m/Y',
    allowInput: true
});
</script>
@endsection
```

---

### 4. **SweetAlert2 per Conferme/Notifiche**

```blade
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
// Esempio conferma eliminazione
function confirmDelete(url) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Questa azione è irreversibile!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sì, elimina!',
        cancelButtonText: 'Annulla',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// Esempio notifica successo
@if(session('success'))
Swal.fire({
    icon: 'success',
    title: 'Fatto!',
    text: '{{ session("success") }}',
    timer: 2000,
    showConfirmButton: false
});
@endif

// Esempio notifica errore
@if(session('error'))
Swal.fire({
    icon: 'error',
    title: 'Errore!',
    text: '{{ session("error") }}'
});
@endif
</script>
@endsection
```

---

### 5. **Modal Bootstrap 5 Avanzato** (già in Bootstrap, ma con Velzon styling)

```blade
<!-- Trigger Button -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
    <i class="ri-add-line align-bottom me-1"></i> Aggiungi Cliente
</button>

<!-- Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="ri-user-add-line align-middle me-1"></i> Nuovo Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customer.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="surname" class="form-label">Cognome</label>
                            <input type="text" class="form-control" id="surname" name="surname" required>
                        </div>
                        <!-- Altri campi... -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line align-bottom me-1"></i> Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

### 6. **GridJS** (Alternativa moderna a DataTables)

Se preferisci un'esperienza più moderna e leggera:

```blade
@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/libs/gridjs/theme/mermaid.min.css') }}">
@endsection

<div id="customers-grid"></div>

@section('script')
<script src="{{ URL::asset('build/libs/gridjs/gridjs.umd.js') }}"></script>
<script>
new gridjs.Grid({
    columns: ['ID', 'Nome', 'Cognome', 'Nazione', 'Città', 'Gruppo', {
        name: 'Azioni',
        formatter: (cell, row) => {
            return gridjs.html(`
                <a href="/editcustomer/${row.cells[0].data}" class="btn btn-sm btn-success">
                    <i class="ri-pencil-fill"></i>
                </a>
                <button onclick="deleteCustomer(${row.cells[0].data})" class="btn btn-sm btn-danger">
                    <i class="ri-delete-bin-5-line"></i>
                </button>
            `);
        }
    }],
    server: {
        url: '{{ route("customers.data") }}',
        then: data => data.data.map(customer => [
            customer.id,
            customer.name,
            customer.surname,
            customer.country,
            customer.city,
            customer.group,
            customer.id
        ])
    },
    search: true,
    sort: true,
    pagination: {
        limit: 10,
        server: {
            url: (prev, page, limit) => `${prev}?page=${page + 1}&limit=${limit}`
        }
    },
    language: {
        search: {
            placeholder: 'Cerca...'
        },
        pagination: {
            previous: 'Precedente',
            next: 'Successivo',
            showing: 'Mostrando',
            results: () => 'risultati'
        }
    }
}).render(document.getElementById("customers-grid"));
</script>
@endsection
```

---

### 7. **Validazione Form con Parsley.js** (opzionale, se vuoi validazione client-side)

Velzon include Parsley per validazione frontend:

```blade
@section('css')
<link href="{{ URL::asset('build/libs/parsleyjs/parsley.css') }}" rel="stylesheet" type="text/css" />
@endsection

<form action="{{ route('customer.store') }}" method="POST" data-parsley-validate>
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" name="name" 
               required 
               data-parsley-required-message="Il nome è obbligatorio">
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" 
               required 
               data-parsley-type="email"
               data-parsley-required-message="L'email è obbligatoria"
               data-parsley-type-message="Inserisci un'email valida">
    </div>
    
    <button type="submit" class="btn btn-primary">Salva</button>
</form>

@section('script')
<script src="{{ URL::asset('build/libs/parsleyjs/parsley.min.js') }}"></script>
<script>
$('form[data-parsley-validate]').parsley();
</script>
@endsection
```

---

## 🎨 Pattern CSS Velzon Comuni

### Card con Header Actions:
```blade
<div class="card">
    <div class="card-header align-items-center d-flex">
        <h4 class="card-title mb-0 flex-grow-1">Titolo</h4>
        <div class="flex-shrink-0">
            <button class="btn btn-success btn-sm">
                <i class="ri-add-line align-bottom me-1"></i> Azione
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Contenuto -->
    </div>
</div>
```

### Ribbon Labels:
```blade
<div class="card ribbon-box">
    <div class="card-body">
        <div class="ribbon ribbon-primary ribbon-shape">Importante</div>
        <h5 class="fs-14 text-end">Contenuto</h5>
    </div>
</div>
```

### Badges & Tags:
```blade
<span class="badge bg-success">Attivo</span>
<span class="badge bg-danger">Inattivo</span>
<span class="badge bg-warning">In Attesa</span>
```

---

## 🔄 AJAX Helper Pattern

### Pattern Riutilizzabile per AJAX CRUD:

```javascript
// Salva in: /public/js/ajax-helpers.js

const AjaxHelper = {
    /**
     * Generic AJAX form submit
     */
    submitForm: function(formId, successCallback) {
        $(`#${formId}`).on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Successo!',
                        text: response.message,
                        timer: 2000
                    });
                    if (successCallback) successCallback(response);
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = Object.values(errors).flat().join('<br>');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        html: errorMsg
                    });
                }
            });
        });
    },
    
    /**
     * Generic DELETE with confirmation
     */
    confirmDelete: function(url, successCallback) {
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Non potrai annullare questa operazione!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, elimina!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire('Eliminato!', response.message, 'success');
                        if (successCallback) successCallback(response);
                    },
                    error: function(xhr) {
                        Swal.fire('Errore!', xhr.responseJSON.message, 'error');
                    }
                });
            }
        });
    },
    
    /**
     * Load data into modal
     */
    loadIntoModal: function(url, modalId) {
        $.get(url, function(data) {
            $(`#${modalId} .modal-body`).html(data);
            $(`#${modalId}`).modal('show');
        });
    }
};
```

**Uso:**
```javascript
// In qualsiasi vista
AjaxHelper.confirmDelete('/customer/delete/123', function() {
    $('#customers-table').DataTable().ajax.reload();
});

AjaxHelper.submitForm('customer-form', function(response) {
    $('#addCustomerModal').modal('hide');
    $('#customers-table').DataTable().ajax.reload();
});
```

---

## 📋 Checklist Conversione Vista

Per ogni vista da convertire, segui questi step:

### ✅ Lista (list.blade.php)
- [ ] Sostituire CDN esterni con libs Velzon locali
- [ ] Implementare DataTables/GridJS con AJAX server-side
- [ ] Aggiungere pulsanti Export (Excel/PDF) con Velzon buttons
- [ ] Implementare SweetAlert2 per conferme eliminazione
- [ ] Tradurre testi in italiano
- [ ] Aggiungere icone Remix Icon
- [ ] Testare responsive

### ✅ Nuovo/Modifica (new.blade.php, edit.blade.php)
- [ ] Convertire select in Choices.js per ricerca
- [ ] Implementare Flatpickr per date
- [ ] Aggiungere validazione Parsley.js (opzionale)
- [ ] Implementare salvataggio AJAX (opzionale)
- [ ] Usare card Velzon con header
- [ ] Aggiungere breadcrumb component
- [ ] Gestire errori validazione con SweetAlert2
- [ ] Testare responsive

---

## 🎯 Prossimi Passi

**Opzione A - Conversione Guidata Vista per Vista:**
Ti mostro 2-3 opzioni di implementazione per ogni vista, tu scegli quale preferisci.

**Opzione B - Sistema Ibrido:**
Mantenere alcune viste con DataTables classico, altre con GridJS, a seconda della complessità.

**Opzione C - Full AJAX SPA-style:**
Convertire tutto in AJAX senza page reload, usando modal per CRUD.

---

## 📞 Durante lo Sviluppo

**Ti chiederò prima di ogni conversione:**
1. Vuoi DataTables o GridJS per questa vista?
2. Preferisci form in modal o pagina separata?
3. AJAX o form submit tradizionale?
4. Vuoi export Excel/PDF?
5. Validazione client (Parsley) o solo server?

**Così mantieni il controllo totale** su come implementare ogni feature!

---

**Pronto per iniziare?** 
Dimmi da quale vista vuoi partire e ti mostro le opzioni disponibili! 🚀
