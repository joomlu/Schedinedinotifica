import $ from 'jquery';
import { initOnce, isDev } from '../core/once';

// Regla CAP fail-safe: si el CAP no está en los dataset, se conserva el valor manual
// sin limpiar selects ni bloquear el formulario; si existe, se autocompleta la cadena
// CAP → Comune → Provincia → Regione → Nazione=Italia.

const http = (typeof window !== 'undefined' && window.http) ? window.http : (typeof window !== 'undefined' ? window.axios : null);

function joinEndpoint(base, path) {
    if (!base) return path;
    if (base.endsWith('/')) base = base.slice(0, -1);
    return `${base}${path}`;
}

function normalizeSelect2Response(resp) {
    const payload = resp && resp.data !== undefined ? resp.data : resp;
    if (!payload) return { results: [], pagination: { more: false } };
    if (Array.isArray(payload)) return { results: normalizeItems(payload), pagination: { more: false } };
    const results = payload.results || payload.data || payload.items || [];
    const pagination = payload.pagination || {};
    const more = typeof pagination.more !== 'undefined' ? !!pagination.more : !!payload.more;
    return {
        results: normalizeItems(Array.isArray(results) ? results : []),
        pagination: { more: !!more },
    };
}

function normalizeItems(items) {
    return items.map((item) => {
        if (!item || item.text) return item;
        const fallback = item.nome || item.label || item.cap || item.sigla || item.codice_iso2 || item.id || '';
        return { ...item, text: fallback };
    });
}

function ensureOption(selectEl, { id, text }, triggerChange = true) {
    if (!selectEl || id === undefined || id === null || id === '') return;
    const option = new Option(text || id, id, true, true);
    selectEl.innerHTML = '';
    selectEl.appendChild(option);
    if (triggerChange) {
        $(selectEl).trigger('change');
    }
}

function extractAjaxParams(params) {
    const data = params && typeof params === 'object' ? (params.data || params) : {};
    return {
        term: data.term || '',
        page: data.page || 1,
    };
}

function clearSelect(selectEl, placeholder = '') {
    if (!selectEl) return;
    selectEl.innerHTML = '';
    const option = new Option(placeholder, '', false, false);
    selectEl.appendChild(option);
    $(selectEl).val(null).trigger('change');
}

function clearCapManual(input) {
    if (input) {
        input.value = '';
    }
}

function setDisabled(selectEl, disabled) {
    if (!selectEl) return;
    const isDisabled = !!disabled;
    selectEl.disabled = isDisabled;
    if (!isDisabled) {
        selectEl.removeAttribute('disabled');
    } else {
        selectEl.setAttribute('disabled', 'disabled');
    }
    if (typeof $ !== 'undefined' && selectEl.tagName === 'SELECT') {
        $(selectEl).prop('disabled', isDisabled);
    }
}

function optionLabel(selectEl) {
    if (!selectEl) return '';
    const opt = selectEl.options[selectEl.selectedIndex];
    return opt ? (opt.text || '') : '';
}

function dispatchChange(container, detail) {
    if (!container || typeof CustomEvent !== 'function') return;
    container.dispatchEvent(new CustomEvent('geoselect:change', { detail }));
}

function triggerInitialQuery(el) {
    const inst = el ? $(el).data('select2') : null;
    if (!inst || el.dataset.loadedOnce) return;
    // select2 espera un callback aunque no lo usemos aquí
    inst.trigger('query', { term: '', page: 1, callback: () => {} });
    el.dataset.loadedOnce = '1';
}

