<fieldset>
<h3 class="light">Summary</h3>
<p>Sub Total&nbsp;&nbsp;&nbsp;
<span class="price"><?php echo currency_symbol($this->cart_library->accountID) . number_format($cart_summary['subtotal'], 2); ?></span>
</p>
<?php
if($cart_summary['subscription_total'] > 0):
	$subscriptions = array('weekly'=>0, 'monthly'=>0, 'yearly'=>0);
	foreach($cart_summary['subscriptions'] as $sub){
		$subscriptions[$sub->frequency] += number_format($sub->price, 2);
	}
	foreach ($subscriptions as $index=>$price){
		if($price > 0) {
			echo '<p>' . ucfirst($index) . ' Subscription Total&nbsp;&nbsp;&nbsp;
			<span class="price">' . currency_symbol($this->cart_library->accountID) . number_format($price, 2) . '</span>
			</p>';
		}
	}
	?>
<?php endif; ?>
<div class="vouchers">
	<p>Do you have a voucher code? <span title="If a voucher code is entered, any automatic discount will be removed"><i class="<?php echo $fa_weight; ?> fa-info-circle"></i></span></p>
	<?php echo form_open(); ?>
		<div class="flex nowrap">
			<div class="form-group">
				<?php
				$data = array(
					'name' => 'voucher',
					'id' => 'voucher',
					'class' => 'form-control'
				);
				echo form_input($data);
				?>
			</div>
			<button class='btn <?php if ($in_crm) { echo 'btn-primary'; } else { echo 'btn-fixed-height'; } ?>'>Apply</button>
		</div>
	<?php echo form_close(); ?>
	<?php
	if ($cart_summary['vouchers'] !== FALSE) {
		echo '<ul>';
		foreach ($cart_summary['vouchers'] as $id => $code) {
			echo '<li><a href="' . site_url($cart_base . ($checkout ? 'checkout' : 'cart') . '/removevoucher/' . $id) . '" title="Remove Voucher" class="remove"><i class="' . $fa_weight . ' fa-times"></i></a> ' . $code . '</li>';
		}
		echo '</ul>';
	}
	?>
</div><?php
if ($cart_summary['discount'] > 0) {
	?><p>
		Discount&nbsp;&nbsp;&nbsp;
		<span class="price">-<?php echo currency_symbol($this->cart_library->accountID) . number_format($cart_summary['discount'], 2); ?></span>
	</p><?php
}
?>
<p>
	Total&nbsp;&nbsp;&nbsp;
	<span class="price"><?php echo currency_symbol($this->cart_library->accountID) . number_format($cart_summary['total'], 2); ?></span>
</p>
<?php
if ($this->cart_library->cart_type == 'booking') {

	echo form_open();
	?><hr />
	<h3 class="light">Confirmation</h3>
	<div class="form-group">
		<?php
		$options = array(
			'future' => 'Current & Future Sessions',
			'all' => 'All Sessions'
		);
		echo form_dropdown('resend_confirmation', $options, set_value('resend_confirmation', NULL, FALSE), 'id="resend_confirmation" class="form-control select2"');
		?>
	</div>
	<input type="submit" class="btn btn-primary" value="Resend Confirmation" /><?php
	echo form_close();
	?><hr />
	<h3 class="light">Account Balance&nbsp;&nbsp;&nbsp;<span class="price"><?php echo currency_symbol($this->cart_library->accountID) . number_format($this->cart_library->get_family_account_balance(), 2); ?> </span></h3>
	<p>
		<a href="<?php echo site_url('participants/payments/' . $this->cart_library->familyID . '/new'); ?>" class="btn btn-primary">Make Payment</a>
	</p><?php
} else {
	if ($checkout === TRUE) {
		?><button class="btn submit-checkout <?php if ($in_crm) { echo 'btn-primary'; } else { echo 'btn-block btn-red'; } ?>">Book<?php if ($cart_summary['total'] > 0 && $max_payment > 0) { echo ' &amp; Pay'; } ?></button>
		<p class="text-right"><a href="<?php echo site_url($cart_base . 'cart'); ?>" class="remove">Return to cart</a></p><?php
	} else {
		?><a href="<?php echo site_url($cart_base . 'checkout'); ?>" class="btn <?php if ($in_crm) { echo 'btn-primary'; } else { echo 'btn-block btn-red'; } ?>">Checkout</a>
		<p class="text-right"><a href="<?php echo site_url($cart_base . 'cart/empty'); ?>" class="remove">Clear cart</a></p><?php
	}
	if ($in_crm) {
		?><hr />
		<h3 class="light">Account Balance&nbsp;&nbsp;&nbsp;
			<span class="price"><?php echo currency_symbol($this->cart_library->accountID) . number_format($this->cart_library->get_family_account_balance(), 2); ?></span>
		</h3>
		<?php
	}
}
?></fieldset>
