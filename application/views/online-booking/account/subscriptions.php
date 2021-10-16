<?php display_messages('fas'); ?>
<?php
if ($subscriptions->num_rows() > 0) {
	?><div class="table-responsive">
  		<table class="table table-striped table-bordered">
		  	<thead>
				<tr>
					<th scope="col">Subscription Name</th>
					<th scope="col">Participant Name</th>
					<th scope="col">Price</th>
					<th scope="col">Frequency</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($subscriptions->result() as $row): ?>
					<tr>
						<td><?php echo $row->subName ?></td>
						<td><?php if($row->first_name != NULL) {
							echo $row->first_name . ' ' . $row->last_name;
						} else {
							echo $row->contact_first_name . ' ' . $row->contact_last_name;
						} ?></td>
						<td><?php echo currency_symbol($this->cart_library->accountID) . number_format($row->price, 2); ?></td>
						<td><?php echo ucfirst($row->frequency) ?></td>
						<td>
							<a href="<?php echo site_url('account/subscription/' . $row->psID); ?>" class="btn">View Details</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div><?php
}
