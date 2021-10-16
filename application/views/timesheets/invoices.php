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
			<?php
			if ($show_all === TRUE) {
				?><div class='col-sm-2'>
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
				</div><?php
			}
			?>
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
if ($invoices->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No invoices found.
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
						<?php
						if ($show_all === TRUE) {
							?><th>
								Staff
							</th><?php
						}
						?>
						<th>
							Invoice No.
						</th>
						<th>
							Subject
						</th>
						<th>
							Amount
						</th>
						<th>
							Status
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($invoices->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('timesheets/invoice/' . $row->invoiceID, mysql_to_uk_date($row->date)); ?>
							</td>
							<?php
							if ($show_all === TRUE) {
								?><td>
									<?php echo $row->first . ' ' . $row->surname; ?>
								</td><?php
							}
							?>
							<td>
								<?php echo $this->settings_library->get('staff_invoice_prefix') . $row->number; ?>
							</td>
							<td>
								<?php echo $row->subject; ?>
							</td>
							<td>
								<?php echo currency_symbol() . number_format($row->amount, 2); ?>
							</td>
							<td>
								<?php
								if ($row->sent == 1) {
									?><span class="label label-inline label-success">Sent</span><?php
								} else {
									?><span class="label label-inline label-danger">Draft</span><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-success btn-sm' href='<?php echo site_url('timesheets/invoice/' . $row->invoiceID); ?>' title="View">
										<i class='far fa-arrow-right'></i>
									</a>
									<?php
									if ($row->sent != 1) {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('timesheets/removeinvoice/' . $row->invoiceID); ?>' title="Remove">
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
