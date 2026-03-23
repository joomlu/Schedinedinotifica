@extends('layouts.master-without-nav')
@section('title')
@lang('translation.password-reset')
@endsection
@section('content')

        <div class="auth-page-wrapper pt-5">
            <!-- auth page bg -->
            <div class="auth-one-bg-position auth-one-bg login-hotel-bg"  id="auth-particles">
                <div class="bg-overlay"></div>

                <div class="shape">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                        <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                    </svg>
                </div>
            </div>

            <!-- auth page content -->
            <div class="auth-page-content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            @include('auth.partials.brand-header')
                        </div>
                    </div>
                    <!-- end row -->

                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6 col-xl-5">
                            <div class="card mt-4">

                                <div class="card-body p-4">
                                    <div class="mb-4">
                                        <div class="avatar-lg mx-auto">
                                            <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                                <i class="ri-mail-line"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-2 mt-4">
                                        <div class="text-muted text-center mb-4 mx-lg-3">
                                            <h4 class="">Verifica email</h4>
                                            <p>Schermata di verifica predisposta per il circuito Laravel standard.</p>
                                        </div>

                                        <form>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="digit1-input" class="visually-hidden">Dight 1</label>
                                                        <input type="text"
                                                            class="form-control form-control-lg bg-light border-light text-center"
                                                            onkeyup="moveToNext(this, 2)" maxLength="1"
                                                            id="digit1-input">
                                                    </div>
                                                </div><!-- end col -->

                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="digit2-input" class="visually-hidden">Dight 2</label>
                                                        <input type="text"
                                                            class="form-control form-control-lg bg-light border-light text-center"
                                                            onkeyup="moveToNext(this, 3)" maxLength="1"
                                                            id="digit2-input">
                                                    </div>
                                                </div><!-- end col -->

                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="digit3-input" class="visually-hidden">Dight 3</label>
                                                        <input type="text"
                                                            class="form-control form-control-lg bg-light border-light text-center"
                                                            onkeyup="moveToNext(this, 4)" maxLength="1"
                                                            id="digit3-input">
                                                    </div>
                                                </div><!-- end col -->

                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="digit4-input" class="visually-hidden">Dight 4</label>
                                                        <input type="text"class="form-control form-control-lg bg-light border-light text-center"
                                                            onkeyup="moveToNext(this, 4)" maxLength="1"
                                                            id="digit4-input">
                                                    </div>
                                                </div><!-- end col -->
                                            </div>
                                        </form><!-- end form -->

                                        <div class="mt-3">
                                            <button type="button" class="btn btn-success w-100">Conferma</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->

                            <div class="mt-4 text-center">
                                <p class="mb-0">Non hai ricevuto il codice? <a href="{{ route('password.request') }}" class="fw-semibold text-primary text-decoration-underline">Recupera accesso</a> </p>
                            </div>

                        </div>
                    </div>
                    <!-- end row -->
                </div>
                <!-- end container -->
            </div>
            <!-- end auth page content -->

            <!-- footer -->
            <footer class="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            @include('auth.partials.footer-copy')
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->
        </div>
        <!-- end auth-page-wrapper -->


@endsection
@include('auth.partials.shared-style')
@section('script')
    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/two-step-verification.init.js') }}"></script>
@endsection
