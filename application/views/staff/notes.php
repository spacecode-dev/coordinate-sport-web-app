<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
if ($this->auth->user->department == 'office') {
	?><div class="alert alert-info">
		<p>You can add development notes in this area by clicking on <?php echo anchor('staff/notes/' . $staffID . '/new', 'Create New'); ?>, however you will not be able to see previously added development notes or edit them in this area unless you have a higher access level.</p>
	</div><?php
} else {
	$form_classes = 'card card-custom card-search';
	if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
	echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
		<div class="card-header">
			<div class="card-title">
				<h3 class="card-label">Search</h3>
			</div>
			<div class="card-toolbar">
				<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_date_from">Date From</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_date_from',
						'id' => 'field_date_from',
						'class' => 'form-control datepicker',
						'value' => $search_fields['date_from']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_date_to">Date To</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_date_to',
						'id' => 'field_date_to',
						'class' => 'form-control datepicker',
						'value' => $search_fields['date_to']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_summary">Summary</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_summary',
						'id' => 'field_summary',
						'class' => 'form-control',
						'value' => $search_fields['summary']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_content">Content</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_content',
						'id' => 'field_content',
						'class' => 'form-control',
						'value' => $search_fields['content']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_type">Type</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'appraisal' => 'Appraisal',
						'disciplinary' => 'Disciplinary',
						'feedbacknegative' => '<span style=\"color:#F33;\">Feedback (Negative)</span>',
						'feedbackpositive' => '<span style=\"color:#060;\">Feedback (Positive)</span>',
						'induction' => 'Induction',
						'late' => 'Late',
						'misc' => 'Miscellaneous ',
						'observation' => 'Observation',
						'payroll' => 'Payroll',
						'pupilassessment' => 'Pupil Assessment',
					);
					echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
					?>
				</div>
			</div>
		</div>
		<div class='card-footer'>
			<div class="d-flex justify-content-between">
				<button class='btn btn-primary btn-submit' type="submit">
					<i class='far fa-search'></i> Search
				</button>
				<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
					Cancel
				</a>
			</div>
		</div>
		<?php echo form_hidden('search', 'true'); ?>
	<?php echo form_close(); ?>
	<div id="results"></div>
	<?php
	if ($notes->num_rows() == 0) {
		?>
		<div class="alert alert-info">
			No items found. Do you want to <?php echo anchor('staff/notes/'.$staffID.'/new/', 'create one'); ?>?
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
								Date
							</th>
							<th>
								Summary
							</th>
							<th>
								Type
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($notes->result() as $row) {
							?>
							<tr>
								<td>
									<?php echo mysql_to_uk_date($row->date); ?>
								</td>
								<td class="name">
									<?php echo anchor('staff/notes/edit/' . $row->noteID, $row->summary); ?>
								</td>
								<td>
									<?php
									switch ($row->type) {
										case "feedbackpositive":
											echo "<span style=\"color:#060;\">Feedback (Positive)</span>";
											break;
										case "feedbacknegative":
											echo "<span style=\"color:#F33;\">Feedback (Negative)</span>";
											break;
										case "pupilassessment":
											echo "Pupil Assessment";
											break;
										case "misc":
											echo "Miscellaneous";
											break;
										default:
											echo ucwords($row->type);
											if ($row->type == 'observation' && !empty($row->observation_score)) {
												echo ' ('  . $row->observation_score . '%)';
											}
											break;
									}
									?>
								</td>
								<td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('staff/notes/edit/' . $row->noteID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/notes/remove/' . $row->noteID); ?>' title="Remove">
											<i class='far fa-trash'></i>
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
		</div>
		<?php
		echo $this->pagination_library->display($page_base);
	}
}
