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
					<strong><label for="field_note">Note</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_note',
					'id' => 'field_note',
					'class' => 'form-control',
					'value' => $search_fields['note']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_note">Blocks</label></strong>
				</p>
				<?php
				$blockID = NULL;
				if (isset($search_fields['search_blocks'])) {
					$blockID = $search_fields['search_blocks'];
				}
				$options = array(
					'' => 'Select'
				);
				foreach ($search_blocks->result() as $row) {
					$options[$row->blockID] = $row->name;
				}
				echo form_dropdown('search_blocks', $options, set_value('search_blocks', $this->crm_library->htmlspecialchars_decode($blockID), FALSE), 'id="search_note" class="form-control select2"');
				?></div>
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
if ($costs->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No costs found. Do you want to <?php echo anchor('bookings/costs/'.$bookingID.'/new', 'create one'); ?>?
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
							Block
						</th>
						<th>
							Item
						</th>
						<th>
							Category
						</th>
						<th>
							Amount
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($costs->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('bookings/costs/edit/' . $row->costID. '/' .$bookingID, mysql_to_uk_date($row->date)); ?>
							</td>
							<td>
								<?php echo anchor('bookings/blocks/edit/' . $row->blockID, $row->name); ?>
							</td>
							<td>
								<?php echo $row->note; ?>
							</td>
							<td>
								<?php echo $row->category; ?>
							</td>
							<td>
								<?php echo currency_symbol() . $row->amount; ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('bookings/costs/edit/' . $row->costID. '/' .$bookingID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('bookings/costs/remove/' . $row->costID. '/' .$bookingID); ?>' title="Remove">
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
