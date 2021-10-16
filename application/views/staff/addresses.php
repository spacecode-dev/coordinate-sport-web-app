<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
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
					<strong><label for="field_relationship">Relationship</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_relationship',
					'id' => 'field_relationship',
					'class' => 'form-control',
					'value' => $search_fields['relationship']
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
					'main' => 'Main',
					'additional' => 'Additional',
					'emergency' => 'Emergency',
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
if ($addresses->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No addresses found. Do you want to <?php echo anchor('staff/addresses/'.$staffID.'/new/', 'create one'); ?>?
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
							Type
						</th>
						<th>
							Name (Relationship)
						</th>
						<th>
							Address
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($addresses->result() as $row) {
						?>
						<tr>
							<td>
								<?php echo ucwords($row->type);
								?>
							</td>
							<td>
								<?php
								if ($row->type == 'emergency') {
									echo $row->name . ' (' . $row->relationship . ')';
								} else {
									echo $staff_info->first . ' ' . $staff_info->surname . ' (Self)';
								}
								?>
							</td>
							<td class="name">
								<?php
								$addresses_array = array();
								if (!empty($row->address1)) {
									$addresses_array[] = $row->address1;
								}
								if (!empty($row->address2)) {
									$addresses_array[] = $row->address2;
								}
								if (!empty($row->town)) {
									$addresses_array[] = $row->town;
								}
								if (!empty($row->county)) {
									$addresses_array[] = $row->county;
								}
								if (!empty($row->postcode)) {
									$addresses_array[] = $row->postcode;
								}
								if (count($addresses_array) > 0) {
									echo anchor('staff/addresses/edit/' . $row->addressID, implode(", ", $addresses_array));
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('staff/addresses/edit/' . $row->addressID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<?php
									if ($row->type != 'main') {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/addresses/remove/' . $row->addressID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a><?php
									}
									?>
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
