<?php display_messages('fas'); ?>
<?php
if ($payment_plans->num_rows() > 0) {
	?><div class="table-responsive">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th scope="col">Start Date</th>
			<th scope="col">Contact</th>
			<th scope="col">Total Amount</th>
			<th scope="col">Plan</th>
			<th scope="col">Status</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($payment_plans->result() as $row) {
			?><tr>
			<td><?php echo mysql_to_uk_datetime($row->added); ?></td>
			<td><?php echo $row->first_name . ' ' . $row->last_name; ?></td>
			<td><?php echo currency_symbol($this->cart_library->accountID) . number_format($row->amount, 2); ?></td>
			<td><?php echo $row->interval_count . ' ' . ucwords($row->interval_unit) . 'ly Payments'; ?></td>
			<td><?php
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
				<span class="label label-<?php echo $label_colour; ?>"><?php echo ucwords($row->status); ?></span></td>
			</tr><?php
		}
		?>
		</tbody>
	</table>
	</div><?php
}
