<meta charset="utf-8">
<meta content='width=device-width, initial-scale=1' name='viewport'>
<title><?php
if (isset($title)) {
	echo $title . ' | ';
}
if (!empty($this->settings_library->get('sign_in_page_title', 'default'))) {
	echo $this->settings_library->get('sign_in_page_title', 'default');
} else {
	echo $this->settings_library->get('company', 'default');
}
?></title>

<!--begin::Fonts-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<!--end::Fonts-->
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

<link rel="icon" type="image/png" href="<?php echo $this->crm_library->asset_url('public/images/favicon.png'); ?>">
<link rel="shortcut icon" href="<?php echo $this->crm_library->asset_url('public/images/favicon.ico'); ?>">
<link rel="apple-touch-icon" href="<?php echo $this->crm_library->asset_url('public/images/favicon.png'); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo $this->settings_library->get('company', 'default'); ?>">
<meta name="application-name" content="<?php echo $this->settings_library->get('company', 'default'); ?>">
<meta name="msapplication-TileImage" content="<?php echo $this->crm_library->asset_url('public/images/favicon.png'); ?>">
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
	uniqueId: undefined
});
</script>
<!-- End Upscope Code -->
