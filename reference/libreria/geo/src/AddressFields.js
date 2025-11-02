(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.Libreria = root.Libreria || {};
    root.Libreria.AddressFields = factory();
  }
}(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  function AddressFields(opts){
    this.root = typeof opts.root === 'string' ? document.querySelector(opts.root) : opts.root;
    if (!this.root) throw new Error('AddressFields requires a root element');
    this.ids = {
      typeaway: this.q(opts.prefix + '_typeaway'),
      address: this.q(opts.prefix + '_address'),
      num: this.q(opts.prefix + '_num'),
      internal: this.q(opts.prefix + '_internal'),
      inline: this.q(opts.inlineId)
    };
    this.init();
  }
  AddressFields.prototype.q = function(id){ return id ? document.getElementById(id) : null; };
  AddressFields.prototype.getValues = function(){
    var ids = this.ids; function v(el){ return (el && el.value || '').trim(); }
    return { typeaway: v(ids.typeaway), address: v(ids.address), num: v(ids.num), internal: v(ids.internal) };
  };
  AddressFields.prototype.renderInline = function(){
    var el = this.ids.inline; if (!el) return;
    var v = this.getValues(); var parts = [];
    var left = [v.typeaway, v.address].filter(Boolean).join(' ');
    if (left) parts.push(left);
    if (v.num) parts.push('Num. ' + v.num);
    if (v.internal) parts.push('Int. ' + v.internal);
    el.textContent = parts.length ? ('Indirizzo: ' + parts.join(' · ')) : '';
  };
  AddressFields.prototype.notify = function(){
    try { this.root.dispatchEvent(new CustomEvent('address:change', { detail: this.getValues() })); } catch(_){ }
  };
  AddressFields.prototype.init = function(){
    var self = this;
    ['change','input'].forEach(function(evt){
      Object.keys(self.ids).forEach(function(k){ var el = self.ids[k]; if (el && el.tagName) el.addEventListener(evt, function(){ self.renderInline(); self.notify(); }); });
    });
    self.renderInline();
  };

  return AddressFields;
}));