class GeoItaliaUI {
    constructor(root) {
        this.root = root;
        this.prefix = root.dataset.prefix || 'geo';
        this.base = root.dataset.endpointBase || '/geo';
        this.fallbackBase = root.dataset.endpointFallback || '/api/geo';
        if (this.fallbackBase === this.base) {
            this.fallbackBase = null;
        }
        this.initial = this.parseInitial(root.dataset.initial);
        this.manualMode = !!this.initial.manual;
        this.resolveSeq = 0;
        this.state = {
            nazione: null,
            regione: null,
            provincia: null,
            comune: null,
            cap: null,
        };

        this.nodes = {
            nazione: root.querySelector('[data-role="nazione"]'),
            regione: root.querySelector('[data-role="regione"]'),
            provincia: root.querySelector('[data-role="provincia"]'),
            comune: root.querySelector('[data-role="comune"]'),
            cap: root.querySelector('[data-role="cap"]'),
            manualFlag: root.querySelector('[data-role="manual-flag"]'),
            manualRegione: root.querySelector('[data-role="manual-regione"]'),
            manualProvincia: root.querySelector('[data-role="manual-provincia"]'),
            manualComune: root.querySelector('[data-role="manual-comune"]'),
            manualCap: root.querySelector('[data-role="manual-cap"]'),
            cittadinanza: root.querySelector('[data-role="cittadinanza"]'),
            italiaBlocks: root.querySelectorAll('[data-role="italia-block"]'),
            esteroBlocks: root.querySelectorAll('[data-role="estero-block"]'),
            btnItalia: root.querySelector('[data-action="italia"]'),
            btnEstero: root.querySelector('[data-action="estero"]'),
            btnReset: root.querySelector('[data-action="geo-reset"]'),
        };

        if (!http) {
            if (isDev()) {
                // eslint-disable-next-line no-console
                console.error('Geo Italia: http client non disponibile');
            }
            return;
        }

        this.initSelects();
        this.bindToggles();
        this.applyInitial();
        this.updateModeUI();
    }

    parseInitial(raw) {
        if (!raw) return {};
        try {
            return JSON.parse(raw) || {};
        } catch (err) {
            return {};
        }
    }

    select2BaseConfig(el) {
        const allowClearAttr = el ? (el.dataset.allowClear || el.getAttribute('data-allow-clear')) : null;
        const allowClear = allowClearAttr === null ? true : !['0', 'false', 'no'].includes(allowClearAttr);
        const placeholder = el ? (el.dataset.placeholder || el.getAttribute('placeholder') || 'Seleziona...') : 'Seleziona...';
        const minSearchAttr = el ? parseInt(el.dataset.minSearch || el.getAttribute('data-min-search') || '', 10) : NaN;
        return {
            theme: 'bootstrap-5',
            width: '100%',
            dropdownAutoWidth: true,
            allowClear,
            placeholder,
            closeOnSelect: true,
            tags: false,
            multiple: false,
            tokenSeparators: [],
            minimumResultsForSearch: Number.isFinite(minSearchAttr) ? minSearchAttr : 0,
            minimumInputLength: 0,
            delay: 150,
            language: 'it',
        };
    }

    fetchGeo(path, params = {}) {
        const primaryUrl = joinEndpoint(this.base, path);
        const fallbackUrl = this.fallbackBase && this.fallbackBase !== this.base
            ? joinEndpoint(this.fallbackBase, path)
            : null;

        const request = (url) => http
            .get(url, { params })
            .then((resp) => (resp && resp.data !== undefined ? resp.data : resp));

        const shouldFallback = (payload) => {
            const normalized = normalizeSelect2Response(payload);
            const results = normalized.results || [];
            if (results.length) return false;
            if (typeof payload === 'string') return true;
            const hasShape = payload && typeof payload === 'object'
                ? Array.isArray(payload.results) || Array.isArray(payload.data) || Array.isArray(payload.items)
                : false;
            return !hasShape;
        };

        return request(primaryUrl)
            .then((data) => {
                if (fallbackUrl && shouldFallback(data)) {
                    return request(fallbackUrl).then((fbData) => {
                        this.base = this.fallbackBase;
                        return fbData;
                    });
                }
                return data;
            })
            .catch((err) => {
                if (!fallbackUrl) throw err;
                return request(fallbackUrl).then((data) => {
                    this.base = this.fallbackBase;
                    return data;
                });
            });
    }

    initSelects() {
        this.initNazioni();
        this.initRegioni();
        this.initProvince();
        this.initComuni();
        this.initCap();
        this.initReset();
    }

    resetBelowNazione() {
        clearSelect(this.nodes.regione, this.nodes.regione?.dataset.placeholder || '');
        clearSelect(this.nodes.provincia, this.nodes.provincia?.dataset.placeholder || '');
        clearSelect(this.nodes.comune, this.nodes.comune?.dataset.placeholder || '');
        clearSelect(this.nodes.cap, this.nodes.cap?.dataset.placeholder || '');
        clearCapManual(this.nodes.manualCap);
        this.state.regione = null;
        this.state.provincia = null;
        this.state.comune = null;
        this.state.cap = null;
    }

