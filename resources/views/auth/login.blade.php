@extends('layouts.master-without-nav')
@section('title', 'Accedi a SchedinaNotifica')
@section('content')
<div class="sn-login">
    <a class="sn-login-skip" href="#accesso">Vai al modulo di accesso</a>
    <header class="sn-login-header">
        <a class="sn-login-brand" href="{{ route('site.home') }}" aria-label="Tanggo Software — pagina iniziale">
            <img src="{{ asset('site-assets/img/logo-tanggo.png') }}" alt="" width="56" height="56">
            <span><strong>Tanggo Software</strong><small>SchedinaNotifica</small></span>
        </a>
        <a class="sn-login-back" href="{{ route('site.home') }}"><span aria-hidden="true">←</span> Torna al sito</a>
    </header>

    <main class="sn-login-main">
        <section class="sn-login-story" aria-labelledby="sn-login-story-title">
            <img src="{{ asset('site-assets/img/foto-reception.webp') }}" alt="" width="1600" height="900" fetchpriority="high">
            <div class="sn-login-story-copy">
                <p class="sn-login-eyebrow">Al fianco di chi accoglie</p>
                <h2 id="sn-login-story-title">Più tempo<br>per i tuoi ospiti.</h2>
                <p>Ritrova tutto ciò che serve per la tua struttura.<br class="sn-login-desktop-break"> E torna a dedicarti all’ospitalità.</p>
                <span class="sn-login-story-sign">SchedinaNotifica <span aria-hidden="true">/</span> Tanggo Software</span>
            </div>
        </section>

        <section class="sn-login-panel" aria-labelledby="sn-login-title">
            <div class="sn-login-form-wrap">
                <p class="sn-login-eyebrow">La tua area di lavoro</p>
                <h1 id="sn-login-title">Accesso struttura</h1>
                <p class="sn-login-intro">Accedi a SchedinaNotifica e riprendi la gestione della tua struttura.</p>
                <form id="accesso" action="{{ route('login') }}" method="POST" tabindex="-1">
                    @csrf
                    <div class="sn-login-field">
                        <label for="login">Nome di accesso o email <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" id="login" name="login" placeholder="Il tuo nome di accesso o la tua email" autocomplete="username" aria-required="true" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
                        @error('login')
                            <p class="sn-login-error" id="login-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sn-login-field">
                        <label for="password-input">Password personale <span aria-hidden="true">*</span></label>
                        <div class="sn-login-password">
                            <input type="password" class="form-control password-input @error('password') is-invalid @enderror" name="password" placeholder="La tua password" id="password-input" autocomplete="current-password" aria-required="true" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button class="sn-login-toggle" type="button" id="password-addon" aria-label="Mostra password" aria-controls="password-input" aria-pressed="false"><i class="ri-eye-line" aria-hidden="true"></i></button>
                        </div>
                        @error('password')
                            <p class="sn-login-error" id="password-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sn-login-options">
                        <div class="sn-login-remember">
                            <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                            <label for="auth-remember-check">Ricordami</label>
                        </div>
                        <a href="{{ route('password.request') }}">Password dimenticata?</a>
                    </div>
                    <button class="sn-login-submit" type="submit">Accedi <span aria-hidden="true">→</span></button>
                </form>
                <div class="sn-login-help">
                    <span class="sn-login-help-icon" aria-hidden="true"><i class="ri-customer-service-2-line"></i></span>
                    <div><p>Hai bisogno di una mano?</p><a class="sn-login-whatsapp" href="https://wa.me/393930991968?text=Ciao%2C%20ho%20bisogno%20di%20assistenza%20con%20SchedinaNotifica." target="_blank" rel="noopener noreferrer" aria-label="Assistenza su WhatsApp, si apre in una nuova scheda"><i class="ri-whatsapp-line" aria-hidden="true"></i> Scrivici su WhatsApp <span aria-hidden="true">↗</span></a><a class="sn-login-contact" href="{{ route('site.contatti') }}">Altri contatti Tanggo Software</a></div>
                </div>
            </div>
        </section>
    </main>
    <div class="sn-login-about">
        <p>Software gestionale per ospitalità, ricezione e adempimenti telematici.</p>
        <p>Realizzato con ❤️ per il lavoro quotidiano della struttura.</p>
    </div>
    <footer class="sn-login-footer">© 2026 @Schedine di Notifica - Tanggo Platform | Versione 2.0</footer>
</div>
@endsection
@include('auth.partials.login-style')
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('password-addon');
        var input = document.getElementById('password-input');
        if (!toggle || !input) return;
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            var visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(visible));
            toggle.setAttribute('aria-label', visible ? 'Nascondi password' : 'Mostra password');
            toggle.querySelector('i').className = visible ? 'ri-eye-off-line' : 'ri-eye-line';
        });
    });
</script>
@endsection
