<?php
display_messages();
?>

<div class='card card-custom'>
	<div class='table-responsive'>
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
					<th> Name </th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo anchor('participants/tools/childrenwithoutbookings', 'Participants with no bookings', 'class="confirm"'); ?></td>
					<td><?php echo anchor('participants/tools/childrenwithoutbookings', '<i class="far fa-trash"> </i>', 'class="confirm btn btn-danger btn-sm"'); ?></td>
				</tr>
				<tr>
					<td><?php echo anchor('participants/tools/parentswithoutchildren', 'Account Holders without Participants', 'class="confirm"'); ?></td>
					<td><?php echo anchor('participants/tools/parentswithoutchildren', '<i class="far fa-trash"> </i>', 'class="confirm btn btn-danger btn-sm"'); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>