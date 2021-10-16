<!--begin::Global Config(global config for global JS scripts)-->
<script>var KTAppSettings = { "breakpoints": { "sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1200 }, "colors": { "theme": { "base": { "white": "#ffffff", "primary": "#6993FF", "secondary": "#E5EAEE", "success": "#1BC5BD", "info": "#8950FC", "warning": "#FFA800", "danger": "#F64E60", "light": "#F3F6F9", "dark": "#212121" }, "light": { "white": "#ffffff", "primary": "#E1E9FF", "secondary": "#ECF0F3", "success": "#C9F7F5", "info": "#EEE5FF", "warning": "#FFF4DE", "danger": "#FFE2E5", "light": "#F3F6F9", "dark": "#D6D6E0" }, "inverse": { "white": "#ffffff", "primary": "#ffffff", "secondary": "#212121", "success": "#ffffff", "info": "#ffffff", "warning": "#ffffff", "danger": "#ffffff", "light": "#464E5F", "dark": "#ffffff" } }, "gray": { "gray-100": "#F3F6F9", "gray-200": "#ECF0F3", "gray-300": "#E5EAEE", "gray-400": "#D6D6E0", "gray-500": "#B5B5C3", "gray-600": "#80808F", "gray-700": "#464E5F", "gray-800": "#1B283F", "gray-900": "#212121" } }, "font-family": "Poppins" };</script>
<!--end::Global Config-->
<!--begin::Global Theme Bundle(used by all pages)-->
<script src="<?php echo $this->crm_library->asset_url('dist/plugins/global/plugins.bundle.js'); ?>"></script>
<script src="<?php echo $this->crm_library->asset_url('dist/plugins/custom/prismjs/prismjs.bundle.js'); ?>"></script>
<script src="<?php echo $this->crm_library->asset_url('dist/js/scripts.bundle.js'); ?>"></script>
<!--end::Global Theme Bundle-->

<script src="<?php echo $this->crm_library->asset_url('dist/plugins/custom/tinymce/tinymce.bundle.js?v=7.0.4'); ?>"></script>
<script src="<?php echo $this->crm_library->asset_url('dist/js/pages/crud/forms/editors/tinymce.js?v=7.0.4'); ?>"></script>



<?php
if (($this->uri->segment(1) == 'dashboard' && $this->uri->segment(2) == 'availability') || $this->uri->segment(1) == 'finance' || $this->uri->segment(1) == 'timesheets' || $this->uri->segment(1) == 'checkins' || ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'timetable') || ($this->uri->segment(1) == 'staff' && $this->uri->segment(2) == 'checkins')) {
	$this->config->load('google', TRUE);
	?><script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('maps_frontend_api_key', 'google'); ?>"></script><?php
}
if ($this->uri->segment(1) == 'user-activity') {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/user-activity.js'); ?>"></script><?php
}
if (($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'confirmation') || ($this->uri->segment(1) == 'sessions' && $this->uri->segment(2) == 'bulk')) {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/email.js'); ?>"></script><?php
}
if (($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'new-account')) {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/init-wizard.js'); ?>"></script><?php
}
if ($this->uri->segment(1) == 'settings' && $this->uri->segment(2) == 'groups' && ($this->uri->segment(3) == 'new' || $this->uri->segment(3) == 'edit')) {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/groups.js'); ?>"></script><?php
}
if ($this->uri->segment(1) == 'settings' && $this->uri->segment(2) == 'styling') {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/editor.js'); ?>"></script><?php
}
if ($this->uri->segment(1) == 'booking' || ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'subscriptions' && $this->uri->segment(3) == 'session') || ($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'subscriptions' && $this->uri->segment(3) == 'session')) {
	?><script>var currency_symbol = '<?php echo
	currency_symbol(); ?>';</script>
	<script src="<?php echo $this->crm_library->asset_url('dist/js/components/book.js'); ?>"></script><?php
}
if (($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'subscriptions' && $this->uri->segment(3) == 'all') || ($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'subscriptions')) {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/subscriptions.js'); ?>"></script><?php
}
if ($this->uri->segment(1) == 'settings' && $this->uri->segment(2) == 'tags') {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/editable-input.js'); ?>"></script><?php
}

if ($this->uri->segment(1) == 'settings' || ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'participants' && $this->uri->segment(3) != "") || ($this->uri->segment(1) == 'customers' && $this->uri->segment(2) == 'safety' && $this->uri->segment(3) != "")) {
	?><script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script><?php
}

if (($this->uri->segment(1) == 'customers' && $this->uri->segment(2) == 'schools') || ($this->uri->segment(1) == 'customers' && $this->uri->segment(2) == 'organisations') || ($this->uri->segment(1) == 'customers' && $this->uri->segment(2) == 'prospects' && $this->uri->segment(3) == 'schools') || ($this->uri->segment(1) == 'customers' && $this->uri->segment(2) == 'prospects' && $this->uri->segment(3) == 'organisations')) {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/customer-slide-out.js'); ?>"></script><?php
}

if ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'history') {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/booking-notification-slide-out.js'); ?>"></script><?php
}

if (($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'bookings') || ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'dashboard')) {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/bookings-view-overlay.js'); ?>"></script><?php
}

if ($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'subscriptions' && $this->uri->segment(4) == 'new') {
	?><script src="<?php echo $this->crm_library->asset_url('dist/js/components/participant-subscription.js'); ?>"></script><?php
}
