<?php display_messages('fas'); ?>
<?php
if ($payments->num_rows() > 0) {
	?><div class="table-responsive">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th scope="col">Booking Date</th>
			<th scope="col">Participants</th>
			<th scope="col">Amount</th>
			<th scope="col">Balance Due</th>
			<th scope="col">Booked By</th>
			<th scope="col">View Details</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($payments->result() as $row) {
			?><tr>
			<td><?php echo mysql_to_uk_datetime($row->booked); ?></td>
			<td>
				<?php
				if(empty($row->child_names) && empty($row->individual_names)){
					echo $row->contact_first.' '.$row->contact_last;
				}else{
					$participants = array_merge((array)explode(",", $row->child_names), (array)explode(",", $row->individual_names));
					$participants = array_filter($participants);
					sort($participants);
					echo implode(', ', $participants);
				}
				?>
			</td>
			<td><?php
				if ($row->total > 0) {
					echo currency_symbol($this->cart_library->accountID) . number_format($row->total, 2);
				} else if($row->subtotal > 0 && $row->discount == 0) {
					echo currency_symbol($this->cart_library->accountID) . number_format($row->subtotal, 2);
				}else if($row->subscription_total > 0 && $row->discount == 0) {
					echo currency_symbol($this->cart_library->accountID) . number_format($row->subscription_total, 2);
				}else{
					echo 'Free';
				}
				if ($row->childcarevoucher_providerID > 0) {
					echo ' (Childcare Voucher)';
				}
				?></td>
			<td><?php echo currency_symbol($this->cart_library->accountID) . number_format($row->balance, 2); ?></td>
			<td><?php echo $row->contact_first . ' ' . $row->contact_last; ?></td>
			<td><a href="<?php echo site_url('account/booking/' . $row->cartID); ?>#details" class="btn btn-small">View Details</a></td>
			</tr><?php
		}
		?>
		</tbody>
	</table>
	</div><?php
}
