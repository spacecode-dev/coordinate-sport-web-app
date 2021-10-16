<?php display_messages('fas');
if ($prevent_payment !== TRUE) {
	?><?php echo form_open('account/pay#details', array(
		'id' => 'pay'
	)); ?>
		<fieldset>
			<p>The minimum payment amount is <strong><?php echo currency_symbol($this->cart_library->accountID) . number_format($min_payment, 2); ?></strong>.</p>
			<div class="form-group">
				<?php
				echo form_label('Payment Amount <em>*</em>', 'payment_amount');
				$data = array(
					'name' => 'payment_amount',
					'id' => 'payment_amount',
					'class' => 'form-control',
					'step' => 0.01,
					'min' => $min_payment,
					'max' => $max_payment,
					'data-min-payment' => $min_payment,
					'value' => round(set_value('payment_amount', $min_payment, FALSE), 2)
				);
				?><div class="input-group">
					<span class="input-group-addon"><?php echo currency_symbol($this->cart_library->accountID); ?></span>
					<?php echo form_number($data); ?>
				</div>
			</div>
			<?php
			if ($payment_gateway == 'stripe') {
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
				$this->load->helper('stripe_helper');
				stripe_js($stripe_pk);
			}
			?>
			<input type="submit" class="btn" value="Pay">
		</fieldset><?php
	echo form_close();
}