    resetBelowRegione() {
        clearSelect(this.nodes.provincia, this.nodes.provincia?.dataset.placeholder || '');
        clearSelect(this.nodes.comune, this.nodes.comune?.dataset.placeholder || '');
        clearSelect(this.nodes.cap, this.nodes.cap?.dataset.placeholder || '');
        clearCapManual(this.nodes.manualCap);
        this.state.regione = null;
        this.state.provincia = null;
        this.state.comune = null;
        this.state.cap = null;
    }

    resetBelowProvincia() {
        clearSelect(this.nodes.comune, this.nodes.comune?.dataset.placeholder || '');
        clearSelect(this.nodes.cap, this.nodes.cap?.dataset.placeholder || '');
        clearCapManual(this.nodes.manualCap);
        this.state.provincia = null;
        this.state.comune = null;
        this.state.cap = null;
    }

    resetBelowComune() {
        clearSelect(this.nodes.cap, this.nodes.cap?.dataset.placeholder || '');
        clearCapManual(this.nodes.manualCap);
        this.state.comune = null;
        this.state.cap = null;
    }

    clearResolvedHierarchy(preserveCapValue = false) {
        clearSelect(this.nodes.regione, this.nodes.regione?.dataset.placeholder || '');
        clearSelect(this.nodes.provincia, this.nodes.provincia?.dataset.placeholder || '');
        clearSelect(this.nodes.comune, this.nodes.comune?.dataset.placeholder || '');
        this.state.regione = null;
        this.state.provincia = null;
        this.state.comune = null;

        if (!preserveCapValue) {
            this.state.cap = null;
            if (this.nodes.cap) {
                clearSelect(this.nodes.cap, this.nodes.cap.dataset.placeholder || this.nodes.cap.getAttribute('placeholder') || '');
                if (typeof $ !== 'undefined') {
                    $(this.nodes.cap).val(null).trigger('change.select2');
                }
            }
            clearCapManual(this.nodes.manualCap);
        }
        // Si preserveCapValue es true (flujo de selección de CAP), no tocar el select CAP ni state.cap; se confirmará o invalidará en hydrateFromResolve.
    }

    initNazioni() {
        const el = this.nodes.nazione;
        if (!el) return;

        // Placeholder explícito para asegurar el enmascarado visual en modo Estero
        const hasPlaceholder = el.dataset.placeholder || el.getAttribute('placeholder');
        if (!hasPlaceholder) {
            const ph = 'Seleziona nazione';
            el.setAttribute('placeholder', ph);
            el.dataset.placeholder = ph;
        }

        // Asegura opción vacía inicial para que Select2 muestre el placeholder
        if (!el.querySelector('option[value=""]')) {
            const currentVal = el.value;
            el.insertBefore(new Option('', '', false, false), el.firstChild);
            if (currentVal) {
                el.value = currentVal;
            }
        }

        $(el).select2({
            ...this.select2BaseConfig(el),
            ajax: {
                delay: 150,
                transport: (params, success, failure) => {
                    const { term, page } = extractAjaxParams(params);
                    this.fetchGeo('/nazioni', {
                        q: term,
                        page,
                    })
                        .then((resp) => success(resp && resp.data !== undefined ? resp.data : resp))
                        .catch((err) => failure(err));
                },
                processResults: (resp) => normalizeSelect2Response(resp),
            },
        });

        $(el).on('select2:select', (e) => {
            const data = e.params.data || {};
            this.state.nazione = data;
            const isItaly = !!data.is_italia || (data.codice_iso2 || '').toUpperCase() === 'IT' || (data.text || '').toLowerCase().includes('italia');
            if (data.cittadinanza) {
                this.updateCittadinanza(data.cittadinanza);
            }
            this.resetBelowNazione();
            if (isItaly) {
                this.enterItaliaMode(false);
            } else {
                this.enterEsteroMode();
            }
            this.emitChange();
        });

        $(el).on('select2:clear', () => {
            this.state.nazione = null;
            this.updateCittadinanza('');
            this.resetBelowNazione();
            this.enterEsteroMode();
            this.emitChange();
        });

    }

