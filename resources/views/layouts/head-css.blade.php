@yield('css')
<!-- Layout config Js -->
<script src="{{ URL::asset('build/js/layout.js') }}"></script>
<!-- Bootstrap Css -->
<link href="{{ URL::asset('build/css/bootstrap.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('build/css/app.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- custom Css-->
<link href="{{ URL::asset('build/css/custom.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- Select2 CSS (locale) -->
<link href="{{ URL::asset('libs/select2/css/select2.min.css') }}" rel="stylesheet" />
<!-- Overrides per allineare Select2 a .form-control -->
<link href="{{ URL::asset('css/select2-overrides.css') }}" rel="stylesheet" />
<!-- Brand responsive overrides (logo sizes) -->
<link href="{{ URL::asset('css/brand-overrides.css') }}" rel="stylesheet" />
