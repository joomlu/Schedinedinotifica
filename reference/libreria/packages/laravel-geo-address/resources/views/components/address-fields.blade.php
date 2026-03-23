@php
  $prefix = $prefix ?? 'addr';
  $containerId = $containerId ?? ($prefix . '_addr_wrap');
  $showInline = isset($showInline) ? (bool)$showInline : true;
  $inlineId = $inlineId ?? ($prefix . '_address_inline');
  $types = $types ?? [
    'Via','Viale','Vicolo','Piazza','Piazzale','Piazzetta','Corso','Largo',
    'Borgo','Contrada','Traversa','Rotonda','Salita','Discesa','Rampa',
    'Passaggio','Galleria','Cortile','Calle','Campo','Fondamenta','Stradone'
  ];
@endphp
<div id="{{ $containerId }}" class="address-fields-component">
  <div class="row g-3">
    <div class="col-md-3">
      <label for="{{ $prefix }}_typeaway" class="form-label">Tipo di Via</label>
      <select id="{{ $prefix }}_typeaway" class="form-select" data-placeholder="Seleziona tipo via">
        <option value="">Seleziona...</option>
        @foreach($types as $t)
          <option value="{{ $t }}">{{ $t }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-5">
      <label for="{{ $prefix }}_address" class="form-label">Strada</label>
      <input id="{{ $prefix }}_address" type="text" class="form-control" placeholder="Es. Garibaldi">
    </div>
    <div class="col-md-2">
      <label for="{{ $prefix }}_num" class="form-label">Num.</label>
      <input id="{{ $prefix }}_num" type="text" class="form-control" placeholder="Es. 12, 12B">
    </div>
    <div class="col-md-2">
      <label for="{{ $prefix }}_internal" class="form-label">Int.</label>
      <input id="{{ $prefix }}_internal" type="text" class="form-control" placeholder="Interno, scala, ecc.">
    </div>
    @if($showInline)
      <div class="col-12">
        <div id="{{ $inlineId }}" class="form-text text-muted small mt-1"></div>
      </div>
    @endif
  </div>
</div>

<script>
  (function(){
    var root = document.getElementById(@json($containerId));
    if (!root) return;
    var ids = {
      typeaway: document.getElementById(@json($prefix . '_typeaway')),
      address: document.getElementById(@json($prefix . '_address')),
      num: document.getElementById(@json($prefix . '_num')),
      internal: document.getElementById(@json($prefix . '_internal')),
      inline: document.getElementById(@json($inlineId)),
    };
    function getValues(){
      return {
        typeaway: (ids.typeaway && ids.typeaway.value || '').trim(),
        address: (ids.address && ids.address.value || '').trim(),
        num: (ids.num && ids.num.value || '').trim(),
        internal: (ids.internal && ids.internal.value || '').trim(),
      };
    }
    function renderInline(){
      if (!ids.inline) return;
      var v = getValues();
      var partsLeft = [];
      var mainLeft = [v.typeaway, v.address].filter(Boolean).join(' ');
      if (mainLeft) partsLeft.push(mainLeft);
      if (v.num) partsLeft.push('Num. ' + v.num);
      if (v.internal) partsLeft.push('Int. ' + v.internal);
      var left = partsLeft.join(' · ');
      ids.inline.textContent = left ? ('Indirizzo: ' + left) : '';
    }
    function notify(){
      try {
        root.dispatchEvent(new CustomEvent('address:change', { detail: getValues() }));
      } catch(_){}
    }
    ['change','input'].forEach(function(evt){
      Object.values(ids).forEach(function(el){
        if (el && el.tagName) el.addEventListener(evt, function(){ renderInline(); notify(); });
      });
    });
    renderInline();
  })();
</script>
