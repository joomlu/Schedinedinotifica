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
                    @if(session('status'))
                        <div class="alert alert-success" data-server-alert="success">{{ session('status') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success" data-server-alert="success">{{ session('success') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning" data-server-alert="warning">{{ session('warning') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger" data-server-alert="error">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" data-server-alert="error">
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
</body>

</html>
