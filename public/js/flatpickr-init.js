/*
 * Inizializzazione Flatpickr per tutti i date picker
 * Uso: Includi questo script dopo vendor-scripts in viste con campi date
 */

document.addEventListener('DOMContentLoaded', function() {
    // Import Italian locale
    if (typeof flatpickr !== 'undefined') {
        // Basic date pickers (singola data)
        const basicPickers = document.querySelectorAll('[data-provider="flatpickr"]:not([data-range-date])');
        basicPickers.forEach(function(element) {
            const format = element.getAttribute('data-date-format') || 'd M, Y';
            const minDate = element.getAttribute('data-min-date') || null;
            const maxDate = element.getAttribute('data-max-date') || null;
            
            flatpickr(element, {
                dateFormat: 'Y-m-d', // Formato per il backend
                altInput: true, // Mostra formato alternativo all'utente
                altFormat: format, // Formato italiano visibile
                locale: 'it',
                allowInput: true,
                minDate: minDate,
                maxDate: maxDate,
                disableMobile: false
            });
        });

        // Range date pickers (periodo)
        const rangePickers = document.querySelectorAll('[data-provider="flatpickr"][data-range-date="true"]');
        rangePickers.forEach(function(element) {
            const format = element.getAttribute('data-date-format') || 'd M, Y';
            
            flatpickr(element, {
                mode: 'range',
                dateFormat: 'Y-m-d', // Formato per il backend
                altInput: true,
                altFormat: format, // Formato italiano visibile
                locale: 'it',
                allowInput: true,
                disableMobile: false
            });
        });

        // Linked pickers (arrivo → partenza con minDate dinamico)
        const arriveFields = document.querySelectorAll('[data-linked-to]');
        arriveFields.forEach(function(arriveElement) {
            const departureSelector = arriveElement.getAttribute('data-linked-to');
            const departureElement = document.querySelector(departureSelector);
            
            if (departureElement) {
                const arrivePicker = flatpickr(arriveElement, {
                    dateFormat: 'Y-m-d', // Formato per il backend
                    altInput: true,
                    altFormat: 'd M, Y', // Formato italiano visibile
                    locale: 'it',
                    allowInput: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (departurePicker && selectedDates.length > 0) {
                            departurePicker.set('minDate', selectedDates[0]);
                        }
                    }
                });

                const departurePicker = flatpickr(departureElement, {
                    dateFormat: 'Y-m-d', // Formato per il backend
                    altInput: true,
                    altFormat: 'd M, Y', // Formato italiano visibile
                    locale: 'it',
                    allowInput: true,
                    minDate: arriveElement.value || null
                });
            }
        });
    }
});