    initRegioni() {
        const el = this.nodes.regione;
        if (!el) return;

        $(el).select2({
            ...this.select2BaseConfig(el),
            ajax: {
                delay: 150,
                transport: (params, success, failure) => {
                    const { term, page } = extractAjaxParams(params);
                    this.fetchGeo('/regioni', {
                        q: term,
                        page,
                        geo_nazione_id: this.nodes.nazione ? this.nodes.nazione.value || null : null,
                    })
                        .then((resp) => success(resp && resp.data !== undefined ? resp.data : resp))
                        .catch((err) => failure(err));
                },
                processResults: (resp) => normalizeSelect2Response(resp),
            },
        });

        $(el).on('select2:select', () => {
            this.resetBelowRegione();
            this.emitChange();
        });

        $(el).on('select2:clear', () => {
            this.resetBelowRegione();
            this.emitChange();
        });

    }

    initProvince() {
        const el = this.nodes.provincia;
        if (!el) return;

        $(el).select2({
            ...this.select2BaseConfig(el),
            ajax: {
                delay: 150,
                transport: (params, success, failure) => {
                    const { term, page } = extractAjaxParams(params);
                    this.fetchGeo('/province', {
                        q: term,
                        page,
                        geo_regione_id: this.nodes.regione ? this.nodes.regione.value || null : null,
                    })
                        .then((resp) => success(resp && resp.data !== undefined ? resp.data : resp))
                        .catch((err) => failure(err));
                },
                processResults: (resp) => normalizeSelect2Response(resp),
            },
        });

        $(el).on('select2:select', (e) => {
            const data = e.params.data || {};
            if (data.geo_regione_id && this.nodes.regione && this.nodes.regione.value && `${data.geo_regione_id}` !== `${this.nodes.regione.value}`) {
                clearSelect(this.nodes.provincia, this.nodes.provincia?.dataset.placeholder || '');
                this.resetBelowProvincia();
                this.emitChange();
                return;
            }
            this.resetBelowProvincia();
            if (data && data.sigla) {
                this.resolve({ sigla_provincia: data.sigla });
            }
            this.emitChange();
        });

        $(el).on('select2:clear', () => {
            this.resetBelowProvincia();
            this.emitChange();
        });

    }

    initComuni() {
        const el = this.nodes.comune;
        if (!el) return;

        $(el).select2({
            ...this.select2BaseConfig(el),
            ajax: {
                delay: 150,
                transport: (params, success, failure) => {
                    const { term, page } = extractAjaxParams(params);
                    this.fetchGeo('/comuni', {
                        q: term,
                        page,
                        geo_provincia_id: this.nodes.provincia ? this.nodes.provincia.value || null : null,
                    })
                        .then((resp) => success(resp && resp.data !== undefined ? resp.data : resp))
                        .catch((err) => failure(err));
                },
                processResults: (resp) => normalizeSelect2Response(resp),
            },
        });

        $(el).on('select2:select', (e) => {
            this.resetBelowComune();
            const data = e.params.data || {};
            if (data.geo_provincia_id && this.nodes.provincia && this.nodes.provincia.value && `${data.geo_provincia_id}` !== `${this.nodes.provincia.value}`) {
                clearSelect(this.nodes.comune, this.nodes.comune?.dataset.placeholder || '');
                this.emitChange();
                return;
            }
            if (data && data.id) {
                this.resolve({ geo_comune_id: data.id });
            }
            this.emitChange();
        });

        $(el).on('select2:clear', () => {
            this.resetBelowComune();
            this.emitChange();
        });

    }

