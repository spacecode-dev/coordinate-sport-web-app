<!DOCTYPE HTML>
<html>
<head>
	<title><?php echo ucwords($doc_info->type); ?></title>
	<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/print.css'); ?>" />
</head>
<body>
	<div class="intro">
		<h1><?php echo ucwords($doc_info->type); ?></h1>
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
				$desc = $this->settings_library->get('safety_school_desc');
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
			<td>Fire Evacuation Procedure/Emergency Exits</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['fire_alarm_tests']) && !empty($doc_info->details['fire_alarm_tests'])) {
					?><p>Alarm Tests:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['fire_alarm_tests'] == strip_tags($doc_info->details['fire_alarm_tests'])) {
						$doc_info->details['fire_alarm_tests'] = '<p>' . nl2br($doc_info->details['fire_alarm_tests']) . '</p>';
					}
					echo $doc_info->details['fire_alarm_tests'];
				}
				if (isset($doc_info->details['fire_assembly_points']) && !empty($doc_info->details['fire_assembly_points'])) {
					?><p>Assembly Points:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['fire_assembly_points'] == strip_tags($doc_info->details['fire_assembly_points'])) {
						$doc_info->details['fire_assembly_points'] = '<p>' . nl2br($doc_info->details['fire_assembly_points']) . '</p>';
					}
					echo $doc_info->details['fire_assembly_points'];
				}
				if (isset($doc_info->details['fire_procedure']) && !empty($doc_info->details['fire_procedure'])) {
					?><p>Procedure:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['fire_procedure'] == strip_tags($doc_info->details['fire_procedure'])) {
						$doc_info->details['fire_procedure'] = '<p>' . nl2br($doc_info->details['fire_procedure']) . '</p>';
					}
					echo $doc_info->details['fire_procedure'];
				}
			?></td>
		</tr>
		<tr>
			<td>School Accident Procedure</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['accident_reporting_procedure']) && !empty($doc_info->details['accident_reporting_procedure'])) {
					?><p>Reporting Procedure:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['accident_reporting_procedure'] == strip_tags($doc_info->details['accident_reporting_procedure'])) {
						$doc_info->details['accident_reporting_procedure'] = '<p>' . nl2br($doc_info->details['accident_reporting_procedure']) . '</p>';
					}
					echo $doc_info->details['accident_reporting_procedure'];
				}
				if (isset($doc_info->details['accident_book']) && !empty($doc_info->details['accident_book'])) {
					?><p>Specify location of accident reporting book:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['accident_book'] == strip_tags($doc_info->details['accident_book'])) {
						$doc_info->details['accident_book'] = '<p>' . nl2br($doc_info->details['accident_book']) . '</p>';
					}
					echo $doc_info->details['accident_book'];
				}
				if (isset($doc_info->details['accident_contact']) && !empty($doc_info->details['accident_contact'])) {
					?><p>Specify the relevant school contact:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['accident_contact'] == strip_tags($doc_info->details['accident_contact'])) {
						$doc_info->details['accident_contact'] = '<p>' . nl2br($doc_info->details['accident_contact']) . '</p>';
					}
					echo $doc_info->details['accident_contact'];
				}
			?></td>
		</tr>
		<tr>
			<td>Behaviour Policy</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['behaviour_rewards']) && !empty($doc_info->details['behaviour_rewards'])) {
					?><p>Rewards:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['behaviour_rewards'] == strip_tags($doc_info->details['behaviour_rewards'])) {
						$doc_info->details['behaviour_rewards'] = '<p>' . nl2br($doc_info->details['behaviour_rewards']) . '</p>';
					}
					echo $doc_info->details['behaviour_rewards'];
				}
				if (isset($doc_info->details['behaviour_procedure']) && !empty($doc_info->details['behaviour_procedure'])) {
					?><p>Procedures for bad behaviour:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['behaviour_procedure'] == strip_tags($doc_info->details['behaviour_procedure'])) {
						$doc_info->details['behaviour_procedure'] = '<p>' . nl2br($doc_info->details['behaviour_procedure']) . '</p>';
					}
					echo $doc_info->details['behaviour_procedure'];
				}
				if (isset($doc_info->details['behaviour_sen_medical']) && !empty($doc_info->details['behaviour_sen_medical'])) {
					?><p>SEN &amp; Medical Information:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['behaviour_sen_medical'] == strip_tags($doc_info->details['behaviour_sen_medical'])) {
						$doc_info->details['behaviour_sen_medical'] = '<p>' . nl2br($doc_info->details['behaviour_sen_medical']) . '</p>';
					}
					echo $doc_info->details['behaviour_sen_medical'];
				}
			?></td>
		</tr>
		<tr>
			<td>Further School Info</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['further_dos_donts']) && !empty($doc_info->details['further_dos_donts'])) {
					?><p>School Do's &amp; Don'ts:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['further_dos_donts'] == strip_tags($doc_info->details['further_dos_donts'])) {
						$doc_info->details['further_dos_donts'] = '<p>' . nl2br($doc_info->details['further_dos_donts']) . '</p>';
					}
					echo $doc_info->details['further_dos_donts'];
				}
				if (isset($doc_info->details['further_helpful_info']) && !empty($doc_info->details['further_helpful_info'])) {
					?><p>Helpful delivery info:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['further_helpful_info'] == strip_tags($doc_info->details['further_helpful_info'])) {
						$doc_info->details['further_helpful_info'] = '<p>' . nl2br($doc_info->details['further_helpful_info']) . '</p>';
					}
					echo $doc_info->details['further_helpful_info'];
				}
				if (isset($doc_info->details['further_behaviour']) && !empty($doc_info->details['further_behaviour'])) {
					?><p>Overview on the schools behaviour:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['further_behaviour'] == strip_tags($doc_info->details['further_behaviour'])) {
						$doc_info->details['further_behaviour'] = '<p>' . nl2br($doc_info->details['further_behaviour']) . '</p>';
					}
					echo $doc_info->details['further_behaviour'];
				}
				if (isset($doc_info->details['further_carpark']) && !empty($doc_info->details['further_carpark'])) {
					?><p>Car park open and close time:</p><?php
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['further_carpark'] == strip_tags($doc_info->details['further_carpark'])) {
						$doc_info->details['further_carpark'] = '<p>' . nl2br($doc_info->details['further_carpark']) . '</p>';
					}
					echo $doc_info->details['further_carpark'];
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
			<td>Further Comments</td>
			<td colspan="2"><?php
				if (isset($doc_info->details['further_comments']) && !empty($doc_info->details['further_comments'])) {
					// convert pre-wysiwyg fields to html
					if ($doc_info->details['further_comments'] == strip_tags($doc_info->details['further_comments'])) {
						$doc_info->details['further_comments'] = '<p>' . nl2br($doc_info->details['further_comments']) . '</p>';
					}
					echo $doc_info->details['further_comments'];
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
