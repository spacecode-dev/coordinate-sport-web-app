<?php
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
					<strong><label for="field_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_from',
					'id' => 'field_from',
					'class' => 'form-control datepicker',
					'value' => mysql_to_uk_date($date_from)
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_to',
					'id' => 'field_to',
					'class' => 'form-control datepicker',
					'value' => mysql_to_uk_date($date_to)
				);
				echo form_input($data);
				?>
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
<div id="availabilitycal_desktop">
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered availability_cal'>
				<thead>
					<tr>
						<th>Slot</th>
						<?php
						$i = 0;
						foreach ($days as $day) {
							echo '<th>' . $day . '</th>';
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$tooltips = array();
					$mobile_data = array();
					foreach ($slots as $slotID => $slot) {
						?><tr>
							<td><strong><?php echo $slot['name']; ?></strong><br />
							<?php echo $slot['startTime'] . '-' . $slot['endTime']; ?></td>
							<?php
							foreach ($days as $day => $day_label) {
								?><td><?php
								foreach ($slot_data[$slotID][$day] as $activityID => $data) {
									echo '<div class="item">' . $activities[$activityID] . ' <span class="icons">';
									foreach ($data as $key => $items) {
										switch ($key) {
											case 'available':
											default:
												$label = 'Available:';
												$label_colour = 'success';
												break;
											case 'provisional':
												$label = 'With Provisional Conflicts:';
												$label_colour = 'warning';
												break;
											case 'conflict':
												$label = 'With Conflicts:';
												$label_colour = 'danger';
												break;
										}
										echo '<span class="label label-' . $label_colour . '"';
										$tooltip_data = array(
											'<strong>' . $label . '</strong>'
										);
										$mobile_data[$day][$slotID][$activities[$activityID]][] = '<span class="label label-inline label-' . $label_colour . '">' . $label . '</span>';
										if (count($items) > 0) {
											foreach ($items as $item) {
												// if only partially available, show in italics
												if ($item['only_partially_available'] === TRUE) {
													$tooltip_data[] = '<em>' . $item['name'] . '</em>';
													$mobile_data[$day][$slotID][$activities[$activityID]][] = '<strong><em>' . $item['name'] . '</em></strong>';
												} else {
													$tooltip_data[] = $item['name'];
													$mobile_data[$day][$slotID][$activities[$activityID]][] = '<strong>' . $item['name'] . '</strong>';
												}
												// show conflicts if any
												if (count($item['conflicts']) > 0) {
													$tooltip_data[] = '<em>' . implode(', ', $item['conflicts']) . '</em>';
													$mobile_data[$day][$slotID][$activities[$activityID]][] = '<em>' . implode(', ', $item['conflicts']) . '</em>';
												}
											}
										} else {
											$tooltip_data[] = '<em>None</em>';
											$mobile_data[$day][$slotID][$activities[$activityID]][] = '<em>None</em>';
										}
										$tooltip_key = 'tooltip-' . $slotID . '-' . $day . '-' . $activityID . '-' . $key;
										$tooltips[] = '<div class="' . $tooltip_key . '">' . implode('<br />', $tooltip_data) . '</div>';
										echo ' data-tooltip="' . $tooltip_key . '"';
										echo '>' . count($items) . '</span>';
									}
									echo '</span></div>';
								}
								?></td><?php
							}
							?>
						</tr><?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div id="availabilitycal_mobile">
	<?php
	foreach ($days as $day => $day_label) {
		echo '<h3>' . $day_label . '</h3>';
		foreach ($slots as $slotID => $slot) {
			?><div class='card card-custom card-collapsed'>
				<div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label"><?php echo $slot['name'] . ' <small>' . $slot['startTime'] . '-' . $slot['endTime'] . '</small>'; ?></h3>
					</div>
					<div class="card-toolbar">
	   					<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
	   					<i class="ki ki-arrow-down icon-nm"></i>
	   					</a>
	   				</div>
				</div>
				<div class="card-body">
					<?php
					foreach ($mobile_data[$day][$slotID] as $activity => $items) {
						echo '<h4>' . $activity . '</h4>';
						echo implode('<br />', $items);
					}
					?>
				</div>
			</div><?php
		}
	}
	?>
</div>
<div class="tooltips">
	<?php echo implode("\n", $tooltips); ?>
</div>
