(function(){
  function initSelect2(){
    if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') return;

    // Evita doppie inizializzazioni
    function ensureSelect2($el, opts){
      if ($el.data('select2')) return; // già inizializzato
      var hasPlaceholder = !!($el.attr('data-placeholder'));
      $el.select2(Object.assign({
        width: '100%',
        language: 'it',
        minimumResultsForSearch: 0, // forza sempre la ricerca
        allowClear: hasPlaceholder
      }, opts || {}));
    }

    // Basic single
    $('.js-example-basic-single').each(function(){
      ensureSelect2($(this), { placeholder: $(this).attr('data-placeholder') || 'Seleziona un\'opzione', minimumResultsForSearch: 0 });
    });

    // Basic multiple
    $('.js-example-basic-multiple').each(function(){
      ensureSelect2($(this), { multiple: true, placeholder: $(this).attr('data-placeholder') || 'Seleziona uno o più', minimumResultsForSearch: 0 });
    });

    // Data array (se presente data-items in JSON)
    $('.js-example-data-array').each(function(){
      var $el = $(this);
      var items = $el.attr('data-items');
      var data = [];
      try { data = items ? JSON.parse(items) : []; } catch(e) { data = []; }
      ensureSelect2($el, { data: data, placeholder: $el.attr('data-placeholder') || 'Seleziona', minimumResultsForSearch: 0 });
    });

    // Templating con icone (usa data-icon)
    function formatWithIcon (state) {
      if (!state.id) { return state.text; }
      var $state = $(
        '<span>' +
          (state.element && state.element.dataset.icon ? '<i class="' + state.element.dataset.icon + ' me-2"></i>' : '') +
          state.text +
        '</span>'
      );
      return $state;
    }
    $('.js-example-templating').each(function(){
      ensureSelect2($(this), { templateResult: formatWithIcon, templateSelection: formatWithIcon, minimumResultsForSearch: 0 });
    });

    // Templating con bandiere (usa data-flag come URL immagine)
    function formatWithFlag (state) {
      if (!state.id) { return state.text; }
      var url = state.element && state.element.dataset.flag;
      var flagClass = state.element && state.element.dataset.flagClass; // supporto librerie CSS (es. flag-icons)
      var html = '<span>';
      if (flagClass) {
        html += '<span class="' + flagClass + ' me-2" style="display:inline-block;vertical-align:middle"></span>';
      } else if (url) {
        html += '<img src="' + url + '" class="me-2" style="width:16px;height:12px;object-fit:cover;border:1px solid #ddd" />';
      }
      html += state.text + '</span>';
      var $state = $(html);
      return $state;
    }
    $('.select-flag-templating').each(function(){
      ensureSelect2($(this), { templateResult: formatWithFlag, templateSelection: formatWithFlag, minimumResultsForSearch: 0 });
    });

    // Autofill (AJAX) - già gestito da public/js/autofill-select.js, ma garantiamo stile/lingua
    $('.autofill-select').each(function(){
      var $el = $(this);
      if ($el.data('select2')) return; // l'altro script la inizializza al focus
      // Inizializzazione base per aspetto coerente (verrà popolata via AJAX al focus)
      ensureSelect2($el, { placeholder: $el.attr('data-placeholder') || 'Seleziona', allowClear: true, minimumResultsForSearch: 0 });
    });

    // Applica Select2 di default a tutti i select non ancora coperti (escludi quelli marcati esplicitamente)
    $('select').filter(function(){
      var $el = $(this);
      return !$el.is('.js-example-basic-single, .js-example-basic-multiple, .js-example-data-array, .js-example-templating, .select-flag-templating, .autofill-select')
             && !$el.data('select2')
             && !$el.is('[data-no-select2]');
    }).each(function(){
      ensureSelect2($(this), { placeholder: $(this).attr('data-placeholder') || 'Seleziona', minimumResultsForSearch: 0 });
    });
  }

  document.addEventListener('DOMContentLoaded', initSelect2);
  // Supporto per contenuti caricati dinamicamente
  document.addEventListener('ajax:complete', initSelect2);
})();
