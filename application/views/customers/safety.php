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
					<strong><label for="field_addressID">Address</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($addresses->num_rows() > 0) {
					foreach ($addresses->result() as $row) {
						$options[$row->addressID] = $row->address1 . ', ' . $row->postcode;
					}
				}
				echo form_dropdown('search_addressID', $options, $search_fields['addressID'], 'id="field_addressID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'camp induction' => 'Event/Project Induction',
					'school induction' => 'School Induction',
					'risk assessment' => 'Risk Assessment'
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_expired">Expired</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_expired', $options, $search_fields['expired'], 'id="field_expired" class="select2 form-control"');
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
	<a class="btn btn-sm btn-success" href="<?php echo site_url('customers/safety/camp/' . $org_id . '/new'); ?>">
		<i class="far fa-plus"></i> Event/Project Induction
	</a>
	<a class="btn btn-sm btn-success" href="<?php echo site_url('customers/safety/school/' . $org_id . '/new'); ?>">
		<i class="far fa-plus"></i> School Induction</a>
	<a class="btn btn-sm btn-success" href="<?php echo site_url('customers/safety/risk/' . $org_id . '/new'); ?>">
		<i class="far fa-plus"></i> Risk Assessment
	</a>
</div>
<?php
if ($documents->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No documents found.
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
							Address
						</th>
						<th>
							Type
						</th>
						<th>
							Expiry
						</th>
						<th class="min">
							Renewed
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($documents->result() as $row) {
						$row->details = @$this->crm_library->mb_unserialize($row->details);
						// if no or corrupt details, set as empty array
						if (!is_array($row->details)) {
							$row->details = array();
						}

						// get edit path
						$edit_path = NULL;
						switch ($row->type) {
							case 'school induction':
								$edit_path = 'school';
								break;
							case 'camp induction':
								$edit_path = 'camp';
								break;
							case 'risk assessment':
								$edit_path = 'risk';
								break;
						}
						?>
						<tr>
							<td class="name">
								<?php echo anchor('customers/safety/' . $edit_path . '/' . $row->docID, mysql_to_uk_date($row->date)); ?>
							</td>
							<td>
								<?php
								$addresses = array();
								if (!empty($row->address1)) {
									$addresses[] = $row->address1;
								}
								if (!empty($row->address2)) {
									$addresses[] = $row->address2;
								}
								if (!empty($row->address3)) {
									$addresses[] = $row->address3;
								}
								if (!empty($row->town)) {
									$addresses[] = $row->town;
								}
								if (!empty($row->county)) {
									$addresses[] = $row->county;
								}
								if (!empty($row->postcode)) {
									$addresses[] = $row->postcode;
								}

								echo implode(", ", $addresses);

								if (array_key_exists("location", $row->details) && !empty($row->details['location'])) {
									echo " (" . $row->details['location'] . ")";
								}
								?>
							</td>
							<td>
								<?php
								if ($row->type == 'camp induction')  {
									echo 'Event/Project Induction';
								} else {
									echo ucwords($row->type);
								}
								if (!empty($row->lesson_type)) {
									echo ' (' . $row->lesson_type . ')';
								}
								?>
							</td>
							<td>
								<?php
								if (strtotime($row->expiry) <= time()) {
									echo "<span style=\"color:red;\">";
								}
								echo mysql_to_uk_date($row->expiry);
								if (strtotime($row->expiry) <= time()) {
									echo "</span>";
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->renewed == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('customers/safety/renewed/' . $row->docID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('customers/safety/renewed/' . $row->docID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-success btn-sm confirm-duplicate' href='<?php echo site_url('customers/safety/duplicate/' . $row->docID); ?>' title="Duplicate">
										<i class='far fa-copy'></i>
									</a>
									<a class='btn btn-primary btn-sm' href='<?php echo site_url('customers/safety/view/' . $row->docID); ?>' target="_blank" title="Print">
										<i class='far fa-print'></i>
									</a>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('customers/safety/' . $edit_path . '/' . $row->docID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('customers/safety/remove/' . $row->docID); ?>' title="Remove">
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
