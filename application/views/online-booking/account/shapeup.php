<?php
display_messages('fas');
if ($bookings->num_rows() > 0) {
	?><div class="table-responsive">
  		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col">Date(s)</th>
					<th scope="col">Event</th>
					<th scope="col" class="min">View Progress</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($bookings->result() as $row) {
					?><tr>
						<td><?php
							echo mysql_to_uk_date($row->startDate);
							if (strtotime($row->startDate) < strtotime($row->endDate)) {
								echo " to " . mysql_to_uk_date($row->endDate);
							}
						?></td>
						<td><?php echo $row->name; ?></td>
						<td><a href="<?php echo site_url('account/shapeup/' . $row->cartID); ?>#details" class="btn btn-small">View Progress</a></td>
					</tr><?php
				}
				?>
			</tbody>
		</table>
	</div><?php
}
