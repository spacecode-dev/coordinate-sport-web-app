<?php
display_messages();
if ($org_id != NULL) {
	$data = array(
		'orgID' => $org_id,
		'tab' => $tab
	);
	$this->load->view('customers/tabs.php', $data);
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
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'main' => 'Main',
					'delivery' => 'Delivery',
					'billing' => 'Billing',
					'other' => 'Other'
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_address">Address</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_address',
					'id' => 'field_address',
					'class' => 'form-control',
					'value' => $search_fields['address']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_town">Town</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_town',
					'id' => 'field_town',
					'class' => 'form-control',
					'value' => $search_fields['town']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_county"><?php echo localise('county'); ?></label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_county',
					'id' => 'field_county',
					'class' => 'form-control',
					'value' => $search_fields['county']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_postcode">Post Code</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_postcode',
					'id' => 'field_postcode',
					'class' => 'form-control',
					'value' => $search_fields['postcode']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_phone">Phone</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_phone',
					'id' => 'field_phone',
					'class' => 'form-control',
					'value' => $search_fields['phone']
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
	<div class="slide-out-btn text-right mb-4 d-none">
		<a class="btn btn-sm btn-success" href="<?php echo site_url('customers/addresses/'.$org_id.'/new/');?>"><i class="far fa-plus"></i> Create New</a>
	</div>
<?php
if ($addresses->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No addresses found. Do you want to <?php echo anchor('customers/addresses/'.$org_id.'/new/', 'create one'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class="fixed-scrollbar addresses"></div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Type
						</th>
						<th>
							Address
						</th>
						<th>
							Town
						</th>
						<th>
							<?php echo localise('county'); ?>
						</th>
						<th>
							Post Code
						</th>
						<th>
							Phone
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
							<td class="name">
								<?php
								$addresses_array = array();
								if (!empty($row->address1)) {
									$addresses_array[] = $row->address1;
								}
								if (!empty($row->address2)) {
									$addresses_array[] = $row->address2;
								}
								if (!empty($row->address3)) {
									$addresses_array[] = $row->address3;
								}
								if (count($addresses_array) > 0) {
									echo anchor('customers/addresses/edit/' . $row->addressID, implode(", ", $addresses_array));
								}
								?>
							</td>
							<td>
								<?php echo $row->town; ?>
							</td>
							<td>
								<?php echo $row->county; ?>
							</td>
							<td>
								<?php
								if (!empty($row->postcode)) {
									echo anchor('https://maps.google.co.uk/maps?t=m&f=d&saddr=Current+Location&daddr=' . urlencode($row->postcode), $row->postcode, 'target="_blank"');
								}
								?>
							</td>
							<td>
								<?php echo $row->phone; ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('customers/addresses/edit/' . $row->addressID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<?php
									if ($row->type != 'main') {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('customers/addresses/remove/' . $row->addressID); ?>' title="Remove">
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
