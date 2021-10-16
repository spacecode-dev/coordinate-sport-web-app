<?php
if ($mode == 'confirm') {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
}
?>
<p>Please read and update your details.</p>
<?php
echo form_open();
	display_messages('fas');
	$data = array(
		'marketing_consent' => $marketing_consent,
		'existing_newsletters' => $existing_newsletters,
		'source' => $source,
		'source_other' => $source_other
	);
	$this->load->view('online-booking/account/partials/consent', $data);
	?>
	<button class='btn'>Update</button>
<?php echo form_close(); ?>
