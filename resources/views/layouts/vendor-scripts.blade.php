<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins.js') }}"></script>
<!-- jQuery & Select2 locali (offline friendly) -->
<script src="{{ asset('libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('libs/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('libs/select2/js/i18n/it.js') }}"></script>
<!-- Axios (locale) per AJAX standardizzato -->
<script src="{{ asset('libs/axios/axios.min.js') }}"></script>
<!-- Flatpickr Italian locale -->
<script src="{{ URL::asset('build/libs/flatpickr/l10n/it.js') }}"></script>
<!-- Axios helper -->
<script src="{{ URL::asset('build/js/utils/http.js') }}"></script>
<!-- SweetAlert2 (locale, già presente in build/libs) -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- SweetAlert i18n injection from Laravel translations -->
<script>
	window.saI18n = {
		buttons: {
			confirm: @json(__('translation.alerts.buttons.confirm')),
			cancel: @json(__('translation.alerts.buttons.cancel')),
			ok: @json(__('translation.alerts.buttons.ok')),
		},
		success: { title: @json(__('translation.alerts.success.title')), text: @json(__('translation.alerts.success.text')) },
		error: { title: @json(__('translation.alerts.error.title')), text: @json(__('translation.alerts.error.text')) },
		warning: { title: @json(__('translation.alerts.warning.title')), text: @json(__('translation.alerts.warning.text')) },
		info: { title: @json(__('translation.alerts.info.title')), text: @json(__('translation.alerts.info.text')) },
		confirm_delete: { title: @json(__('translation.alerts.confirm_delete.title')), text: @json(__('translation.alerts.confirm_delete.text')) },
		confirm_create: { title: @json(__('translation.alerts.confirm_create.title')), text: @json(__('translation.alerts.confirm_create.text')) },
		confirm_update: { title: @json(__('translation.alerts.confirm_update.title')), text: @json(__('translation.alerts.confirm_update.text')) },
	};
</script>
<!-- Alerts helper -->
<script src="{{ URL::asset('js/alerts.js') }}"></script>
<!-- Flash messages toasts -->
@if(session('success'))
	<script>window.alerts && window.alerts.toastSuccess(@json(session('success')));</script>
@endif
@if(session('error'))
	<script>window.alerts && window.alerts.toastError(@json(session('error')));</script>
@endif
@if(session('warning'))
	<script>window.alerts && window.alerts.toastWarning(@json(session('warning')));</script>
@endif
@if(session('info') || session('status'))
	<script>window.alerts && window.alerts.toastInfo(@json(session('info') ?? session('status')));</script>
@endif
<!-- Init Select2 globale -->
<script src="{{ URL::asset('js/select2-init.js') }}"></script>
@yield('script')
@yield('script-bottom')
