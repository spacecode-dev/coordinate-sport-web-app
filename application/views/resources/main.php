<?php
display_messages();
$data = array(
	'resource' => $resource,
	'resources' => $resources
);
$this->load->view('resources/tabs.php', $data);
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
if ($files->num_rows() == 0) {
	if(count($resources)==0){ ?>
		<div class="alert alert-info">	No resources found.<?php if ($read_only !== TRUE) { ?>Do you want to <?php echo anchor('settings/resources', 'create one'); ?>?<?php } ?>
	<?php
	} else {
	?>
		<div class="alert alert-info">	No files found.<?php if ($read_only !== TRUE) { ?>Do you want to <?php echo anchor($add_url, 'create one'); ?>?<?php } ?>
	</div>
	<?php
	}
	?>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<?php
					if ($read_only !== TRUE) {
						?><tr>
							<th></th>
							<?php
							if ( $brands->num_rows() > 0) {
								?><th colspan="<?php echo $brands->num_rows(); ?>" class="min">
									Always Send with Bookings
								</th><?php
							}
							?>
							<th></th>
						</tr><?php
					}
					?>
					<tr>
						<th>
							Name
						</th>
						<?php
						if ($read_only !== TRUE && $brands->num_rows() > 0) {
							foreach ($brands->result() as $row) {
								?><th class="min">
									<?php echo $row->name; ?>
								</th><?php
							}
						}
						?>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($files->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('attachment/files/' . $row->path . '/' . $row->accountID, $row->name, 'target="_blank"'); ?>
							</td>
							<?php
							if ($read_only !== TRUE && $brands->num_rows() > 0) {
								$row->brands = explode(',', $row->brands);
								foreach ($brands->result() as $brand) {
									?><td class="has_icon ajax_toggle">
										<?php
										if (is_array($row->brands) && in_array($brand->brandID, $row->brands)) {
											?><a class='btn btn-success btn-sm' href="<?php echo site_url('resources/sendwithbookings/' . $brand->brandID . '/' . $row->attachmentID); ?>/no" title="Yes">
												<i class='far fa-check'></i>
											</a><?php
										} else {
											?><a class='btn btn-danger btn-sm' href="<?php echo site_url('resources/sendwithbookings/' . $brand->brandID . '/' . $row->attachmentID); ?>/yes" title="No">
												<i class='far fa-times'></i>
											</a><?php
										}
										?>
									</td><?php
								}
							}
							?>
							<td>
								<div class='text-right'>
									<?php
									if ($read_only === TRUE) {
										?><a class='btn btn-info btn-sm' href='<?php echo site_url('attachment/files/' . $row->path . '/' . $row->accountID); ?>' title="View">
											<i class='far fa-download'></i>
										</a><?php
									} else {
										?><a class='btn btn-warning btn-sm' href='<?php echo site_url('resources/'.$resource['resourceID'].'/'.'edit/' . $row->attachmentID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('resources/'.$resource['resourceID']. '/' .'remove/' . $row->attachmentID); ?>' title="Remove">
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
}
echo $this->pagination_library->display($page_base);
