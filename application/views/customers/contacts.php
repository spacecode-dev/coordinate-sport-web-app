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
					<strong><label for="field_position">Position</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_position',
					'id' => 'field_position',
					'class' => 'form-control',
					'value' => $search_fields['position']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_tel">Phone</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_tel',
					'id' => 'field_tel',
					'class' => 'form-control',
					'value' => $search_fields['tel']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_mobile">Mobile</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_mobile',
					'id' => 'field_mobile',
					'class' => 'form-control',
					'value' => $search_fields['mobile']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_email">Email</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_email',
					'id' => 'field_email',
					'class' => 'form-control',
					'value' => $search_fields['email']
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
					'' => 'Select',
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
	<div class="slide-out-btn text-right mb-4 d-none">
		<a class="btn btn-success btn-sm" href="<?php echo site_url('customers/contacts/' . $org_id . '/new'); ?>"><i class="far fa-plus"></i> Create New</a>
	</div>
<?php
if ($contacts->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No contacts found. Do you want to <?php echo anchor('customers/contacts/'.$org_id.'/new/', 'create one'); ?>?
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
							Position
						</th>
						<th>
							Phone
						</th>
						<th>
							Email
						</th>
						<th>
							Main
						</th>
						<th>
							Active
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($contacts->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('customers/contacts/edit/' . $row->contactID, $row->name); ?>
							</td>
							<td>
								<?php echo $row->position; ?>
							</td>
							<td>
								<?php
								$phone_array = array();
								if (!empty($row->tel)) {
									$phone_array[] = $row->tel;
								}
								if (!empty($row->mobile)) {
									$phone_array[] = $row->mobile;
								}
								if (count($phone_array) > 0) {
									echo implode(", ", $phone_array);
								}
								?>
							</td>
							<td class="has_icon">
								<?php
								if (!empty($row->email)) {
									?><a href="mailto:<?php echo $row->email; ?>" class="btn btn-default btn-sm"><i class="far fa-envelope"></i></a><?php
								}
								?>
							</td>
							<td class="has_icon">
								<?php
								if($row->isMain == 1) {
									?><span class='btn btn-success btn-sm no-action' title="Yes">
										<i class='far fa-check'></i>
									</span><?php
								} else {
									?><a class='btn btn-danger btn-sm' href='<?php echo site_url('customers/contacts/main/' . $row->contactID); ?>' title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="center ajax_toggle">
								<?php
									if($row->active == 1) {
										?><a class='btn btn-success btn-sm' href="<?php echo site_url('customers/contacts/active/' . $row->contactID); ?>/0" title="Yes">
											<i class='far fa-check'></i>
										</a><?php
									} else {
										?><a class='btn btn-danger btn-sm' href="<?php echo site_url('customers/contacts/active/' . $row->contactID); ?>/1" title="No">
											<i class='far fa-times'></i>
										</a><?php
									}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm mt-1' href='<?php echo site_url('customers/contacts/edit/' . $row->contactID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<?php
									if ($row->isMain != 1) {
										?><a class='btn btn-danger btn-sm confirm-delete mt-1' href='<?php echo site_url('customers/contacts/remove/' . $row->contactID); ?>' title="Remove">
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
