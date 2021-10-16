<?php display_messages('fas'); ?>
<p><a href="<?php echo site_url('account/participants/new'); ?>" class="btn lightbox">Add New</a></p>
<?php
if ($children->num_rows() > 0) {
	?><div class="table-responsive">
  		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Pickup PIN</th>
					<th scope="col">Age</th>
					<th scope="col">School</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($children->result() as $row) {
					?><tr>
						<td><?php echo anchor('account/participants/' . $row->childID, $row->first_name .  ' ' . $row->last_name, 'class="lightbox"'); ?></td>
						<td> <?php echo ($row->pin != 0)?$row->pin:'' ?>
						<td><?php echo calculate_age($row->dob); ?></td>
						<td><?php echo $row->school; ?></td>
					</tr><?php
				}
				?>
			</tbody>
		</table>
	</div><?php
}
?>
<script>
	function lightbox_callback(new_participant) {
		$.magnificPopup.close();
		location.reload();
	}
</script>
