<?php
display_messages();
$data = array(
	'tab' => $tab
);
$this->load->view('equipment/tabs.php', $data);
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
if ($equipment->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No equipment found. <?php if ($this->auth->user->department != 'coaching') { ?>Do you want to <?php	echo anchor('equipment/new', 'create one'); ?>?<?php } ?>
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
							Location
						</th>
						<th>
							Notes
						</th>
						<th class="min">
							Total
						</th>
						<th class="min">
							Available
						</th>
						<?php
						if ($this->auth->user->department != 'coaching') {
							?><th></th><?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($equipment->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php
								if ($this->auth->user->department == 'coaching') {
									echo $row->name;
								} else {
									echo anchor('equipment/edit/' . $row->equipmentID, $row->name);
								}
								?>
							</td>
							<td>
								<?php echo $row->location; ?>
							</td>
							<td>
								<?php echo nl2br($row->notes); ?>
							</td>
							<td class="min">
								<?php echo $row->quantity; ?>
							</td>
							<td class="min">
								<?php echo (intval($row->quantity) - intval($ci->items_available($row->equipmentID))); ?>
							</td>
							<?php
							if ($this->auth->user->department != 'coaching') {
								?><td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('equipment/edit/' . $row->equipmentID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('equipment/remove/' . $row->equipmentID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a>
									</div>
								</td><?php
							}
							?>
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
