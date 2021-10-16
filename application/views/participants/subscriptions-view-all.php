<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
?>
<div class="subscription-message"></div>
<?php echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
					<strong><label for="field_date_from">Project Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_project_name',
					'id' => 'search_project_name',
					'class' => 'form-control',
					'value' => $search_fields['project_name']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_to">Participant Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_participant_name',
					'id' => 'search_participant_name',
					'class' => 'form-control',
					'value' => $search_fields['participant_name']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_to">Frequency</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'weekly' => 'Weekly',
					'monthly' => 'Monthly',
					'yearly' => 'Yearly'
				);
				echo form_dropdown('search_frequency', $options, $search_fields['frequency'], 'id="search_frequency" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_amount">Price (<?php echo currency_symbol(); ?>)</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_price',
					'id' => 'search_price',
					'class' => 'form-control',
					'value' => $search_fields['price']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_amount">Number of Sessions per Week</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_no_sessions_per_week',
					'id' => 'search_no_sessions_per_week',
					'class' => 'form-control',
					'value' => $search_fields['no_sessions_per_week']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_contact_id">Provider</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'stripe' => 'Stripe',
					'gocardless' => 'GoCardless'
				);
				echo form_dropdown('search_provider', $options, $search_fields['provider'], 'id="search_provider" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_contact_id">Status</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'active' => 'Active',
					'inactive' => 'Inactive'
				);
				echo form_dropdown('search_status', $options, $search_fields['status'], 'id="search_status" class="select2 form-control"');
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
if ($subs->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No subscriptions found.
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
							Project Name
						</th>
						<th>
							Participant Name
						</th>
						<th>
							Subscription Name
						</th>
						<th>
							Frequency
						</th>
						<th>
							Price
						</th>
						<th>
							Number of Sessions per Week
						</th>
						<th>
							Provider
						</th>
						<th>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($subs->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('bookings/edit/' . $row->bookingID, $row->name); ?>
							</td>
							<td>
								<?php echo $row->first_name.' '.$row->last_name; ?>
							</td>
							<td>
								<?php echo $row->subName; ?>
							</td>
							<td>
								<?php echo ucfirst($row->frequency); ?>
							</td>
							<td>
								<?php echo currency_symbol() . number_format($row->price, 2); ?>
							</td>
							<td>
								<?php echo $row->no_of_sessions_per_week; ?>
							</td>
							<td>
								<?php echo ucfirst($row->payment_provider); ?>
							</td>
							<td>
								<div class='text-right'>
									<?php
									if ($row->status == 'active'){
										?><a class='btn btn-danger btn-sm inactive-sub' data-provider="<?php echo $row->payment_provider; ?>" data-id="<?php echo $row->id; ?>" href='javascript:void(0);' title="Cancel" data-csrf-name="<?php echo $this->security->get_csrf_token_name();?>" data-csrf-hash="<?php echo $this->security->get_csrf_hash();?>">
											Cancel
										</a><?php
									}
									if ($row->status == 'inactive'){ ?>
										<a class='btn font-weight-bold btn-sm btn-success activate-sub' data-id="<?php echo $row->id; ?>" href='javascript:void(0);' title="Activate">
											Activate
										</a>
									<?php }
									?>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('participants/subscriptions/session/'.$row->cartID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
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
