<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);

	$data['tab'] = "exception";
	$this->load->view('staff/availability-tabs.php', $data);
}
?>
<div id="results"></div>
<?php echo form_open_multipart($page_base); ?>
<div class='card card-custom'>
	<div class="card-body">
		<div class='row'>
			<div class='col-sm-3 form-group'>
			<?php
				echo form_label('Select for all lessons', 'staff_all');
				$options = array(
					'' => 'Select'
				);
				if ($staff->num_rows() > 0) {
					foreach ($staff->result() as $row) {
						$options[$row->staffID] = $row->first . ' ' . $row->surname;
					}
				}
				echo form_dropdown('staff_all', $options, NULL, 'id="staff_all" class="form-control select2"');
			?>
			</div>
		</div>
	
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="exception_table">
				<thead>
					<tr>
						<th>
							Lesson
						</th>
						<th>
							Date
						</th>
						<th>
							Replacement
						</th>
						<th>
							Assign to
						</th>
						<th>
							Reason
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					echo form_hidden(array('process' => 1));
					echo form_hidden(array('action' => $type));
					
					foreach ($lesson_list as $lessonID => $dates) {
						foreach($dates as $date => $row){
							// store lesson
							echo form_hidden(array('lessons[]' => $lessonID));
							
							$replacementID = NULL;
							$reason_select = NULL;
							$reason = NULL;
							$assign_to = NULL;
							if(isset($already_exceptions[$lessonID][$date])){
								$exception_info = $already_exceptions[$lessonID][$date];
								$replacementID = $exception_info->staffID;
								$reason_select = $exception_info->reason_select;
								$reason = $exception_info->reason;
								$assign_to = $exception_info->assign_to;
							}
							
							?>
							<tr class='exception_info lesson-staff' data-lesson='<?php echo $lessonID; ?>' data-date='<?php echo mysql_to_uk_date($date); ?>'>
								<td>	
									<?php
									$hidden_fields = array(
										'type['.$lessonID.']['.$date.']'  => $type,
										'hidden_reason_select['.$lessonID.']['.$date.']' => set_value('reason_select['.$lessonID.']['.$date.']', $this->crm_library->htmlspecialchars_decode($reason_select))
									);
									echo form_hidden($hidden_fields);
									?>
									
									<?php echo $row->project_name." > ".$row->block_name." > ".ucfirst($row->day) ." (".substr($row->lesson_start_time, 0, 5)." to ".substr($row->lesson_end_time, 0, 5).")"; ?>
								</td>
								<td>
									<?php echo mysql_to_uk_date($date); ?>
								</td>
								<td>
									<?php
										$options = array(
											'' => 'Select'
										);
										if ($staff->num_rows() > 0) {
											foreach ($staff->result() as $row) {
												$options[$row->staffID] = $row->first . ' ' . $row->surname;
											}
										}
										echo form_dropdown('staffID['.$lessonID.']['.$date.']', $options, set_value('staffID['.$lessonID.']['.$date.']', $this->crm_library->htmlspecialchars_decode($replacementID)), 'id="staffID_' . $lessonID .'_' . $date . '" class="form-control select2"');
						
									?>
								</td>
								<td class="name">
									<?php
									echo "<div class='form-group'>";
									$options = array(
										'' => 'Select',
										'staff' => 'Staff',
										'company' => 'Company',
										'customer' => 'Customer'
									);
									echo form_dropdown('assign_to['.$lessonID.']['.$date.']', $options, set_value('assign_to['.$lessonID.']['.$date.']', $this->crm_library->htmlspecialchars_decode($assign_to)), 'id="assign_to_' . $lessonID .'_' . $date . '" class="form-control select2"');
									echo "</div>";
									?>
								</td>
								<td>
									<?php
									echo "<div class='form-group'>";
									$options = array(
										'' => array(
											'name' => 'Select',
											'extras' => NULL
										),
										'authorised absence' => array(
											'name' => 'Authorised Absence',
											'extras' => 'data-assigned="staff"'
										),
										'unauthorised absence' => array(
											'name' => 'Unauthorised Absence',
											'extras' => 'data-assigned="staff"'
										),
										'sick' => array(
											'name' => 'Sick',
											'extras' => 'data-assigned="staff"'
										),
										'timetable conflict' => array(
											'name' => 'Timetable Conflict',
											'extras' => 'data-assigned="company"'
										),
										'other' => array(
											'name' => 'Other (Please specify)',
											'extras' => 'data-assigned="staff company customer"'
										)
									);
									echo form_dropdown_advanced('reason_select['.$lessonID.']['.$date.']', $options, set_value('reason_select['.$lessonID.']['.$date.']', $this->crm_library->htmlspecialchars_decode($reason_select)), 'id="reason_select_' . $lessonID .'_' . $date . '" class="form-control select2"');
									echo "</div>";
									echo "<div class='form-group'>";
									$data = array(
										'name' => 'reason['.$lessonID.']['.$date.']',
										'id' => 'reason_' . $lessonID .'_' . $date,
										'class' => 'form-control',
										'value' => set_value('reason['.$lessonID.']['.$date.']', $this->crm_library->htmlspecialchars_decode($reason)),
										'maxlength' => 255
									);
									echo form_input($data);
									echo "</div>";
									?>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class='form-actions d-flex justify-content-between'>
	<button class='btn btn-primary btn-submit' type="submit">
		<i class='far fa-save'></i> Save
	</button>
	<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
</div>
<?php	
echo form_close(); ?>