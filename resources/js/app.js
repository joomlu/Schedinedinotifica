import './core/http';
import './core/init';
import { register } from './core/registry';
import { isDev } from './core/once';

import $ from 'jquery';
import 'bootstrap';
import * as bootstrap from 'bootstrap';
import select2 from 'select2/dist/js/select2.full.js';
import flatpickr from 'flatpickr';
import Swal from 'sweetalert2';

import { initSelect2 } from './ui/select2';
import { initDataTables } from './ui/datatables';
import { initDatepicker } from './ui/datepicker';
import { initCalendario } from './ui/calendario';
import { initGeoItalia } from './ui/geo-italia';
import { initTipologieFiltro } from './components/filtri/tipologie-filtro';
import { initCustomerGroupCascade } from './components/filtri/customer-group-cascade';
import { attachLiveTableFilter } from './ui/table-live-filter';
import { initLayoutShell } from './ui/layout-shell';
import { initConfigUx } from './ui/config-ux';
import { initHelpPopovers } from './ui/help-popover';

// Esponi jQuery globalmente
if (typeof window !== 'undefined') {
    window.$ = window.jQuery = $;
    window.bootstrap = bootstrap;
    window.Swal = Swal;
    if (typeof select2 === 'function') {
        select2(window, $);
    }
}

// Guard DEV: impedisce l'uso diretto dei plugin senza data-ui richiesto
if (isDev()) {
    if ($.fn.select2) {
        const originalSelect2 = $.fn.select2;
        $.fn.select2 = function select2Guard(...args) {
            const invalid = this.filter((_, el) => el.getAttribute('data-ui') !== 'select-search');
            if (invalid.length) {
                // eslint-disable-next-line no-console
                console.error("Uso diretto di select2 vietato. Usare data-ui='select-search'.", invalid.toArray());
            }
            return originalSelect2.apply(this, args);
        };
    }

    if ($.fn.DataTable) {
        const originalDataTable = $.fn.DataTable;
        $.fn.DataTable = function dataTableGuard(...args) {
            const invalid = this.filter((_, el) => el.getAttribute('data-ui') !== 'datatable');
            if (invalid.length) {
                // eslint-disable-next-line no-console
                console.error('Uso diretto di DataTable vietato. Usare data-ui="datatable".', invalid.toArray());
            }
            return originalDataTable.apply(this, args);
        };
    }
}

function retrofitDataUi(scope = document) {
    if (!scope.querySelectorAll) return;
    const tables = scope.querySelectorAll('table#buttons-datatables:not([data-ui]), table.table.display:not([data-ui])');
    tables.forEach((el) => {
        el.setAttribute('data-ui', 'datatable');
        if (isDev()) {
            // eslint-disable-next-line no-console
            console.warn('Compat: aggiunto data-ui="datatable" a tabella legacy', el);
        }
    });

    const selects = scope.querySelectorAll('select.select2:not([data-no-select2="1"]):not([data-ui]), select[data-select2]:not([data-ui])');
    selects.forEach((el) => {
        el.setAttribute('data-ui', 'select-search');
        if (isDev()) {
            // eslint-disable-next-line no-console
            console.warn('Compat: aggiunto data-ui="select-search" a select legacy', el);
        }
    });

    const pickers = scope.querySelectorAll('input[data-provider="flatpickr"]:not([data-ui]), input.flatpickr-input:not([data-ui])');
    pickers.forEach((el) => {
        el.setAttribute('data-ui', 'datepicker');
        if (isDev()) {
            // eslint-disable-next-line no-console
            console.warn('Compat: aggiunto data-ui="datepicker" a input legacy', el);
        }
    });
}

function guardExternalCssLinks() {
    if (!isDev() || typeof document === 'undefined' || typeof window === 'undefined') return;
    const blocked = ['bootstrap.min.css', 'app.min.css', 'custom.min.css', 'select2', 'datatables', 'cdn'];
    const offenders = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).filter((link) => {
        const href = (link.getAttribute('href') || '').toLowerCase();
        const hasKeyword = blocked.some((kw) => href.includes(kw));
        if (!hasKeyword) return false;
        try {
            const url = new URL(href, window.location.origin);
            return url.origin !== window.location.origin;
        } catch (err) {
            return href.startsWith('http');
        }
    });
    if (offenders.length) {
        // eslint-disable-next-line no-console
        console.warn('CSS esterno vietato: assicurarsi che bootstrap/app/custom/select2/datatable provengano solo da asset locali.', offenders.map((el) => el.getAttribute('href')));
    }
}

// Flatpickr global (per eventuali usi legacy)
if (typeof window !== 'undefined') {
    window.flatpickr = flatpickr;
}

// Registrazione moduli in ordine definito
register('select2', initSelect2);
register('datepicker', initDatepicker);
register('calendario', initCalendario);
register('datatable', initDataTables);
register('geo-italia', initGeoItalia);
register('tipologie-filtro', initTipologieFiltro);
register('customer-group-cascade', initCustomerGroupCascade);
register('config-ux', initConfigUx);
register('help-popover', initHelpPopovers);

// Live table filter global (per riuso nei CRUD)
if (typeof window !== 'undefined') {
    window.attachLiveTableFilter = attachLiveTableFilter;
}

// Bootstrap di inizializzazione centralizzato
if (typeof window !== 'undefined' && window.UI && typeof window.UI.init === 'function') {
    document.addEventListener('DOMContentLoaded', () => {
        retrofitDataUi(document);
        guardExternalCssLinks();
        initLayoutShell();
        window.UI.init(document);
    });
    document.addEventListener('shown.bs.modal', (e) => {
        retrofitDataUi(e.target);
        window.UI.init(e.target);
    });

    if (window.Livewire && typeof window.Livewire.hook === 'function') {
        window.Livewire.hook('message.processed', (message, component) => {
            retrofitDataUi(component.el);
            window.UI.init(component.el);
        });
    }
}
