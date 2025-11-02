<?php
  $prefix = $prefix ?? 'addr';
  $containerId = $containerId ?? ($prefix . '_addr_wrap');
  $showInline = isset($showInline) ? (bool)$showInline : true;
  $inlineId = $inlineId ?? ($prefix . '_address_inline');
  $types = $types ?? [
    'Via','Viale','Vicolo','Piazza','Piazzale','Piazzetta','Corso','Largo',
    'Borgo','Contrada','Traversa','Rotonda','Salita','Discesa','Rampa',
    'Passaggio','Galleria','Cortile','Calle','Campo','Fondamenta','Stradone'
  ];
?>
<div id="<?php echo e($containerId); ?>" class="address-fields-component">
  <div class="row g-3">
    <div class="col-md-3">
      <label for="<?php echo e($prefix); ?>_typeaway" class="form-label">Tipo di Via</label>
      <select id="<?php echo e($prefix); ?>_typeaway" class="form-select" data-placeholder="Seleziona tipo via">
        <option value="">Seleziona...</option>
        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>
    <div class="col-md-5">
      <label for="<?php echo e($prefix); ?>_address" class="form-label">Strada</label>
      <input id="<?php echo e($prefix); ?>_address" type="text" class="form-control" placeholder="Es. Garibaldi">
    </div>
    <div class="col-md-2">
      <label for="<?php echo e($prefix); ?>_num" class="form-label">Num.</label>
      <input id="<?php echo e($prefix); ?>_num" type="text" class="form-control" placeholder="Es. 12, 12B">
    </div>
    <div class="col-md-2">
      <label for="<?php echo e($prefix); ?>_internal" class="form-label">Int.</label>
      <input id="<?php echo e($prefix); ?>_internal" type="text" class="form-control" placeholder="Interno, scala, ecc.">
    </div>
    <?php if($showInline): ?>
      <div class="col-12">
        <div id="<?php echo e($inlineId); ?>" class="form-text text-muted small mt-1"></div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  (function(){
    var root = document.getElementById(<?php echo json_encode($containerId, 15, 512) ?>);
    if (!root) return;
    var ids = {
      typeaway: document.getElementById(<?php echo json_encode($prefix . '_typeaway', 15, 512) ?>),
      address: document.getElementById(<?php echo json_encode($prefix . '_address', 15, 512) ?>),
      num: document.getElementById(<?php echo json_encode($prefix . '_num', 15, 512) ?>),
      internal: document.getElementById(<?php echo json_encode($prefix . '_internal', 15, 512) ?>),
      inline: document.getElementById(<?php echo json_encode($inlineId, 15, 512) ?>),
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
<?php /**PATH /Users/jorgeluccitelli/Herd/Schedinedinotifica/resources/views/components/address-fields.blade.php ENDPATH**/ ?>