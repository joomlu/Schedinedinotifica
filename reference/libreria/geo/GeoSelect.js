(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    module.exports = factory(require('jquery'));
  } else {
    root.Libreria = root.Libreria || {};
    root.Libreria.GeoSelect = factory(root.jQuery);
  }
}(typeof self !== 'undefined' ? self : this, function ($) {
  'use strict';

  if (!$ || !$.fn) { throw new Error('GeoSelect requires jQuery'); }

  // Helper: init select2 with AJAX via axios/http
  function makeAjaxSelect($el, options){
    var cfg = Object.assign({
      width: '100%',
      language: 'it',
      placeholder: $el.attr('data-placeholder') || 'Seleziona',
      allowClear: true,
      ajax: {
        transport: function (params, success, failure) {
          var client = (typeof window !== 'undefined' && window.http && typeof window.http.get === 'function') ? window.http : (typeof window !== 'undefined' && window.axios ? window.axios : null);
          var query = params.data && params.data.term ? params.data.term : '';
          var add = options.extraParams ? options.extraParams() : {};
          if (!client) return failure(new Error('HTTP client not ready'));
          client.get(options.url, { params: Object.assign({ q: query }, add) })
                .then(function(resp){ success(resp); })
                .catch(function(err){ failure(err); });
        },
        processResults: function(resp){
          var items = Array.isArray(resp.data) ? resp.data : resp;
          var mapped = (options.mapItems ? items.map(options.mapItems) : items);
          return { results: mapped };
        },
        delay: 150
      }
    }, options.select2 || {});
    return $el.select2(cfg);
  }

  function GeoSelect(cfg){
    this.$nation = $(cfg.nation);
    this.$region = $(cfg.region);
    this.$province = $(cfg.province);
    this.$city = $(cfg.city);
    this.$cap = $(cfg.cap);
    this.container = cfg.container ? (typeof cfg.container === 'string' ? document.querySelector(cfg.container) : cfg.container) : null;
    this.http = (typeof window !== 'undefined' && window.http && typeof window.http.get === 'function') ? window.http : (typeof window !== 'undefined' ? window.axios : null);
    this.onChange = typeof cfg.onChange === 'function' ? cfg.onChange : function(){};
    this.preselectItaly = !!cfg.preselectItaly;
    this.manualForNonItaly = cfg.manualForNonItaly !== false;
    this.filterCapByCity = !!cfg.filterCapByCity;
    this.autoSelectUniqueCap = !!cfg.autoSelectUniqueCap;
    this.backfillRegionFromProvince = cfg.backfillRegionFromProvince !== false;
    this.isManualMode = false;
    this._silent = { region:false, province:false };
    this._selectedCity = null;
    this._selectedCityLabel = '';
    this.init();
  }

  GeoSelect.prototype.init = function(){
    var self = this;
    var http = this.http;
    function reset($el){ $el.val(null).trigger('change').prop('disabled', true); }
    function enable($el){ $el.prop('disabled', false); }
    function notify(){
      try {
        var v = self.getValues();
        try { self.onChange(v); } catch(_){ }
        if (self.container && typeof CustomEvent === 'function'){
          self.container.dispatchEvent(new CustomEvent('geoselect:change', { detail: v }));
        }
      } catch(_){ }
    }
    function ensureSingleSelection($sel, value, text){
      if (!value) return;
      var opt = new Option(text || value, value, true, true);
      $sel.html('').append(opt);
      if ($sel.data('select2')){ $sel.trigger('change.select2'); } else { $sel.trigger('change'); }
    }
    function setRegionByCode(code){
      if (!code) return Promise.resolve();
      return http.get('/regions', { params: { q: code } }).then(function(resp){
        var list = Array.isArray(resp.data) ? resp.data : resp;
        var item = (list || []).find(function(r){ return (r.codice_regione||'').toString() === code.toString(); });
        if (item){
          self._silent.region = true;
          var opt = new Option(item.denominazione_regione || item.codice_regione, item.codice_regione, true, true);
          self.$region.html('').append(opt).trigger('change');
        }
        return Promise.resolve();
      });
    }
    function setProvinceBySigla(sigla){
      if (!sigla) return Promise.resolve();
      return http.get('/provinces-all', { params: { q: sigla } }).then(function(resp){
        var list = Array.isArray(resp.data) ? resp.data : resp;
        var item = (list || []).find(function(p){ return (p.sigla_provincia||'').toUpperCase() === String(sigla).toUpperCase(); });
        if (item){
          var text = (item.denominazione_provincia || '') + (item.sigla_provincia ? (' ('+item.sigla_provincia+')') : '');
          self._silent.province = true;
          var opt = new Option(text, item.sigla_provincia, true, true);
          self.$province.html('').append(opt).trigger('change');
          if (item.codice_regione){ return setRegionByCode(item.codice_regione); }
        }
        return Promise.resolve();
      });
    }

    // Nation
    makeAjaxSelect(this.$nation, {
      url: '/nations',
      mapItems: function(item){
        var name = item.denominazione_nazione || item.denominazione_cittadinanza || item.sigla_nazione || '';
        return { id: name, text: name };
      }
    });
    if (this.preselectItaly){
      var opt = new Option('Italia', 'IT', true, true);
      this.$nation.append(opt).trigger('change');
      enable(this.$region); enable(this.$province); enable(this.$city); enable(this.$cap);
    }

    // Region
    makeAjaxSelect(this.$region, { url: '/regions', mapItems: function(item){ return { id: item.codice_regione, text: item.denominazione_regione }; } });
    // Province
    makeAjaxSelect(this.$province, {
      url: '/provinces-all',
      extraParams: function(){ return { codice_regione: self.$region.val() || '' }; },
      mapItems: function(item){
        var name = item.denominazione_provincia || '';
        var sigla = item.sigla_provincia || '';
        return { id: sigla, text: name + (sigla ? ' ('+sigla+')' : ''), _raw: item };
      }
    });
    // City
    makeAjaxSelect(this.$city, {
      url: '/cities-by-province',
      extraParams: function(){ return { sigla_provincia: self.$province.val() || '' }; },
      mapItems: function(item){
        var name = item.denominazione_ita || item.Comune || '';
        return { id: name, text: name, _raw: item };
      }
    });
    // CAP
    makeAjaxSelect(this.$cap, {
      url: '/cap-by-province',
      extraParams: function(){
        var base = { sigla_provincia: self.$province.val() || '' };
        if (self.filterCapByCity){
          base.comune = (self._selectedCityLabel || '');
          base.codice_istat = (self._selectedCity && self._selectedCity.codice_istat ? self._selectedCity.codice_istat : '');
        }
        return base;
      },
      mapItems: function(item){
        var cap = item.cap || item.CAP || '';
        return { id: cap, text: cap, _raw: item };
      }
    });

    // Events
    this.$nation.on('change', function(){
      var raw = (self.$nation.val() || '').toString();
      var val = raw.trim();
      var upper = val.toUpperCase();
      var lower = val.toLowerCase();
      var isItaly = upper === 'IT' || upper === 'ITA' || lower === 'italia' || lower === 'italy' || lower.indexOf('italia')>=0 || lower.indexOf('italy')>=0;
      if (isItaly){
        enable(self.$region); enable(self.$province); enable(self.$city); enable(self.$cap);
      } else {
        // switch to manual: here we simply disable selects
        reset(self.$region); reset(self.$province); reset(self.$city); reset(self.$cap);
      }
      notify();
    });

    this.$region.on('change', function(){
      if (self._silent.region){ self._silent.region = false; enable(self.$province); notify(); return; }
      if (self.$region.val()){ enable(self.$province); } else { self.$province.val(null).trigger('change'); }
      self.$city.val(null).trigger('change');
      self.$cap.val(null).trigger('change');
      notify();
    });

    this.$province.on('select2:select', function(e){
      var data = e.params && e.params.data ? e.params.data : null;
      var raw = data && data._raw ? data._raw : null;
      if (self.backfillRegionFromProvince && raw && raw.codice_regione){ setRegionByCode(raw.codice_regione); }
    });

    this.$province.on('change', function(){
      enable(self.$city); enable(self.$cap);
      if (self._silent.province){ self._silent.province = false; notify(); return; }
      self.$city.val(null).trigger('change');
      self.$cap.val(null).trigger('change');
      notify();
    });

    this.$city.on('select2:select', function(e){
      var data = e.params && e.params.data ? e.params.data : null;
      var raw = data && data._raw ? data._raw : null;
      if (raw){
        var prov = raw.sigla_provincia || raw.Provincia || '';
        var reg = raw.codice_regione || raw.Regione || '';
        var comune = raw.denominazione_ita || raw.Comune || '';
        var istat = raw.codice_istat || '';
        self._selectedCity = { name: comune, codice_istat: istat };
        self._selectedCityLabel = comune;
        if (prov){ setProvinceBySigla(prov).then(function(){ enable(self.$city); enable(self.$cap); }); }
        if (reg){ setRegionByCode(reg); }
        if (self.filterCapByCity){
          self.$cap.val(null).trigger('change');
          var params = { sigla_provincia: self.$province.val() || '', comune: comune, codice_istat: istat };
          http.get('/cap-by-province', { params: params }).then(function(resp){
            var list = Array.isArray(resp.data) ? resp.data : resp;
            if (self.autoSelectUniqueCap && Array.isArray(list) && list.length === 1){
              var only = list[0];
              var cap = only.cap || only.CAP || '';
              if (cap){ var opt = new Option(cap, cap, true, true); self.$cap.html('').append(opt).trigger('change'); }
            } else {
              setTimeout(function(){ self.$cap.select2('open'); }, 50);
            }
          }).catch(function(){});
        }
      }
      notify();
    });

    this.$cap.on('select2:select', function(e){
      var data = e.params && e.params.data ? e.params.data : null;
      var raw = data && data._raw ? data._raw : null;
      if (raw){
        var prov = raw.sigla_provincia || raw.SiglaProvincia || raw.provincia_sigla || raw.Provincia || '';
        var comune = raw.denominazione_ita || raw.denominazione_comune || raw.nome_comune || raw.nomeComune || raw.denominazione || raw.Comune || raw.comune || '';
        var istat = raw.codice_istat || raw.codiceISTAT || raw.codice_istat_6 || '';
        function selectCity(){
          if (comune){ enable(self.$city); ensureSingleSelection(self.$city, comune, comune); self._selectedCity = { name: comune, codice_istat: istat }; self._selectedCityLabel = comune; }
        }
        if (prov){ setProvinceBySigla(prov).then(function(){ enable(self.$city); enable(self.$cap); selectCity(); }); } else { selectCity(); }
      }
      notify();
    });

    this.$city.on('change', function(){ notify(); });
    this.$cap.on('change', function(){
      var currentCap = (self.$cap.val() || '').toString().trim();
      var hasCity = !!(self.$city.val());
      if (currentCap && !hasCity){
        var params = { q: currentCap };
        var provVal = (self.$province.val() || '').toString().trim();
        if (provVal) params.sigla_provincia = provVal;
        http.get('/cap-by-province', { params: params }).then(function(resp){
          var list = Array.isArray(resp.data) ? resp.data : resp;
          if (Array.isArray(list) && list.length){
            var item = list[0];
            var prov = item.sigla_provincia || item.SiglaProvincia || item.provincia_sigla || item.Provincia || '';
            var comune = item.denominazione_ita || item.denominazione_comune || item.nome_comune || item.nomeComune || item.denominazione || item.Comune || item.comune || '';
            var istat = item.codice_istat || item.codiceISTAT || item.codice_istat_6 || '';
            function selectCity(){ if (comune){ enable(self.$city); ensureSingleSelection(self.$city, comune, comune); self._selectedCity = { name: comune, codice_istat: istat }; self._selectedCityLabel = comune; } }
            if (prov){ setProvinceBySigla(prov).then(function(){ enable(self.$city); enable(self.$cap); selectCity(); }); } else { selectCity(); }
          }
          notify();
        }).catch(function(){ notify(); });
      } else {
        notify();
      }
    });
  };

  GeoSelect.prototype.getValues = function(){
    function getSelect($el){ return { value: $el.val() || '', label: ($el.find(':selected').text() || '').trim() }; }
    return {
      nation: getSelect(this.$nation),
      region: getSelect(this.$region),
      province: getSelect(this.$province),
      city: getSelect(this.$city),
      cap: getSelect(this.$cap),
      manualMode: false
    };
  };

  return GeoSelect;
}));
