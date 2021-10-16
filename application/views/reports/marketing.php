<?php
display_messages();

$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'marketing-report-search']); ?>
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
		<?php
		$data = array(
			'name' => 'marketing_order',
			'type' => 'hidden',
			'id' => 'marketing_order_field',
			'class' => 'form-control',
			'value' => $search_fields['marketing_order'] == 'asc' ? 'desc' : 'asc'
		);
		echo form_input($data);
		?>
		<div class='row'>
			
			<div class="col-sm-2">
				<p>
					<strong><label for="field_name">Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_name',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => $search_fields['name']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_marketing_consent">Marketing Consent</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_marketing_consent', $options, $search_fields['marketing_consent'], 'id="field_marketing_consent" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_privacy_policy">Privacy Policy</label></strong>
				</p>
				<?php echo form_dropdown('search_privacy_policy', $options, $search_fields['privacy_policy'], 'id="field__privacy_policy" class="select2 form-control"'); ?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_newsletters">Newsletters</label></strong>
				</p>
				<?php echo form_dropdown('search_newsletters', $newsletters_options, $search_fields['newsletters'], 'id="field_newsletters" class="select2 form-control"'); ?>
			</div>

			<div class='col-sm-2'>
				<p>
					<strong><label for="search_referral_data">Referral Data</label></strong>
				</p>
				<?php echo form_dropdown('search_referral_data', $options, $search_fields['referral_data'], 'id="field_referral_data" class="select2 form-control"'); ?>
			</div>

			<div class='col-sm-2'>
				<p>
					<strong><label for="search_activities">Activity Type</label></strong>
				</p>
				<?php echo form_dropdown('search_activities', $activities_options, $search_fields['activities'], 'id="marketing_report_field_activities" class="select2 form-control"'); ?>
			</div>

		</div>
		<div class="row">
			<div class="col-sm-2">
				<p>
					<strong><label for="search_age_from">Age from</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_age_from',
					'id' => 'field_age_from',
					'class' => 'form-control',
					'value' => $search_fields['age_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class="col-sm-2">
				<p>
					<strong><label for="search_age_to">Age to</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_age_to',
					'id' => 'field_age_to',
					'class' => 'form-control',
					'value' => $search_fields['age_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_from',
					'id' => 'marketing_report_field_date_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_date_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_to',
					'id' => 'marketing_report_field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>

			<div class='col-sm-2'>
				<p>
					<strong><label for="search_lessons">Session Type</label></strong>
				</p>
				<?php echo form_dropdown('search_lessons', $lessons_options, $search_fields['lessons'], 'id="marketing_report_field_lessons" class="select2 form-control"'); ?>
			</div>

			<div class="col-sm-2<?php echo ($search_fields['date_to'] || $search_fields['date_from']) ?'':' hidden';?>">
				<p>
					<strong><label for="search_postcode">Postcode</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_postcode',
					'id' => 'marketing_report_field_postcode',
					'class' => 'form-control',
					'value' => $search_fields['postcode']
				);
				echo form_input($data);
				?>
			</div>

			<div class='col-sm-2'>
				<p>
					<strong><label for="search_departments">Department</label></strong>
				</p>
				<?php echo form_dropdown('search_departments', $departments_options, $search_fields['departments'], 'id="marketing_report_field_departments" class="select2 form-control"'); ?>
			</div>

			<div class='col-sm-2'>
				<p>
					<strong><label for="search_schoolID">School</label></strong>
				</p>
				<?php echo form_dropdown('search_schoolID', $schools_options, $search_fields['schoolID'], 'id="search_schoolID" class="select2 form-control"'); ?>
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
if ($row_data->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No data found.
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
					<?php if ($form_submitted) { ?>
						<a id="marketing_order" style="color:#464E5F; cursor: pointer">Name</a>
						<?php
						switch ($search_fields['marketing_order']) {
							case 'asc':
								?> <i class="far fa-angle-up" style="float: right;"></i> <?php
								break;
							default:
								?> <i class="far fa-angle-down" style="float: right;"></i> <?php
								break;
						}
						?>
					<?php } else { 
							$url = parse_url($_SERVER['REQUEST_URI']);
							if (isset($_GET['order']['name'])) {
								switch ($_GET['order']['name']) {
									case 'asc':
										echo anchor($url['path'] . '?order[name]=desc', 'Name', 'Style="color:#464E5F"');
										?> <i class="far fa-angle-up" style="float: right;"></i> <?php
										break;
									default:
										echo anchor($url['path'] . '?order[name]=asc', 'Name', 'Style="color:#464E5F"');
										?> <i class="far fa-angle-down" style="float: right;"></i> <?php
										break;
								}
							} else {
								echo anchor($url['path'] . '?order[name]=desc', 'Name', 'Style="color:#464E5F"');
								?> <i class="far fa-angle-up" style="float: right;"></i> <?php
							}
					} ?>
					</th>
					<?php
					foreach (['Marketing Consent', 'Privacy Policy', 'Newsletters', 'Referral Data', 'Email', 'Mobile'] as $label) {
						?>
						<th><?php echo $label; ?></th><?php
					}
					?>
				</tr>
				</thead>
				<tbody>
				<?php
				$column_totals = array();
				foreach ($row_data->result() as $row) {
					// filter by age, if filter is set
					if (!is_null($search_fields['age_from']) || !is_null($search_fields['age_to'])) {
						$from = is_null($search_fields['age_from']) ? 0 : $search_fields['age_from'];
						$to = is_null($search_fields['age_to']) ? 100 : $search_fields['age_to'];
						$show = false;
						if ($row->age >= $from && $row->age <= $to) {
							$show = true;
						}
						if (!$show) {
							foreach (explode(',', $row->children_age) as $childAge) {
								if ($childAge >= $from && $childAge <= $to) {
									$show = true;
									break;
								}
							}
						}
					}
					if (isset($show) && !$show) {
						continue;
					}
					$hours = 0;
					?>
					<tr>
					<td>
						<?php echo $row->first_name . ' ' . $row->last_name; ?>
					</td>
					<td>
						<?php
						echo $row->marketing_consent == 1 ? 'Yes' : 'No';
						echo ($row->marketing_consent_date && (!is_null($search_fields['date_from']) || !is_null($search_fields['date_to'])))
							? ' ' . (new DateTime($row->marketing_consent_date))->format('m/d/Y H:i:s') : '';
						?>
					</td>
					<td>
						<?php
						echo $row->privacy_agreed == 1 ? 'Yes' : 'No';
						echo $row->privacy_agreed_date ? ' ' . (new DateTime($row->privacy_agreed_date))->format('m/d/Y H:i:s') : '';
						?>
					</td>
					<td>
						<?php echo $row->newsletters ?: '-'; ?>
					</td>
					<td>
						<?php echo $row->source == 'Other' ? ($row->source_other ?: '-') : ($row->source ?: '-'); ?>
					</td>
					<td>
						<?php echo ($row->marketing_consent == 1) ? ($row->email ?: '-') : '-'; ?>
					</td>
					<td>
						<?php echo ($row->marketing_consent == 1)  ? ($row->mobile ?: '-') : '-'; ?>
					</td>
					</tr><?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
echo $this->pagination_library->display($page_base);
