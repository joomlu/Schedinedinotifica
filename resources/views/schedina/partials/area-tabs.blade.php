@php
    $schedinaNavigationKey = 'schedine_navigazione.'.\App\Support\StrutturaCorrente::getId();
    if (request()->routeIs('schedina', 'schedina.bozze')) {
        session()->put($schedinaNavigationKey.'.'.(request()->routeIs('schedina.bozze') ? 'bozze' : 'elenco'),
            array_intersect_key(request()->query(), array_flip(['q', 'page'])));
    }
    $schedinaNewActive = request()->routeIs('newschedina');
    $schedinaDraftActive = request()->routeIs('schedina.bozze')
        || (request()->routeIs('schedina.edit') && ($schedina->circuito ?? null) === 'bozza');
@endphp
<nav class="nav nav-pills nav-fill gap-2 mb-4 customer-entry-nav" aria-label="Nuova / Elenco / Bozze schedine">
    <a href="{{ route('newschedina') }}"
       class="nav-link border fs-5 fw-semibold py-3 {{ $schedinaNewActive ? 'active' : 'bg-body-tertiary' }}"
       @if($schedinaNewActive) aria-current="page" @endif>Nuova schedina</a>
    <a href="{{ route('schedina', session($schedinaNavigationKey.'.elenco', [])) }}"
       class="nav-link border fs-5 fw-semibold py-3 {{ !$schedinaNewActive && !$schedinaDraftActive ? 'active' : 'bg-body-tertiary' }}"
       @if(!$schedinaNewActive && !$schedinaDraftActive) aria-current="page" @endif>Elenco schedine</a>
    <a href="{{ route('schedina.bozze', session($schedinaNavigationKey.'.bozze', [])) }}"
       class="nav-link border fs-5 fw-semibold py-3 {{ $schedinaDraftActive ? 'active' : 'bg-body-tertiary' }}"
       @if($schedinaDraftActive) aria-current="page" @endif>Bozze</a>
</nav>
