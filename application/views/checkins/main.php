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
					<strong><label for="field_staff_id">Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($staff_list->num_rows() > 0) {
					foreach ($staff_list->result() as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_view">View</label></strong>
				</p>
				<?php
				$options = array(
					'map' => 'Check-in (Map)',
					'details' => 'Check-in (Details)'
				);
				echo form_dropdown('view', $options, $view, 'id="field_view" class="select2 form-control"');
				?>
			</div>
			<?php if ($view == 'details'): ?>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_view">Status</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'FF0000' => 'Not Checked In (Red Pin)',
					'FFBF00' => 'Checked In Late (Amber Pin)',
					'008000' => 'Checked In On Time (Green Pin)',
					'0000FF' => 'Checked Out (Blue Pin)',
				);
				echo form_dropdown('status', $options, $search_fields['status'], 'id="field_status" class="select2 form-control"');
				?>
			</div>
			<?php endif; ?>
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
if ($view == 'map') {
	if (count($markers) == 0) {
		?>
		<div class="alert alert-info">
			No data found.
		</div>
		<?php
	} else {
		?><script>
			var checkin_markers = <?php echo json_encode($markers); ?>;
		</script>
		<div id="checkin_map"></div><?php
	}
} else {
	$this->load->view('checkins/checkins_details.php', $markers);
}
