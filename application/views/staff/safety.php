<?php
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
?>
<h3>Unread Documents</h3>
<?php
if ($unread->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No documents found.
	</div>
	<?php
} else {
	?><div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Organisation/Address
						</th>
						<th>
							Type
						</th>
						<th>
							Expiry
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($unread->result() as $row) {
						$row->details = @unserialize($row->details);
						// if no or corrupt details, set as empty array
						if (!is_array($row->details)) {
							$row->details = array();
						}
						?>
						<tr>
							<td>
								<?php echo mysql_to_uk_date($row->date); ?>
							</td>
							<td class="name">
								<strong><?php echo $row->name; if ($row->outdated == 1) { echo ' (Updated)'; } ?></strong><br />
								<?php
								$addresses = array();
								if (!empty($row->address1)) {
									$addresses[] = $row->address1;
								}
								if (!empty($row->address2)) {
									$addresses[] = $row->address2;
								}
								if (!empty($row->address3)) {
									$addresses[] = $row->address3;
								}
								if (!empty($row->town)) {
									$addresses[] = $row->town;
								}
								if (!empty($row->county)) {
									$addresses[] = $row->county;
								}
								if (!empty($row->postcode)) {
									$addresses[] = $row->postcode;
								}
								if (count($addresses) > 0) {
									echo implode(", ", $addresses);
								}

								if (array_key_exists("location", $row->details) && !empty($row->details['location'])) {
									echo " (" . $row->details['location'] . ")";
								}
								?>
							</td>
							<td>
								<?php echo ucwords($row->type); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->expiry); ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div><?php
}
?>
<h3>Read Documents</h3>
<?php
if ($read->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No documents found.
	</div>
	<?php
} else {
	?><div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Read
						</th>
						<th>
							Date
						</th>
						<th>
							Organisation/Address
						</th>
						<th>
							Version
						</th>
						<th>
							Type
						</th>
						<th>
							Expiry
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($read->result() as $row) {
						$row->details = @unserialize($row->details);
						// if no or corrupt details, set as empty array
						if (!is_array($row->details)) {
							$row->details = array();
						}
						?>
						<tr>
							<td>
								<?php echo mysql_to_uk_datetime($row->read); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->date); ?>
							</td>
							<td class="name">
								<strong><?php echo $row->name; ?></strong><br />
								<?php
								$addresses = array();
								if (!empty($row->address1)) {
									$addresses[] = $row->address1;
								}
								if (!empty($row->address2)) {
									$addresses[] = $row->address2;
								}
								if (!empty($row->address3)) {
									$addresses[] = $row->address3;
								}
								if (!empty($row->town)) {
									$addresses[] = $row->town;
								}
								if (!empty($row->county)) {
									$addresses[] = $row->county;
								}
								if (!empty($row->postcode)) {
									$addresses[] = $row->postcode;
								}
								if (count($addresses) > 0) {
									echo implode(", ", $addresses);
								}

								if (array_key_exists("location", $row->details) && !empty($row->details['location'])) {
									echo " (" . $row->details['location'] . ")";
								}
								?>
							</td>
							<td>
								<?php
								if ($row->outdated == 1) {
									echo 'Outdated';
								} else {
									echo 'Latest';
								}
								?>
							</td>
							<td>
								<?php echo ucwords($row->type); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->expiry); ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div><?php
}
