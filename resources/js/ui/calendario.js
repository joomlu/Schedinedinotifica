import flatpickr from 'flatpickr';
import { Italian } from 'flatpickr/dist/l10n/it.js';
import { initOnce } from '../core/once';

flatpickr.localize(Italian);

function boolAttr(el, key) {
    const val = (el.getAttribute(key) || '').toLowerCase();
    return ['1', 'true', 'yes'].includes(val);
}

function parseVariant(el) {
    return (el.getAttribute('data-calendar-variant') || 'single').toLowerCase();
}

function parseDefaultDate(el) {
    return el.getAttribute('data-default-date')
        || el.getAttribute('data-deafult-date')
        || el.value
        || null;
}

function todayIso() {
    return new Date().toISOString().slice(0, 10);
}

function configurePair(groupNodes) {
    const start = groupNodes.start;
    const end = groupNodes.end;
    if (!start || !end || !start._flatpickr || !end._flatpickr) return;

    const variant = parseVariant(start);
    const isCheckFlow = variant === 'checkin' || parseVariant(end) === 'checkout';
    const baseMin = isCheckFlow ? todayIso() : null;

    if (baseMin) {
        start._flatpickr.set('minDate', baseMin);
        if (!start.value) {
            end._flatpickr.set('minDate', baseMin);
        }
    }

    const syncEndMinDate = () => {
        const startDate = start._flatpickr.selectedDates[0] || null;
        end._flatpickr.set('minDate', startDate || baseMin || null);

        const endDate = end._flatpickr.selectedDates[0] || null;
        if (startDate && endDate && endDate < startDate) {
            end._flatpickr.clear();
        }
    };

    start._flatpickr.config.onChange.push(syncEndMinDate);
    start._flatpickr.config.onValueUpdate.push(syncEndMinDate);
    syncEndMinDate();
}

export function initCalendario(root = document) {
    const scope = root || document;
    const inputs = scope.querySelectorAll
        ? scope.querySelectorAll('input[data-ui="calendario"][data-provider="flatpickr"]')
        : [];

    const groups = new Map();

    inputs.forEach((el) => {
        if (!initOnce(el, 'calendario')) return;

        const variant = parseVariant(el);
        const dateFormat = el.getAttribute('data-date-format') || 'Y-m-d';
        const altInput = boolAttr(el, 'data-alt-input');
        const altFormat = el.getAttribute('data-alt-format') || 'd/m/Y';
        const minDateAttr = el.getAttribute('data-min-date') || null;
        const maxDateAttr = el.getAttribute('data-max-date') || null;
        const defaultDate = parseDefaultDate(el);
        const isRange = boolAttr(el, 'data-range-date') || variant === 'range';
        const isBirth = variant === 'birth';
        const isCheck = variant === 'checkin' || variant === 'checkout';

        const opts = {
            locale: Italian,
            dateFormat,
            altInput,
            altFormat,
            allowInput: true,
            disableMobile: true,
            defaultDate: defaultDate || undefined,
            minDate: minDateAttr || undefined,
            maxDate: maxDateAttr || undefined,
        };

        if (isRange) {
            opts.mode = 'range';
        }

        if (isBirth && !opts.maxDate) {
            opts.maxDate = todayIso();
        }

        if (isCheck && !opts.minDate) {
            opts.minDate = todayIso();
        }

        flatpickr(el, opts);

        const group = el.getAttribute('data-calendar-group');
        const role = el.getAttribute('data-calendar-role');
        if (group && role && (role === 'start' || role === 'end')) {
            const current = groups.get(group) || { start: null, end: null };
            current[role] = el;
            groups.set(group, current);
        }
    });

    groups.forEach((pair) => configurePair(pair));
}
