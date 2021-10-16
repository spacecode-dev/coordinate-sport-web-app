<?php
	$mainRowspan = 2;
	if (count($session_types) > 1) {
		$mainRowspan = 3;
	}
?>
<div class='card'>
	<div class="fixed-scrollbar"></div>
	<div class='table-responsive'>
		<table border="1" class='table table-striped table-bordered'>
			<thead>
			<tr>
				<th align="left" rowspan="<?= $mainRowspan ?>">Staff</th>
				<th align="left" rowspan="<?= $mainRowspan ?>">Payroll Number</th>
				<th align="left" rowspan="<?= $mainRowspan ?>">Qualification Level</th>
				<?php if($mileage_section == 1){ ?>
				<th align="left" rowspan="<?= $mainRowspan ?>">Personal Mileage Cost</th>
				<?php } ?>
				<?php if(count($session_types) > 1) { ?>
					<th align="left" colspan="<?= count($session_types) * 3 * 2 ?>">Session Types</th>
				<?php } else if(count($session_types) == 1) { ?>
					<th align="left" colspan="6"><?= $session_types[0]->name ?></th>
				<?php } ?>
				<th align="left" colspan="2">Pay Types</th>
				<th align="left" rowspan="<?= $mainRowspan ?>">Total Hours</th>
				<th align="left" rowspan="<?= $mainRowspan ?>">Total Pay (<?php echo currency_symbol(); ?>)</th>
			</tr>
			<?php if(count($session_types) > 1) { ?>
				<tr>
					<?php foreach ($session_types as $type) {
						echo "<th align='left' colspan='6'>{$type->name}</th>";
					}?>
					<th align="left" rowspan="2"> Salaried </th>
					<th align="left" rowspan="2"> Non-Salaried </th>
				</tr>
			<?php }?>
				<tr>
					<?php foreach ($session_types as $type) {
						echo "<th align='left' colspan='2'>" . $this->settings_library->get_staffing_type_label('head') . "</th>" .
							"<th align='left' colspan='2'>" . $this->settings_library->get_staffing_type_label('lead') . "</th>" .
							"<th align='left' colspan='2'>" . $this->settings_library->get_staffing_type_label('assistant') . "</th>";
					}?>
				</tr>
			</thead>
			<tbody>
			<?php $column_totals = array(); ?>
			<?php foreach ($staff_with_payroll_data as $row) {
				?>
				<tr>
					<td align="left">
						<?php echo $row->first . ' ' . $row->surname; ?>
					</td>
					<td align="left">
						<?php echo $row->payroll_number; ?>
					</td>
					<td align="left">
						<?php echo (($row->qualToDisplay)? $quals[$row->qualToDisplay]->name : ''); ?>
					</td>
					<?php
					if($mileage_section == 1){ 
						if(!array_key_exists('total_personal_mileage_cost', $column_totals)){
							$column_totals['total_personal_mileage_cost'] = 0;
						}
					
						?>
						<td align="left">
							<?php echo currency_symbol() .number_format($row->personal_mileage_cost,2); 
							$column_totals['total_personal_mileage_cost'] += $row->personal_mileage_cost; 
							?>
						</td>
					<?php
					}
					$salaried = $nonsalaried = 0;
					if(count($session_types) > 0) {
						foreach ($session_types as $type) {
							foreach (['head', 'lead', 'assistant'] as $role) {
								$hours = 0;
								$payment = 0;

								if(!isset($column_totals[$type->typeID][$role]['hours'])) {
									$column_totals[$type->typeID][$role]['hours'] = 0;
								}

								if(!isset($column_totals[$type->typeID][$role]['payment'])) {
									$column_totals[$type->typeID][$role]['payment'] = 0;
								}

								if (isset($payroll_data[$row->staffID]['session_type_hours'][$type->typeID][$role])) {
									$hours = $payroll_data[$row->staffID]['session_type_hours'][$type->typeID][$role];
									$payment = $payroll_data[$row->staffID]['total_pay_by_role'][$type->typeID][$role];

									$column_totals[$type->typeID][$role]['hours'] += $hours;
									$column_totals[$type->typeID][$role]['payment'] += $payment;
								}

								echo sprintf('<td align="left">%s</td>', number_format($hours, 2));
								echo sprintf('<td align="left">%s</td>',number_format($payment, 2));
							}
						}
					}?>
					<?php

						if(!array_key_exists('total_salaried', $column_totals)){
							$column_totals['total_salaried'] = 0;
						}
						if(!array_key_exists('total_nonsalaried', $column_totals)){
							$column_totals['total_nonsalaried'] = 0;
						}
						if(!array_key_exists('total_hours', $column_totals)){
							$column_totals['total_hours'] = 0;
						}

						if(!array_key_exists('total_pay', $column_totals)){
							$column_totals['total_pay'] = 0;
						}

						$column_totals['total_salaried'] += $payroll_data[$row->staffID]['salaried'];
						$column_totals['total_nonsalaried'] += $payroll_data[$row->staffID]['nonsalaried'];
						$column_totals['total_hours'] += $payroll_data[$row->staffID]['hours'];
						$column_totals['total_pay'] += $payroll_data[$row->staffID]['total_pay'] - $row->personal_mileage_cost;
					?>
					<td align="left">
						<?php echo number_format($payroll_data[$row->staffID]['salaried'], 2); ?>
					</td>
					<td align="left">
						<?php echo number_format($payroll_data[$row->staffID]['nonsalaried'], 2); ?>
					</td>
					<td align="left">
						<?php echo number_format($payroll_data[$row->staffID]['hours'], 2); ?>
					</td>
					<td align="left">
						<?php echo number_format($payroll_data[$row->staffID]['total_pay']  - $row->personal_mileage_cost, 2); ?>
					</td>
				</tr><?php
			}
			if (count($column_totals) > 0) {
				?><tr>
					<td align="left"><strong>Totals</strong></td>
					<td></td>
					<td></td>
					<?php if($mileage_section == 1){ ?>
					<td align="left">
						<?php echo number_format($column_totals['total_personal_mileage_cost'], 2); ?>
					</td>
					<?php } ?>
					<?php
						if(count($session_types) > 0) {
							foreach($session_types as $type) {
								foreach (['head', 'lead', 'assistant'] as $role) {
									echo sprintf('<td align="left">%s</td>', number_format($column_totals[$type->typeID][$role]['hours'], 2));
									echo sprintf('<td align="left">%s</td>', number_format($column_totals[$type->typeID][$role]['payment'], 2));
								}
							}
						}
					?>
					<td align="left">
						<?php echo number_format($column_totals['total_salaried'], 2); ?>
					</td>
					<td align="left">
						<?php echo number_format($column_totals['total_nonsalaried'], 2); ?>
					</td>
					<td align="left">
						<?php echo number_format($column_totals['total_hours'], 2); ?>
					</td>
					<td align="left">
						<?php echo number_format($column_totals['total_pay'], 2); ?>
					</td>
				</tr><?php
			}
			?>
			</tbody>
		</table>
	</div>
</div>
