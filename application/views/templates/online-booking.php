<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('templates/online-booking/meta'); ?>
</head>
<body class="<?php if (isset($body_class)) { echo $body_class; } if (isset($lightbox) && $lightbox == TRUE) { echo ' lightbox'; } ?>">
	<?php
	if (!isset($lightbox) || $lightbox != TRUE) {
		$this->load->view('templates/online-booking/header');
	}
	echo $content;
	if (!isset($lightbox) || $lightbox != TRUE) {
		$this->load->view('templates/online-booking/footer');
	}
	$this->load->view('templates/online-booking/footer-meta');
	?>
</body>
</html>
