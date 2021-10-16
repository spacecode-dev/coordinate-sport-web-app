<?php
display_messages();
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
					<strong><label for="field_is_active">Active</label></strong>
				</p>
				<?php
				$options = array(
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_is_active', $options, $search_fields['is_active'], 'id="field_is_active" class="select2 form-control"');
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
if ($sessiontypes->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No types found. Do you want to <?php echo anchor('settings/sessiontypes/new/', 'create one'); ?>?
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
							Name
						</th>
						<th>
							Active
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($sessiontypes->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php
								if ($this->auth->has_features('online_booking') && !empty($row->colour)) {
									echo anchor('settings/sessiontypes/edit/' . $row->typeID, $row->name, 'class="label label-inline" style="' . label_style($row->colour) . '"');
								} else {
									echo anchor('settings/sessiontypes/edit/' . $row->typeID, $row->name);
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if ($row->active == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('settings/sessiontypes/active/' . $row->typeID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('settings/sessiontypes/active/' . $row->typeID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<?php
									if ($this->auth->has_features('online_booking')) {
										$booking_link = $this->auth->get_bookings_site();
										if ($booking_link !== FALSE) {
											?><a href="<?php echo $booking_link . '/type/' . $row->typeID; ?>" class='btn btn-primary btn-sm' title="Online Booking" target="_blank">
												<i class='far fa-book'></i>
											</a> <?php
										}
									}
									?>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('settings/sessiontypes/edit/' . $row->typeID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('settings/sessiontypes/remove/' . $row->typeID); ?>' title="Remove">
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
