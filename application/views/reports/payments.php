<?php
display_messages();
$this->load->view('reports/payments-tabs.php', [
	'tab' => $tab
]);
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
	echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'participant-billing-report-search']); ?>

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
					<strong><label for="field_amount">Transaction Amount</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_amount',
					'id' => 'field_amount',
					'class' => 'form-control',
					'value' => $search_fields['amount']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_participant_name">Participant Name</label></strong>
				</p>
				<?php
				$options = [];
				if (count($participants) > 0) {
					foreach ($participants as $id => $label) {
						$options[$id] = $label;
					}
				}
				echo form_dropdown('search_participant_name', $options, $search_fields['participant_name'], 'id="field_participant_name" class="select2-ajax form-control" data-ajax-url="' . site_url('ajax/participants') . '"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_project_name">Project Name</label></strong>
				</p>
				<?php
				$options =[];
				if (count($projects) > 0) {
					foreach ($projects as $id => $label) {
						$options[$id] = $label;
					}
				}
				echo form_dropdown('search_project_name', $options, $search_fields['project_name'], 'id="field_project_name" class="select2-ajax form-control" data-ajax-url="' . site_url('ajax/projects') . '"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_project_code">Project Code</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($project_codes->num_rows() > 0) {
					foreach ($project_codes->result() as $row) {
						$options[$row->codeID] = $row->code;
					}
				}
				echo form_dropdown('search_project_code', $options, $search_fields['project_code'], 'id="field_project_code" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_block">Block</label></strong>
				</p>
				<?php
				$options = [];
				if (count($blocks) > 0) {
					foreach ($blocks as $id => $label) {
						$options[$id] = $label;
					}
				}
				echo form_dropdown('search_block', $options, $search_fields['block'], 'id="field_block" class="select2-ajax form-control" data-ajax-url="' . site_url('ajax/blocks') . '"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_session_type">Session Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($session_types->num_rows() > 0) {
					foreach ($session_types->result() as $row) {
						$options[$row->typeID] = $row->name;
					}
				}
				echo form_dropdown('search_session_type', $options, $search_fields['session_type'], 'id="fieldsession_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_brand"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('search_brand', $options, $search_fields['brand'], 'id="field_brand_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_payment_method">Payment Method</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'card' => 'Credit/Debit Card',
					'cash' => 'Cash',
					'cheque' => 'Cheque',
					'online' => 'Online',
					'direct debit' => 'Direct Debit',
					'childcare voucher' => 'Childcare Voucher',
					'credit note' => 'Credit Note',
					'other' => 'Other'
				);
				echo form_dropdown('search_payment_method', $options, $search_fields['payment_method'], 'id="field_payment_method" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_transaction_ref">Transaction Reference</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_transaction_ref',
					'id' => 'field_transaction_ref',
					'class' => 'form-control',
					'value' => $search_fields['transaction_ref']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_note">Note</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_note',
					'id' => 'field_note',
					'class' => 'form-control',
					'value' => $search_fields['note']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p class="mb-0">
					<strong><label for="field_by_project">Group by Project</label></strong>
				</p>
				<div>
					<?php
					$data = array(
						'name' => 'search_by_project',
						'id' => 'field_by_project',
						'value' => 1,
						'checked' => $search_fields['by_project']
					);
					echo form_checkbox($data);
					?>
				</div>
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
if ($payments->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?><div class='card'>
		<div class="fixed-scrollbar"></div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Transaction Date
						</th>
						<th>
							Transaction Amount
						</th>
						<th>
							Applicable Amount
						</th>
						<th>
							Account Holder
						</th>
						<th>
							Participant Names
						</th>
						<th>
							Project Names
						</th>
						<th>
							Project Codes
						</th>
						<th>
							Blocks
						</th>
						<th>
							Session Types
						</th>
						<th>
							Departments
						</th>
						<th>
							Session Count
						</th>
						<th>
							Payment Type
						</th>
						<th>
							Payment Method
						</th>
						<th>
							Transaction Reference
						</th>
						<th>
							Note
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$totals = [
						'amount' => 0,
						'amount_partial' => 0,
						'session_count' => 0
					];
					foreach ($payments->result() as $row) {
						foreach ($totals as $key => $val) {
							$totals[$key] += $row->$key;
						}
						?><tr>
							<td>
								<?php echo anchor('participants/payments/edit/' . $row->paymentID, mysql_to_uk_date($row->added), 'target="_blank"'); ?>
							</td>
							<td>
								<?php
								if ($search_fields['by_project'] == 1) {
									echo '-';
								} else {
									if ($row->amount < 0) {
										echo '<span class="text-red">';
									}
									echo $row->amount;
									if ($row->amount < 0) {
										echo '</span>';
									}
								}
								?>
							</td>
							<td>
								<?php
								if($row->is_sub == '0') {
									if ($row->amount_partial < 0) {
										echo '<span class="text-red">';
									}
									echo $row->amount_partial;
									if ($row->amount_partial < 0) {
										echo '</span>';
									}
								}else{
									echo '<span>-</span>';
								}
								?>
							</td>
							<td class="wrap">
								<span class="truncate"><?php
								if ($row->internal == 1) {
									$booking_contacts = explode('###', $row->booking_contact_names);
									$booking_contacts = array_filter($booking_contacts);
									sort($booking_contacts);
									echo implode(", ", $booking_contacts);
								} else if (!empty($row->payment_contact)) {
									echo $row->payment_contact;
								}
								?></span>
							</td>
							<td class="wrap">
								<span class="truncate"><?php
								$participant_names = array_merge(explode('###', $row->children_names), explode('###', $row->contact_names));
								$participant_names = array_filter($participant_names);
								sort($participant_names);
								echo implode(", ", $participant_names);
								?></span>
							</td>
							<td class="wrap">
								<span class="truncate"><?php echo $row->project_names; ?></span>
							</td>
							<td class="wrap">
								<span class="truncate"><?php echo $row->project_codes; ?></span>
							</td>
							<td class="wrap">
								<span class="truncate"><?php echo $row->blocks; ?></span>
							</td>
							<td class="wrap">
								<span class="truncate"><?php echo $row->session_types; ?></span>
							</td>
							<td class="wrap">
								<span class="truncate"><?php echo $row->departments; ?></span>
							</td>
							<td>
								<?php echo $row->session_count; ?>
							</td>
							<td>
								<?php
								if ($row->internal == 1) {
									echo 'Internal';
									if (!empty($row->staff_first_name) && !empty($row->staff_last_name)) {
										echo ' (' . $row->staff_first_name . ' ' . $row->staff_last_name . ')';
									}
								} else {
									echo 'External';
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->method) {
									case 'card':
										echo 'Credit/Debit Card';
										break;
									default:
										echo ucwords($row->method);
										break;
								}
								?>
							</td>
							<td>
								<?php echo $row->transaction_ref; ?>
							</td>
							<td>
								<?php echo $row->note; ?>
							</td>
						</tr><?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td>Totals</td>
						<td>
							<?php
							if ($totals['amount'] < 0) {
								echo '<span class="text-red">';
							}
							echo $totals['amount'];
							if ($totals['amount'] < 0) {
								echo '</span>';
							}
							?>
						</td>
						<td>
							<?php
							if ($totals['amount_partial'] < 0) {
								echo '<span class="text-red">';
							}
							echo $totals['amount_partial'];
							if ($totals['amount_partial'] < 0) {
								echo '</span>';
							}
							?>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>
							<?php echo $totals['session_count']; ?>
						</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php
}
