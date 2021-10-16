<?php
display_messages();
if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
?>
<div class='card card-custom'>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
			<h3 class="card-label">Marketing &amp; Privacy</h3>
		</div>
	</div>
	<?php
	if ($contacts->num_rows() == 0) {
		?>
		<div class="card-body">
			<div class="alert alert-info">
				No contacts found.
			</div>
		</div>
		<?php
	} else {
		?><div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Marketing Consent
						</th>
						<th>
							Privacy Policy
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($contacts->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php
								echo anchor('participants/privacy/edit/' . $row->contactID, trim(ucwords($row->title) . ' ' . $row->first_name . ' ' . $row->last_name));
								?>
							</td>
							<td>
								<?php
								if (empty($row->marketing_consent_date)) {
									echo '<em>Not asked</em>';
								} else {
									if ($row->marketing_consent == 1) {
										echo 'Yes';
									} else {
										echo 'No';
									}
									echo ', ' . mysql_to_uk_datetime($row->marketing_consent_date);
								}
								?>
							</td>
							<td>
								<?php
								if (empty($row->privacy_agreed_date)) {
									echo '<em>Not asked</em>';
								} else {
									if ($row->privacy_agreed == 1) {
										echo 'Yes, ' . mysql_to_uk_datetime($row->privacy_agreed_date);
									} else {
										echo 'No';
									}
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('participants/privacy/edit/' . $row->contactID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
								</div>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}
	?>
</div>
<div class='card card-custom'>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
			<h3 class="card-label">Participant Referral Data</h3>
		</div>
	</div>
	<?php
	if ($contacts->num_rows() == 0) {
		?>
		<div class="card-body">
			<div class="alert alert-info">
				No contacts found.
			</div>
		</div>
		<?php
	} else {
		?>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Source
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($contacts->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php
								echo trim(ucwords($row->title) . ' ' . $row->first_name . ' ' . $row->last_name);
								?>
							</td>
							<td>
								<?php
								if (strtolower($row->source) == 'other' && !empty($row->source_other)) {
						            echo $row->source_other;
						        } else if (!empty($row->source)) {
						            echo $row->source;
						        } else {
						            echo 'Unknown';
						        }
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('participants/privacy/edit/' . $row->contactID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
								</div>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}
	?>
</div>
<?php
if ($logs->num_rows() > 0) {
	?><div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">History</h3>
			</div>
		</div>
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
