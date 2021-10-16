<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('templates/master/meta'); ?>
</head>
<body class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable<?php if (isset($body_class)) { echo ' ' . $body_class; } if (($this->auth->account_overridden === TRUE || $this->auth->user_overridden === TRUE) && $this->auth->account->status === 'trial') { echo ' has_2_top_bars'; } else if ($this->auth->account_overridden === TRUE || $this->auth->user_overridden === TRUE || $this->auth->account->status === 'trial') {  echo ' has_top_bar'; } ?>">
	<?php
	if (!isset($lightbox) || $lightbox != TRUE) {
		$this->load->view('templates/master/header');
	}
	echo $content;
	if (!isset($lightbox) || $lightbox != TRUE) {
		$this->load->view('templates/master/footer');
	}
	$this->load->view('templates/master/footer-meta');
	?>
</body>
</html>
