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
					<strong><label for="field_code">Project Code</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_code',
					'id' => 'field_code',
					'class' => 'form-control',
					'value' => $search_fields['code']
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
					1 => 'Yes',
					0 => 'No'
				);
				echo form_dropdown('active', $options, $search_fields['active'], 'id="field_is_active" class="select2 form-control"');
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
if (count($project_codes) < 1) {
	?>
	<div class="alert alert-info">
		No project codes found. Do you want to <?php echo anchor('settings/projectcodes/new/', 'create one'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered checkbox-enable-td'>
				<thead>
					<tr>
						<th>
							Project Code
						</th>
						<th>
							Description
						</th>
						<th>
							Active
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($project_codes as $row) {
						?>
						<tr id="code-row-<?= $row->codeID ?>">
							<td class="name">
								<?php echo anchor('settings/projectcodes/edit/' . $row->codeID, $row->code); ?>
							</td>
							<td>
								<?php echo $row->desc; ?>
							</td>
							<?php $active = NULL;
							if (isset($row->active)) {
								$active = $row->active;
							} ?>
							<td class="has_icon ajax_toggle">
								<?php
									if($row->active == 1) {
										?><a class='btn btn-success btn-sm' href="<?php echo site_url('settings/projectcodes/active/' . $row->codeID); ?>/no" title="Yes">
											<i class='far fa-check'></i>
										</a><?php
									} else {?>
										<a class='btn btn-danger btn-sm' href="<?php echo site_url('settings/projectcodes/active/' . $row->codeID); ?>/yes" title="No">
												<i class='far fa-times'></i>
										</a><?php
									}
								?>
							</td>
							<td class="text-center w70p">
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('settings/projectcodes/edit/' . $row->codeID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('settings/projectcodes/remove/' . $row->codeID); ?>' title="Remove">
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
