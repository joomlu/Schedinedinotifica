<div class="mb-4">
    <h1 class="h3 mb-3">Liste, import ed export</h1>
    <nav class="nav nav-tabs gap-2" aria-label="Liste, import ed export">
        <a class="nav-link {{ request()->routeIs('customer.export.*') ? 'active' : '' }}" {{ request()->routeIs('customer.export.*') ? 'aria-current=page' : '' }} href="{{ route('customer.export.index', session('clienti_export.'.\App\Support\StrutturaCorrente::getId(), [])) }}">Liste ed export</a>
        <a class="nav-link {{ request()->routeIs('customer.import.*', 'customer.imported.*') ? 'active' : '' }}" {{ request()->routeIs('customer.import.*', 'customer.imported.*') ? 'aria-current=page' : '' }} href="{{ route('customer.import.index') }}">Import clienti</a>
    </nav>
</div>
