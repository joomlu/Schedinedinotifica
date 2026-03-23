@extends('layouts.master-without-nav')
@section('title')
    Conferma password
@endsection

@section('content')
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg login-hotel-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        @include('auth.partials.brand-header')
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Conferma password</h5>
                                    <p class="text-muted">Per continuare, conferma la tua password personale.</p>

                                    <div class="avatar-xl mx-auto text-primary">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-4">
                                            <i class="ri-shield-keyhole-line fs-1"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-borderless alert-warning text-center mb-2 mx-2" role="alert">
                                    Questa conferma viene richiesta prima di eseguire operazioni sensibili.
                                </div>

                                <div class="p-2">
                                    <form method="POST" action="{{ route('password.confirm') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password personale</label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password"
                                                name="password"
                                                placeholder="Inserisci la password personale"
                                                required
                                                autocomplete="current-password">
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="text-end">
                                            <button class="btn btn-primary w-md" type="submit">Conferma</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="mb-0">
                                Hai bisogno di recuperare l'accesso?
                                <a href="{{ route('password.request') }}" class="fw-semibold text-primary text-decoration-underline">Recupera password</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        @include('auth.partials.footer-copy')
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@include('auth.partials.shared-style')
