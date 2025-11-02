@extends('layouts.master')

@section('title', 'Indirizzo — Demo')

@section('content')
  <div class="container-xxl py-4">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">Indirizzo</h5></div>
          <div class="card-body">
            <x-address-fields prefix="addr" />
            <hr/>
            <pre id="log" class="mt-3" style="background:#f8f9fa; padding:12px; border-radius:8px"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script-bottom')
<script>
  (function(){
    const root = document.getElementById('addr_addr_wrap')
      || document.getElementById('addr_addr')
      || document.querySelector('[id$="_addr_wrap"]')
      || document;
    root.addEventListener('address:change', function(e){
      const log = document.getElementById('log');
      if (log) log.textContent = JSON.stringify(e.detail, null, 2);
    });
  })();
</script>
@endsection
