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
			<?php
			if ($org_type == 'school') {
				?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_type">Type</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'private' => 'Private',
						'local' => 'Local Authority'
					);
					echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_school_type">School Type</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'infant' => 'Infant',
						'junior' => 'Junior',
						'primary' => 'Primary',
						'secondary' => 'Secondary',
						'college' => 'College',
						'special' => 'Special',
						'other' => 'Other'
					);
					echo form_dropdown('search_school_type', $options, $search_fields['school_type'], 'id="field_school_type" class="select2 form-control"');
					?>
				</div><?php
			}
			?>
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
if ($customers->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No customers found. Do you want to <?php
		$prospects = NULL;
		if ($is_prospect == TRUE) {
			$prospects = 'prospect/';
		}
		echo anchor('customers/new/' . $prospects . $org_type, 'create one'); ?>?
	</div>
	<?php
} else {
	echo $this->pagination_library->display($page_base);
	echo form_open('customers/bulk', 'id="customers"');
	// set hidden fields
	$hidden_fields = array(
		'redirect_to' => $page_base
	);
	if (!empty($search_fields['search'])) {
		$hidden_fields['redirect_to'] .= '/recall';
	}
	echo form_hidden($hidden_fields);
	?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered bulk-checkboxes'>
				<thead>
					<tr>
						<th class="bulk-checkbox">
							<input type="checkbox" />
						</th>
						<th>
							Name
						</th>
						<th>
							Post Code
						</th>
						<th>
							Phone
						</th>
						<th>
							Message
						</th>
						<th>
							Web Site
						</th>
						<?php if ($is_prospect != TRUE) {
							?><th>
								Has Bookings
							</th><?php
						} ?>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($customers->result() as $row) {
						?>
						<tr>
							<td class="center">
								<input name="orgs[]" value="<?php echo $row->orgID; ?>"<?php if (array_key_exists('orgs', $bulk_data) && array_key_exists($row->orgID, $bulk_data['orgs'])) { echo " checked=\"checked\""; } ;?> type="checkbox" />
							</td>
							<td class="name">
								<?php if($is_mobile_device){ ?>
									<a href="<?php echo site_url('customers/edit/' . $row->orgID); ?>" title="<?php echo $row->name;?>">
										<?php echo $row->name;?>
									</a>
								<?php }else{?>
									<a class="customer-slide-out-toggle" href="javascript:void(0);"
									   title="<?php echo $row->name;?>" data-contact="0" data-id="<?php echo $row->orgID;?>">
										<?php echo $row->name;?>
									</a>
								<?php } ?>
							</td>
							<td>
								<?php
								if (!empty($row->postcode)) { ?>
									<?php if($is_mobile_device){ ?>
										<a class="customer-slide-out-toggle" href="javascript:void(0);"
										   title="<?php echo $row->postcode;?>" data-contact="1" data-id="<?php echo $row->orgID;?>">
											<?php echo $row->postcode;?>
										</a>
									<?php }else{?>
										<a href="<?php echo site_url('customers/addresses/' . $row->orgID); ?>" title="<?php echo $row->postcode;?>"
											<?php echo $row->postcode;?>
										</a>
									<?php } ?>
								<?php } ?>
								<?php
								if (!empty($row->postcode)) {
									//echo anchor('customers/addresses/' . $row->orgID, $row->postcode);
								}
								?>
							</td>
							<td>
								<?php echo $row->phone; ?>
							</td>
							<td class="has_icon">
								<?php
								if (!empty($row->email)) {
									?><a href="<?php echo site_url('messages/sent/'.$row->type.'s/new/' . $row->orgID); ?>" class="btn btn-default btn-sm"><i class="far fa-envelope"></i></a><?php
								}
								?>
							</td>
							<td class="has_icon">
								<?php
								if (!empty($row->website)) {
									echo "<a href=\"";
									if (substr($row->website, 0, 4) != "http") {
										echo "http://";
									}
									echo htmlspecialchars($row->website)."\" target=\"_blank\" class=\"btn btn-default btn-sm\"><i class=\"far fa-globe\"></i></a>";
								}
								?>
							</td>
							<?php
							if ($is_prospect != TRUE) {
								?><td class="has_icon">
									<?php
									if ($row->booking_count > 0 || $row->block_count > 0) {
										?><span class='btn btn-success btn-sm no-action' title="Yes">
											<i class='far fa-check'></i>
										</span><?php
									} else {
										?><span class='btn btn-danger btn-sm no-action' title="No">
											<i class='far fa-times'></i>
										</span><?php
									}
									?>
								</td><?php
							}
							?>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('customers/edit/' . $row->orgID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('customers/remove/' . $row->orgID); ?>' title="Remove">
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
	<div class='card card-custom'>
		<div class="card-body">
			<div class="row">
				<div class="col-sm-2">
					<?php
					$options = array(
						'tag' => 'Tag',
						'passwords' => 'Assign & Send Passwords'
					);
					if ($this->settings_library->get('send_customer_password') != 1) {
						unset($options['passwords']);
					}

					// sort
					asort($options);

					$options = array(
						'' => 'Bulk Action'
					) + $options;

					$action = NULL;
					if (array_key_exists('action', $bulk_data)) {
						$action = $bulk_data['action'];
					}

					echo form_dropdown('action', $options, $action, 'id="action" class="select2 form-control"');
					?>
				</div>
				<div class="col-sm-2 bulk-supplementary tag">
					<?php
					$tags = array();
					if (array_key_exists('tags', $bulk_data)) {
						$tags = $bulk_data['tags'];
					}
					$options = array();
					if (count($tag_list) > 0) {
						foreach ($tag_list as $tag) {
							$options[$tag] = $tag;
						}
					}
					echo form_dropdown('tags[]', $options, set_value('tags', $tags), 'id="tags" multiple="multiple" class="form-control select2-tags"');
					?>
				</div>
				<div class="col-sm-2">
					<button class='btn btn-primary btn-submit' type="submit">
						Go
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
	echo form_close();
	echo $this->pagination_library->display($page_base);
} ?>
<?php if (!$is_mobile_device){ ?>
<div id="view-customer-slide-out" class="offcanvas offcanvas-right booking p-5">
	<div class="offcanvas-header d-flex justify-content-end mb-3 pl-5 pr-5 pt-5">
		<a href="javascript:void(0);" class="btn btn-xs btn-icon btn-primary" id="customer-slide-out-close">
			<i class="fas fa-times text-white"></i>
		</a>
	</div>
	<!--begin::Content-->
	<div class="offcanvas-content h-90 pl-5 pr-5 pr-5 ">
		<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>
	</div>
	<!--end::Content-->
</div>
<!-- end::User Panel-->
<?php } ?>
