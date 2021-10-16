<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);

	$data['tab'] = "add-qualifications";
	$this->load->view('staff/qualifications-tabs.php', $data);
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
					<strong><label for="field_level">Level</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_level',
					'id' => 'field_level',
					'class' => 'form-control',
					'value' => $search_fields['level']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_ref">Qualification No.</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_ref',
					'id' => 'field_ref',
					'class' => 'form-control',
					'value' => $search_fields['ref']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_issue_from">Issue Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_issue_from',
					'id' => 'field_issue_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['issue_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_issue_to">Issue Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_issue_to',
					'id' => 'field_issue_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['issue_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_expiry_from">Expiry Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_expiry_from',
					'id' => 'field_expiry_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['expiry_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_expiry_to">Expiry Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_expiry_to',
					'id' => 'field_expiry_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['expiry_to']
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
<?php echo form_close(); ?>
<div id="results"></div>
<?php
if ($quals->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No qualifications found. Do you want to <?php echo anchor('staff/quals/'.$staffID.'/new/', 'create one'); ?>?
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
							Level
						</th>
						<th>
							Qualification No.
						</th>
						<th>
							Issue Date
						</th>
						<th>
							Expiry Date
						</th>
	                    <th>
	                        Attachments
	                    </th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($quals->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('staff/quals/edit/' . $row->qualID, $row->name); ?>
							</td>
							<td>
								<?php echo $row->level; ?>
							</td>
							<td>
								<?php echo $row->reference; ?>
							</td>
							<td>
								<?php
								if (!empty($row->issue_date)) {
									echo mysql_to_uk_date($row->issue_date);
								}
								?>
							</td>
							<td>
								<?php
								if (!empty($row->expiry_date)) {
									echo mysql_to_uk_date($row->expiry_date);
								}
								?>
							</td>
	                        <td class="attachment-name">
	                            <?php
	                                if (isset($attachments[$row->qualID])) {
	                                    echo anchor('attachment/staff/' . $attachments[$row->qualID]->path, $attachments[$row->qualID]->name, 'target="_blank"');
	                                }
	                            ?>
	                        </td>
							<td>
								<div class='text-right'>
	                                <?php
	                                if (isset($attachments[$row->qualID])) { ?>
	                                    <a class='btn btn-danger btn-sm confirm-delete remove-attachment' href='<?php echo site_url('staff/attachments/remove/' . $attachments[$row->qualID]->attachmentID . '/additional_quals') ?>' title="Remove Attachment">
	                                        <i class='far fa-times'></i>
	                                    </a>
	                                <?php }
	                                ?>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('staff/quals/edit/' . $row->qualID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/quals/remove/' . $row->qualID); ?>' title="Remove">
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
