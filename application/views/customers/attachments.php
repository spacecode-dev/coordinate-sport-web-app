<?php
display_messages();
if ($orgID != NULL) {
	$data = array(
		'orgID' => $orgID,
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
					<strong><label for="field_comment">Comment</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_comment',
					'id' => 'field_comment',
					'class' => 'form-control',
					'value' => $search_fields['comment']
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
	<a class="btn btn-sm btn-success" href="<?php echo site_url($add_url);?>"><i class="far fa-plus"></i> Create New</a>
</div>
<?php
if ($files->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No files found. Do you want to <?php echo anchor($add_url, 'create one'); ?>?
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
							Comment
						</th>
						<th class="min">
							Coach Access
						</th>
						<th class="min">
							Send with Confirmation
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($files->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('attachment/customer/' . $row->path . '/' . $row->accountID, $row->name, 'target="_blank"'); ?>
							</td>
							<td>
								<?php echo $row->comment; ?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->coachaccess == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('customers/attachments/coachaccess/' . $row->attachmentID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('customers/attachments/coachaccess/' . $row->attachmentID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->sendwithconfirmation == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('customers/attachments/sendwithconfirmation/' . $row->attachmentID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('customers/attachments/sendwithconfirmation/' . $row->attachmentID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<?php
									$ext = pathinfo($row->name, PATHINFO_EXTENSION);
									if ($this->auth->has_features('attachments_editing') && in_array($ext, array('doc', 'docx', 'xls', 'xlsx'))) {
										?><a href="<?php echo site_url('attachment/edit/customer/' . $row->path . '/' . $row->accountID); ?>" class='btn btn-info btn-sm' title="Edit Online" target="_blank">
											<i class='far fa-cloud'></i>
										</a><?php
									}
									?>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('customers/attachments/edit/' . $row->attachmentID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('customers/attachments/remove/' . $row->attachmentID); ?>' title="Remove">
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
}
echo $this->pagination_library->display($page_base);
