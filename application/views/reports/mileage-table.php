<?php

$rowspan = "";
if($search_fields['filter_by_mode_of_transport'] == 1 || $search_fields['filter_by_activate_fuel_card'] == 1)
	$rowspan = " rowspan = '2'";
?>
<div class='card'>
	<div class="fixed-scrollbar"></div>
	<div class='table-responsive'>
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
					<th <?php echo $rowspan ?>>
						Staff
					</th>
					<th <?php echo $rowspan ?>>
						Payroll Number
					</th>
					<?php 
					if($search_fields['filter_by_mode_of_transport'] == 1){ 
						foreach($mileage_mode->result() as $result){
							echo "<th colspan='2'>".$result->name."</th>";
						}
					}
					?>
					<th <?php echo $rowspan ?>>
						Total Business Mileage
					</th>
					<th <?php echo $rowspan ?>>
						Total Amount
					</th>
					<?php
					if($mileage_activate_fuel_cards == 1 && $search_fields['filter_by_activate_fuel_card'] == 1){
						echo "<th colspan = '2'> Fuel Card </th>";
					}
					?>
					<th <?php echo $rowspan ?>>
						Total Mileage
					</th>
				</tr>
				<?php
				if($search_fields['filter_by_mode_of_transport'] == 1 || $search_fields['filter_by_activate_fuel_card'] == 1){ 
					echo "<tr>";
					if($search_fields['filter_by_mode_of_transport'] == 1){
						foreach($mileage_mode->result() as $result){
							echo "<th> Business Mileage </th>";
							echo "<th> Amount </th>";
						}
					}
					if($search_fields['filter_by_activate_fuel_card'] == 1){
						echo "<th> Personal Mileage </th>";
						echo "<th> Deduction </th>";
					}
					echo "</tr>";
				}
				?>
			</thead>
			<tbody>
				<?php
				$count = 0;
				$overall_mileage = 0;
				$overall_amount = 0;
				$overall_business_mileage = 0;
				$overall_personal_mileage = $overall_personal_mileage_cost = 0;
				foreach ($staff as $row) {
					$overall_business_mileage = 0;
					if (!array_key_exists($row->staffID, $mileage_data) || $row->activate_mileage != 1) {
						continue;
					}
					$count++;
					?>
					<tr>
						<td>
							<?php echo $row->first . ' ' . $row->surname; ?>
						</td>
						<td>
							<?php echo $row->payroll_number; ?>
						</td>
						<?php 
						if($search_fields['filter_by_mode_of_transport'] == 1){  
							$total_mileage = 0;
							$total_amount = 0;
							foreach($mileage_mode->result() as $result){
								$mileage = isset($mileage_data[$row->staffID][$result->mileageID]["mileage"])?$mileage_data[$row->staffID][$result->mileageID]["mileage"]:0;
								$amount = isset($mileage_data[$row->staffID][$result->mileageID]["amount"])?$mileage_data[$row->staffID][$result->mileageID]["amount"]:0;
								$total_mileage += $mileage;
								$total_amount += $amount;
								echo "<td>".number_format($mileage,2)."</td>";
								echo "<td>".number_format($amount,2)."</td>";
							}
							echo "<td>".number_format($total_mileage,2)."</td>";
							echo "<td>".number_format($total_amount,2)."</td>";
							$overall_business_mileage += $total_mileage;
						}else{ 
							$overall_mileage += $mileage_data[$row->staffID]["mileage"];
							$overall_amount += $mileage_data[$row->staffID]["amount"];
							$overall_business_mileage += $mileage_data[$row->staffID]["mileage"];
							?>
						<td>
							<?php echo number_format($mileage_data[$row->staffID]["mileage"],2) ?>
						</td>
						<td>
							<?php echo number_format($mileage_data[$row->staffID]["amount"],2) ?>
						</td>
						<?php } ?>
						<?php
						if($mileage_activate_fuel_cards == 1 && $search_fields['filter_by_activate_fuel_card'] == 1){
							$overall_personal_mileage += isset($personal_mileage_cost[$row->staffID]["overall_mileage"])?$personal_mileage_cost[$row->staffID]["overall_mileage"]:0.00;
							$overall_personal_mileage_cost += isset($personal_mileage_cost[$row->staffID]["overall_personal_cost"])?$personal_mileage_cost[$row->staffID]["overall_personal_cost"]:0.00;
							echo "<td>".(isset($personal_mileage_cost[$row->staffID]["overall_mileage"])?number_format($personal_mileage_cost[$row->staffID]["overall_mileage"],2):'0.00')."</td>";
							echo "<td>".(isset($personal_mileage_cost[$row->staffID]["overall_personal_cost"])?number_format($personal_mileage_cost[$row->staffID]["overall_personal_cost"],2):'0.00')."</td>";
						}
						?>
						<td>
							<?php echo number_format($overall_business_mileage,2) ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>