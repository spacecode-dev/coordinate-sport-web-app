<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);

	$this->load->view('staff/availability-tabs.php', $data);
}
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		echo form_hidden(array('process' => 1)); ?>
			<div class="card-body"><p>Exceptions to availability, e.g. holidays, appointments, etc can be added on the <?php echo anchor('staff/availability/' . $staffID . '/exceptions', 'availability exceptions', 'class="btn btn-sm btn-primary"'); ?> page.</p></div>
		<?php
		for ($week=1; $week <= $weeks; $week++) {
			if ($weeks > 1) {
				?><div class="card-body"><h3>Week <?php echo $week; ?></h3></div><?php
			}
			?>
			<div class='table-responsive'>
				<table class='availability table table-striped table-bordered'>
					<thead>
						<tr>
							<th scope="col">Time</th>
							<th scope="col" data-position="1" data-status="0" class="chk-all text-primary pointer">Mon</th>
							<th scope="col" data-position="2" data-status="0" class="chk-all text-primary pointer">Tue</th>
							<th scope="col" data-position="3" data-status="0" class="chk-all text-primary pointer">Wed</th>
							<th scope="col" data-position="4" data-status="0" class="chk-all text-primary pointer">Thu</th>
							<th scope="col" data-position="5" data-status="0" class="chk-all text-primary pointer">Fri</th>
							<th scope="col" data-position="6" data-status="0" class="chk-all text-primary pointer">Sat</th>
							<th scope="col" data-position="7" data-status="0" class="chk-all text-primary pointer">Sun</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($available_hours as $start) {
							// if in half hour
							if (substr($start, 3, 2) == "30") {
								// set end to next hour
								$end = sprintf("%02d", substr($start, 0, 2)+1).":00";
							} else {
								// else set to next half hour
								$end = (substr($start, 0, 2)).":30";
							}
							if($end == "24:00"){
								$end = "23:59";
							}
							?><tr>
								<td><?php echo $start . " to " . $end; ?></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][0][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][0][$start]) && $availability_info[$week][0][$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][1][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][1][$start]) && $availability_info[$week][1][$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][2][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][2][$start]) && $availability_info[$week][2][$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][3][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][3][$start]) && $availability_info[$week][3] [$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][4][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][4][$start]) && $availability_info[$week][4][$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][5][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][5][$start]) && $availability_info[$week][5][$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
								<td><input type="checkbox" name="availability_info[<?php echo $week; ?>][6][<?php echo $start; ?>]" class="auto" value="1"<?php if (isset($availability_info[$week][6][$start]) && $availability_info[$week][6][$start] == 1) { echo " checked=\"checked\""; } ?> /></td>
							</tr><?php
						}
						?>
					</tbody>
				</table>
			</div>
			<div class="card-body">
				<p>Select: <a href="#" class="selectall">All</a> | <a href="#" class="selectmon">Mon</a> | <a href="#" class="selecttue">Tue</a> | <a href="#" class="selectwed">Wed</a> | <a href="#" class="selectthu">Thu</a> | <a href="#" class="selectfri">Fri</a> | <a href="#" class="selectsat">Sat</a> | <a href="#" class="selectsun">Sun</a> | Unselect: <a href="#" class="unselectall">All</a> | <a href="#" class="unselectmon">Mon</a> | <a href="#" class="unselecttue">Tue</a> | <a href="#" class="unselectwed">Wed</a> | <a href="#" class="unselectthu">Thu</a> | <a href="#" class="unselectfri">Fri</a> | <a href="#" class="unselectsat">Sat</a> | <a href="#" class="unselectsun">Sun</a></p>
			</div>
			<?php
		}
		?>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
	<?php
echo form_close();
