<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script>
	window.AppDateConfig = {
		displayFormat: "{{ config('app.date.display_format', 'd/m/Y') }}",
		backendFormat: "{{ config('app.date.backend_format', 'Y-m-d') }}"
	};
</script>
<script src="{{ URL::asset('build/js/plugins.js') }}"></script>
@yield('script')
@yield('script-bottom')