    initCap() {
        const el = this.nodes.cap;
        if (!el) return;

        const $cap = $(el);
        const allowManual = (el.getAttribute('data-allow-manual') || '').toLowerCase();
        const allowManualEntry = ['1', 'true', 'yes'].includes(allowManual);

        $cap.select2({
            ...this.select2BaseConfig(el),
            ajax: {
                delay: 150,
                transport: (params, success, failure) => {
                    const { term, page } = extractAjaxParams(params);
                    this.fetchGeo('/cap', {
                        q: term,
                        page,
                        geo_comune_id: this.nodes.comune ? this.nodes.comune.value || null : null,
                        geo_provincia_id: this.nodes.provincia ? this.nodes.provincia.value || null : null,
                    })
                        .then((resp) => success(resp && resp.data !== undefined ? resp.data : resp))
                        .catch((err) => failure(err));
                },
                processResults: (resp) => normalizeSelect2Response(resp),
            },
        });

        $(el).on('select2:select', (e) => {
            const data = e.params.data;
            if (data && data.id) {
                const mismatchComune = data.geo_comune_id && this.nodes.comune && this.nodes.comune.value && `${data.geo_comune_id}` !== `${this.nodes.comune.value}`;
                const mismatchProvincia = data.geo_provincia_id && this.nodes.provincia && this.nodes.provincia.value && `${data.geo_provincia_id}` !== `${this.nodes.provincia.value}`;
                if (mismatchComune || mismatchProvincia) {
                    clearSelect(this.nodes.cap, this.nodes.cap?.dataset.placeholder || '');
                    this.state.cap = null;
                    this.emitChange();
                    return;
                }
                this.clearResolvedHierarchy(true);
                this.state.cap = { id: data.id, text: data.text || data.id };
                this.resolve({ cap: data.id });
            }
            this.emitChange();
        });

        $(el).on('select2:clear', () => {
            // Limpiar jerarquía vinculada para evitar arrastre al cargar un nuevo CAP
            this.clearResolvedHierarchy(false);
            this.state.cap = null;
            this.emitChange();
        });

        $(el).on('change', () => {
            const val = el.value;
            if (!val) {
                const placeholder = el.dataset.placeholder || el.getAttribute('placeholder') || '';
                el.innerHTML = '';
                el.appendChild(new Option(placeholder, '', false, false));
                clearCapManual(this.nodes.manualCap);
                this.state.cap = null;
                this.emitChange();
                return;
            }
            if (val && val.length >= 3) {
                this.clearResolvedHierarchy(true);
                this.state.cap = { id: val, text: val };
                this.resolve({ cap: val });
            }
            this.emitChange();
        });

        // Sin trigger forzado: Select2 gestionará las queries al teclear

        if (allowManualEntry) {
            $cap.on('select2:close', () => {
                const inst = $cap.data('select2');
                const term = inst && inst.dropdown && inst.dropdown.$search
                    ? inst.dropdown.$search.val().trim()
                    : '';
                if (term) {
                    ensureOption(el, { id: term, text: term });
                    this.resolve({ cap: term });
                }
                if (inst && inst.dropdown && inst.dropdown.$search) {
                    inst.dropdown.$search.val('');
                }
                this.emitChange();
            });
        }
    }

    bindToggles() {
        if (this.nodes.btnItalia) {
            this.nodes.btnItalia.addEventListener('click', () => {
                this.enterItaliaMode();
            });
        }
        if (this.nodes.btnEstero) {
            this.nodes.btnEstero.addEventListener('click', () => {
                this.enterEsteroMode();
            });
        }
    }

    initReset() {
        if (!this.nodes.btnReset) return;
        this.nodes.btnReset.addEventListener('click', () => {
            this.performReset();
        });
    }

    performReset() {
        // Limpieza completa sin alterar la lógica principal de selección
        this.manualMode = false;
        this.state = {
            nazione: null,
            regione: null,
            provincia: null,
            comune: null,
            cap: null,
        };

        clearSelect(this.nodes.nazione, this.nodes.nazione?.dataset.placeholder || this.nodes.nazione?.getAttribute('placeholder') || '');
        clearSelect(this.nodes.regione, this.nodes.regione?.dataset.placeholder || this.nodes.regione?.getAttribute('placeholder') || '');
        clearSelect(this.nodes.provincia, this.nodes.provincia?.dataset.placeholder || this.nodes.provincia?.getAttribute('placeholder') || '');
        clearSelect(this.nodes.comune, this.nodes.comune?.dataset.placeholder || this.nodes.comune?.getAttribute('placeholder') || '');
        clearSelect(this.nodes.cap, this.nodes.cap?.dataset.placeholder || this.nodes.cap?.getAttribute('placeholder') || '');
        clearCapManual(this.nodes.manualCap);
        if (this.nodes.manualRegione) this.nodes.manualRegione.value = '';
        if (this.nodes.manualProvincia) this.nodes.manualProvincia.value = '';
        if (this.nodes.manualComune) this.nodes.manualComune.value = '';
        if (this.nodes.cittadinanza) this.nodes.cittadinanza.value = '';
        if (this.nodes.manualFlag) this.nodes.manualFlag.value = 0;

        this.updateModeUI();

        // Forzar Italia como base limpia
        this.prefillItalia();
        this.emitChange();
    }

    enterItaliaMode(forceItalia = true) {
        this.manualMode = false;
        this.updateModeUI();
        if (forceItalia) {
            this.prefillItalia();
        }
    }

