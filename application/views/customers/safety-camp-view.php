<!DOCTYPE HTML>
<html>
<head>
	<title>Event/Project Induction</title>
	<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/print.css'); ?>" />
</head>
<body>
	<div class="intro">
		<h1>Event/Project Induction</h1>
		<p class="noprint"><a href="#" class="print">Print</a></p>
	</div>
	<table>
		<tr>
			<th>Name of Assessor</th>
			<th>Location/Site</th>
			<th>Date of Assessment</th>
		</tr>
		<tr>
			<td><?php echo $doc_info->first . ' ' . $doc_info->surname; ?></td>
			<td><?php echo $doc_info->org;
			$addresses = array();
			if (!empty($doc_info->address1)) {
				$addresses[] = $doc_info->address1;
			}
			if (!empty($doc_info->address2)) {
				$addresses[] = $doc_info->address2;
			}
			if (!empty($doc_info->address3)) {
				$addresses[] = $doc_info->address3;
			}
			if (!empty($doc_info->town)) {
				$addresses[] = $doc_info->town;
			}
			if (!empty($doc_info->county)) {
				$addresses[] = $doc_info->county;
			}
			if (!empty($doc_info->postcode)) {
				$addresses[] = $doc_info->postcode;
			}

			echo "<br />" . implode(", ", $addresses);

			if (array_key_exists("location", $doc_info->details) && !empty($doc_info->details['location'])) {
				echo " (" . $doc_info->details['location'] . ')';
			}
			?></td>
			<td><?php echo mysql_to_uk_date($doc_info->date); ?></td>
		</tr>
		<tr>
			<th>Date of Next Assessment</th>
			<th colspan="2">Signed</th>
		</tr>
		<tr>
			<td><?php echo mysql_to_uk_date($doc_info->expiry); ?></td>
			<td colspan="2"><?php echo substr($doc_info->first, 0, 1) . ". " . $doc_info->surname; ?></td>
		</tr>
	</table>

	<table>
		<tr>
			<th>Description of Task/Process</th>
		</tr>
		<tr>
			<td>
				<?php
				$smart_tags = array(
					'{company}' => $this->auth->account->company
				);
				$desc = $this->settings_library->get('safety_camp_desc');
				foreach ($smart_tags as $key => $value) {
					$desc = str_replace($key, $value, $desc);
				}
				echo $desc;
				?>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<th>Policies/Procedures</th>
			<th colspan="2">Additional Information</th>
		</tr>
		<tr>
			<td>Emergency Contacts</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['venue_contact1']) && !empty($doc_info->details['venue_contact1'])) {
					?><p>Primary:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['venue_contact1'] == strip_tags($doc_info->details['venue_contact1'])) {
						$doc_info->details['venue_contact1'] = '<p>' . nl2br($doc_info->details['venue_contact1']) . '</p>';
					}
					echo $doc_info->details['venue_contact1'];
				}
				if (isset($doc_info->details['venue_contact2']) && !empty($doc_info->details['venue_contact2'])) {
					?><p>Secondary:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['venue_contact2'] == strip_tags($doc_info->details['venue_contact2'])) {
						$doc_info->details['venue_contact2'] = '<p>' . nl2br($doc_info->details['venue_contact2']) . '</p>';
					}
					echo $doc_info->details['venue_contact2'];
				}
			?></td>
		</tr>
		<tr>
			<td>Open and Lock up Procedure</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['open_lockup']) && !empty($doc_info->details['open_lockup'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['open_lockup'] == strip_tags($doc_info->details['open_lockup'])) {
						$doc_info->details['open_lockup'] = '<p>' . nl2br($doc_info->details['open_lockup']) . '</p>';
					}
					echo $doc_info->details['open_lockup'];
				}
			?></td>
		</tr>
		<tr>
			<td>Parent Registration Area</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['registration_area']) && !empty($doc_info->details['registration_area'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['registration_area'] == strip_tags($doc_info->details['registration_area'])) {
						$doc_info->details['registration_area'] = '<p>' . nl2br($doc_info->details['registration_area']) . '</p>';
					}
					echo $doc_info->details['registration_area'];
				}
			?></td>
		</tr>
		<tr>
			<td>Fire Evacuation Procedure/Emergency Exits</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['fire_procedure']) && !empty($doc_info->details['fire_procedure'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['fire_procedure'] == strip_tags($doc_info->details['fire_procedure'])) {
						$doc_info->details['fire_procedure'] = '<p>' . nl2br($doc_info->details['fire_procedure']) . '</p>';
					}
					echo $doc_info->details['fire_procedure'];
				}
			?></td>
		</tr>
		<tr>
			<th>Areas for Use</th>
			<th>Indoor</th>
			<th>Outdoor</th>
		</tr>
		<tr>
			<td>Toilets</td>
			<td><?php
				if (isset($doc_info->details['indoor_toilets']) && !empty($doc_info->details['indoor_toilets'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['indoor_toilets'] == strip_tags($doc_info->details['indoor_toilets'])) {
						$doc_info->details['indoor_toilets'] = '<p>' . nl2br($doc_info->details['indoor_toilets']) . '</p>';
					}
					echo $doc_info->details['indoor_toilets'];
				}
			?></td>
			<td><?php
				if (isset($doc_info->details['outdoor_toilets']) && !empty($doc_info->details['outdoor_toilets'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['outdoor_toilets'] == strip_tags($doc_info->details['outdoor_toilets'])) {
						$doc_info->details['outdoor_toilets'] = '<p>' . nl2br($doc_info->details['outdoor_toilets']) . '</p>';
					}
					echo $doc_info->details['outdoor_toilets'];
				}
			?></td>
		</tr>
		<tr>
			<td>Lunch</td>
			<td><?php
				if (isset($doc_info->details['indoor_lunch']) && !empty($doc_info->details['indoor_lunch'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['indoor_lunch'] == strip_tags($doc_info->details['indoor_lunch'])) {
						$doc_info->details['indoor_lunch'] = '<p>' . nl2br($doc_info->details['indoor_lunch']) . '</p>';
					}
					echo $doc_info->details['indoor_lunch'];
				}
			?></td>
			<td><?php
				if (isset($doc_info->details['outdoor_lunch']) && !empty($doc_info->details['outdoor_lunch'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['outdoor_lunch'] == strip_tags($doc_info->details['outdoor_lunch'])) {
						$doc_info->details['outdoor_lunch'] = '<p>' . nl2br($doc_info->details['outdoor_lunch']) . '</p>';
					}
					echo $doc_info->details['outdoor_lunch'];
				}
			?></td>
		</tr>
		<tr>
			<td>Activity</td>
			<td><?php
				if (isset($doc_info->details['indoor_activity']) && !empty($doc_info->details['indoor_activity'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['indoor_activity'] == strip_tags($doc_info->details['indoor_activity'])) {
						$doc_info->details['indoor_activity'] = '<p>' . nl2br($doc_info->details['indoor_activity']) . '</p>';
					}
					echo $doc_info->details['indoor_activity'];
				}
			?></td>
			<td><?php
				if (isset($doc_info->details['outdoor_activity']) && !empty($doc_info->details['outdoor_activity'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['outdoor_activity'] == strip_tags($doc_info->details['outdoor_activity'])) {
						$doc_info->details['outdoor_activity'] = '<p>' . nl2br($doc_info->details['outdoor_activity']) . '</p>';
					}
					echo $doc_info->details['outdoor_activity'];
				}
			?></td>
		</tr>
		<tr>
			<td>Areas Not for Use</td>
			<td><?php
				if (isset($doc_info->details['indoor_not']) && !empty($doc_info->details['indoor_not'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['indoor_not'] == strip_tags($doc_info->details['indoor_not'])) {
						$doc_info->details['indoor_not'] = '<p>' . nl2br($doc_info->details['indoor_not']) . '</p>';
					}
					echo $doc_info->details['indoor_not'];
				}
			?></td>
			<td><?php
				if (isset($doc_info->details['outdoor_not']) && !empty($doc_info->details['outdoor_not'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['outdoor_not'] == strip_tags($doc_info->details['outdoor_not'])) {
						$doc_info->details['outdoor_not'] = '<p>' . nl2br($doc_info->details['outdoor_not']) . '</p>';
					}
					echo $doc_info->details['outdoor_not'];
				}
			?></td>
		</tr>
		<tr>
			<td>Accident Procedure</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['accident_procedure']) && !empty($doc_info->details['accident_procedure'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['accident_procedure'] == strip_tags($doc_info->details['accident_procedure'])) {
						$doc_info->details['accident_procedure'] = '<p>' . nl2br($doc_info->details['accident_procedure']) . '</p>';
					}
					echo $doc_info->details['accident_procedure'];
				}
			?></td>
		</tr>
		<tr>
			<td>Equipment</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['equipment']) && is_array($doc_info->details['equipment']) && count($doc_info->details['equipment']) > 0) {
					?><p>Equipment Checklist:</p><?php
					echo "<ul>";
						foreach ($doc_info->details['equipment'] as $item) {
							$item_key = preg_replace("/[^a-z0-9]/", '', strtolower($item));
							echo "<li>" . $item;
							if (isset($doc_info->details['equipment_details']) && is_array($doc_info->details['equipment_details']) && array_key_exists($item_key, $doc_info->details['equipment_details']) && !empty($doc_info->details['equipment_details'][$item_key])) {
								echo ' (' . $doc_info->details['equipment_details'][$item_key] . ')';
							}
							echo "</li>";
						}
					echo "</ul>";
				}
				if (isset($doc_info->details['equipment_additional']) && !empty($doc_info->details['equipment_additional'])) {
					?><p>Any Additional Equipment:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['equipment_additional'] == strip_tags($doc_info->details['equipment_additional'])) {
						$doc_info->details['equipment_additional'] = '<p>' . nl2br($doc_info->details['equipment_additional']) . '</p>';
					}
					echo $doc_info->details['equipment_additional'];
				}
			?></td>
		</tr>
		<tr>
			<td colspan="3">
				<p>Venue Images:</p>
				<?php
				if (isset($doc_info->details['venue_images']) && is_array($doc_info->details['venue_images']) && count($doc_info->details['venue_images']) > 0) {
					foreach ($doc_info->details['venue_images'] as $img) {
						if (AWS) {
							$src = $this->aws_library->s3_presigned_url('orgs/' . $doc_info->orgID . '/safety/thumb.' . $img);
						} else {
							$src = base_url('public/uploads/orgs/' . $doc_info->orgID . '/safety/thumb.' . $img);
						}
						echo "<p><img src=\"" . $src . "\" alt=\"Venue Image\" /></p>";
					}
				} else {
					echo "<p>None</p>";
				}
				?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<p>School Site Map:</p>
				<?php
				if (isset($doc_info->details['map_images']) && is_array($doc_info->details['map_images']) && count($doc_info->details['map_images']) > 0) {
					foreach ($doc_info->details['map_images'] as $img) {
						if (AWS) {
							$src = $this->aws_library->s3_presigned_url('orgs/' . $doc_info->orgID . '/safety/thumb.' . $img);
						} else {
							$src = base_url('public/uploads/orgs/' . $doc_info->orgID . '/safety/thumb.' . $img);
						}
						echo "<p><img src=\"" . $src . "\" alt=\"Map Image\" /></p>";
					}
				} else {
					echo "<p>None</p>";
				}
				?>
			</td>
		</tr>
	</table>
	<?php
	$data = array(
		'confirmed' => $confirmed,
		'docID' => $doc_info->docID
	);
	$this->load->view('customers/safety-confirm', $data);
	?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="<?php echo $this->crm_library->asset_url('dist/js/components/print.js'); ?>"></script>
</body>
</html>
