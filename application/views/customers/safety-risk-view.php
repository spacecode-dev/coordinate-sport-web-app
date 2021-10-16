<!DOCTYPE HTML>
<html>
<head>
	<title><?php echo ucwords($doc_info->type); ?></title>
	<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/print.css'); ?>" />
</head>
<body class="group">
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
			<th>Person/Group at Risk</th>
			<th>Signed</th>
		</tr>
		<tr>
			<td><?php echo mysql_to_uk_date($doc_info->expiry); ?></td>
			<td><?php
			if (isset($doc_info->details['who']) && !empty($doc_info->details['who'])) {
				echo $doc_info->details['who'];
			}
			?></td>
			<td><?php echo substr($doc_info->first, 0, 1) . ". " . $doc_info->surname; ?></td>
		</tr>
	</table>
	<table>
		<tr>
			<th colspan="3">Description of Task/Process</th>
		</tr>
		<tr>
			<td colspan="3">
				<?php
				$smart_tags = array(
					'{company}' => $this->auth->account->company
				);
				$desc = $this->settings_library->get('safety_risk_desc');
				foreach ($smart_tags as $key => $value) {
					$desc = str_replace($key, $value, $desc);
				}
				echo $desc;
				?>
			</td>
		</tr>
	</table>
	<table class="tablesorter">
		<thead>
			<tr>
				<th>Hazard</th>
				<th>Potential Effect</th>
				<th class="min">Likelihood 1-5</th>
				<th class="min">Severity 1-5</th>
				<th class="min">Risk 1-25</th>
				<th>Minimise Risk By (Control Measures)</th>
				<th class="min">Residual&nbsp;Risk 1-25</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ($hazards->num_rows() == 0) {
				?><tr>
					<td colspan="7"><em>No hazards</em></td>
				</tr><?php
			} else {
				// loop
				foreach ($hazards->result() as $row) {
					?><tr>
						<td class="name"><?php echo $row->hazard; ?></td>
						<td><?php
						// convert pre-wysiwyg fields to html
						if ($row->potential_effect == strip_tags($row->potential_effect)) {
							$row->potential_effect = '<p>' . nl2br($row->potential_effect) . '</p>';
						}
						echo $row->potential_effect;
						?></td>
						<td class="min"><?php echo $row->likelihood; ?></td>
						<td class="min"><?php echo $row->severity; ?></td>
						<td class="min"><?php echo $row->risk; ?></td>
						<td><?php echo nl2br($row->control_measures); ?></td>
						<td class="min"><?php echo $row->residual_risk; ?></td>
					</tr><?php
				}
			}
			?>
			<tr>
				<td class="top">
					<p><strong>Likelihood of occurrence</strong></p>
					<ol>
						<li>Highly unlikely ever to occur</li>
						<li>Could occur but very rarely</li>
						<li>Could occur rarely</li>
						<li>Could occur from time to time</li>
						<li>Likely to occur often</li>
					</ol>
				</td>
				<td class="top">
					<p><strong>Severity of outcome</strong></p>
					<ol>
						<li>Slight inconvenience</li>
						<li>Minor injury requiring first aid</li>
						<li>Medical attention required</li>
						<li>Major injury leading to hospitalisation</li>
						<li>Fatality or serious injury leading to disability</li>
					</ol>
				</td>
				<td colspan="3" class="top">
					<p><strong>Risk = Likelihood x Severity</strong></p>
					<p>&lt;7 = Tolerable<br />
					8-16 = Not acceptable unless strict control measures in place and monitored throughout activity<br />
					16-25= Un Safe - Do Not Use or Do Activity</p>
				</td>
				<td colspan="2" class="top">
					<?php
					if (isset($doc_info->details['final']) && !empty($doc_info->details['final'])) {
						?><p><strong>Final Assessment &amp; Comments</strong></p><?php
						// convert pre-wysiwyg fields to html
						if ($doc_info->details['final'] == strip_tags($doc_info->details['final'])) {
							$doc_info->details['final'] = '<p>' . nl2br($doc_info->details['final']) . '</p>';
						}
						echo $doc_info->details['final'];
					}
					?>
					<p><strong>Review Date</strong></p>
					<p><?php
					if (strtotime($doc_info->expiry . " 23:59") < time()) {
						echo "<span style=\"color:red;\">";
					}
					echo date("d/m/Y", strtotime($doc_info->expiry));
					if (strtotime($doc_info->expiry . " 23:59") < time()) {
						echo "</span>";
					}
					?></p>
				</td>
			</tr>
		</tbody>
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
