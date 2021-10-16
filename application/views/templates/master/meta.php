<meta charset="utf-8">
<meta content='width=device-width, initial-scale=1' name='viewport'>
<title><?php if (isset($title)) { echo $title . ' | '; } echo $this->auth->account->company; ?></title>


<!--begin::Fonts-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<!--end::Fonts-->
<!--begin::Page Vendors Styles(used by this page)-->
<!--<link href="<?php echo $this->crm_library->asset_url('dist/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css'); ?>" rel="stylesheet" type="text/css" />-->
<!--end::Page Vendors Styles-->
<!--begin::Global Theme Styles(used by all pages)-->
<link href="<?php echo $this->crm_library->asset_url('dist/plugins/global/plugins.bundle.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->crm_library->asset_url('dist/plugins/custom/prismjs/prismjs.bundle.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->crm_library->asset_url('dist/css/style.bundle.css'); ?>" rel="stylesheet" type="text/css" />
<!--end::Global Theme Styles-->
<!--begin::Layout Themes(used by all pages)-->
<link href="<?php echo $this->crm_library->asset_url('dist/css/themes/layout/header/base/light.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->crm_library->asset_url('dist/css/themes/layout/header/menu/light.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->crm_library->asset_url('dist/css/themes/layout/brand/light.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->crm_library->asset_url('dist/css/themes/layout/aside/light.css'); ?>" rel="stylesheet" type="text/css" />
<!--end::Layout Themes-->
<?php
if($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'bookings'){
	?><link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/resizable.css'); ?>" /><?php
}
if ($this->uri->segment(1) == 'booking' || ($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'bookings' && $this->uri->segment(3) == 'view') || ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'subscriptions' && $this->uri->segment(3) == 'session') || ($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'bookings') || ($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'subscriptions' && $this->uri->segment(3) == 'session')) {
	?><link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/book.css'); ?>" /><?php
}
if (($this->uri->segment(1) == 'participants' && $this->uri->segment(2) == 'new-account')) {
	?><link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/new-account-wizard.css'); ?>" /><?php
}
if (($this->uri->segment(1) == 'participants' || $this->uri->segment(1) == 'staff' || $this->uri->segment(2) == 'view')) {
	?><link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/listing.css'); ?>" /><?php
}

if ($this->uri->segment(1) == 'settings' || ($this->uri->segment(1) == 'bookings' && $this->uri->segment(2) == 'participants' && $this->uri->segment(3) != "") || ($this->uri->segment(1) == 'customers' && $this->uri->segment(2) == 'safety' && $this->uri->segment(3) != "")) { ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<?php } ?>

<?php
$favicon_data = @unserialize($this->settings_library->get('favicon'));
$favicon = 'public/images/favicon.png';
if ($favicon_data !== FALSE) {
	$favicon = 'attachment/setting/favicon/' . $this->auth->user->accountID;
}
?>

<link rel="icon" type="image/png" href="<?php echo $this->crm_library->asset_url($favicon); ?>">
<link rel="shortcut icon" href="<?php echo $this->crm_library->asset_url($favicon); ?>">
<link rel="apple-touch-icon" href="<?php echo $this->crm_library->asset_url($favicon); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo $this->settings_library->get('company', 'default'); ?>">
<meta name="application-name" content="<?php echo $this->settings_library->get('company', 'default'); ?>">
<meta name="msapplication-TileImage" content="<?php echo $this->crm_library->asset_url($favicon); ?>">
<meta name="msapplication-TileColor" content="#FFFFFF">
<meta name="theme-color" content="#FFFFFF">
<link rel="manifest" href="<?php echo base_url('manifest.json'); ?>">
<meta name="robots" content="noindex, nofollow" />
<?php
if (defined('ANALYTICS_ID') && !empty(ANALYTICS_ID)) {
	?><script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', '<?php echo ANALYTICS_ID; ?>', 'auto');
		ga('set', '&uid', <?php echo $this->auth->user->staffID; ?>);
		ga('set', 'siteSpeedSampleRate', 50);
		ga('send', 'pageview');
	</script><?php
}
?>
<!-- Begin Upscope Code -->
<script>
(function(w, u, d){var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};var l = function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://code.upscope.io/G4rWT5ZXrr.js';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(typeof u!=="function"){w.Upscope=i;l();}})(window, window.Upscope, document);
Upscope('init');
</script>
<script>
Upscope('updateConnection', {
	uniqueId: '<?php echo $this->auth->user->staffID; ?>',
	identities: <?php echo json_encode([$this->auth->user->first . ' ' . $this->auth->user->surname, $this->auth->user->email]); ?>
});
</script>
<!-- End Upscope Code -->
