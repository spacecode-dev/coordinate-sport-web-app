<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'bikeability-report-search']); ?>
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
					<strong><label for="field_type">Register Type</label></strong>
				</p>
				<?php
				$options = array(
					'children_individuals' => 'Children/Individuals',
					'names' => 'Names Only'
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
				?>
			</div>
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
			<div class='col-sm-2' style="display: none">
				<p>
					<strong><label for="field_org_id">School</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($orgs->num_rows() > 0) {
					foreach ($orgs->result() as $row) {
						$options[$row->orgID] = $row->name;
					}
				}
				echo form_dropdown('search_org_id', $options, $search_fields['org_id'], 'id="field_org_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2' style="display: none">
				<p>
					<strong><label for="field_brand_id">Department</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="field_brand_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_brand_id">Project Name</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($projects->num_rows() > 0) {
					foreach ($projects->result() as $row) {
						$options[$row->bookingID] = $row->name;
					}
				}
				echo form_dropdown('search_project_id', $options, $search_fields['project_id'], 'id="field_project_id" class="select2 form-control"');
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
<div id="bikeability_report"></div>
<?php
if (count($report_data) == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?><div class='card'>
		<div class="fixed-scrollbar"></div>
		<div class='table-responsive'>
			<?php
			switch ($search_fields['type']) {
				case 'children_individuals':
				default:
					?><table class='table table-striped table-bordered'>
						<thead>
							<tr>
								<th>
									Instructor
								</th>
								<th>
									Date
								</th>
								<th>
									Start Time
								</th>
								<th>
									End Time
								</th>
								<th>
									Session Type
								</th>
								<th>
									Trainee Name
								</th>
								<th>
									Trainee Email
								</th>
								<th>
									Trainee Postcode
								</th>
								<!--<th>
									Level at Start of Session
								</th>-->
								<th>
									Level at End of Session
								</th>
								<!--<th>
									Session Outcome
								</th>-->
								<th>
									Gender
								</th>
								<th>
									Age
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($report_data as $row) {
								?><tr>
									<td>
										<?php echo $row['instructor']; ?>
									</td>
									<td>
										<?php echo $row['date']; ?>
									</td>
									<td>
										<?php echo $row['startTime']; ?>
									</td>
									<td>
										<?php echo $row['endTime']; ?>
									</td>
									<td>
										<?php echo $row['session_type']; ?>
									</td>
									<td>
										<?php echo $row['trainee_name']; ?>
									</td>
									<td>
										<?php echo $row['trainee_email']; ?>
									</td>
									<td>
										<?php echo $row['trainee_postcode']; ?>
									</td>
									<!--<td>
										<?php echo $row['level_at_start']; ?>
									</td>-->
									<td>
										<?php echo $row['level_at_end']; ?>
									</td>
									<!--<td>
										<?php echo $row['lesson_outcome']; ?>
									</td>-->
									<td>
										<?php echo $row['gender']; ?>
									</td>
									<td>
										<?php echo $row['age']; ?>
									</td>
								</tr><?php
							}
							?>
						</tbody>
					</table><?php
					break;
			case 'names':
				?><table class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th rowspan="3">
								School
							</th>
							<th rowspan="3">
								Date(s)
							</th>
							<th rowspan="3">
								Block
							</th>
							<!--<th rowspan="3">
								Level Taught
							</th>-->
							<th colspan="5">
								Number of Girls
							</th>
							<th colspan="5">
								Number of Boys
							</th>
						</tr>
						<tr>
							<th rowspan="2">
								Participating
							</th>
							<th rowspan="2">
								SEND
							</th>
							<th colspan="3">
								Achieved Level
							</th>
							<th rowspan="2">
								Participating
							</th>
							<th rowspan="2">
								SEND
							</th>
							<th colspan="3">
								Achieved Level
							</th>
						</tr>
						<tr>
							<th>
								1
							</th>
							<th>
								2
							</th>
							<th>
								3
							</th>
							<th>
								1
							</th>
							<th>
								2
							</th>
							<th>
								3
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($report_data as $row) {
							?><tr>
								<td>
									<?php echo $row['school']; ?>
								</td>
								<td>
									<?php echo $row['dates']; ?>
								</td>
								<td>
									<?php echo $row['block']; ?>
								</td>
								<!--<td>
									<?php echo $row['level_taught']; ?>
								</td>-->
								<td>
									<?php echo $row['girls_participating']; ?>
								</td>
								<td>
									<?php echo $row['girls_send']; ?>
								</td>
								<td>
									<?php echo $row['girls_achieved_l1']; ?>
								</td>
								<td>
									<?php echo $row['girls_achieved_l2']; ?>
								</td>
								<td>
									<?php echo $row['girls_achieved_l3']; ?>
								</td>
								<td>
									<?php echo $row['boys_participating']; ?>
								</td>
								<td>
									<?php echo $row['boys_send']; ?>
								</td>
								<td>
									<?php echo $row['boys_achieved_l1']; ?>
								</td>
								<td>
									<?php echo $row['boys_achieved_l2']; ?>
								</td>
								<td>
									<?php echo $row['boys_achieved_l3']; ?>
								</td>
							</tr><?php
						}
						?>
					</tbody>
				</table><?php
				break;
			}
			?>
		</div>
	</div>
	<?php
}
