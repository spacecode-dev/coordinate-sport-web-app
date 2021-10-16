<?php if (isset($search))  {
	$form_classes = 'card card-custom card-search';
	echo form_open('', ['class' => $form_classes]); ?>
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
				<?php if(isset($search['brands'])) { ?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_brand_id">Department</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					if ($search['brands']->num_rows() > 0) {
						foreach ($search['brands']->result() as $row) {
							$options[$row->brandID] = $row->name;
						}
					}
					echo form_dropdown('search_brand_id', $options, $search['search_fields']['brand_id'], 'id="field_brand_id" class="select2 form-control"');
					?>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class='card-footer'>
			<div class="d-flex justify-content-between">
				<button class='btn btn-primary btn-submit' type="submit">
					<i class='far fa-search'></i> Search
				</button>
			</div>
		</div>
	<?php echo form_close();
} ?>
<div class="box-content" id="dashboard-detail" data-url="<?php echo $request; ?>">
	<div class="results">
		<p class="loading">Loading...</p>
	</div>
</div>
