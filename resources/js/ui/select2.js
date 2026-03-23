import $ from 'jquery';
import { initOnce, isDev } from '../core/once';

export function initSelect2(root = document) {
    const scope = root || document;
    const selects = scope.querySelectorAll
        ? scope.querySelectorAll('select[data-ui="select-search"]')
        : [];

    const hasPlugin = $.fn && typeof $.fn.select2 === 'function';
    if (!hasPlugin) {
        if (isDev()) {
            // eslint-disable-next-line no-console
            console.error('Select2 non disponibile in runtime');
        }
        selects.forEach((el) => el.removeAttribute('data-init-select2'));
        return;
    }

    selects.forEach((el) => {
        if (el.getAttribute('data-no-select2') === '1') return;
        if (el.closest('[data-ui="geo-italia"]')) return;
        if (el.getAttribute('data-geo') === '1') return;
        if (!initOnce(el, 'select2')) return;
        // Enforce single-select (no multiple, no tags) as standard
        el.removeAttribute('multiple');
        $(el).prop('multiple', false);
        el.removeAttribute('data-tags');
        el.removeAttribute('tags');

        const placeholder = el.getAttribute('data-placeholder') || el.getAttribute('placeholder') || 'Seleziona...';
        const parentModal = el.closest('.modal');
        const allowClearAttr = el.getAttribute('data-allow-clear');
        const minSearchAttr = parseInt(el.getAttribute('data-min-search') || '', 10);
        const allowManualAttr = (el.getAttribute('data-allow-manual') || '').toLowerCase();
        const allowManual = ['1', 'true', 'yes'].includes(allowManualAttr);

        const cfg = {
            theme: 'bootstrap-5',
            width: '100%',
            dropdownAutoWidth: true,
            language: 'it',
            placeholder,
            minimumResultsForSearch: Number.isFinite(minSearchAttr) ? minSearchAttr : 0,
            minimumInputLength: 0,
            closeOnSelect: true,
            allowClear: allowClearAttr === null ? false : !['0', 'false', 'no'].includes(allowClearAttr),
            multiple: false,
            tags: allowManual,
            tokenSeparators: [],
        };

        if (allowManual) {
            cfg.createTag = (params) => {
                const term = (params.term || '').trim();
                if (!term) return null;

                return {
                    id: term,
                    text: term,
                    newTag: true,
                };
            };

            cfg.insertTag = (data, tag) => {
                const exists = data.some((item) => String(item.id).trim() === String(tag.id).trim());
                if (!exists) {
                    data.unshift(tag);
                }
            };
        }

        if (parentModal) {
            cfg.dropdownParent = $(parentModal);
        }

        const $el = $(el);
        $el.select2(cfg);

        if (allowManual) {
            $el.on('select2:close', () => {
                const inst = $el.data('select2');
                const term = inst && inst.dropdown && inst.dropdown.$search
                    ? inst.dropdown.$search.val().trim()
                    : '';

                if (!term) return;
                const exists = Array.from(el.options).some((opt) => opt.value === term);
                if (!exists) {
                    const opt = new Option(term, term, true, true);
                    el.appendChild(opt);
                }

                $el.val(term).trigger('change');
                if (inst && inst.dropdown && inst.dropdown.$search) {
                    inst.dropdown.$search.val('');
                }
            });
        }
    });

    if (isDev()) {
        const stray = Array.from(document.querySelectorAll('.select2-hidden-accessible'))
            .filter((el) => el.getAttribute('data-ui') !== 'select-search');
        if (stray.length) {
            // eslint-disable-next-line no-console
            console.warn('USO NON CONSENTITO: inizializza select2 solo con data-ui="select-search" e window.UI.init', stray);
        }
    }
}