    enterEsteroMode(opts = {}) {
        const { preserveNation = false, skipEmit = false } = opts;
        this.manualMode = true;
        // No limpiar nación/cittadinanza si venimos de un resolve extranjero
        if (!preserveNation) {
            this.state.nazione = null;
            if (this.nodes.nazione) {
                clearSelect(this.nodes.nazione, this.nodes.nazione.dataset.placeholder || this.nodes.nazione.getAttribute('placeholder') || '');
                if (typeof $ !== 'undefined') {
                    $(this.nodes.nazione).val(null).trigger('change.select2');
                }
            }
            if (this.nodes.cittadinanza) {
                this.nodes.cittadinanza.value = '';
            }
        }
        this.updateModeUI();
        if (this.nodes.nazione && typeof $ !== 'undefined') {
            const placeholder = this.nodes.nazione.dataset.placeholder || this.nodes.nazione.getAttribute('placeholder') || '';
            const mask = () => {
                const inst = $(this.nodes.nazione).data('select2');
                const $render = inst && inst.$container ? inst.$container.find('.select2-selection__rendered') : null;
                if ($render && placeholder) {
                    $render.text(placeholder);
                    $render.attr('title', '');
                }
            };
            if (typeof queueMicrotask === 'function') {
                queueMicrotask(mask);
            } else {
                setTimeout(mask, 0);
            }
        }
        if (!skipEmit) {
            this.emitChange();
        }
    }

    prefillItalia() {
        this.fetchGeo('/nazioni', { is_italia: 1, page: 1, per_page: 1 })
            .then((resp) => {
                const { results } = normalizeSelect2Response(resp);
                const it = Array.isArray(results) ? results[0] : null;
                if (it && this.nodes.nazione) {
                    ensureOption(this.nodes.nazione, { id: it.id, text: it.text || it.nome || 'Italia' });
                    this.state.nazione = it;
                    if (it.cittadinanza) {
                        this.updateCittadinanza(it.cittadinanza);
                    }
                }
                this.emitChange();
            })
            .catch(() => {});
    }

    updateModeUI() {
        if (this.nodes.manualFlag) {
            this.nodes.manualFlag.value = this.manualMode ? 1 : 0;
        }

        this.nodes.italiaBlocks.forEach((el) => {
            el.classList.toggle('d-none', this.manualMode);
            const select = el.querySelector('select');
            if (select) {
                // In Italia mode all guided selects must stay enabled; disable only when in Estero.
                setDisabled(select, this.manualMode);
            }
        });

        this.nodes.esteroBlocks.forEach((el) => {
            el.classList.toggle('d-none', !this.manualMode);
            const input = el.querySelector('input');
            if (input) setDisabled(input, !this.manualMode || input.hasAttribute('data-disabled-static'));
        });

        // Solo visual: en modo Estero ocultar el texto "Italia" del render Select2 (sin tocar valor/estado)
        if (this.manualMode && this.nodes.nazione && typeof $ !== 'undefined') {
            const placeholder = this.nodes.nazione.dataset.placeholder || this.nodes.nazione.getAttribute('placeholder') || '';
            const mask = () => {
                const inst = $(this.nodes.nazione).data('select2');
                const $render = inst && inst.$container ? inst.$container.find('.select2-selection__rendered') : null;
                if ($render && placeholder) {
                    $render.text(placeholder);
                    $render.attr('title', '');
                }
            };
            if (typeof queueMicrotask === 'function') {
                queueMicrotask(mask);
            } else {
                setTimeout(mask, 0);
            }
        }

        if (this.nodes.btnItalia) {
            this.nodes.btnItalia.classList.toggle('active', !this.manualMode);
        }
        if (this.nodes.btnEstero) {
            this.nodes.btnEstero.classList.toggle('active', this.manualMode);
        }
    }

