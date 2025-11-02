(function(global){
  // GeoSelect
  // Componente di orchestrazione Select2 per Nazione→Regione→Provincia→Città→CAP
  // Obiettivi: UX coerente, bidirezionale, performance accettabili, opzioni opt-in per comportamenti estesi.
  // Contratto sintetico:
  // - Input: selettori jQuery per nation, region, province, city, cap; flag di configurazione.
  // - Output: sincronizzazione dei campi e callback onChange(values) con etichette/valori correnti.
  // - Errori: le chiamate AJAX falliscono in silenzio (no throw), mantenendo i campi interagibili.
  // - Successo: i campi si popolano correttamente in avanti e all’indietro secondo i flag.
  const DEBUG = !!global.GEO_DEBUG;
  function makeAjaxSelect($el, options){
    const cfg = Object.assign({
      width: '100%',
      language: 'it',
      placeholder: $el.attr('data-placeholder') || 'Seleziona',
      allowClear: true,
      ajax: {
        transport: function (params, success, failure) {
      const client = (window.http && typeof window.http.get === 'function') ? window.http : (typeof axios !== 'undefined' ? axios : null);
          const query = params.data && params.data.term ? params.data.term : '';
          const add = options.extraParams ? options.extraParams() : {};
      if (!client) { return failure(new Error('HTTP client not ready')); }
      client.get(options.url, { params: Object.assign({ q: query }, add) })
        .then(resp => success(resp))
        .catch(err => failure(err));
        },
        processResults: function(resp){
          const items = Array.isArray(resp.data) ? resp.data : resp;
          const mapped = (options.mapItems ? items.map(options.mapItems) : items);
          return { results: mapped };
        },
        delay: 150
      }
    }, options.select2 || {});
    const instance = $el.select2(cfg);
    // Forza il primo fetch anche senza digitare (quando si apre il menu)
    $el.on('select2:open', function(){
      const sf = document.querySelector('.select2-container--open .select2-search__field');
      if (sf) {
        // trigger input per attivare la chiamata AJAX con q=""
        const ev = new Event('input', { bubbles: true });
        sf.dispatchEvent(ev);
      }
    });
    return instance;
  }

  class GeoSelect {
    constructor(cfg){
      this.$nation = $(cfg.nation);
      this.$region = $(cfg.region);
      this.$province = $(cfg.province);
      this.$city = $(cfg.city);
      this.$cap = $(cfg.cap);
      this.container = cfg.container ? (typeof cfg.container === 'string' ? document.querySelector(cfg.container) : cfg.container) : null;
      // HTTP client (axios o wrapper http)
      this.http = (window.http && typeof window.http.get === 'function') ? window.http : axios;

      // Manual inputs (optional): if not provided, infer by appending _manual to IDs
      this.$regionManual = $(cfg.regionManual || (this.$region.length ? ('#' + this.$region.attr('id') + '_manual') : null));
      this.$provinceManual = $(cfg.provinceManual || (this.$province.length ? ('#' + this.$province.attr('id') + '_manual') : null));
      this.$cityManual = $(cfg.cityManual || (this.$city.length ? ('#' + this.$city.attr('id') + '_manual') : null));
      this.$capManual = $(cfg.capManual || (this.$cap.length ? ('#' + this.$cap.attr('id') + '_manual') : null));

      this.onChange = typeof cfg.onChange === 'function' ? cfg.onChange : function(){};
      this.preselectItaly = !!cfg.preselectItaly;
      this.manualForNonItaly = cfg.manualForNonItaly !== false; // default true
      // Feature flags (opt-in per evitare impatti dove già funziona)
      this.filterCapByCity = !!cfg.filterCapByCity; // filtra CAP in base alla città selezionata
      this.autoSelectUniqueCap = !!cfg.autoSelectUniqueCap; // seleziona automaticamente il CAP se unico
      this.backfillRegionFromProvince = cfg.backfillRegionFromProvince !== false; // di default true
      this.isManualMode = false;
      this.init();
    }
    init(){
      const self = this;
      function reset($el){ $el.val(null).trigger('change').prop('disabled', true); }
      function enable($el){ $el.prop('disabled', false); }
  const http = this.http;
      // Flag per evitare reset quando il set è programmatico
      this._silent = { region: false, province: false };
      const notify = ()=>{
        try {
          const v = this.getValues();
          // callback utente
          try { this.onChange(v); } catch(_){ }
          // evento DOM per componenti Blade riusabili
          if (this.container && typeof CustomEvent === 'function'){
            this.container.dispatchEvent(new CustomEvent('geoselect:change', { detail: v }));
          }
        } catch(_){ }
      };
      const ensureOption = ($sel, value, text)=>{
        if (!value) return;
        // Verifica esistenza
        let exists = false;
        $sel.find('option').each(function(){ if ($(this).val() == value) { exists = true; return false; } });
        if (!exists){
          // Aggiungi l'opzione (non selezionata) con etichetta/text coerente
          const opt = new Option(text || value, value, false, false);
          $sel.append(opt);
        }
        // Se Select2 è attivo, usa la sua API per selezionare e aggiornare la UI
        if ($sel.data('select2')){
          try {
            $sel.val(value);
            $sel.select2('trigger', 'select', { data: { id: value, text: text || value } });
          } catch(err){
            // Fallback a change standard
            $sel.val(value).trigger('change');
          }
        } else {
          // Fallback nativo
          $sel.val(value).trigger('change');
        }
      };
      // Forza una selezione singola sostituendo le opzioni correnti (utile quando Select2 non aggiorna la label)
      const ensureSingleSelection = ($sel, value, text)=>{
        if (!value) return;
        const opt = new Option(text || value, value, true, true);
        $sel.html('').append(opt);
        if ($sel.data('select2')){
          try {
            $sel.trigger('change.select2');
          } catch(err){ $sel.trigger('change'); }
        } else {
          $sel.trigger('change');
        }
      };
      const setProvinceBySigla = (sigla)=>{
        if (!sigla) return Promise.resolve();
        return http.get('/provinces-all', { params: { q: sigla } }).then(resp=>{
          const list = Array.isArray(resp.data) ? resp.data : resp;
          const item = (list || []).find(p=> (p.sigla_provincia||'').toUpperCase() === sigla.toUpperCase());
          if (item){
            const text = (item.denominazione_provincia || '') + (item.sigla_provincia ? (' ('+item.sigla_provincia+')') : '');
            this._silent.province = true;
            ensureOption(this.$province, item.sigla_provincia, text);
            if (item.codice_regione){
              return setRegionByCode(item.codice_regione);
            }
          }
          return Promise.resolve();
        });
      };
      const setRegionByCode = (code)=>{
        if (!code) return Promise.resolve();
        return http.get('/regions', { params: { q: code } }).then(resp=>{
          const list = Array.isArray(resp.data) ? resp.data : resp;
          const item = (list || []).find(r=> (r.codice_regione||'').toString() === code.toString());
          if (item){
            this._silent.region = true;
            ensureOption(this.$region, item.codice_regione, item.denominazione_regione || item.codice_regione);
          }
          return Promise.resolve();
        });
      };

      // Nazione: usa la denominazione come value per non cambiare i dati salvati rispetto ai form esistenti
      makeAjaxSelect(this.$nation, {
        url: '/nations',
        mapItems: function(item){
          const name = item.denominazione_nazione || item.denominazione_cittadinanza || item.sigla_nazione || '';
          return { id: name, text: name };
        }
      });

      // Preselect Italia
      if (this.preselectItaly){
        const opt = new Option('Italia', 'IT', true, true);
        this.$nation.append(opt).trigger('change');
        enable(this.$region); enable(this.$province); enable(this.$city); enable(this.$cap);
      }

      // Regione
      makeAjaxSelect(this.$region, {
        url: '/regions',
        mapItems: function(item){ return { id: item.codice_regione, text: item.denominazione_regione }; }
      });
      
      // Provincia (ricerca globale o filtrata per regione se presente)
      makeAjaxSelect(this.$province, {
        url: '/provinces-all',
        extraParams: ()=>({ codice_regione: this.$region.val() || '' }),
        mapItems: function(item){
          const name = item.denominazione_provincia || '';
          const sigla = item.sigla_provincia || '';
          return { id: sigla, text: name + (sigla ? ' ('+sigla+')' : ''), _raw: item };
        }
      });

      // Città (ricerca anche globale se provincia non selezionata)
      makeAjaxSelect(this.$city, {
        url: '/cities-by-province',
        extraParams: ()=>({ sigla_provincia: this.$province.val() || '' }),
        mapItems: function(item){
          const name = item.denominazione_ita || item.Comune || '';
          const cap = item.cap || item.CAP || '';
          const prov = item.sigla_provincia || item.Provincia || '';
          return {
            // Salva come valore il nome città per coerenza con i dati storici del form
            id: name,
            // Mostrare solo il nome della città (senza provincia o CAP)
            text: name,
            _raw: item
          };
        }
      });

      // CAP (ricerca anche globale se provincia non selezionata)
      makeAjaxSelect(this.$cap, {
        url: '/cap-by-province',
        extraParams: ()=>{
          const base = { sigla_provincia: this.$province.val() || '' };
          if (this.filterCapByCity){
            base.comune = (this._selectedCityLabel || '');
            base.codice_istat = (this._selectedCity && this._selectedCity.codice_istat ? this._selectedCity.codice_istat : '');
          }
          return base;
        },
        mapItems: function(item){
          const cap = item.cap || item.CAP || '';
          // Visualizza solo il numero del CAP
          return { id: cap, text: cap, _raw: item };
        }
      });

      // Enable/Reset logic
      const enterManualMode = ()=>{
        this.isManualMode = true;
        // Hide Select2 groups and enable manual inputs
        if (this.$region.data('select2')) this.$region.select2('close');
        if (this.$province.data('select2')) this.$province.select2('close');
        if (this.$city.data('select2')) this.$city.select2('close');
        if (this.$cap.data('select2')) this.$cap.select2('close');

        this.$region.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');
        this.$province.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');
        this.$city.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');
        this.$cap.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');

        this.$regionManual.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        this.$provinceManual.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        this.$cityManual.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        this.$capManual.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        // In modalità manuale per nazioni estere, consenti CAP/Postal code alfanumerici con max 10
        if (this.$capManual && this.$capManual.length){
          this.$capManual.attr('maxlength', '10');
          this.$capManual.attr('pattern', '^[A-Za-z0-9\-\s]{1,10}$');
          this.$capManual.attr('title', 'Inserisci un CAP/Postal code valido (fino a 10 caratteri, numeri/lettere)');
        }
      };
      const exitManualMode = ()=>{
        this.isManualMode = false;
        // Hide manual inputs and show Select2 groups
        this.$regionManual.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');
        this.$provinceManual.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');
        this.$cityManual.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');
        this.$capManual.closest('.col-md-6, .mb-3, .form-group').addClass('d-none');

        this.$region.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        this.$province.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        this.$city.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        this.$cap.closest('.col-md-6, .mb-3, .form-group').removeClass('d-none');
        // Ripristina i vincoli tipici CAP italiani sul campo manuale (anche se nascosto)
        if (this.$capManual && this.$capManual.length){
          this.$capManual.attr('maxlength', '10');
          this.$capManual.attr('pattern', '^[0-9]{4,5}$');
          this.$capManual.attr('title', 'Inserisci un CAP valido (4-5 cifre)');
        }
      };

      // Initialize manual groups hidden
      exitManualMode();

      this.$nation.on('change', ()=>{
        const raw = (this.$nation.val() || '').toString();
        const val = raw.trim();
        // Considera sia codice (IT/ITA) che denominazione (Italia/Italy)
        const upper = val.toUpperCase();
        const lower = val.toLowerCase();
        const isItaly = upper === 'IT' || upper === 'ITA' || lower === 'italia' || lower === 'italy' || lower.includes('italia') || lower.includes('italy');
        if (isItaly){
          exitManualMode();
          enable(this.$region); enable(this.$province); enable(this.$city); enable(this.$cap);
          // reset manual fields
          this.$regionManual.val(''); this.$provinceManual.val(''); this.$cityManual.val(''); this.$capManual.val('');
        } else {
          if (this.manualForNonItaly){
            // switch to manual mode
            enterManualMode();
            // also reset and disable select2 cascade
            reset(this.$region); reset(this.$province); reset(this.$city); reset(this.$cap);
          } else {
            // fallback: keep selects but disabled
            reset(this.$region); reset(this.$province); reset(this.$city); reset(this.$cap);
          }
        }
        notify();
      });

      this.$region.on('change', ()=>{
        if (this._silent.region){
          this._silent.region = false;
          enable(this.$province);
          notify();
          return;
        }
        if (this.$region.val()){
          enable(this.$province);
        } else {
          // Non disabilitare: permetti ricerca globale
          this.$province.val(null).trigger('change');
        }
        // Svuota città e cap ma lasciali attivi
        this.$city.val(null).trigger('change');
        this.$cap.val(null).trigger('change');
        notify();
      });

      // Autocomplete inverso: scegliendo Provincia senza Regione (ricerca globale) imposta Regione con nome esteso
      this.$province.on('select2:select', (e)=>{
        const data = e.params && e.params.data ? e.params.data : null;
        const raw = data && data._raw ? data._raw : null;
        if (this.backfillRegionFromProvince && raw && raw.codice_regione){
          const reg = raw.codice_regione;
          // Usa il resolver per popolare la Regione con etichetta estesa
          setRegionByCode(reg);
        }
      });

      this.$province.on('change', ()=>{
        // città e cap rimangono sempre abilitati (ricerca globale)
        enable(this.$city); enable(this.$cap);
        if (this._silent.province){
          this._silent.province = false;
          // Non svuotare città/CAP quando la provincia è impostata automaticamente da città/CAP
          notify();
          return;
        }
        // Cambio manuale provincia: svuota città e cap per coerenza
        this.$city.val(null).trigger('change');
        this.$cap.val(null).trigger('change');
        notify();
      });

      // Autocomplete inverso: scegliendo Città o CAP, popolare Provincia/Regione se disponibili
      this.$city.on('select2:select', (e)=>{
        const data = e.params && e.params.data ? e.params.data : null;
        const raw = data && data._raw ? data._raw : null;
        if (raw){
          const prov = raw.sigla_provincia || raw.Provincia || '';
          const reg = raw.codice_regione || raw.Regione || '';
          const comune = raw.denominazione_ita || raw.Comune || '';
          const istat = raw.codice_istat || '';
          // Memorizza l'ultima città selezionata per passare i filtri al CAP
          this._selectedCity = { name: comune, codice_istat: istat };
          this._selectedCityLabel = comune;
          if (prov){
            setProvinceBySigla(prov).then(()=>{
              enable(this.$city); enable(this.$cap);
            });
          }
          if (reg){ setRegionByCode(reg); }
          if (this.filterCapByCity){
            // Resetta il CAP e carica i CAP della città selezionata; se unico, selezionalo automaticamente (se abilitato)
            this.$cap.val(null).trigger('change');
            const params = { sigla_provincia: this.$province.val() || '', comune, codice_istat: istat };
            http.get('/cap-by-province', { params }).then(resp=>{
              const list = Array.isArray(resp.data) ? resp.data : resp;
              if (this.autoSelectUniqueCap && Array.isArray(list) && list.length === 1){
                const only = list[0];
                const cap = only.cap || only.CAP || '';
                if (cap){ ensureOption(this.$cap, cap, cap); }
              } else {
                // Se multipli, lascia filtrato: apri il dropdown per mostrare solo quelli della città
                setTimeout(()=>{ this.$cap.select2('open'); }, 50);
              }
            }).catch(()=>{ /* ignora errori */ });
          }
        }
        notify();
      });

      // Self-test opzionale disponibile via GeoSelect.selfTest(); non influisce sulla UI
      GeoSelect.selfTest = GeoSelect.selfTest || function(){
        const results = [];
        // Test 1: mapping città mostra solo nome
        const sampleCity = { denominazione_ita: 'Roma', sigla_provincia: 'RM', cap: '00100' };
        const mappedCityText = (function(it){
          const name = it.denominazione_ita || it.Comune || '';
          return name;
        })(sampleCity);
        results.push({ name: 'City label only name', pass: mappedCityText === 'Roma' });
        // Test 2: extraParams CAP con filterCapByCity
        const selectedCity = { name: 'Roma', codice_istat: '058091' };
        const extra = (function(sel){ return { sigla_provincia: 'RM', comune: sel.name, codice_istat: sel.codice_istat }; })(selectedCity);
        results.push({ name: 'CAP extraParams include comune/istat', pass: extra.comune === 'Roma' && extra.codice_istat === '058091' });
        // Sommario
        const pass = results.every(r=>r.pass);
        return { pass, results };
      };

      this.$cap.on('select2:select', (e)=>{
        const data = e.params && e.params.data ? e.params.data : null;
        const raw = data && data._raw ? data._raw : null;
        if (raw){
          const prov = raw.sigla_provincia || raw.SiglaProvincia || raw.provincia_sigla || raw.Provincia || '';
          const comune = raw.denominazione_ita || raw.denominazione_comune || raw.nome_comune || raw.nomeComune || raw.denominazione || raw.Comune || raw.comune || '';
          const istat = raw.codice_istat || raw.codiceISTAT || raw.codice_istat_6 || '';
          const selectCity = ()=>{
            if (comune){
              // Assicurati che il select città sia abilitato prima di selezionare
              enable(this.$city);
              const idCity = comune;
              const textCity = comune;
              // Usa selezione forzata per evitare problemi di UI
              ensureSingleSelection(this.$city, idCity, textCity);
              if (DEBUG) { try { console.debug('[GeoSelect] City selected via CAP', { comune, istat, prov }); } catch(_){} }
              this._selectedCity = { name: comune, codice_istat: istat };
              this._selectedCityLabel = comune;
            }
          };
          if (prov){
            setProvinceBySigla(prov).then(()=>{
              enable(this.$city); enable(this.$cap);
              selectCity();
            });
          } else {
            selectCity();
          }
        }
        this.onChange(this.getValues());
      });

  this.$city.on('change', ()=>notify());
      this.$cap.on('change', ()=>{
        // Fallback: se la città non è ancora valorizzata, prova a dedurla dal CAP correntemente selezionato
        const currentCap = (this.$cap.val() || '').toString().trim();
        const hasCity = !!(this.$city.val());
        if (currentCap && !hasCity){
          const params = { q: currentCap };
          // Se abbiamo una provincia selezionata, usa il filtro per maggiore precisione
          const provVal = (this.$province.val() || '').toString().trim();
          if (provVal) params.sigla_provincia = provVal;
          http.get('/cap-by-province', { params }).then(resp=>{
            const list = Array.isArray(resp.data) ? resp.data : resp;
            if (Array.isArray(list) && list.length){
              const item = list[0];
              const prov = item.sigla_provincia || item.SiglaProvincia || item.provincia_sigla || item.Provincia || '';
              const comune = item.denominazione_ita || item.denominazione_comune || item.nome_comune || item.nomeComune || item.denominazione || item.Comune || item.comune || '';
              const istat = item.codice_istat || item.codiceISTAT || item.codice_istat_6 || '';
              const selectCity = ()=>{
                if (comune){
                  // Abilita il select città prima di selezionare
                  enable(this.$city);
                  ensureSingleSelection(this.$city, comune, comune);
                  if (DEBUG) { try { console.debug('[GeoSelect] City selected via CAP change fallback', { comune, istat, prov, currentCap }); } catch(_){} }
                  this._selectedCity = { name: comune, codice_istat: istat };
                  this._selectedCityLabel = comune;
                }
              };
              if (prov){
                setProvinceBySigla(prov).then(()=>{ enable(this.$city); enable(this.$cap); selectCity(); });
              } else {
                selectCity();
              }
            }
            notify();
          }).catch(()=>{
            notify();
          });
        } else {
          notify();
        }
      });
    }
    getValues(){
      const getSelect = ($el)=>({ value: $el.val() || '', label: ($el.find(':selected').text() || '').trim() });
      const getInput = ($el)=>({ value: ($el.val() || '').toString(), label: ($el.val() || '').toString() });
      const rawNation = (this.$nation.val() || '').toString().trim();
      const up = rawNation.toUpperCase();
      const low = rawNation.toLowerCase();
      const isItaly = up === 'IT' || up === 'ITA' || low === 'italia' || low === 'italy' || low.includes('italia') || low.includes('italy');
      const usingManual = this.manualForNonItaly && !isItaly;
      return {
        nation: getSelect(this.$nation),
        region: usingManual ? getInput(this.$regionManual) : getSelect(this.$region),
        province: usingManual ? getInput(this.$provinceManual) : getSelect(this.$province),
        city: usingManual ? getInput(this.$cityManual) : getSelect(this.$city),
        cap: usingManual ? getInput(this.$capManual) : getSelect(this.$cap),
        manualMode: usingManual
      };
    }
  }

  global.GeoSelect = GeoSelect;
})(window);
