<?php
display_messages();

$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'searchform']); ?>
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
		<?php
		$data = array(
			'name' => 'participant_order',
			'type' => 'hidden',
			'id' => 'participant_order_field',
			'class' => 'form-control',
			'value' => $search_fields['participant_order'] == 'desc' ? 'asc' : 'desc'
		);
		echo form_input($data);
		?>
		<div class='row'>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_parent">Account Holder</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_parent',
					'id' => 'field_parent',
					'class' => 'form-control',
					'value' => $search_fields['parent']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_child">Participant</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_child',
					'id' => 'field_child',
					'class' => 'form-control',
					'value' => $search_fields['child']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_filter">Filter</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'All',
					'children' => 'Participants',
					'orphanParents' => 'Adults'
				);
				echo form_dropdown('search_filter', $options, $search_fields['filter'], 'id="field_filter" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_postcode">Post Code</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_postcode',
					'id' => 'field_postcode',
					'class' => 'form-control',
					'value' => $search_fields['postcode']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_county"><?php echo localise('county'); ?></label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_county',
					'id' => 'field_county',
					'class' => 'form-control',
					'value' => $search_fields['county']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_phone">Phone</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_phone',
					'id' => 'field_phone',
					'class' => 'form-control',
					'value' => $search_fields['phone']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_email">Email</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_email',
					'id' => 'field_email',
					'class' => 'form-control',
					'value' => $search_fields['email']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_org_id">School</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($schools->num_rows() > 0) {
					foreach ($schools->result() as $row) {
						$options[$row->orgID] = $row->name;
					}
				}
				echo form_dropdown('search_org_id', $options, $search_fields['org_id'], 'id="field_org_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_min_age">Min Age</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				for ($age = 5; $age <= 70; $age++) {
					$options[$age] = $age;
				}
				echo form_dropdown('search_min_age', $options, $search_fields['min_age'], 'id="field_min_age" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_max_age">Max Age</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				for ($age = 5; $age <= 70; $age++) {
					$options[$age] = $age;
				}
				echo form_dropdown('search_max_age', $options, $search_fields['max_age'], 'id="field_max_age" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_booking_cart">Booking Cart Full</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'full' => 'Full',
					'empty' => 'Empty'
				);
				echo form_dropdown('search_booking_cart', $options, $search_fields['booking_cart'], 'id="field_booking_cart" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_is_active">Active</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'1' => 'Yes',
					'0' => 'No'
				);
				echo form_dropdown('search_is_active', $options, $search_fields['is_active'], 'id="field_is_active" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_transaction_ref">Transaction Reference</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_transaction_ref',
					'id' => 'field_transaction_ref',
					'class' => 'form-control',
					'value' => $search_fields['transaction_ref']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_is_active">Outstanding Balances</label></strong>
				</p>
				<?php
				if(isset($search_fields['is_balance_due']) && ($search_fields['is_balance_due'] == '2' || $search_fields['is_balance_due'] == '3')){
					$search_fields['is_balance_due'] = '';
				}
				$options = array(
					'' => 'Select',
					'1' => 'Yes',
					'2' => 'No',
					'3' => 'Export'
				);
				echo form_dropdown('search_is_balance_due', $options, $search_fields['is_balance_due'], 'id="field_is_balance_due" class="select2 form-control"');
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
if ($families->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		<?php
		if (!empty($search_fields['transaction_ref'])) {
			?>This Transaction Reference is not associated with a participant booking, please check the code and try again<?php
		} else {
			?>No participant account found. Do you want to <?php	echo anchor('participants/new', 'create one'); ?>?<?php
		}
		?>
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base, 1); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Account Holder
						</th>
						<?php if ($form_submitted) { ?>
							<th>
								<a id="participant_order" style="color:#464E5F; cursor: pointer"> Participant </a>
								<?php
								switch ($search_fields['participant_order']) {
									case 'asc':
										?> <?php
										break;
									default:
										?> <?php
										break;
								}
								?>
							</th>
						<?php } else { ?>
						<th>
						<?php
							$url = parse_url($_SERVER['REQUEST_URI']);
							if (isset($_GET['order']['participant'])) {
								switch ($_GET['order']['participant']) {
									case 'asc':
										echo anchor($url['path'] . '', 'Participant', 'Style="color:#464E5F"');
										?> <?php
										break;
									default:
										echo anchor($url['path'] . '?order[participant]=asc', 'Participant', 'Style="color:#464E5F"');
										?> <?php
										break;
								}
							} else {
								echo anchor($url['path'] . '?order[participant]=asc', 'Participant', 'Style="color:#464E5F"');
								?> <?php
							}
						?>
						</th>
						<?php } ?>
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
							Message
						</th>
						<th>
							Book
						</th>
						<th>
							Active
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($families->result() as $row) {
						?>
						<tr>
							<td>
								<div class="name participant-club">
									<?php
									$profile_pic = @unserialize($row->profile_pic);
									if($profile_pic !== FALSE){
										$args = array(
											'alt' => 'Image',
											'src' => 'attachment/participant/profile_pic/thumb/'.$row->contactID,
											'class' => 'responsive-img'
										);
										echo '<div class="profile_pic">' . img($args) . '</div>';
									}else{
										echo "<div class='img-container bg-random-".substr($row->contactID, -1)."'>".substr(trim($row->contact_first), 0, 1)."</div>";
									}
									echo '<div>';
									echo anchor('participants/view/' . $row->familyID, $row->contact_first . ' ' . $row->contact_last);
									if (!empty($row->contact_title)) {
										echo ' (' . ucwords($row->contact_title) . ')';
									}
									if (!empty($row->contactID)) {
										?><br />
										ID: <?php echo $row->contactID;
									}
									echo '</div>';
									?>
								</div>
							</td>
							<td>
								<?php echo $row->child_first . ' ' . $row->child_last ?>
							</td>
							<td>
								<?php echo $row->postcode; ?>
							</td>
							<td>
								<?php echo $row->county; ?>
							</td>
							<td>
								<?php echo $row->school; ?>
							</td>
							<td>
								<?php
								$numbers = array();
								if (!empty($row->phone)) {
									$numbers[] = $row->phone;
								}
								if (!empty($row->mobile)) {
									$numbers[] = $row->mobile;
								}
								if (!empty($row->workPhone)) {
									$numbers[] = $row->workPhone;
								}
								if (count($numbers) > 0) {
									echo implode(", ", $numbers);
								}
								?>
							</td>
							<td class="has_icon">
								<?php
								if (!empty($row->email)) {
									?><a href="<?php echo site_url('messages/sent/participants/new/' . $row->contactID); ?>" class="btn btn-default btn-sm"><i class="far fa-envelope"></i></a><?php
								}
								?>
							</td>
							<td>
								<?php
								if (!empty($row->childID)) {
									echo form_open('booking/cart/jump/' . $row->contactID . '/' . $row->childID, 'class="book_online"');
										$options = array(
											'' => 'Select event'
										);
										foreach ($upcoming_events as $blockID => $event) {
											$age = calculate_age($row->child_dob, $event['age_at']);
											if ((empty($event['min_age']) || $age >= $event['min_age']) && ((empty($event['max_age'])) || $age <= $event['max_age'])) {
												$options[$blockID] = $event['label'];
											}
										}
										echo form_dropdown('blockID', $options, NULL, 'class="blockID form-control select2"');
									echo form_close();
								} else if (!empty($row->contactID)) {
									echo form_open('booking/cart/jump/' . $row->contactID, 'class="book_online"');
										$options = array(
											'' => 'Select event'
										);
										foreach ($upcoming_events_individuals as $blockID => $event) {
											$age = calculate_age($row->contact_dob, $event['age_at']);
											if ((empty($event['min_age']) || $age >= $event['min_age']) && ((empty($event['max_age'])) || $age <= $event['max_age'])) {
												$options[$blockID] = $event['label'];
											}
										}
										echo form_dropdown('blockID', $options, NULL, 'class="blockID form-control select2"');
									echo form_close();
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->active == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('participants/active/' . $row->contactID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('participants/active/' . $row->contactID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='<?php echo (!empty($search_fields['transaction_ref']))?'fixed-3-icons':'fixed-2-icons' ?>'>
									<?php
									if ($this->auth->has_features('participants')) {
										?><a class='btn btn-info mb-2 btn-sm' href='<?php echo site_url('booking/cart/init/' . $row->contactID); ?>' title="View Online Booking Cart">
											<i class='far fa-shopping-cart'></i>
										</a> <?php
									}
									?>
									<a class='btn btn-success mb-2 btn-sm' href='<?php echo site_url('participants/view/' . $row->familyID); ?>' title="Edit">
										<i class='far fa-globe'></i>
									</a>
									<?php
									if (!empty($search_fields['transaction_ref'])) {
										?><a class='btn btn-info btn-sm' href='<?php echo site_url('participants/payments/' . $row->familyID); ?>/recall#results' title="Payments">
											<i class='far fa-sack-dollar'></i>
										</a> <?php
									}
									?>
								</div>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
echo $this->pagination_library->display($page_base, 1);?>
<!-- Data Model for Profile Picture -->
<div class="modal fade" id="myModal" role="dialog">
	<div class="modal-dialog modal-dialog-centered" style="width:50%; min-width:600px">
		<!-- Modal content-->
		<div class="modal-content" style="background-color:#2a89ec;">
			<div class="modal-body">
				<div style="text-align:center">
					<img src="<?php echo $this->crm_library->asset_url("public/images/warning-icons.png")?>" title="Warning Icon" width="50px" />
				</div>
				<div style="text-align:center">
					<p style="color:white; padding-top:3%">Would you like to export a list of Account Holders and their account balances?</p><br />
				</div>
				<div style="text-align:center">
					<a href="javascript:void(0)" id="Yestoexport" class="btn btn-default"  style="background-color:#2D7190; border-color: #2D7190; color:#fff; padding:1% 5%"> Yes </a>&nbsp;&nbsp;
					<a href="javascript:void(0)" data-dismiss="modal" class="btn btn-default" style="background-color:#2D7190; border-color: #2D7190; color:#fff; padding:1% 5%"> No </a>
				</div>
			</div>
		</div>
	</div>
</div>
