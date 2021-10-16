<?php
$favicon_data = @unserialize($this->settings_library->get('favicon', $this->online_booking->accountID));
$favicon = 'public/images/favicon.png';
if ($favicon_data !== FALSE) {
	$favicon = 'attachment/setting/favicon/' . $this->online_booking->accountID;
}
?>

<meta charset="utf-8">
<meta content='width=device-width, initial-scale=1' name='viewport'>
<title><?php if (isset($title)) { echo $title . ' | '; } echo $this->online_booking->account->company; ?></title>
<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/online-booking.css'); ?>" />
<link rel="icon" type="image/png" href="<?php echo $this->crm_library->asset_url($favicon); ?>">
<link rel="shortcut icon" href="<?php echo $this->crm_library->asset_url($favicon); ?>">
<link rel="apple-touch-icon" href="<?php echo $this->crm_library->asset_url($favicon); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo $this->online_booking->account->company; ?>">
<meta name="application-name" content="<?php echo $this->online_booking->account->company; ?>">
<meta name="msapplication-TileImage" content="<?php echo $this->crm_library->asset_url($favicon); ?>">
<meta name="msapplication-TileColor" content="#FFFFFF">
<meta name="theme-color" content="#FFFFFF">
<?php
$custom_css = $this->settings_library->get('online_booking_css', $this->online_booking->accountID);
if (!empty($custom_css)) {
	$custom_css = trim(strip_tags($custom_css));
	$minifier = new MatthiasMullie\Minify\CSS($custom_css);
	$custom_css = $minifier->minify();
	echo '<style>' . $custom_css . '</style>';
}
$custom_meta = $this->settings_library->get('online_booking_meta', $this->online_booking->accountID);
if (!empty($custom_meta)) {
	echo $custom_meta;
}
?>
<!-- Begin Upscope Code -->
<script>
(function(w, u, d){var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};var l = function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://code.upscope.io/G4rWT5ZXrr.js';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(typeof u!=="function"){w.Upscope=i;l();}})(window, window.Upscope, document);
Upscope('init');
</script>
<script>
Upscope('updateConnection', {
	<?php
	if ($this->online_booking->user !== FALSE) {
		?>uniqueId: '<?php echo $this->online_booking->user->contactID; ?>',
		identities: <?php echo json_encode([$this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name, $this->online_booking->user->email]); ?><?php
	} else {
		?>uniqueId: undefined<?php
	}
	?>
});
</script>
<!-- End Upscope Code -->
