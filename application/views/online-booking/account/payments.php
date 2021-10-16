<?php display_messages('fas'); ?>
<?php
if ($this->cart_library->get_family_account_balance() < 0) {
	?><p><a href="<?php echo site_url('account/pay'); ?>#details" class="btn">Make Payment</a></p><?php
}
if ($payments->num_rows() > 0) {
	?><div class="table-responsive">
  		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col">Date</th>
					<th scope="col">Amount</th>
					<th scope="col">Received From</th>
					<th scope="col">Payment Method</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($payments->result() as $row) {
					?><tr>
						<td><?php echo mysql_to_uk_datetime($row->added); ?></td>
						<td><?php echo currency_symbol($this->cart_library->accountID) . number_format($row->amount, 2); ?></td>
						<td><?php
						if ($row->internal == 1) {
							echo 'Internal';
						} else {
							echo $row->first_name . ' ' . $row->last_name;
						}
						?></td>
						<td><?php
						switch ($row->method) {
							case 'card':
								echo 'Credit/Debit Card';
								break;
							default:
								echo ucwords($row->method);
								break;
						}
						if (!empty($row->transaction_ref)) {
							echo "<br />Ref: " . $row->transaction_ref;
						}
						?></td>
					</tr><?php
				}
				?>
			</tbody>
		</table>
	</div><?php
}
