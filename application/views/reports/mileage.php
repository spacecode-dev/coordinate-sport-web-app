<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'timesheets-report-search']); ?>
	<div class="card-header">
		<div class="card-title">
			<h3 class="card-label">Search</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class='row'>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_from',
					'id' => 'field_date_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_to',
					'id' => 'field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_staff_id">Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if (count($staff_All) > 0) {
					foreach ($staff_All as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
				?>
			</div>
		</div>
		<div class='row'>
			<div class='col-sm-3'>
				<p>
					<strong><label for="field_filter_by">Filter By</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'filter_by_mode_of_transport',
					'value' => 1,
					'id' => 'filter_by_mode_of_transport_id'
				);
				if ($search_fields['filter_by_mode_of_transport'] == 1) {
					$data['checked'] = true;
				}

				?>
				<div>
					<?php echo form_checkbox($data); ?>
					<label for="filter_by_mode_of_transport_id">
						Default mode of transport
					</label>
				</div>
				<?php
				if($mileage_activate_fuel_cards == 1){
					$data = array(
						'name' => 'filter_by_activate_fuel_card',
						'value' => 1,
						'id' => 'filter_by_activate_fuel_card'
					);
					if ($search_fields['filter_by_activate_fuel_card'] == 1) {
						$data['checked'] = true;
					}

					?>
					<div>
						<?php echo form_checkbox($data); ?>
						<label for="filter_by_activate_fuel_card">
							Show Fuel Card Mileage
						</label>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-search'></i> Search
			</button>
			<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
				Cancel
			</a>
		</div>
	</div>
	<?php echo form_hidden('search', 'true'); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
$has_results = FALSE;
if (count($staff) > 0) {
	foreach ($staff as $row) {
		// check if has data for this staff member
		if (array_key_exists($row->staffID, $mileage_data)) {
			$has_results = TRUE;
			break;
		}
	}
}
if ($has_results !== TRUE || (count($mileage_data) == 0)) {
	?>
	<div class="alert alert-info">
		<i class="far fa-info-circle"></i>
		No data found.
	</div>
	<?php
} else {
	
	$rowspan = "";
	if($search_fields['filter_by_mode_of_transport'] == 1 || $search_fields['filter_by_activate_fuel_card'] == 1)
		$rowspan = " rowspan = '2'";
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
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
					// When Default Transport mode is checked 
					$overall_mileage_by_mode = array();
					$overall_amount_by_mode = array();
					$overall_personal_mileage = $overall_personal_mileage_cost = 0;
					
					foreach($mileage_mode->result() as $result){
						$overall_mileage_by_mode[$result->mileageID] = 0;
						$overall_amount_by_mode[$result->mileageID] = 0;
					}
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
									echo "<td>".currency_symbol() .number_format($amount,2)."</td>";
									$overall_mileage_by_mode[$result->mileageID] += $mileage;
									$overall_amount_by_mode[$result->mileageID] += $amount;
								}
								echo "<td>".number_format($total_mileage,2)."</td>";
								echo "<td>".currency_symbol() .number_format($total_amount,2)."</td>";
								$overall_business_mileage += $total_mileage;
								$overall_mileage += $total_mileage;
								$overall_amount += $total_amount;
							}else{ 
								$overall_mileage += $mileage_data[$row->staffID]["mileage"];
								$overall_amount += $mileage_data[$row->staffID]["amount"];
								$overall_business_mileage += $mileage_data[$row->staffID]["mileage"];
							?>
							<td>
								<?php echo number_format($mileage_data[$row->staffID]["mileage"],2) ?>
							</td>
							<td>
								<?php echo currency_symbol() .number_format($mileage_data[$row->staffID]["amount"],2) ?>
							</td>
							<?php } ?>
							<?php
							if($mileage_activate_fuel_cards == 1 && $search_fields['filter_by_activate_fuel_card'] == 1){
								$overall_personal_mileage += isset($personal_mileage_cost[$row->staffID]["overall_mileage"])?$personal_mileage_cost[$row->staffID]["overall_mileage"]:0.00;
								$overall_personal_mileage_cost += isset($personal_mileage_cost[$row->staffID]["overall_personal_cost"])?$personal_mileage_cost[$row->staffID]["overall_personal_cost"]:0.00;
								echo "<td>".(isset($personal_mileage_cost[$row->staffID]["overall_mileage"])?number_format($personal_mileage_cost[$row->staffID]["overall_mileage"],2):'0.00')."</td>";
								echo "<td>".currency_symbol() .(isset($personal_mileage_cost[$row->staffID]["overall_personal_cost"])?number_format($personal_mileage_cost[$row->staffID]["overall_personal_cost"],2):'0.00')."</td>";
								
								$overall_business_mileage += (isset($personal_mileage_cost[$row->staffID]["overall_mileage"])?$personal_mileage_cost[$row->staffID]["overall_mileage"]:'0.00');
							}
							?>
							<td>
								<?php echo number_format($overall_business_mileage,2) ?>
							</td>
						</tr>
					<?php
					}
					if($count > 0){
						$overall_business_mileage = 0;
						echo "<tr>
							<td> Total </td>
							<td> </td>";
						$total_mileage = 0;
						$total_amount = 0;
						if($search_fields['filter_by_mode_of_transport'] == 1){
							foreach($mileage_mode->result() as $result){
								echo "<td>".number_format($overall_mileage_by_mode[$result->mileageID],2)."</td>";
								echo "<td>".currency_symbol() .number_format($overall_amount_by_mode[$result->mileageID],2)."</td>";
								$total_mileage += $overall_mileage_by_mode[$result->mileageID];
								$total_amount += $overall_amount_by_mode[$result->mileageID];
							}
							echo "<td>".number_format($total_mileage,2)."</td>";
							echo "<td>".currency_symbol() .number_format($total_amount,2)."</td>";
							$overall_business_mileage += $total_mileage;
						}else{
							echo "<td>".number_format($overall_mileage,2)."</td>";
							echo "<td>".currency_symbol() .number_format($overall_amount,2)."</td>";
							$overall_business_mileage += $overall_mileage;
						}
						if($mileage_activate_fuel_cards == 1 && $search_fields['filter_by_activate_fuel_card'] == 1){
							echo "<td>".number_format($overall_personal_mileage,2)."</td>";
							echo "<td>".currency_symbol() .number_format($overall_personal_mileage_cost,2)."</td>";
						}
						echo "<td>".number_format($overall_business_mileage + $overall_personal_mileage,2)."</td>";
						echo "</tr>";
					}
					?>
				</tbody>
			</table>
		</div>
	</div><?php
}
