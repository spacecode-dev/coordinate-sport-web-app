<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
echo form_open_multipart($submit_to, 'id="project_subscription_form"');
echo form_fieldset();?>
<div class="card card-custom">
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
			<h3 class="card-label">Details</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<div class='form-group'>
			<?php
				echo form_label('Subscription Name <em>*</em>', 'subName');
				$subName = NULL;
				if (isset($sub_info->subName)) {
					$subName = $sub_info->subName;
				}
				$data = array(
					'name' => 'subName',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => set_value('subName', $this->crm_library->htmlspecialchars_decode($subName), FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
			?>
			</div>
			<div class='form-group'>
			<?php
				echo form_label('Price <em>*</em>', 'price');
				$price = NULL;
				if (isset($sub_info->price)) {
					$price = $sub_info->price;
				}
				$data = array(
					'name' => 'price',
					'id' => 'field_price',
					'class' => 'form-control',
					'value' => set_value('price', $this->crm_library->htmlspecialchars_decode($price), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?>
			</div>
			<div class='form-group'>
				<?php
					echo form_label('Frequency', 'frequency');
					$frequency = NULL;
					$extra_attrs = NULL;
					if (isset($sub_info->frequency)) {
						$frequency = $sub_info->frequency;
						if(isset($sub_info->is_active) && $sub_info->is_active) {
							$extra_attrs = ' disabled readonly';
						}
					}
					$options = array(
						'weekly' => 'Weekly',
						'monthly' => 'Monthly',
						'yearly' => 'Yearly'
					);

					echo form_dropdown('frequency', $options, set_value('frequency', $this->crm_library->htmlspecialchars_decode($frequency), FALSE), 'id="frequency" class="form-control select2"' . $extra_attrs);
				?>
			</div>
			<div class='form-group'>
				<?php
					echo form_label('Session Types', 'types');

					$types = array();

					if (isset($sub_info->types) && !empty($sub_info->types)) {
						if(strpos($sub_info->types, ',') !== false){
							$types = explode(",", $sub_info->types);
						}
						$types[] = $sub_info->types;
					}
					if(count($types) == count($lesson_types)){
						$array = array("0" => "Deselect All", "All Session Types" => "All Session Types");
						$types = array("All Session Types");
						echo form_dropdown('types[]', $array, set_value('types', $types), 'id="types" multiple="multiple" class="form-control select2 tagClass" onChange="hide_show_session_types(this)"');
					}else{
						echo form_dropdown('types[]', $lesson_types, set_value('types', $types), 'id="types" multiple="multiple" class="form-control select2 tagClass" onChange="hide_show_session_types(this)"');
					}
				?>
			</div>
			<div class='form-group' style="display:none">
				<?php
					echo form_label('Session Types', 'session_type');
					$types = array();

					if (isset($sub_info->types) && is_array($sub_info->types)) {
						$types = $sub_info->types;
					}
					echo form_dropdown('session_types[]', $lesson_types, NULL, ' id="session_type" class="form-control" ');
				?>
			</div>
			<div class='form-group'>
				<?php
					echo form_label('Provider <em>*</em>', 'payment_provider');
					$payment_provider = NULL;
					$extra_attrs = NULL;
					if (isset($sub_info->payment_provider)) {
						$payment_provider = $sub_info->payment_provider;
						if(isset($sub_info->is_active) && $sub_info->is_active) {
							$extra_attrs = ' disabled readonly';
						}
					}
					$options = array(
						'' => 'Select',
						'gocardless' => 'GoCardless',
						'stripe' => 'Stripe',
					);
					if($gc_error !== NULL):
						unset($options['gocardless']);
					endif;
					if($stripe_error !== NULL):
						unset($options['stripe']);
					endif;

					echo form_dropdown('payment_provider', $options, set_value('payment_provider', $this->crm_library->htmlspecialchars_decode($payment_provider), FALSE), 'id="payment_provider" class="form-control select2 select2-chosen"' . $extra_attrs);
				?>
				<div class="gocardless-integration-error hide">
					<?php if($gc_error !== NULL): ?>
						<div class="alert alert-danger" role="alert">
						<i class="far fa-exclamation-triangle"></i>
							<?php echo $gc_error; ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="stripe-integration-error hide">
					<?php if($stripe_error !== NULL): ?>
						<div class="alert alert-danger" role="alert">
						<i class="far fa-exclamation-triangle"></i>
							<?php echo $stripe_error; ?>
						</div>
					<?php endif; ?>
				</div>

			</div>
			<div class='form-group'>
				<?php
					echo form_label('No. of Sessions per Week', 'no_of_sessions_per_week');
					$no_of_sessions_per_week = 1;
					if (isset($sub_info->no_of_sessions_per_week)) {
						$no_of_sessions_per_week = $sub_info->no_of_sessions_per_week;

					}
					$data = array(
						'name' => 'no_of_sessions_per_week',
						'id' => 'no_of_sessions_per_week',
						'class' => 'form-control',
						'value' => set_value('no_of_sessions_per_week', $this->crm_library->htmlspecialchars_decode($no_of_sessions_per_week), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
					echo form_label('Session Change Cut Off', 'session_cut_off');
					$session_cut_off = NULL;
					if (isset($sub_info->session_cut_off)) {
						$session_cut_off = $sub_info->session_cut_off;
					}
					$data = array(
						'name' => 'session_cut_off',
						'id' => 'session_cut_off',
						'class' => 'form-control',
						'value' => set_value('session_cut_off', $this->crm_library->htmlspecialchars_decode($session_cut_off), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?>
				<small class="text-muted form-text">In Days. Participants will not be able to change the sessions they attend this many days before they happen</small>
			</div>
		</div>
	</div>
	<div class="card-footer">
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-save'></i> Save
			</button>
			<a class='btn btn-default' href="<?php echo site_url($return_to); ?>" class="btn">Cancel</a>
		</div>
	</div>
</div>
<?php echo form_fieldset_close() ?>
<?php if(isset($sub_info->subID) && $history->num_rows() > 0): ?>
<div class="card card-custom">
	<div class='card-header'>
		<div class="card-title">
			<h3 class="card-label">History</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Participant Name
						</th>

						<th>
							Subscription Name
						</th>

						<th>
							Status
						</th>

						<th>
							Date of Last Payment
						</th>

						<th>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($history->result() as $row): ?>
						<tr>
							<td class="name">
							<?php
							if(!empty($row->first_name)) {
								echo $row->first_name . ' ' . $row->last_name;
							}else{
								echo $row->contact_name . ' ' . $row->contact_last_name;
							}
							?>
							</td>

							<td class="subName">
								<?php echo $row->subName ?>
							</td>

							<td>
								<?php
								switch ($row->status) {
									case 'cancelled':
									default:
										$label_colour = 'danger';
										break;
									case 'inactive':
										$label_colour = 'warning';
										break;
									case 'active':
									case 'completed':
										$label_colour = 'success';
										break;
								}
								?>
								<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($row->status); ?></span>
							</td>

							<td>
								<?php echo $row->last_payment_date == NULL ? 'No Payment Taken Yet' : date('d/m/Y H:i', strtotime($row->last_payment_date)); ?>
							</td>

							<td>
								<div class='text-right'>
									<?php
										if (!empty($row->gc_subscription_id)) {
											$gocardless_url = 'https://manage';
											if ($this->settings_library->get('gocardless_environment') == 'sandbox') {
												$gocardless_url .= '-sandbox';
											}

											$gocardless_url .= '.gocardless.com/subscriptions/' . $row->gc_subscription_id
											?><a class='btn btn-info btn-xs' href='<?php echo $gocardless_url; ?>' title="View Payment" target="_blank">
												<i class='far fa-sack-dollar'></i>
											</a> <?php
										}
										if($row->childID != null && $row->childID != ""){
										?>
											<a class='btn btn-warning btn-xs' href='<?php echo site_url('bookings/subscriptions/session/'.$cartArray[$row->childID]); ?>' title="Edit">
												<i class='far fa-pencil'></i>
											</a>
										<?php }else{ ?>
										<a class='btn btn-warning btn-xs' href='<?php echo site_url('bookings/subscriptions/session/'.$cartArray[$row->contactID]); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<?php } ?>
										<?php
										if ($row->status != 'cancelled'){
											?><a class='btn btn-danger btn-xs confirm' href='<?php echo site_url('bookings/subscriptions/cancel/' . $row->subID . '/' . $row->$participants_id_field); ?>' title="Cancel" data-message="Are you sure you want to cancel this subscription? This will stop any future payments.">
												<i class='far fa-ban'></i>
											</a><?php
										}
									?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php endif; ?>
<?php
echo form_close();
?>
