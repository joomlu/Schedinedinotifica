@push('css')
<style>
    .login-hotel-bg {
        background-image: url("{{ asset('images/login-hotel-reception.png') }}") !important;
        background-size: cover !important;
        background-position: center 68% !important;
        background-repeat: no-repeat !important;
    }

    .login-hotel-bg .bg-overlay {
        background: linear-gradient(135deg, rgba(62, 17, 23, 0.58), rgba(140, 29, 44, 0.34)) !important;
        opacity: 1 !important;
    }

    .auth-page-content {
        padding-bottom: 1.25rem;
    }

    .footer {
        padding-top: .35rem;
        padding-bottom: 2.4rem;
    }

    .login-footer-copy {
        max-width: 860px;
        margin: 0 auto;
    }

    .login-footer-copy p {
        line-height: 1.45;
    }

    .login-footer-meta {
        letter-spacing: .01em;
    }
</style>
@endpush