    applyInitial() {
        const initial = this.initial || {};

        if (initial.manual) {
            // Entrar en modo manual sin emitir hasta poblar los campos
            this.enterEsteroMode({ preserveNation: true, skipEmit: true });
        } else {
            this.enterItaliaMode(false);
            if (!initial.nazione_id && !initial.nazione_text) {
                // Forza Italia quando no hay nación inicial visible u oculta
                this.prefillItalia();
            }
        }

        if (this.nodes.manualRegione && initial.regione_text) {
            this.nodes.manualRegione.value = initial.regione_text;
        }
        if (this.nodes.manualProvincia && initial.provincia_text) {
            this.nodes.manualProvincia.value = initial.provincia_text;
        }
        if (this.nodes.manualComune && initial.comune_text) {
            this.nodes.manualComune.value = initial.comune_text;
        }
        if (this.nodes.manualCap && initial.cap_text) {
            this.nodes.manualCap.value = initial.cap_text;
        }

        if (initial.nazione_id || initial.nazione_text) {
            ensureOption(this.nodes.nazione, {
                id: initial.nazione_id || initial.nazione_text,
                text: initial.nazione_text || initial.nazione_id,
            });
        }

        if (initial.regione_id || initial.regione_text) {
            ensureOption(this.nodes.regione, {
                id: initial.regione_id || initial.regione_text,
                text: initial.regione_text || initial.regione_id,
            });
        }

        if (initial.provincia_id || initial.provincia_text) {
            ensureOption(this.nodes.provincia, {
                id: initial.provincia_id || initial.provincia_text,
                text: initial.provincia_text || initial.provincia_id,
            });
        }

        if (initial.comune_id || initial.comune_text) {
            ensureOption(this.nodes.comune, {
                id: initial.comune_id || initial.comune_text,
                text: initial.comune_text || initial.comune_id,
            });
        }

        if (initial.cap) {
            ensureOption(this.nodes.cap, { id: initial.cap, text: initial.cap });
        }

        if (this.nodes.cittadinanza && initial.cittadinanza) {
            this.updateCittadinanza(initial.cittadinanza);
        }

        if (initial.cap) {
            this.resolve({ cap: initial.cap });
        } else if (initial.comune_id) {
            this.resolve({ geo_comune_id: initial.comune_id });
        }

        // Emitir un único cambio coherente tras poblar iniciales en modo manual
        if (initial.manual) {
            this.emitChange();
        }
    }

    resolve(params) {
        const seq = ++this.resolveSeq;
        this.fetchGeo('/resolve', params)
            .then((data) => {
                if (seq !== this.resolveSeq) return; // respuesta obsoleta
                this.hydrateFromResolve(data || {});
                this.emitChange();
            })
            .catch(() => {});
    }

