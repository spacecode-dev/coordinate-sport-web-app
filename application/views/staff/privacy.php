<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}

if ($staff_info->privacy_agreed == 1) {
	?><div class="alert alert-success">
		Staff member last agreed to the privacy policy on <?php echo mysql_to_uk_datetime($staff_info->privacy_agreed_date); ?>
	</div><?php
} else {
	?><div class="alert alert-info">
		Staff member has not yet agreed to the privacy policy
	</div><?php
}
if ($logs->num_rows() > 0) {
	?><h3>History</h3>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Summary
						</th>
						<th>
							Details
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($logs->result() as $row) {
						?>
						<tr>
							<td>
								<?php echo mysql_to_uk_datetime($row->added); ?>
							</td>
							<td>
								<?php echo $row->summary; ?>
							</td>
							<td>
								<?php echo nl2br($row->content); ?>
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
