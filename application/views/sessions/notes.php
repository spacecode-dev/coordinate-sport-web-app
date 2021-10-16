<?php
display_messages();
if ($bookingID != NULL && $lessonID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $lesson_info->blockID,
		'lessonID' => $lessonID,
		'tab' => $tab
	);
	$this->load->view('sessions/tabs.php', $data);
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
					<strong><label for="field_summary">Summary</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_summary',
					'id' => 'field_summary',
					'class' => 'form-control',
					'value' => $search_fields['summary']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_content">Content</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_content',
					'id' => 'field_content',
					'class' => 'form-control',
					'value' => $search_fields['content']
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
if ($notes->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No notes found. Do you want to <?php echo anchor('sessions/notes/'.$lessonID.'/new/', 'create one'); ?>?
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
							Date
						</th>
						<th>
							Summary
						</th>
						<th>
							By
						</th>
						<th>
							Status
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($notes->result() as $row) {
						?>
						<tr>
							<td>
								<?php echo mysql_to_uk_datetime($row->added); ?>
							</td>
							<td class="name">
								<?php
								if ($row->type == 'evaluation') {
									$row->summary = 'Session Evaluation';
									if (!empty($row->date)) {
										$row->summary .= ' for ' . mysql_to_uk_date($row->date);
									}
								}
								echo anchor('sessions/notes/edit/' . $row->noteID, $row->summary);
								?>
							</td>
							<td>
								<?php echo $row->staff_first . ' ' . $row->staff_last; ?>
							</td>
							<td>
								<?php
								if ($row->type == 'evaluation') {
									switch ($row->status) {
										case 'submitted':
										default:
											$label_colour = 'warning';
											break;
										case 'approved':
											$label_colour = 'success';
											break;
										case 'rejected':
											$label_colour = 'danger';
											break;
									}
									?><span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($row->status); ?></span><?php
								} else {
									echo '-';
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<?php
									if ($row->status == 'approved') {
										?><a class='btn btn-success btn-sm' href='<?php echo site_url('sessions/notes/edit/' . $row->noteID); ?>' title="View">
											<i class='far fa-globe'></i>
										</a>
										<span class='btn btn-danger btn-sm no-action' title='Locked'>
											<i class='far fa-lock'></i>
										</span><?php
									} else {
										?><a class='btn btn-warning btn-sm' href='<?php echo site_url('sessions/notes/edit/' . $row->noteID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('sessions/notes/remove/' . $row->noteID); ?>' title="Remove">
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