    hydrateFromResolve(data) {
        const { nazione, regione, provincia, comune, caps, cap_default: capDefault } = data;

        const currentCapValue = this.nodes.cap ? this.nodes.cap.value : null;

        const commitSelect2Value = (el, value) => {
            if (!el || typeof $ === 'undefined') return;
            const val = `${value}`;
            const commit = () => {
                $(el).val(val).trigger('change.select2');
            };
            if (typeof queueMicrotask === 'function') {
                queueMicrotask(commit);
            } else {
                setTimeout(commit, 0);
            }
        };

        if (nazione && this.nodes.nazione) {
            ensureOption(this.nodes.nazione, { id: nazione.id, text: nazione.text || nazione.nome });
            this.state.nazione = nazione;
            if (nazione.cittadinanza) {
                this.updateCittadinanza(nazione.cittadinanza);
            }
            const isItaly = !!nazione.is_italia || (nazione.codice_iso2 || '').toUpperCase() === 'IT';
            if (isItaly) {
                this.enterItaliaMode(false);
            } else {
                this.enterEsteroMode({ preserveNation: true, skipEmit: true });
            }
        }

        if (!this.manualMode && !regione && this.nodes.regione) {
            clearSelect(this.nodes.regione, this.nodes.regione.dataset.placeholder || this.nodes.regione.getAttribute('placeholder') || '');
            this.state.regione = null;
        }

        if (!this.manualMode && regione && this.nodes.regione) {
            const regioneId = `${regione.id}`;
            ensureOption(this.nodes.regione, { id: regioneId, text: regione.text || regione.nome }, false);
            this.state.regione = regione;
            commitSelect2Value(this.nodes.regione, regioneId);
        }

        if (!this.manualMode && !provincia && this.nodes.provincia) {
            clearSelect(this.nodes.provincia, this.nodes.provincia.dataset.placeholder || this.nodes.provincia.getAttribute('placeholder') || '');
            this.state.provincia = null;
        }

        if (!this.manualMode && provincia && this.nodes.provincia) {
            const provinciaId = `${provincia.id}`;
            ensureOption(this.nodes.provincia, { id: provinciaId, text: provincia.text || provincia.nome }, false);
            this.state.provincia = provincia;
            commitSelect2Value(this.nodes.provincia, provinciaId);
        }

        if (!this.manualMode && !comune && this.nodes.comune) {
            clearSelect(this.nodes.comune, this.nodes.comune.dataset.placeholder || this.nodes.comune.getAttribute('placeholder') || '');
            this.state.comune = null;
        }

        if (!this.manualMode && comune && this.nodes.comune) {
            const comuneId = `${comune.id}`;
            ensureOption(this.nodes.comune, { id: comuneId, text: comune.text || comune.nome }, false);
            this.state.comune = comune;
            commitSelect2Value(this.nodes.comune, comuneId);
        }

        if (!this.manualMode && caps && Array.isArray(caps) && caps.length) {
            const capList = caps.map((c) => ({ id: c.cap, text: c.cap }));
            const select = this.nodes.cap;
            if (select) {
                select.innerHTML = '';
                capList.forEach((c) => {
                    const opt = new Option(c.text, c.id, false, false);
                    select.appendChild(opt);
                });

                // Prioriza mantener el CAP actual si coincide; si no, usa capDefault o el primero
                const target = (() => {
                    if (currentCapValue && capList.find((c) => c.id === currentCapValue)) {
                        return currentCapValue;
                    }
                    return capDefault || (capList[0] ? capList[0].id : null);
                })();

                if (target) {
                    ensureOption(select, { id: target, text: target }, false);
                    this.state.cap = { id: target, text: target };
                } else {
                    this.state.cap = null;
                }
            }
        } else if (!this.manualMode && this.nodes.cap) {
            const hasHierarchy = !!(regione || provincia || comune);
            const hasCurrentCap = !!currentCapValue;
            if (hasCurrentCap) {
                // Mantiene el CAP actual aunque /resolve no devuelva caps
                ensureOption(this.nodes.cap, { id: currentCapValue, text: currentCapValue });
                this.state.cap = { id: currentCapValue, text: currentCapValue };
            } else if (hasHierarchy) {
                // Sin caps pero con jerarquía: conservar contexto sin limpiar inputs
                this.state.cap = null;
            } else {
                clearSelect(this.nodes.cap, this.nodes.cap.dataset.placeholder || this.nodes.cap.getAttribute('placeholder') || '');
                if (typeof $ !== 'undefined') {
                    $(this.nodes.cap).val(null).trigger('change.select2');
                }
                this.state.cap = null;
            }
        }
    }

    emitChange() {
        const detail = {
            manualMode: this.manualMode,
            nazione: this.manualMode
                ? (this.state.nazione ? this.statePayload(this.nodes.nazione, this.state.nazione) : { value: null, label: '' })
                : this.statePayload(this.nodes.nazione, this.state.nazione),
            regione: this.manualMode ? { label: this.nodes.manualRegione?.value || '' } : this.statePayload(this.nodes.regione),
            provincia: this.manualMode ? { label: this.nodes.manualProvincia?.value || '' } : this.statePayload(this.nodes.provincia),
            comune: this.manualMode ? { label: this.nodes.manualComune?.value || '' } : this.statePayload(this.nodes.comune),
            cap: this.manualMode ? { value: this.nodes.manualCap?.value || '', label: this.nodes.manualCap?.value || '' } : this.statePayload(this.nodes.cap),
        };
        dispatchChange(this.root, detail);
    }

    updateCittadinanza(value) {
        if (this.nodes.cittadinanza) {
            this.nodes.cittadinanza.value = value || '';
        }
    }

    statePayload(selectEl, cached = null) {
        if (!selectEl) return { value: null, label: '' };
        const val = selectEl.value || null;
        const label = optionLabel(selectEl);
        const cache = cached || {};
        return {
            id: cache.id || val,
            value: val,
            label: cache.text || label,
            codice_iso2: cache.codice_iso2,
            sigla: cache.sigla,
            codice_istat: cache.codice_istat,
        };
    }
}

export function initGeoItalia(root = document) {
    const scope = root || document;
    const nodes = scope.querySelectorAll ? scope.querySelectorAll('[data-ui="geo-italia"]') : [];
    nodes.forEach((node) => {
        if (!initOnce(node, 'geo-italia')) return;
        // eslint-disable-next-line no-new
        new GeoItaliaUI(node);
    });
}
