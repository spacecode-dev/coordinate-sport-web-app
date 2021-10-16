<div class="noprint">
	<h2 id="confirmation">Confirmation</h2>
	<?php
	if ($confirmed->num_rows() == 0) {
		?><p>Please click the button below to confirm you have read the above document. If you believe you have already read it, it may have been updated since.</p>
		<?php echo form_open('customers/safety/confirm/' . $docID); ?>
			<p><input type="submit" name="confirm" value="Confirm" />
		<?php echo form_close();
	} else {
		foreach ($confirmed->result() as $row) {
			?><p>You confirmed you read this document on <?php echo mysql_to_uk_date($row->date); ?>.</p><?php
		}
	}
	?>
</div>