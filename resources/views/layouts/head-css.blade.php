@yield('css')
@stack('css')
<!-- Bootstrap Css -->
<link href="{{ URL::asset('build/css/bootstrap.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Select2 Css -->
<link href="{{ URL::asset('build/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('build/css/app.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- Flatpickr Css -->
<link href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
<!-- custom Css-->
<link href="{{ URL::asset('build/css/custom.min.css') }}"  rel="stylesheet" type="text/css" />
<style>
    /* Uniforma i dati tabellari: nessun testo in grassetto nel body delle tabelle */
    .table tbody td,
    table tbody td,
    .table tbody td strong,
    table tbody td strong,
    .table tbody td.fw-semibold,
    .table tbody td .fw-semibold,
    .table tbody td.fw-bold,
    .table tbody td .fw-bold {
        font-weight: 400 !important;
    }

    /* Standard visivo unico per le pagine di configurazione */
    .config-page .card {
        border-radius: 0.75rem;
    }

    .config-page .card-header {
        padding: 0.875rem 1rem;
    }

    .config-page .card-body {
        padding: 1rem;
    }

    .config-page .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }

    .config-page .form-control,
    .config-page .form-select {
        min-height: 38px;
    }

    .config-page .btn {
        min-height: 38px;
    }

    .config-page .btn.btn-sm {
        min-height: 31px;
    }

    .config-page .table thead th {
        font-size: 0.825rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .config-page .table tbody td {
        font-size: 0.9rem;
    }
</style>
