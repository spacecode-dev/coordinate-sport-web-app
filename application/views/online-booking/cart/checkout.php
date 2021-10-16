<?php
if (!$in_crm) {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
}
display_messages($fa_weight);
?><div class="row">
	<div class="col-sm-12 col-md-8">
		<div class="boxed">
			<?php
			echo form_open($cart_base . 'checkout', array(
				'id' => 'checkout'
			));
				if(isset($cart_summary['subscriptions']) && count($cart_summary['subscriptions'])) {
					$strip_sub_counter=0;
					foreach ($cart_summary['subscriptions'] as $cart_sub) {
						if ($cart_sub->payment_provider == "stripe") {
							$strip_sub_counter++;
						}
					}
					if($strip_sub_counter > 0){
						echo '<input type="hidden" name="stripe_sub_counter" id="stripe_sub_counter" value="' . $strip_sub_counter . '"/>';
					}
				}

				echo form_hidden(['action' => 'checkout']);
				if ($cart_summary['total'] > 0 && is_array($childcarevoucher_providers) && count($childcarevoucher_providers) > 0 && $cart_summary['subscription_total'] === 0) {
					?><fieldset>
						<h3 class="light">Childcare Voucher</h3>
						<p>If <?php if ($in_crm) { echo 'participant wishes'; }  else { echo 'you wish'; } ?> to pay by childcare voucher, please tick the box below and select <?php if ($in_crm) { echo 'their'; }  else { echo 'your'; } ?> voucher provider.<?php if (!$in_crm) { ?> If you do not see the childcare voucher provider you wish to use on our list, please contact our office or your provider to enable us to register.<?php } ?></p>
						<div class="form-group">
							<?php
							$data = array(
								'name' => 'childcarevoucher',
								'id' => 'childcarevoucher',
								'value' => 1
							);
							if (set_value('childcarevoucher', NULL, FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox">
								<label>
									<?php echo form_checkbox($data); ?>
									Use Childcare Voucher?
								</label>
							</div>
						</div>
						<div id="childcarevoucher_details" style="display:none;">
							<div class="form-group">
								<?php
								echo form_label('Childcare Voucher Provider <em>*</em>', 'childcarevoucher_providerID');
								$options = array(
									'' => 'Select',
								);
								foreach ($childcarevoucher_providers as $providerID => $name) {
									$options[$providerID] = $name;
								}
								echo form_dropdown('childcarevoucher_providerID', $options, set_value('childcarevoucher_providerID', NULL, FALSE), 'id="childcarevoucher_providerID" class="form-control select2"');
								?>
							</div>
							<?php
							$childcare_voucher_instruction = trim($this->settings_library->get('childcare_voucher_instruction', $this->cart_library->accountID));
							if (!empty($childcare_voucher_instruction)) {
								echo '<p>' . nl2br($childcare_voucher_instruction) . '</p>';
							}
							if (count($childcarevoucher_provider_notices) > 0) {
								?><div class="notices"><?php
									foreach ($childcarevoucher_provider_notices as $providerID => $notice) {
										?><div class="notice" data-provider="<?php echo $providerID; ?>"><?php echo $notice; ?></div><?php
									}
								?></div><?php
							}
							?>
						</div>
					</fieldset><?php
				}
				if (!empty(trim(strip_tags($terms)))) {
					?><fieldset>
						<h3 class="light">Terms &amp; Conditions</h3>
						<div class="terms_box">
							<?php echo $terms; ?>
						</div>
						<div class="form-group">
							<?php
							$data = array(
								'name' => 'agree_terms',
								'id' => 'agree_terms',
								'value' => 1,
								'required' => 'required'
							);
							if (set_value('agree_terms', NULL, FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox">
								<label>
									<?php echo form_checkbox($data); ?>
									<?php if ($in_crm) { echo 'Participant has'; }  else { echo 'I have'; } ?> read and agree<?php if ($in_crm) { echo 'd'; } ?> to the above terms &amp; conditions
								</label>
							</div>
						</div>
					</fieldset><?php
				}
				?><fieldset>
					<h3 class="light">Payment</h3>
					<?php
					if ($cart_summary['total'] == 0) {
						?><p>This booking is free.</p><?php
					}
					elseif ($cart_summary['total'] == $cart_summary['subscription_total']) {
						echo '<p>This booking is included with your subscription.</p>';
						if((is_array($sub_payment_provider) && in_array('stripe', $sub_payment_provider))) {
							?><p>You are required to enter your card details to set up the subscription through Stripe.</p>
							<?php
							$data = array(
								'type'  => 'hidden',
								'name'  => 'payment_amount',
								'id'    => 'payment_amount',
								'value' => $sub_payment_amount,
								'class' => 'payment_amount'
							);
							echo form_input($data);

							if ($payment_gateway == 'stripe' && !$in_crm) {
								?><div class="payment_fields">
									<div id="card-errors" role="alert"></div>
									<div class="form-group">
										<?php echo form_label('Card Number <em>*</em>', 'cardNumber'); ?>
										<div id="cardNumber"></div>
									</div>
									<div class="form-group">
										<?php echo form_label('Expiry Date <em>*</em>', 'cardExpiry'); ?>
										<div id="cardExpiry"></div>
									</div>
									<div class="form-group">
										<?php echo form_label('CVC <em>*</em>', 'cardCvc'); ?>
										<div id="cardCvc"></div>
										<p class="help-block">
											<small class="text-muted">This is usually the 3 digit number on the back of the card.</small>
										</p>
									</div>
								</div><?php
								$master_config_data = [];
								if(isset($cart_summary['subscriptions']) && count($cart_summary['subscriptions'])) {
									foreach ($cart_summary['subscriptions'] as $cart_sub) {
										if ($cart_sub->payment_provider == "stripe") {
											$child_id = '';$contact_id = '';
											if(isset($cart_sub->childID)){
												if(isset($master_config_data[$cart_sub->stripe_price_id]['childID'])){
													$child_id = ','.$cart_sub->childID;
												}else{
													$child_id = $cart_sub->childID;
												}
											}
											if(isset($cart_sub->contactID)){
												if(isset($master_config_data[$cart_sub->stripe_price_id]['contactID'])){
													$contact_id = ','.$cart_sub->contactID;
												}else{
													$contact_id = $cart_sub->contactID;
												}
											}
											if(isset($master_config_data[$cart_sub->stripe_price_id])) {
												$master_config_data[$cart_sub->stripe_price_id]['price'] += $cart_sub->price;
												$master_config_data[$cart_sub->stripe_price_id]['qty'] += 1;
												$master_config_data[$cart_sub->stripe_price_id]['contactID'] .= $contact_id;
												$master_config_data[$cart_sub->stripe_price_id]['childID'] .= $child_id;
											}else{
												$master_config_data[$cart_sub->stripe_price_id]['price'] = $cart_sub->price;
												$master_config_data[$cart_sub->stripe_price_id]['contactID'] = $cart_sub->contactID;
												$master_config_data[$cart_sub->stripe_price_id]['childID'] = $cart_sub->childID;
												$master_config_data[$cart_sub->stripe_price_id]['qty'] = 1;
											}
											$master_config_data[$cart_sub->stripe_price_id]['cartID'] = $cart_sub->cartID;
											$master_config_data[$cart_sub->stripe_price_id]['subID'] = $cart_sub->subID;
											$master_config_data[$cart_sub->stripe_price_id]['stripe_price_id'] = $cart_sub->stripe_price_id;
										}
									}
								}

								echo form_hidden('payment_method', 'card');
								$this->load->helper('stripe_helper');
								stripe_subscription_js($stripe_pk, json_encode($master_config_data));
							}
						}
					} else if ($max_payment <= 0) {
						?><p>This booking will be paid by the existing credit in <?php if ($in_crm) { echo 'their'; }  else { echo 'your'; } ?> account.</p><?php
					} else {
						if ($allow_payment_plan) {
							?><div class="form-group">
								<?php
								echo form_label('How would you like to pay? <em>*</em>', 'payment_method');
								$options = array(
									'card' => 'Credit/Debit Card',
									'plan' => 'Payment Plan'
								);
								echo form_dropdown('payment_method', $options, set_value('payment_method', NULL, FALSE), 'id="payment_method" class="form-control select2"');
								?>
							</div><?php
						}
						if ($allow_payment_plan) {
							?><div class="method_fields card">
								<h4 class="light">Credit/Debit Card</h4><?php
						}
						if($includes_subscriptions && $only_subscriptions === FALSE) {
							?><p>Please pay for the sessions and initial subscription payment using Credit/Debit card and subsequent subscription payments will be taken through direct debit.</p><?php
						}
						if ($this->settings_library->get('require_full_payment', $this->cart_library->accountID) == 1) {
							if (is_array($childcarevoucher_providers) && count($childcarevoucher_providers) > 0) {
								?><p>Full payment is required unless <?php if ($in_crm) { echo 'participant is'; }  else { echo 'you are'; } ?> paying by childcare vouchers.</p><?php
								} else {
									?><p>Full payment is required.</p><?php
								}

						} elseif($only_subscriptions === FALSE) {
							?>
							<p><?php if ($in_crm) { echo 'Participant'; }  else { echo 'You'; } ?> can choose to pay the whole amount now or just a deposit. If <?php if ($in_crm) { echo 'participant chooses'; }  else { echo 'you choose'; } ?> not to make a payment now, <?php if ($in_crm) { echo 'the'; }  else { echo 'your'; } ?> place will not be secured.</p><?php
						}

						if (!$in_crm && $only_subscriptions === FALSE && $includes_subscriptions === FALSE) {
							?><p>The minimum payment amount is <strong><?php echo currency_symbol($this->cart_library->accountID) . number_format($min_payment, 2); ?></strong>. This may include payments due from other bookings.</p><?php
						}

						if ($max_payment < $cart_summary['total'] && $includes_subscriptions) {
							?><p><?php if ($in_crm) { echo 'Participant has'; }  else { echo 'You have'; } ?> a credit in your account of <strong><?php echo currency_symbol($this->cart_library->accountID) . number_format($cart_summary['total'] - $max_payment, 2); ?></strong> which will be applied first to any payment.</p><?php
						}
						if($only_subscriptions){
							?><p><?php if(!$in_crm) echo 'Please set up a subscription, an initial payment of ' . currency_symbol($this->cart_library->accountID) . number_format($cart_summary['total'], 2, '.', '') .' will be taken today.' ?></p>
							<?php
						} else {
							?><div class="form-group">
								<?php
								echo form_label('Payment Amount <em>*</em>', 'payment_amount');
								$flag = NULL;
								$value = $cart_summary['total'];
								if(!empty($cart_summary['subscription_total'])){
									echo '<input type="hidden" name="session_price" id="session_price" value="'.$cart_summary['subtotal'].'"/>';
									echo '<input type="hidden" name="sub_price" id="sub_price" value="'.$cart_summary['subscription_total'].'"/>';
								}
								$remove_gocardless_sub_amount = 0;
								if(isset($cart_summary['subscriptions']) && count($cart_summary['subscriptions'])) {
									foreach ($cart_summary['subscriptions'] as $cart_sub) {
										if ($cart_sub->payment_provider == "stripe") {
											$flag = '1';
										}else{
											$remove_gocardless_sub_amount -= $cart_sub->price;
										}
									}
								}

								if ($min_payment > $cart_summary['total']) {
									$value = $min_payment;
								}else{
									$value = $value + $remove_gocardless_sub_amount;
								}

								if ($value > $max_payment) {
									$value = $max_payment;
								}

							if(!empty($cart_summary['subscription_total'])) {

								$data = array(
									'name' => 'payment_amount',
									'id' => 'payment_amount',
									'class' => 'form-control',
									'readonly' => 'readonly',
									'data-default-value' => number_format($value, 2, '.', ''),
									'value' => number_format(set_value('payment_amount', $value, FALSE), 2, '.', '')
								);
							}else{
								$data = array(
									'name' => 'payment_amount',
									'id' => 'payment_amount',
									'class' => 'form-control',
									'step' => 0.01,
									'min' => $min_payment,
									'max' => $max_payment,
									'data-min-payment' => $min_payment,
									'data-default-value' => number_format($value, 2, '.', ''),
									'value' => number_format(set_value('payment_amount', $value, FALSE), 2, '.', '')
								);
							}
								?><div class="input-group">
									<span class="input-group-addon"><?php echo currency_symbol($this->cart_library->accountID); ?></span>
									<?php echo form_number($data); ?>
								</div>
							</div>
							<?php
							if ($payment_gateway == 'stripe' && !$in_crm) {
								?><div class="card_payment_fields">
									<div id="card-errors" role="alert"></div>
									<div class="form-group">
										<?php echo form_label('Card Number <em>*</em>', 'cardNumber'); ?>
										<div id="cardNumber"></div>
									</div>
									<div class="form-group">
										<?php echo form_label('Expiry Date <em>*</em>', 'cardExpiry'); ?>
										<div id="cardExpiry"></div>
									</div>
									<div class="form-group">
										<?php echo form_label('CVC <em>*</em>', 'cardCvc'); ?>
										<div id="cardCvc"></div>
										<p class="help-block">
											<small class="text-muted">This is usually the 3 digit number on the back of the card.</small>
										</p>
									</div>
								</div><?php
								$master_config_data = [];
								if(isset($cart_summary['subscriptions']) && count($cart_summary['subscriptions'])) {
									foreach ($cart_summary['subscriptions'] as $cart_sub) {
										if ($cart_sub->payment_provider == "stripe") {

											$child_id = '';$contact_id = '';
											if(isset($cart_sub->childID)){
												if(isset($master_config_data[$cart_sub->stripe_price_id]['childID'])){
													$child_id = ','.$cart_sub->childID;
												}else{
													$child_id = $cart_sub->childID;
												}
											}
											if(isset($cart_sub->contactID)){
												if(isset($master_config_data[$cart_sub->stripe_price_id]['contactID'])){
													$contact_id = ','.$cart_sub->contactID;
												}else{
													$contact_id = $cart_sub->contactID;
												}
											}
											if(isset($master_config_data[$cart_sub->stripe_price_id])) {
												$master_config_data[$cart_sub->stripe_price_id]['price'] += $cart_sub->price;
												$master_config_data[$cart_sub->stripe_price_id]['qty'] += 1;
												$master_config_data[$cart_sub->stripe_price_id]['contactID'] .= $contact_id;
												$master_config_data[$cart_sub->stripe_price_id]['childID'] .= $child_id;
											}else{
												$master_config_data[$cart_sub->stripe_price_id]['price'] = $cart_sub->price;
												$master_config_data[$cart_sub->stripe_price_id]['contactID'] = $cart_sub->contactID;
												$master_config_data[$cart_sub->stripe_price_id]['childID'] = $cart_sub->childID;
												$master_config_data[$cart_sub->stripe_price_id]['qty'] = 1;
											}
											$master_config_data[$cart_sub->stripe_price_id]['cartID'] = $cart_sub->cartID;
											$master_config_data[$cart_sub->stripe_price_id]['subID'] = $cart_sub->subID;
											$master_config_data[$cart_sub->stripe_price_id]['stripe_price_id'] = $cart_sub->stripe_price_id;
										}
									}
								}

								$this->load->helper('stripe_helper');
								if($flag == '1') {
									stripe_js($stripe_pk, $flag, json_encode($master_config_data));
								}else{
									stripe_js($stripe_pk);
								}
							}
						}
						if ($allow_payment_plan) {
							?></div>
							<div class="method_fields plan">
								<h4 class="light">Payment Plan</h4>
								<p><?php if ($in_crm) { echo 'Participant is'; }  else { echo 'You are'; } ?> eligible to pay in instalments which will be taken by direct debit, you can choose a plan below.</p>
								<div class="form-group">
									<?php
									echo form_label('Payment Plan <em>*</em>', 'payment_plan');
									$options = array(
										'' => 'Select'
									);
									foreach ($available_payment_plans as $key => $plan) {
										$options[$key] = $plan['label'];
									}
									echo form_dropdown('payment_plan', $options, set_value('payment_plan', NULL, FALSE), 'id="payment_plan" class="form-control select2"');
									?>
								</div>
							</div><?php
						}

					}
					?>
				</fieldset>
				<fieldset>
					<input type="submit" class="btn <?php if ($in_crm) { echo 'btn-primary'; } else { echo 'btn-red'; } ?>" value="Book<?php if ($cart_summary['total'] > 0 && $max_payment > 0) { echo ' &amp; Pay'; } ?>">
				</fieldset>
			<?php echo form_close(); ?>
		</div>
	</div>
	<div class="col-sm-12 col-md-4 summary">
		<?php
		$data = array(
			'checkout' => $checkout,
			'cart_summary' => $cart_summary,
			'max_payment' => $max_payment
		);
		$this->load->view('online-booking/cart/partials/summary');
		?>
	</div>
</div>
