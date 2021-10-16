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
					<strong><label for="field_reference">Reference</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_reference',
					'id' => 'field_reference',
					'class' => 'form-control',
					'value' => $search_fields['reference']
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
if ($providers->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No providers found. Do you want to <?php echo anchor('settings/childcarevoucherproviders/new/', 'create one'); ?>?
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
							Reference
						</th>
						<th>
							Comment
						</th>
						<th>
							Active
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($providers->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('settings/childcarevoucherproviders/edit/' . $row->providerID, $row->name); ?>
							</td>
							<td>
								<?php echo $row->reference; ?>
							</td>
							<td>
								<?php echo $row->comment; ?>
							</td>
							<td class="has_icon">
								<?php
								if($row->active == 1) {
									?><a href='<?php echo site_url('settings/childcarevoucherproviders/deactivate/' . $row->providerID); ?>' class='btn btn-success btn-sm' title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a href='<?php echo site_url('settings/childcarevoucherproviders/activate/' . $row->providerID); ?>' class='btn btn-danger btn-sm' title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('settings/childcarevoucherproviders/edit/' . $row->providerID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('settings/childcarevoucherproviders/remove/' . $row->providerID); ?>' title="Remove">
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
