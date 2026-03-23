<script src="{{ URL::asset('build/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/select2/js/select2.full.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/feather-icons/feather.min.js') }}"></script>
<!-- Flatpickr JS -->
<script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/flatpickr/l10n/it.js') }}"></script>
@php($appJs = \Illuminate\Support\Facades\Vite::useManifestFilename('.vite/manifest.json')->asset('resources/js/app.js'))
<script type="module" src="{{ $appJs }}" data-entry="app-js"></script>
<script>
    (function () {
        const applyItDataTables = function () {
            try {
                if (window.$ && $.fn && $.fn.dataTable && $.fn.dataTable.defaults) {
                    $.extend(true, $.fn.dataTable.defaults, {
                        language: {
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
                                previous: 'Precedente'
                            }
                        },
                        oLanguage: {
                            sInfo: 'Mostra da _START_ a _END_ di _TOTAL_ elementi',
                            sInfoEmpty: 'Mostra da 0 a 0 di 0 elementi',
                            sInfoFiltered: '(filtrati da _MAX_ elementi totali)'
                        }
                    });
                }
            } catch (e) {}
        };

        const normalizeInfo = function (text) {
            let m = text.match(/^Showing\s+(\d+)\s+to\s+(\d+)\s+of\s+(\d+)\s+(entries|results)/i);
            if (m) return `Mostra da ${m[1]} a ${m[2]} di ${m[3]} elementi`;

            m = text.match(/^Showing\s+(\d+)\s+to\s+(\d+)\s+of\s+(\d+)\s+entries\s+\(filtered from\s+(\d+)\s+total entries\)/i);
            if (m) return `Mostra da ${m[1]} a ${m[2]} di ${m[3]} elementi (filtrati da ${m[4]} elementi totali)`;

            if (/^No data available in table$/i.test(text)) return 'Nessun dato disponibile';
            return text;
        };

        const translateInfoLabels = function (scope) {
            (scope || document).querySelectorAll('.dataTables_info').forEach(function (el) {
                const current = (el.textContent || '').trim();
                if (!current) return;
                const translated = normalizeInfo(current);
                if (translated !== current) el.textContent = translated;
            });
        };

        const boot = function () {
            applyItDataTables();
            translateInfoLabels(document);
            const observer = new MutationObserver(function () {
                translateInfoLabels(document);
            });
            observer.observe(document.body, { childList: true, subtree: true, characterData: true });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>
@stack('scripts')
@yield('script')
@yield('script-bottom')
