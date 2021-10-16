
<?php
$data = array(
	'tab' => $tab,
);
$this->load->view('tabs_export.php', $data);

$form_classes = 'card card-custom card-search';
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
					<strong><label for="field_field1">Field 1</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if (count($valid_fields) > 0) {
					foreach ($valid_fields as $key => $field) {
						$options[$key] = $valid_fields[$key]['name'];
					}
				}
				echo form_dropdown('search_field1', $options, $search_fields['field1'], 'id="field_field1" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_field2">Field 2</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if (count($valid_fields) > 0) {
					foreach ($valid_fields as $key => $field) {
						$options[$key] = $valid_fields[$key]['name'];
					}
				}
				echo form_dropdown('search_field2', $options, $search_fields['field2'], 'id="field_field2" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_field3">Field 3</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if (count($valid_fields) > 0) {
					foreach ($valid_fields as $key => $field) {
						$options[$key] = $valid_fields[$key]['name'];
					}
				}
				echo form_dropdown('search_field3', $options, $search_fields['field3'], 'id="field_field3" class="select2 form-control"');
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
<?php
if ($res == FALSE) {
	// not searched
} else if ($res->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No results found.
	</div>
	<?php
} else {
	?><div class="alert alert-danger">
		<p>Merging records will merge the children, their families and all their bookings, payments, notes and other children into the selected child's family. This may be problematic if the same child is booked under 2 familes for discount reasons as both families will be fully merged as there is no way to split bookings made like this. Once merged, there is no way to undo this.</p>
	</div>
	<?php echo $this->pagination_library->display($page_base); ?>
	<?php
	foreach ($res->result() as $row) {
		echo form_open('dataconflicts/combine');
			?><div class='card card-custom'>
				<div class='table-responsive'>
					<table class='table table-striped table-bordered'>
						<thead>
							<tr>
								<th class="min">
									Select
								</th>
								<th>
									Child
								</th>
								<th>
									DOB
								</th>
								<th>
									Parent/Contact
								</th>
								<th>
									Post Code
								</th>
								<th>
									<?php echo localise('county'); ?>
								</th>
								<th>
									School
								</th>
								<th>
									Phone
								</th>
								<th>
									Email
								</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$duplicate_ids = explode(",", $row->duplicate_ids);

							// remove duplicates
							$duplicate_ids = array_unique($duplicate_ids);

							// look up
							$sql_child = "SELECT p.contactID, c.childID, p.familyID, p.title AS parent_title, p.first_name AS parent_first, p.last_name AS parent_last, c.first_name AS child_first, c.last_name AS child_last, c.orgID, p.postcode, p.county, p.phone, p.mobile, p.workPhone, p.email, c.dob, o.name as org FROM `" . $this->db->dbprefix("family_contacts") . "` AS p INNER JOIN `" . $this->db->dbprefix("family_children") . "` AS c ON p.familyID = c.familyID LEFT JOIN `" . $this->db->dbprefix("orgs") . "` AS o ON c.orgID = o.orgID WHERE c.`childID` IN (" . implode(',', $duplicate_ids) . ") AND c.`accountID` = " . $this->auth->user->accountID;

							$res_child = $this->db->query($sql_child);

							if ($res_child->num_rows() > 0) {
								foreach ($res_child->result() as $child) {
									?><tr>
										<td class="center">
											<input type="radio" name="intoID" value="<?php echo $child->childID; ?>" />
										</td>
										<td>
											<?php echo $child->child_first . ' ' . $child->child_last; ?>
										</td>
										<td>
											<?php echo mysql_to_uk_date($child->dob); ?>
										</td>
										<td>
											<?php echo $child->parent_first . " " . $child->parent_last; ?>
										</td>
										<td>
											<?php echo $child->postcode; ?>
										</td>
										<td>
											<?php echo $child->county; ?>
										</td>
										<td>
											<?php echo $child->org; ?>
										</td>
										<td>
											<?php
											$numbers = array();
											if (!empty($child->phone)) {
												$numbers[] = $child->phone;
											}
											if (!empty($child->mobile)) {
												$numbers[] = $child->mobile;
											}
											if (!empty($child->workPhone)) {
												$numbers[] = $child->workPhone;
											}
											if (count($numbers) > 0) {
												echo implode(", ", $numbers);
											}
											?>
										</td>
										<td>
											<?php
											if (!empty($child->email)) {
												echo "<a href=\"mailto:" . $child->email . "\">" . $child->email . "</a>";
											}
											?>
										</td>
										<td>
											<div class='text-right'>
												<a class='btn btn-success btn-sm' href='<?php echo site_url('participants/view/' . $child->familyID); ?>' title="View" target="_blanl">
													<i class='far fa-globe'></i>
												</a>
											</div>
										</td>
									</tr><?php
								}
							}
							?>
							<?php if(in_array($this->auth->user->staffID, $dataprotection)){ ?>
								<tr>
									<td colspan="10">
										<?php
										$hidden = array(
											'duplicate_ids' => implode(",", $duplicate_ids)
										);
										echo form_hidden($hidden);
										?>
										<button class='btn btn-primary btn-submit' type="submit">
											Merge Into
										</button>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div><?php
		echo form_close();
	}
	echo $this->pagination_library->display($page_base);
}
