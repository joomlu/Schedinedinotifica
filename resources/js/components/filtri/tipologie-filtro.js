import { initOnce, isDev } from '../../core/once';

const LOCK_VERSION = 'strict-v1';

function parseIds(value) {
    return (value || '').split(',').map((s) => s.trim()).filter(Boolean);
}

function dispatchChange(select) {
    if (!select) return;
    select.dispatchEvent(new Event('change', { bubbles: true }));
    if (window.jQuery) {
        window.jQuery(select).trigger('change.select2');
    }
}

function setAvailability(option, enabled) {
    option.disabled = !enabled;
    option.hidden = !enabled;
}

function pickNodes(wrapper) {
    const generale = wrapper.querySelector('[data-role="tipologia-generale"]');
    const struttura = wrapper.querySelector('[data-role="tipologia-struttura"]');
    const classificazione = wrapper.querySelector('[data-role="tipologia-classificazione"]');
    return { generale, struttura, classificazione };
}

export function initTipologieFiltro(root = document) {
    const scope = root || document;
    const wrappers = scope.querySelectorAll ? scope.querySelectorAll('[data-ui="tipologie-filtro"]') : [];

    wrappers.forEach((wrap) => {
        if (!initOnce(wrap, 'tipologie-filtro')) return;

        if (wrap.getAttribute('data-tipologie-lock') !== LOCK_VERSION) {
            if (isDev()) {
                // eslint-disable-next-line no-console
                console.warn('tipologie-filtro: lock version mismatch, inizializzazione bloccata');
            }
            return;
        }

        const { generale, struttura, classificazione } = pickNodes(wrap);

        if (!generale || !struttura || !classificazione) {
            if (isDev()) {
                // eslint-disable-next-line no-console
                console.warn('tipologie-filtro: nodi mancanti', { generale: !!generale, struttura: !!struttura, classificazione: !!classificazione });
            }
            return;
        }

        const applyStrutturaFilter = () => {
            const generaleId = generale.value;
            let resetStruttura = false;

            Array.from(struttura.options).forEach((opt) => {
                if (!opt.value) {
                    setAvailability(opt, true);
                    return;
                }

                const visible = !!generaleId && opt.dataset.generale === generaleId;
                setAvailability(opt, visible);
                if (!visible && opt.selected) {
                    resetStruttura = true;
                }
            });

            if (resetStruttura || !generaleId) {
                struttura.value = '';
                dispatchChange(struttura);
            } else {
                applyClassificazioneFilter();
            }
        };

        const applyClassificazioneFilter = () => {
            const strutturaId = struttura.value;
            let resetClassificazione = false;

            Array.from(classificazione.options).forEach((opt) => {
                if (!opt.value) {
                    setAvailability(opt, true);
                    return;
                }

                const allowedTipologie = parseIds(opt.dataset.tipologie);
                const visible = !!strutturaId && allowedTipologie.includes(strutturaId);
                setAvailability(opt, visible);
                if (!visible && opt.selected) {
                    resetClassificazione = true;
                }
            });

            if (resetClassificazione || !strutturaId) {
                classificazione.value = '';
            }
        };

        generale.addEventListener('change', applyStrutturaFilter);
        struttura.addEventListener('change', applyClassificazioneFilter);

        applyStrutturaFilter();
        applyClassificazioneFilter();
    });
}
