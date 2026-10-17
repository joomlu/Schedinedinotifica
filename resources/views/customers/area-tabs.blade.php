@if(request()->routeIs('newcustomer', 'customers'))
@php
    $isNewCustomer = request()->routeIs('newcustomer');
    $customerListContext = array_intersect_key(
        session('clienti_elenco.'.\App\Support\StrutturaCorrente::getId(), []),
        array_flip(['q', 'tipo_cliente', 'stato', 'ordine', 'page'])
    );
@endphp
<nav class="nav nav-pills nav-fill gap-2 mb-4" aria-label="Nuovo / Lista clienti">
    <a href="{{ route('newcustomer') }}"
       class="nav-link border fs-5 fw-semibold py-3 {{ $isNewCustomer ? 'active' : 'bg-body-tertiary' }}"
       @if($isNewCustomer) aria-current="page" @endif>Nuovo</a>
    <a href="{{ route('customers', $customerListContext) }}"
       class="nav-link border fs-5 fw-semibold py-3 {{ !$isNewCustomer ? 'active' : 'bg-body-tertiary' }}"
       @if(!$isNewCustomer) aria-current="page" @endif>Lista</a>
</nav>
@else
<div class="mb-4">
    <h1 class="h3 mb-3">Liste, import ed export</h1>
    <nav class="nav nav-tabs gap-2" aria-label="Liste, import ed export">
        <a class="nav-link {{ request()->routeIs('customer.export.*') ? 'active' : '' }}" {{ request()->routeIs('customer.export.*') ? 'aria-current=page' : '' }} href="{{ route('customer.export.index', array_intersect_key(session('clienti_export.'.\App\Support\StrutturaCorrente::getId(), []), array_flip(['q', 'tipo_cliente', 'page']))) }}">Liste ed export</a>
        <a class="nav-link {{ request()->routeIs('customer.import.*', 'customer.imported.*') ? 'active' : '' }}" {{ request()->routeIs('customer.import.*', 'customer.imported.*') ? 'aria-current=page' : '' }} href="{{ route('customer.import.index') }}">Import clienti</a>
    </nav>
</div>
@endif
