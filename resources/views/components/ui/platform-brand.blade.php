@props(['href' => null])
<a href="{{ $href ?? route('root') }}" class="logo logo-dark platform-brand" aria-label="Tanggo Software · SchedinaNotifica">
<span class="logo-sm"><img src="{{ asset('site-assets/img/logo-tanggo-112.webp') }}" alt="Tanggo Software" width="40" height="40"></span>
<span class="logo-lg"><img src="{{ asset('site-assets/img/logo-tanggo-112.webp') }}" alt="" width="44" height="44"><span class="platform-brand-text">Tanggo Software<small>SchedinaNotifica</small></span></span>
</a>
