<?php
display_messages('fas');

// shape up vars
$target = 0.05;
$lbs = 2.20462;
$target_loss_kg = 0;
$target_loss_lbs = 0;
$target_weight_kg = 0;
$target_weight_lbs = 0;
$current_loss_kg = 0;
$current_loss_lbs = 0;
$percent_lost = 0;
$first_weight = 0;
$last_weight = 0;
?><h3 class="h4 with-line"><?php echo $event; ?></h3>
<div class="table-responsive">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th scope="col" rowspan="2">Date</th>
				<th scope="col" colspan="2">Weight</th>
			</tr>
			<tr>
				<th scope="col" class="text-center">kg</th>
				<th scope="col" class="text-center">lbs</th>
			</tr>
		</thead>
		<tbody><?php
		if ($sessions->num_rows() > 0) {
			$i = 0;
			foreach ($sessions->result() as $row) {
				?><tr>
					<td><?php
						if (empty($row->date)) {
							echo ucwords($row->day);
						} else {
							echo mysql_to_uk_date($row->date);
						}
					?></td>
					<?php
					$weight = $row->shapeup_weight;
					if ($i == 0) {
						$first_weight = $weight;
					} else if ($weight > 0){
						$last_weight = $weight;
					}
					if ($row->attended == 1) {
						?><td class="text-center"><?php echo number_format($row->shapeup_weight, 1); ?></td>
						<td class="text-center"><?php echo number_format($row->shapeup_weight*$lbs, 1); ?></td><?php
					} else {
						?><td colspan="2" class="text-center">Not Attended</td><?php
					}
					?>
				</tr><?php
				$i++;
			}
			if ($first_weight > 0) {
				$target_loss_kg = $first_weight*$target;
				$target_loss_lbs = $target_loss_kg*$lbs;
				$target_weight_kg = $first_weight*(1-$target);
				$target_weight_lbs = $target_weight_kg*$lbs;
				if ($last_weight > 0) {
					$current_loss_kg = $last_weight-$first_weight;
					$current_loss_lbs = $current_loss_kg*$lbs;
					$percent_lost = ($current_loss_kg/$first_weight)*100;
				}
			}
			?><tr>
				<th colspan="3">Aims/Totals</th>
			</tr>
			<tr>
				<td>5% Weight Loss</td>
				<td class="text-center"><?php echo number_format($target_loss_kg, 1); ?></td>
				<td class="text-center"><?php echo number_format($target_loss_lbs, 1); ?></td>
			</tr>
			<tr>
				<td>Target Weight</td>
				<td class="text-center"><?php echo number_format($target_weight_kg, 1); ?></td>
				<td class="text-center"><?php echo number_format($target_weight_lbs, 1); ?></td>
			</tr>
			<tr>
				<td>Current Weight Loss</td>
				<td class="text-center"><?php echo number_format($current_loss_kg, 1); ?></td>
				<td class="text-center"><?php echo number_format($current_loss_lbs, 1); ?></td>
			</tr>
			<tr>
				<td>Weight Lost</td>
				<td colspan="2" class="text-center<?php
				if ($percent_lost <= -5) {
					echo ' green';
				} else if ($percent_lost <= -2.5){
					echo ' orange';
				} else if ($percent_lost == 0) {
					// zero change
				} else {
					echo ' red';
				}
				?>"><?php echo number_format($percent_lost, 1); ?>%</td>
			</tr><?php
		} else {
			?><tr>
				<td colspan="3"><em>No sessions recorded yet</em></td>
			</tr><?php
		}
		?></tbody>
	</table>
</div>
<p><a href="<?php echo site_url('account/shapeup'); ?>#details" class="btn">Return to List</a></p><?php
