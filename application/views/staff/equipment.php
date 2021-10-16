<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
?>
<div id="results"></div>
<?php
if ($equipment->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No bookings found. <?php if ($this->auth->user->department != 'coaching') { ?>Do you want to <?php	echo anchor('equipment/bookings/new', 'create one'); ?>?<?php } ?>
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Type
						</th>
						<th>
							Booked Out By
						</th>
						<th class="min">
							Quantity
						</th>
						<th>
							Date Out
						</th>
						<th>
							Expected Back
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($equipment->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php
								if ($this->auth->user->department == 'coaching') {
									echo $row->name;
								} else {
									echo anchor('equipment/bookings/edit/' . $row->bookingID, $row->name);
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->type) {
									case 'staff':
									default;
										echo ucwords($row->type);
										break;
									case 'org':
										echo $this->settings_library->get_label('customer');
										break;
									case 'contact':
										echo 'Parent/Contact';
										break;
									case 'child':
										echo 'Child';
										break;
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->type) {
									case 'staff':
										echo $row->staff_label;
										break;
									case 'org':
										echo $row->org_label;
										break;
									case 'contact':
										echo $row->contact_label;
										break;
									case 'child':
										echo $row->child_label;
										break;
								}
								?>
							</td>
							<td class="min">
								<?php echo $row->quantity; ?>
							</td>
							<td>
								<?php echo mysql_to_uk_datetime($row->dateOut); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_datetime($row->dateIn); ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
echo $this->pagination_library->display($page_base);
