<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
$url = parse_url($_SERVER['REQUEST_URI']);
$orderUrl = '';

if (isset($url['query'])){
	$orderUrl = '?' . $url['query'];
}
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'search_form']); ?>
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
			'name' => 'start_date_order',
			'type' => 'hidden',
			'id' => 'start_date_order_field',
			'class' => 'form-control datepicker',
			'value' => $search_fields['start_date_order'] == 'desc' ? 'asc' : 'desc'
		);
		echo form_input($data);
		?>
		<div class='row'>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_start_from">Start From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_start_from',
					'id' => 'field_start_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['start_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_start_to">Start To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_start_to',
					'id' => 'field_start_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['start_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_end_from">End From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_end_from',
					'id' => 'field_end_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['end_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_end_to">End To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_end_to',
					'id' => 'field_end_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['end_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
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
if ($blocks->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No blocks found. Do you want to <?php echo anchor('bookings/blocks/'.$bookingID.'/new/', 'create one'); ?>?
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
						<?php if ($form_submitted) { ?>
							<th>
								<a id="start_date_order" style="color:#464E5F; cursor: pointer">Start Date</a>
								<?php
								switch ($search_fields['start_date_order']) {
									case 'asc':
										?> <i class="far fa-angle-up" style="float: right;"></i> <?php
										break;
									default:
										?> <i class="far fa-angle-down" style="float: right;"></i> <?php
										break;
								}
								?>
							</th>
						<?php } else { ?>
							<th>
							<?php if (isset($_GET['order']['startDate'])) {
								switch ($_GET['order']['startDate']) {
									case 'asc':
										echo anchor($url['path'] . '?order[startDate]=desc', 'Start Date', 'Style="color:#464E5F"');
										?> <i class="far fa-angle-up" style="float: right;"></i> <?php
										break;
									default:
										echo anchor($url['path'] . '?order[startDate]=asc', 'Start Date', 'Style="color:#464E5F"');
										?> <i class="far fa-angle-down" style="float: right;"></i> <?php
										break;
								}
							} else {
								echo anchor($url['path'] . '?order[startDate]=asc', 'Start Date', 'Style="color:#464E5F"');
								?> <i class="far fa-angle-down" style="float: right;"></i> <?php
							} }
							?>
						</th>
						<th>
							End Date
						</th>
						<th>
							Name
						</th>
						<th class="block-cell">
							Register
						</th>
						<th class="block-cell">
							Targets
						</th>
						<th class="block-cell">
							Duplicate
						</th>
						<th class="block-cell text-center">
							Edit
						</th>
						<th class="block-cell">
							Remove
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($blocks->result() as $row) {
						?>
						<tr class="<?php if ($row->provisional == 1) { echo 'striped-dark'; } ?>">
							<td>
								<?php echo mysql_to_uk_date($row->startDate); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->endDate); ?>
							</td>
							<td class="name">
								<?php
								echo anchor('bookings/blocks/edit/' . $row->blockID, $row->name);
								if (!empty($row->org) && !empty($row->orgID) && $row->orgID != $booking_info->orgID) {
									echo ' (' . $row->org . ')';
								}
								?>
							</td>
							<td class="has_icon block-cell center">
								<?php if ($this->auth->has_features('participants') && ($type == 'event' || $booking_info->project == 1)) {
									?><a class='btn btn-success btn-sm' href='<?php echo site_url('bookings/participants/' . $row->blockID); ?>' title="<?php echo $this->settings_library->get_label('participants'); ?>">
										<i class='far fa-user'></i>
									</a> <?php
								}else{
									echo '-';
								}?>
							</td>
							<td class="has_icon block-cell center">
								<?php
								if (empty($row->targets_missed)) {
									?><a class='btn btn-warning btn-sm' href="<?php echo site_url('bookings/sessions/' . $row->bookingID . '/' . $row->blockID); ?>" title="<?php echo $row->name; ?> (No Targets)">
										<i class='far fa-smile'></i>
									</a> <?php
								} else if ($row->targets_missed == 'none') {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('bookings/sessions/' . $row->bookingID . '/' . $row->blockID); ?>" title="<?php echo $row->name; ?>">
										<i class='far fa-smile'></i>
									</a> <?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('bookings/sessions/' . $row->bookingID . '/' . $row->blockID); ?>" title="<?php echo $row->name . ' (' . $row->targets_missed . ')'; ?>">
										<i class='far fa-frown'></i>
									</a> <?php
								}
								?>
							</td>
							<td class="has_icon block-cell center">
								<a class='btn btn-success btn-sm confirm-duplicate' href='<?php echo site_url('bookings/blocks/duplicate/' . $row->blockID); ?>' title="Duplicate">
									<i class='far fa-copy'></i>
								</a>
							</td>
							<td class="has_icon block-cell center">
								<a class='btn btn-warning btn-sm' href='<?php echo site_url('bookings/blocks/edit/' . $row->blockID); ?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</td>
							<td class="has_icon block-cell center">
								<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('bookings/blocks/remove/' . $row->blockID); ?>' title="Remove">
									<i class='far fa-trash'></i>
								</a>
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
