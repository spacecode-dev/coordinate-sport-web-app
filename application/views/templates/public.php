<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('templates/public/meta'); ?>
</head>
<body class="<?php if (isset($body_class)) { echo ' ' . $body_class; } ?>">
	<?php
	$this->load->view('templates/public/header');
	echo $content;
	$this->load->view('templates/public/footer');
	?>
</body>
</html>
