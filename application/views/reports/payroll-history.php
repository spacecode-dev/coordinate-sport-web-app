<?php
/**
 * @var $payroll_data CI_DB_result
 * @var $staff CI_DB_result
 * @var $quals array
 * @var $staff_list CI_DB_result
 * @var $payroll_data array
 * @var $search_fields array
 * @var $page_base string
 */

display_messages();
if (in_array($this->auth->user->department, array('directors', 'management'))) {
	$this->load->view('reports/payroll-tabs.php', [
		'tab' => $tab
	]);
}

$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'method' => 'get', 'id' => 'payroll-history-report-search']); ?>
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
					<strong><label for="field_staff_id">Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if (count($staff_list)) {
					foreach ($staff_list as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
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
if (count($history_data) == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?>

	<?php echo $this->pagination_library->display_get($page_base); ?>

	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
				<tr>
					<th class="col-sm-4">Staff</th>
					<th class="col-sm-4">Date Generated</th>
					<th class="col-sm-4">Date From - Date To Selected</th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ($history_data as $history) { ?>
						<tr>
							<td><?= $history->first . ' ' . $history->surname ?></td>
							<td><?= date('d/m/Y H:i', $history->added) ?></td>
							<td><?= $history->decoded_data['date_from'] . ' - ' . $history->decoded_data['date_to'] ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<?php echo $this->pagination_library->display_get($page_base); ?>
	<?php
}
