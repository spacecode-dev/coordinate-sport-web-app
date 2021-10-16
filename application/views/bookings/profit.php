<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
if (count($blocks) == 0) {
	?>
	<div class="alert alert-info">
		No data
	</div>
	<?php
} else {
	?><div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered profit'>
				<thead>
					<tr>
						<th></th>
						<?php
						$counter = count($blocks);
						if($week != 0)
							$counter = 1;
						if($week == 0){
						foreach ($blocks as $blockID => $name) {
							?><th>
								<?php echo $name; ?>
							</th><?php
						}
						}else{
						?>
						<th>
							<?php  $dt = new DateTime;
							echo "Week ".$week." (".$dt->setISODate($year, $week, 1)->format('jS M').")"; ?>
						</th>
						<?php } ?>
						<th>
							Total
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Revenue</strong></td>
						<td colspan="<?php echo $counter + 1; ?>">&nbsp;</td>
					</tr>
					<?php
					$block_income = array();
					if (count($session_income) > 0) {
						ksort($session_income);
						foreach ($session_income as $type => $block_details) {
							?><tr>
								<td><?php echo $type; ?></td>
								<?php
								$row_total = 0;
								foreach ($blocks as $blockID => $name) {
									$val = 0;
									if (isset($session_income[$type][$blockID])) {
										$val = $session_income[$type][$blockID];
									}
									if(isset($exception_refund[$type][$blockID])){
										$val -= $exception_refund[$type][$blockID];
									}
									if($week == 0){
									?><td><?php
									echo number_format($val, 2);
									}
									// add to row total
									$row_total += $val;
									// add to col total
									if (!isset($block_income[$blockID])) {
										$block_income[$blockID] = 0;
									}
									$block_income[$blockID] += $val;
									if($week == 0){
									?></td><?php
									}
								}
								if($week != 0){ ?>
								<td><?php echo number_format($row_total, 2); ?></td>
								<?php } ?>
 								<td><?php echo number_format($row_total, 2); ?></td>
							</tr><?php
						}
					}
					?>
					<tr>
						<td>Subscriptions</td>
						<td colspan="<?php echo $counter;?>>">
						<td><?php
							$subscription_price = 0.00;
							if($sub_total->num_rows() > 0) {
								foreach ($sub_total->result() as $payment) {
									$subscription_price += empty($payment->amount) ? 0.00 : $payment->amount;
								}
							}
							echo number_format($subscription_price, 2);
							?></td>
					</tr>
					<tr>
						<td>Misc.</td>
						<?php
						$row_total = 0;
						foreach ($blocks as $blockID => $name) {
							$val = 0;
							if (isset($misc_income[$blockID])) {
								$val = $misc_income[$blockID];
							}
							if($week == 0){
							?><td><?php
							echo number_format($val, 2);
							}
							// add to row total
							$row_total += $val;
							// add to col total
							if (!isset($block_income[$blockID])) {
								$block_income[$blockID] = 0;
							}
							$block_income[$blockID] += $val;
							if($week == 0){
							?></td><?php
							}
						}

						if($week != 0){ ?>
						<td><?php echo number_format($row_total, 2); ?></td>
						<?php } ?>
						<td><?php echo number_format($row_total, 2); ?></td>
					</tr>
					<?php
					if (count($contract_income) > 0) {
						ksort($contract_income);
						?><tr>
							<td><strong>Contract Revenue</strong></td>
							<td colspan="<?php echo $counter + 1; ?>">&nbsp;</td>
						</tr><?php
						foreach ($contract_income as $type => $val) {
							?><tr>
								<td><?php echo $type; ?></td>
								<td colspan="<?php echo $counter; ?>">&nbsp;</td>
								<td><?php echo number_format($val, 2); ?></td>
							</tr><?php
						}
					}
					?>
					<tr>
						<td><strong>Total Revenue</strong></td>
						<?php
						$row_total = 0;
						foreach ($blocks as $blockID => $name) {
							$val = 0;
							if (isset($block_income[$blockID])) {
								$val = $block_income[$blockID];
							}
							if($week == 0){
							?><td><?php
							echo number_format($val, 2);
							}
							// add to row total
							$row_total += $val;
							if($week == 0){
							?></td><?php
							}
						}
						$row_total += array_sum($contract_income);
						$row_total += $subscription_price;
						if($week != 0){
						?>
						<td><?php echo number_format($row_total, 2); ?></td>
						<?php } ?>
						<td><?php echo number_format($row_total, 2); ?></td>
					</tr>
					<tr>
						<td><strong>Costs</strong></td>
						<td colspan="<?php echo $counter + 1; ?>">&nbsp;</td>
					</tr>
					<?php
					$block_costs = array();
					if (count($staff_costs) > 0) {
						ksort($staff_costs);
						foreach ($staff_costs as $type => $block_details) {
							?><tr>
								<td>Staff - <?php echo $type; ?></td>
								<?php
								$row_total = 0;
								foreach ($blocks as $blockID => $name) {
									$val = 0;
									if (isset($staff_costs[$type][$blockID])) {
										$val = $staff_costs[$type][$blockID];
									}
									if($week == 0){
									?><td><?php
									echo number_format($val, 2);
									}
									// add to row total
									$row_total += $val;
									// add to col total
									if (!isset($block_costs[$blockID])) {
										$block_costs[$blockID] = 0;
									}
									$block_costs[$blockID] += $val;
									if($week == 0){
									?></td><?php
									}
								}
								if($week != 0){
								?>
								<td><?php echo number_format($row_total, 2); ?></td>
								<?php } ?>
								<td><?php echo number_format($row_total, 2); ?></td>
							</tr><?php
						}
					}
					if (count($costs) > 0) {
						foreach ($costs as $category => $block_details) {
							?><tr>
								<td><?php echo $category; ?></td>
								<?php
								$row_total = 0;
								foreach ($blocks as $blockID => $name) {
									$val = 0;
									if (isset($costs[$category][$blockID])) {
										$val = $costs[$category][$blockID];
									}
									if($week == 0){
									?><td><?php
									echo number_format($val, 2);
									}
									// add to row total
									$row_total += $val;
									// add to col total
									if (!isset($block_costs[$blockID])) {
										$block_costs[$blockID] = 0;
									}
									$block_costs[$blockID] += $val;
									if($week == 0){
									?></td><?php
									}
								}
								if($week != 0){
								?>
								<td><?php echo number_format($row_total, 2); ?></td>
								<?php } ?>
								<td><?php echo number_format($row_total, 2); ?></td>
							</tr><?php
						}
					}
					?>
					<tr>
						<td><strong>Total Costs</strong></td>
						<?php
						$row_total = 0;
						foreach ($blocks as $blockID => $name) {
							$val = 0;
							if (isset($block_costs[$blockID])) {
								$val = $block_costs[$blockID];
							}
							if($week == 0){
							?><td><?php
							echo number_format($val, 2);
							}
							// add to row total
							$row_total += $val;
							if($week == 0){
							?></td><?php
							}
						}
						if($week != 0){ ?>
						<td><?php echo number_format($row_total, 2); ?></td>
						<?php } ?>
						<td><?php echo number_format($row_total, 2); ?></td>
					</tr>
					<tr>
						<td><strong>Profit/Loss</strong></td>
						<?php
						$row_total = 0;
						foreach ($blocks as $blockID => $name) {
							if($week == 0){
							?><td><?php
							}
							$val = 0;
							if (isset($block_income[$blockID])) {
								$val = $block_income[$blockID];
							}
							if (isset($block_costs[$blockID])) {
								$val -= $block_costs[$blockID];
							}
							if($week == 0){
								if ($val < 0) {
									?><span class="text-red"><?php
								}
								echo number_format($val, 2);
								if ($val < 0) {
									?></span><?php
								}
							}
							// add to row total
							$row_total += $val;
							if($week == 0){
							?></td><?php
							}
						}
						if($week != 0){
						?>
						<td><?php
						$row_total += array_sum($contract_income);
						$row_total += $subscription_price;
						if ($row_total < 0) {
							?><span class="text-red"><?php
						}
						echo number_format($row_total, 2);
						if ($row_total < 0) {
							?></span><?php
						}
						?></td>
						<?php } ?>
						<td><?php
						$row_total += array_sum($contract_income);
						$row_total += $subscription_price;
						if ($row_total < 0) {
							?><span class="text-red"><?php
						}
						echo number_format($row_total, 2);
						if ($row_total < 0) {
							?></span><?php
						}
						?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div><?php
}
