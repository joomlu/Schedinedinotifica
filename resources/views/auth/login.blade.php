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
                <p class="sn-login-story-detail">Software gestionale per ospitalità, ricezione e adempimenti telematici.</p>
                <p class="sn-login-story-detail">Realizzato con <span class="sn-login-heart" role="img" aria-label="amore"><i class="ri-heart-fill" aria-hidden="true"></i><span hidden>❤️</span></span> per il lavoro quotidiano della struttura.</p>
                <p class="sn-login-story-copyright">© 2026 @Schedine di Notifica - Tanggo Platform | Versione 2.0</p>
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
                    <div><p>Hai bisogno di una mano?</p><a class="sn-login-contact" href="{{ route('site.contatti') }}">Altri contatti Tanggo Software</a></div>
                </div>
            </div>
        </section>
    </main>
<footer class="site-footer">
      <div class="container footer-grid">
        <div>
          <a class="brand" href="/">
            <img class="brand-logo" src="/site-assets/img/logo-tanggo.png" alt="" width="56" height="56">
            <span>
              <strong>
                Tanggo Software
              </strong>
              <small>
                SchedinaNotifica
              </small>
            </span>
          </a>
          <p>
            Il software e l’affiancamento per il lavoro della tua struttura.
          </p>
        </div>
        <div>
          <h2>
            Esplora
          </h2>
          <a href="/moduli">
            Funzionalità
          </a>
          <a href="/ampliamenti">
            Moduli e ampliamenti
          </a>
          <a href="/demo">
            Demo del programma
          </a>
        </div>
        <div>
          <h2>
            Parliamone
          </h2>
          <a href="/prezzi">
            Piani e prezzi
          </a>
          <a href="/prezzi#max-full">
            Servizio Max Full
          </a>
          <a href="/contatti#demo">
            Richiedi una demo
          </a>
        </div>
        <div>
          <h2>
            La tua area
          </h2>
          <a href="/login">
            Accedi al gestionale
          </a>
          <a href="/contatti">
            Contatti e informazioni
          </a>
          <p>
            Nessun dato degli ospiti nel modulo pubblico: per l’assistenza usa il tuo accesso riservato.
          </p>
        </div>
      </div>
      <div class="container footer-bottom">
        <nav aria-label="Informazioni legali"><a href="/note-legali">Note legali</a> · <a href="/privacy">Privacy</a> · <a href="/cookie">Cookie</a></nav>
        <span>
          © 2026 Experience S.r.l. · SchedinaNotifica
        </span>
        <a href="https://www.cv.joomlu.com/" target="_blank" rel="noopener">
          Realizzato da Jorge Omar Luccitelli
        </a>
      </div>
    </footer>
<a class="whatsapp-contact" href="https://wa.me/393930991968?text=Ciao%2C%20vorrei%20informazioni%20su%20SchedinaNotifica." target="_blank" rel="noopener noreferrer" aria-label="Contattaci su WhatsApp, si apre in una nuova scheda">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M20.52 3.48A11.9 11.9 0 0 0 12.05 0C5.47 0 .11 5.35.1 11.93c0 2.1.55 4.15 1.6 5.96L0 24l6.26-1.64a11.94 11.94 0 0 0 5.78 1.47h.01C18.63 23.83 24 18.48 24 11.9c0-3.18-1.24-6.17-3.48-8.42ZM12.05 21.8a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.97.99-3.63-.24-.37a9.87 9.87 0 0 1-1.52-5.25c0-5.46 4.44-9.9 9.9-9.9a9.83 9.83 0 0 1 7 2.9 9.84 9.84 0 0 1 2.9 7c0 5.45-4.45 9.87-9.91 9.87Zm5.43-7.4c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.18-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.1 4.49.71.3 1.27.48 1.7.62.71.22 1.36.19 1.87.11.57-.08 1.76-.72 2-1.41.25-.7.25-1.3.18-1.42-.08-.13-.28-.2-.58-.35Z"/></svg>
    </a>
</div>
@endsection
@include('auth.partials.login-style')
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
const whatsappContact = document.querySelector('.whatsapp-contact');
if (whatsappContact) {
  let pending = false;
  const avoidOverlap = () => {
    pending = false;
    const button = whatsappContact.getBoundingClientRect();
    const currentLift = parseInt(whatsappContact.style.getPropertyValue('--whatsapp-lift'), 10) || 0;
    const controls = [...document.querySelectorAll('main .btn, main button, main input:not([type=hidden]), main textarea, main [role=combobox], footer a, footer .footer-bottom > span')]
      .map(control => control.getBoundingClientRect()).filter(rect => rect.width > 1 && rect.height > 1);
    // Proteggere anche le righe di testo, senza trattare come piene le aree vuote delle schede.
    document.querySelectorAll('main h1, main h2, main h3, main p, main summary, main small, main strong, main li, main figcaption, main .dashboard-tour, main .dashboard-label').forEach(element => {
      const box = element.getBoundingClientRect();
      if (box.bottom < button.top + currentLift - 252 || box.top > button.bottom + currentLift || box.right < button.left) return;
      const range = document.createRange();
      range.selectNodeContents(element);
      controls.push(...range.getClientRects());
    });
    const lift = Array.from({ length: 22 }, (_, index) => index * 12).find(offset => {
      const top = button.top + currentLift - offset;
      const bottom = button.bottom + currentLift - offset;
      return top >= 100 && !controls.some(rect => rect.left < button.right + 8 && rect.right > button.left - 8 && rect.top < bottom + 8 && rect.bottom > top - 8);
    });
    whatsappContact.style.setProperty('--whatsapp-lift', `${lift ?? 0}px`);
    whatsappContact.classList.toggle('is-obscured', lift === undefined && document.activeElement !== whatsappContact);
  };
  const schedule = () => { if (!pending) { pending = true; requestAnimationFrame(avoidOverlap); } };
  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule);
  window.addEventListener('load', schedule);
  document.fonts?.ready.then(schedule);
  document.addEventListener('focusin', schedule);
  document.addEventListener('focusout', schedule);
  schedule();
}
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
