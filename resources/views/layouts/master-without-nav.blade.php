<!doctype html>
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

    @yield('body')

    @if(session('status'))
        <div
            class="alert alert-success d-none"
            data-server-alert="success"
            @if(session('server_alert_redirect')) data-server-alert-redirect="{{ session('server_alert_redirect') }}" @endif
        >{{ session('status') }}</div>
    @endif
    @if(session('success'))
        <div
            class="alert alert-success d-none"
            data-server-alert="success"
            @if(session('server_alert_redirect')) data-server-alert-redirect="{{ session('server_alert_redirect') }}" @endif
        >{{ session('success') }}</div>
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

    @include('layouts.vendor-scripts')
    <script>
        (function () {
            const alertEl = document.querySelector('[data-server-alert-redirect]');
            if (!alertEl) return;

            const target = alertEl.getAttribute('data-server-alert-redirect');
            if (!target) return;

            const bindRedirect = function () {
                const confirm = document.querySelector('.swal2-confirm');
                if (!confirm || confirm.dataset.redirectBound === '1') return false;

                confirm.dataset.redirectBound = '1';
                confirm.addEventListener('click', function () {
                    window.location.assign(target);
                });
                return true;
            };

            if (bindRedirect()) return;

            const observer = new MutationObserver(function () {
                if (bindRedirect()) observer.disconnect();
            });

            observer.observe(document.body, { childList: true, subtree: true });
        })();
    </script>
    </body>
</html>
