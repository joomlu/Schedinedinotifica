<!doctype html >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Gestionale per strutture ricettive" name="description" />
    <meta content="Tango" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('images/tango.png') }}">
    @include('layouts.head-css')
</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success d-none" data-server-alert="success">{{ session('success') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning d-none" data-server-alert="warning">{{ session('warning') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger d-none" data-server-alert="error">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger d-none" data-server-alert="error">
                            {{ collect($errors->all())->take(3)->implode(' ') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

   

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
    <script>
        (function () {
            const onReady = function (callback) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', callback, { once: true });
                } else {
                    callback();
                }
            };

            const getAlertLibrary = function () {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    return window.Swal;
                }
                return null;
            };

            const getMethod = function (form) {
                const spoofed = form.querySelector('input[name="_method"]');
                return ((spoofed && spoofed.value) || form.getAttribute('method') || 'GET').toUpperCase();
            };

            const getActionType = function (form, submitter) {
                const method = getMethod(form);
                const action = (form.getAttribute('action') || '').toLowerCase();
                const text = (((submitter && (submitter.innerText || submitter.value)) || '') + '').toLowerCase();
                const klass = ((submitter && submitter.className) || '').toLowerCase();
                const submitterName = ((submitter && submitter.getAttribute('name')) || '').toLowerCase();
                const submitterValue = ((submitter && submitter.getAttribute('value')) || '').toLowerCase();

                if (submitterName === 'save_mode' && submitterValue === 'draft') {
                    return 'draft';
                }

                if (submitterName === 'save_mode' && submitterValue === 'to_schedina') {
                    return 'to_schedina';
                }

                if (method === 'DELETE' || action.includes('delete') || action.includes('destroy') || text.includes('elimina') || text.includes('cancella') || klass.includes('danger')) {
                    return 'delete';
                }

                if (method === 'PUT' || method === 'PATCH' || action.includes('update') || text.includes('modifica') || text.includes('aggiorna')) {
                    return 'update';
                }

                return 'save';
            };

            const confirmCopy = function (type) {
                if (type === 'delete') {
                    return {
                        title: 'Conferma eliminazione',
                        text: 'Stai per eliminare un record. Vuoi continuare?',
                        button: 'Sì, elimina',
                    };
                }

                if (type === 'update') {
                    return {
                        title: 'Conferma modifica',
                        text: 'Stai per modificare i dati. Vuoi continuare?',
                        button: 'Sì, modifica',
                    };
                }

                if (type === 'draft') {
                    return {
                        title: 'Conferma salvataggio bozza',
                        text: 'Il cliente verrà salvato come bozza provvisoria. Vuoi continuare?',
                        button: 'Sì, salva bozza',
                    };
                }

                if (type === 'to_schedina') {
                    return {
                        title: 'Salva e apri schedina',
                        text: 'Il cliente verrà salvato e subito aperto in una nuova schedina precompilata. Vuoi continuare?',
                        button: 'Sì, salva in schedina',
                    };
                }

                return {
                    title: 'Conferma salvataggio',
                    text: 'Stai per salvare i dati. Vuoi continuare?',
                    button: 'Sì, salva',
                };
            };

            const showServerAlertsFallback = async function () {
                if (document.body.dataset.serverAlertFallbackShown === '1') {
                    return;
                }

                document.body.dataset.serverAlertFallbackShown = '1';

                const success = document.querySelector('[data-server-alert="success"]');
                const warning = document.querySelector('[data-server-alert="warning"]');
                const error = document.querySelector('[data-server-alert="error"]');
                const swal = getAlertLibrary();

                const show = async function (title, text, icon) {
                    if (!text) return;
                    if (swal) {
                        await swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            confirmButtonText: 'OK',
                        });
                    } else {
                        window.alert(text);
                    }
                };

                if (success) {
                    await show('Operazione completata', (success.textContent || '').trim(), 'success');
                    success.style.display = 'none';
                    return;
                }

                if (warning) {
                    await show('Attenzione', (warning.textContent || '').replace(/\s+/g, ' ').trim(), 'info');
                    warning.style.display = 'none';
                    return;
                }

                if (error) {
                    await show('Attenzione', (error.textContent || '').replace(/\s+/g, ' ').trim(), 'error');
                    error.style.display = 'none';
                }
            };

            const bindGlobalSubmitFallback = function () {
                if (document.body.dataset.globalConfirmFallbackBound === '1') {
                    return;
                }

                document.body.dataset.globalConfirmFallbackBound = '1';

                document.addEventListener('submit', async function (event) {
                    const form = event.target;
                    if (!(form instanceof HTMLFormElement)) return;
                    if (form.dataset.confirmIgnore === '1' || form.dataset.confirm === 'off') return;
                    if (form.dataset.confirmedAction === '1') {
                        delete form.dataset.confirmedAction;
                        return;
                    }
                    const action = (form.getAttribute('action') || '').toLowerCase();
                    if (action.includes('/logout')) return;
                    if (getMethod(form) === 'GET') return;
                    if (form.closest('.auth-page-wrapper, .signin-main, .login-content')) return;
                    if (action.includes('/schedine')) return;

                    const submitter = event.submitter || null;
                    const isDraftAction = !!(
                        submitter &&
                        (
                            (submitter.getAttribute('name') === 'save_mode' && (submitter.getAttribute('value') || '').toLowerCase() === 'draft') ||
                            submitter.hasAttribute('formnovalidate')
                        )
                    );
                    if (isDraftAction) return;

                    event.preventDefault();
                    event.stopPropagation();

                    const type = getActionType(form, submitter);
                    const copy = confirmCopy(type);
                    const swal = getAlertLibrary();

                    let confirmed = false;

                    if (swal) {
                        const result = await swal.fire({
                            title: copy.title,
                            text: copy.text,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: copy.button,
                            cancelButtonText: 'Annulla',
                            reverseButtons: true,
                            focusCancel: true,
                        });
                        if (result.isConfirmed) {
                            const doneTitle = type === 'delete'
                                ? 'Eliminazione confermata'
                                : (type === 'update'
                                    ? 'Modifica confermata'
                                    : (type === 'draft'
                                        ? 'Bozza confermata'
                                        : (type === 'to_schedina'
                                            ? 'Passaggio a schedina confermato'
                                            : 'Salvataggio confermato')));
                            const doneText = type === 'delete'
                                ? 'Premi OK per procedere con l\'eliminazione.'
                                : (type === 'update'
                                    ? 'Premi OK per procedere con la modifica.'
                                    : (type === 'draft'
                                        ? 'Premi OK per procedere con il salvataggio provvisorio.'
                                        : (type === 'to_schedina'
                                            ? 'Premi OK per salvare il cliente e aprire la schedina nuova.'
                                            : 'Premi OK per procedere con il salvataggio.')));

                            const second = await swal.fire({
                                title: doneTitle,
                                text: doneText,
                                icon: 'success',
                                confirmButtonText: 'OK',
                            });

                            confirmed = !!second.isConfirmed;
                        }
                    } else {
                        confirmed = window.confirm(copy.text);
                    }

                    if (!confirmed) return;

                    form.dataset.confirmedAction = '1';
                    if (typeof form.requestSubmit === 'function') {
                        if (submitter) {
                            form.requestSubmit(submitter);
                        } else {
                            form.requestSubmit();
                        }
                    } else {
                        form.submit();
                    }
                }, true);
            };

            onReady(function () {
                bindGlobalSubmitFallback();
                showServerAlertsFallback();
            });
        })();
    </script>
</body>

</html>
