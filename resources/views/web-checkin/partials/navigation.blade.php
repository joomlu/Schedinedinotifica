<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Web check-in">
    <a class="btn {{ request()->routeIs('web_checkin.create') ? 'btn-primary' : 'btn-light border' }}" href="{{ route('web_checkin.create') }}" @if(request()->routeIs('web_checkin.create')) aria-current="page" @endif>Nuovo web check-in</a>
    <a class="btn {{ request()->routeIs('schedina.web') ? 'btn-primary' : 'btn-light border' }}" href="{{ route('schedina.web') }}" @if(request()->routeIs('schedina.web')) aria-current="page" @endif>Elenco web check-in</a>
    <a class="btn {{ request()->routeIs('web_checkin.storico') ? 'btn-primary' : 'btn-light border' }}" href="{{ route('web_checkin.storico') }}" @if(request()->routeIs('web_checkin.storico')) aria-current="page" @endif>Storico</a>
    <a class="btn btn-light ms-md-auto" href="{{ route('cestino.index', ['tipo' => 'Web Check-in']) }}">Cestino</a>
</nav>
