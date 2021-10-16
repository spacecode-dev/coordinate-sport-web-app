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
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'authorised' => 'Authorised Absence',
					'unauthorised' => 'Unauthorised Absence',
					'other' => 'Other',
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_reason">Reason</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_reason',
					'id' => 'field_reason',
					'class' => 'form-control',
					'value' => $search_fields['reason']
				);
				echo form_input($data);
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
if ($exceptions->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No exceptions found. Do you want to <?php echo anchor('staff/availability/'.$staffID.'/exceptions/new/', 'create one'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="exception_table">
				<thead>
					<tr>
						<th>
							Date From
						</th>
						<th>
							Date To
						</th>
						<th>
							Type
						</th>
						<th>
							Reason
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($exceptions->result() as $row) {
						?>
						<tr>
							<td>
								<?php echo anchor('staff/availability/' . $staffID . '/exceptions/edit/' . $row->exceptionsID, mysql_to_uk_datetime($row->from)); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_datetime($row->to); ?>
							</td>
							<td>
								<?php
								switch($row->type) {
									case 'authorised':
									case 'unauthorised':
										echo ucwords($row->type) . ' Absence';
										break;
									default:
										echo ucwords($row->type);
										break;
								};
								?>
							</td>
							<td class="name">
								<?php echo empty($row->reason)?$row->note:ucfirst($row->reason); ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('staff/availability/' . $staffID . '/exceptions/edit/' . $row->exceptionsID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/availability/' . $staffID . '/exceptions/remove/' . $row->exceptionsID); ?>' title="Remove">
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
?>
