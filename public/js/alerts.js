(function(){
  if (window.alerts) return; // avoid double init

  function i18n(){
    const d = (window.saI18n||{});
    const get = (path, fallback)=>{
      try{ return path.split('.').reduce((o,k)=>o&&o[k], d) || fallback; }catch(e){ return fallback; }
    };
    return {
      buttons: {
        confirm: get('buttons.confirm','Conferma'),
        cancel: get('buttons.cancel','Annulla'),
        ok: get('buttons.ok','OK')
      },
      titles: {
        success: get('success.title','Operazione completata'),
        error: get('error.title','Errore'),
        warning: get('warning.title','Attenzione'),
        info: get('info.title','Informazione'),
        confirmDelete: get('confirm_delete.title','Confermi eliminazione?'),
        confirmCreate: get('confirm_create.title','Confermi creazione?'),
        confirmUpdate: get('confirm_update.title','Confermi modifica?')
      },
      texts: {
        success: get('success.text','L\'operazione è stata eseguita correttamente.'),
        error: get('error.text','Si è verificato un errore. Riprova più tardi.'),
        warning: get('warning.text','Controlla i dati inseriti.'),
        info: get('info.text','Operazione in corso.'),
        confirmDelete: get('confirm_delete.text','Questa azione non può essere annullata.'),
        confirmCreate: get('confirm_create.text','Vuoi creare questo elemento?'),
        confirmUpdate: get('confirm_update.text','Vuoi salvare le modifiche?')
      }
    };
  }

  const SwalLib = window.Swal;

  function toast(icon, title, text){
    if(!SwalLib) return;
    return SwalLib.fire({
      icon, title: title || undefined, text: text || undefined,
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 2500, timerProgressBar: true
    });
  }

  const alerts = {
    success: (text, title)=>SwalLib && SwalLib.fire({icon:'success',title:title||i18n().titles.success,text:text||i18n().texts.success,confirmButtonText:i18n().buttons.ok}),
    error: (text, title)=>SwalLib && SwalLib.fire({icon:'error',title:title||i18n().titles.error,text:text||i18n().texts.error,confirmButtonText:i18n().buttons.ok}),
    warning: (text, title)=>SwalLib && SwalLib.fire({icon:'warning',title:title||i18n().titles.warning,text:text||i18n().texts.warning,confirmButtonText:i18n().buttons.ok}),
    info: (text, title)=>SwalLib && SwalLib.fire({icon:'info',title:title||i18n().titles.info,text:text||i18n().texts.info,confirmButtonText:i18n().buttons.ok}),

    toastSuccess: (text, title)=>toast('success', title||i18n().titles.success, text||i18n().texts.success),
    toastError: (text, title)=>toast('error', title||i18n().titles.error, text||i18n().texts.error),
    toastWarning: (text, title)=>toast('warning', title||i18n().titles.warning, text||i18n().texts.warning),
    toastInfo: (text, title)=>toast('info', title||i18n().titles.info, text||i18n().texts.info),

    confirm: async function({title, text, icon='question', confirmText, cancelText}){
      if(!SwalLib) return false;
      const res = await SwalLib.fire({
        title: title||i18n().titles.info,
        text: text||i18n().texts.info,
        icon,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText||i18n().buttons.confirm,
        cancelButtonText: cancelText||i18n().buttons.cancel
      });
      return !!res.isConfirmed;
    },

    confirmDelete: function(opts={}){
      return alerts.confirm({
        title: opts.title||i18n().titles.confirmDelete,
        text: opts.text||i18n().texts.confirmDelete,
        icon: 'warning',
        confirmText: opts.confirmText||i18n().buttons.confirm,
        cancelText: opts.cancelText||i18n().buttons.cancel
      });
    },

    confirmCreate: function(opts={}){
      return alerts.confirm({
        title: opts.title||i18n().titles.confirmCreate,
        text: opts.text||i18n().texts.confirmCreate,
        icon: 'question',
        confirmText: opts.confirmText||i18n().buttons.confirm,
        cancelText: opts.cancelText||i18n().buttons.cancel
      });
    },

    confirmUpdate: function(opts={}){
      return alerts.confirm({
        title: opts.title||i18n().titles.confirmUpdate,
        text: opts.text||i18n().texts.confirmUpdate,
        icon: 'question',
        confirmText: opts.confirmText||i18n().buttons.confirm,
        cancelText: opts.cancelText||i18n().buttons.cancel
      });
    }
  };

  // Auto-wire: confirm before DELETE form submission
  document.addEventListener('submit', function(e){
    const form = e.target;
    if(!(form instanceof HTMLFormElement)) return;
    const methodInput = form.querySelector('input[name="_method"]');
    const method = (methodInput && methodInput.value ? methodInput.value : form.getAttribute('method')||'').toUpperCase();
  const wantsConfirm = form.hasAttribute('data-sa-confirm');
  const confirmType = (form.getAttribute('data-sa-confirm')||'').toLowerCase();
    if(method === 'DELETE'){
      e.preventDefault();
      alerts.confirmDelete().then(ok=>{ if(ok) form.submit(); });
    } else if(wantsConfirm){
      e.preventDefault();
      const fn = confirmType==='create' ? alerts.confirmCreate : alerts.confirmUpdate;
      fn().then(ok=>{ if(ok) form.submit(); });
    }
  }, true);

  // Auto-wire: elements with data-sa-confirm action
  document.addEventListener('click', function(e){
    const el = e.target.closest('[data-sa-confirm]');
    if(!el) return;
    const action = el.getAttribute('data-sa-confirm');
    if(action==='delete'){
      e.preventDefault();
      alerts.confirmDelete().then(ok=>{
        if(!ok) return;
        const form = el.closest('form');
        if(form) form.submit();
        else if(el.tagName==='A' && el.getAttribute('href')) window.location.href = el.getAttribute('href');
      });
    }
  });

  window.alerts = alerts;
})();
