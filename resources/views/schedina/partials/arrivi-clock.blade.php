<script>
(() => {
    const confirmation = 'Confermi il passaggio a schedina? La scheda uscirà da Arrivi e sarà inclusa nei normali flussi delle schedine.';
    const init = () => {
        const form = document.getElementById('schedina-form');
        if (!form) return;
        // Capture: annullare non modifica hidden, pulsanti, dati o stato.
        form.addEventListener('click', event => {
            const button = event.target.closest('[data-arrivi-convert]');
            if (!button) return;
            if (!window.confirm(confirmation)) { event.preventDefault(); event.stopImmediatePropagation(); return; }
            let input = form.querySelector('[name="confirm_conversion"]');
            if (!input) { input = document.createElement('input'); input.type = 'hidden'; input.name = 'confirm_conversion'; form.append(input); }
            input.value = '1';
        }, true);
        if (!form.dataset.arriviClock) return;
        const arrival = form.querySelector('[name="arrive"]');
        const departure = form.querySelector('[name="departure"]');
        const warning = document.getElementById('arrivi-day-warning');
        let clock = @json(\App\Support\ArriviDay::clock());
        let receivedAt = performance.now();
        let sequence = 0;
        const refresh = () => {
            const date = new Date(clock.epoch_ms + performance.now() - receivedAt);
            const parts = new Intl.DateTimeFormat('en-CA', {timeZone: clock.timezone, year:'numeric',month:'2-digit',day:'2-digit'}).formatToParts(date);
            const val = type => parts.find(p => p.type === type).value;
            const today = `${val('year')}-${val('month')}-${val('day')}`;
            if (arrival.value !== today) {
                // Non emette onChange del gruppo calendario e non tocca la partenza.
                if (arrival._flatpickr) arrival._flatpickr.setDate(today, false, 'Y-m-d');
                else arrival.value = today;
            }
            arrival.readOnly = true;
            if (arrival._flatpickr) {
                arrival._flatpickr.set('clickOpens', false);
                if (arrival._flatpickr.altInput) arrival._flatpickr.altInput.readOnly = true;
            }
            warning.hidden = !departure.value || departure.value > today;
        };
        const sync = async () => {
            const requestSequence = ++sequence;
            try {
                const url = new URL(form.dataset.arriviClock, window.location.origin);
                url.searchParams.set('schedina_struttura_id', form.querySelector('[name="schedina_struttura_id"]').value);
                const response = await fetch(url, {headers:{Accept:'application/json'}, cache:'no-store'});
                if (!response.ok) return;
                const next = await response.json();
                if (requestSequence !== sequence) return;
                const at = performance.now();
                // Una risposta lenta non deve riportare il modulo al giorno precedente.
                next.epoch_ms = Math.max(next.epoch_ms, clock.epoch_ms + at - receivedAt);
                clock = next; receivedAt = at; refresh();
            } catch (_) { /* Il server ricalcola sempre la data prima di ogni scrittura. */ }
        };
        refresh(); sync();
        setInterval(refresh, 1000); setInterval(sync, 30000);
        departure.addEventListener('change', refresh);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) { refresh(); sync(); } });
        window.addEventListener('pageshow', () => { refresh(); sync(); });
        form.addEventListener('submit', refresh, true);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
