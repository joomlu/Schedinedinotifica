import flatpickr from 'flatpickr';
import { Italian } from 'flatpickr/dist/l10n/it.js';
import { initOnce } from '../core/once';

flatpickr.localize(Italian);

export function initDatepicker(root = document) {
    const scope = root || document;
    const inputs = scope.querySelectorAll ? scope.querySelectorAll('input[data-ui="datepicker"]') : [];

    inputs.forEach((el) => {
        if (!initOnce(el, 'datepicker')) return;
        const enableTime = el.hasAttribute('data-enable-time');
        const format = el.getAttribute('data-format')
            || el.getAttribute('data-date-format')
            || el.getAttribute('data-dateformat')
            || 'd/m/Y';
        const time24hr = el.getAttribute('data-time-24hr') === '1';
        const minDate = el.getAttribute('data-min-date') || el.getAttribute('data-mindate') || undefined;
        const maxDate = el.getAttribute('data-max-date') || el.getAttribute('data-maxdate') || undefined;
        const altInputAttr = el.getAttribute('data-alt-input') || el.getAttribute('data-altinput');
        const altFormat = el.getAttribute('data-alt-format') || el.getAttribute('data-altformat');
        const altInput = altInputAttr === '1' || altInputAttr === 'true';

        const opts = {
            locale: Italian,
            dateFormat: format,
            allowInput: true,
            enableTime,
            time_24hr: time24hr,
            minDate,
            maxDate,
            disableMobile: true,
            altInput,
            altFormat,
        };
        flatpickr(el, opts);
    });
}
