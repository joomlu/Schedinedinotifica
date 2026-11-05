import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import { initOnce, isDev } from '../core/once';

// Associa plugin alla nostra istanza di jQuery senza toccare window
DataTable.use($);

const DATATABLE_IT = {
    url: null,
    decimal: ',',
    thousands: '.',
    emptyTable: 'Nessun dato disponibile',
    info: 'Mostra da _START_ a _END_ di _TOTAL_ elementi',
    infoEmpty: 'Mostra da 0 a 0 di 0 elementi',
    infoFiltered: '(filtrati da _MAX_ elementi totali)',
    lengthMenu: 'Mostra _MENU_ elementi',
    loadingRecords: 'Caricamento...',
    processing: 'Elaborazione...',
    search: 'Cerca:',
    zeroRecords: 'Nessun elemento trovato',
    paginate: {
        first: 'Primo',
        last: 'Ultimo',
        next: 'Successivo',
        previous: 'Precedente',
    },
    aria: {
        sortAscending: ': attiva per ordinare in modo crescente',
        sortDescending: ': attiva per ordinare in modo decrescente',
    },
};

// Default globali: coprono anche inizializzazioni DataTables legacy senza language esplicito.
if ($.fn?.dataTable?.defaults) {
    $.extend(true, $.fn.dataTable.defaults, {
        language: DATATABLE_IT,
        // Compat con opzioni legacy (DataTables 1.x)
        oLanguage: {
            sEmptyTable: DATATABLE_IT.emptyTable,
            sInfo: DATATABLE_IT.info,
            sInfoEmpty: DATATABLE_IT.infoEmpty,
            sInfoFiltered: DATATABLE_IT.infoFiltered,
            sLengthMenu: DATATABLE_IT.lengthMenu,
            sLoadingRecords: DATATABLE_IT.loadingRecords,
            sProcessing: DATATABLE_IT.processing,
            sSearch: DATATABLE_IT.search,
            sZeroRecords: DATATABLE_IT.zeroRecords,
            oPaginate: {
                sFirst: DATATABLE_IT.paginate.first,
                sLast: DATATABLE_IT.paginate.last,
                sNext: DATATABLE_IT.paginate.next,
                sPrevious: DATATABLE_IT.paginate.previous,
            },
            oAria: {
                sSortAscending: DATATABLE_IT.aria.sortAscending,
                sSortDescending: DATATABLE_IT.aria.sortDescending,
            },
        },
    });
}

if (DataTable?.defaults) {
    DataTable.defaults.language = DATATABLE_IT;
}

function normalizeInfoTextToItalian(infoEl) {
    if (!infoEl) return;
    const txt = (infoEl.textContent || '').trim();
    if (!txt) return;

    // "Showing 1 to 10 of 95 entries/results"
    let match = txt.match(/^Showing\s+(\d+)\s+to\s+(\d+)\s+of\s+(\d+)\s+(entries|results)/i);
    if (match) {
        infoEl.textContent = `Mostra da ${match[1]} a ${match[2]} di ${match[3]} elementi`;
        return;
    }

    // "Showing 0 to 0 of 0 entries (filtered from 95 total entries)"
    match = txt.match(/^Showing\s+(\d+)\s+to\s+(\d+)\s+of\s+(\d+)\s+entries\s+\(filtered from\s+(\d+)\s+total entries\)/i);
    if (match) {
        infoEl.textContent = `Mostra da ${match[1]} a ${match[2]} di ${match[3]} elementi (filtrati da ${match[4]} elementi totali)`;
        return;
    }

    if (/^No data available in table$/i.test(txt)) {
        infoEl.textContent = 'Nessun dato disponibile';
    }
}

function enforceItalianInfoTexts(scope = document) {
    const root = scope || document;
    if (!root.querySelectorAll) return;
    root.querySelectorAll('.dataTables_info').forEach((el) => normalizeInfoTextToItalian(el));
}

export function initDataTables(root = document) {
    const scope = root || document;
    enforceItalianInfoTexts(scope);
    const tables = scope.querySelectorAll ? scope.querySelectorAll('table[data-ui="datatable"]') : [];

    tables.forEach((el) => {
        if (!initOnce(el, 'datatable')) return;
        if ($.fn.dataTable.isDataTable(el)) return;

        const pageLengthAttr = parseInt(el.getAttribute('data-page-length'), 10);
        const pageLength = Number.isNaN(pageLengthAttr) ? 25 : pageLengthAttr;
        const searchingAttr = el.getAttribute('data-searching');
        const responsiveAttr = el.getAttribute('data-responsive');
        const orderAttr = el.getAttribute('data-order');

        let orderConfig;
        if (orderAttr) {
            const parts = orderAttr.split(',');
            if (parts.length === 2) {
                const idx = parseInt(parts[0], 10);
                const dir = parts[1];
                if (!Number.isNaN(idx) && (dir === 'asc' || dir === 'desc')) {
                    orderConfig = [[idx, dir]];
                }
            }
        }

        $(el).DataTable({
            responsive: responsiveAttr === null ? true : responsiveAttr !== '0',
            pageLength,
            autoWidth: false,
            searching: searchingAttr === null ? undefined : searchingAttr !== '0',
            order: orderConfig,
            language: DATATABLE_IT,
        });

        const wrapper = el.closest('.dataTables_wrapper');
        if (wrapper) {
            enforceItalianInfoTexts(wrapper);
            const observer = new MutationObserver(() => enforceItalianInfoTexts(wrapper));
            observer.observe(wrapper, {
                childList: true,
                subtree: true,
                characterData: true,
            });
        }
    });

    if (isDev()) {
        const stray = Array.from(document.querySelectorAll('table'))
            .filter((el) => $(el).hasClass('dataTable') && el.getAttribute('data-ui') !== 'datatable');
        if (stray.length) {
            // eslint-disable-next-line no-console
            console.warn('USO NON CONSENTITO: inizializza DataTable solo con data-ui="datatable" e window.UI.init', stray);
        }
    }
}
