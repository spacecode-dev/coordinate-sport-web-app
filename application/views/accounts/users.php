<?php
display_messages();
$form_classes = 'card card-custom card-search card-search';
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
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'All',
					'staff' => 'Staff',
					'participants' => 'Participants',
					'schools' => 'School Contacts',
					'organisations' => 'Organisation Contacts'
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_first">First Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_first',
					'id' => 'field_first',
					'class' => 'form-control',
					'value' => $search_fields['first']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_last">Last Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_last',
					'id' => 'field_last',
					'class' => 'form-control',
					'value' => $search_fields['last']
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
		</div>
		<?php echo form_hidden('search', 'true'); ?>
	</div>
	<div class="card-footer">
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-search'></i> Search
			</button>
			<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
				Cancel
			</a>
		</div>
	</div>
<?php echo form_close(); ?>
<?php echo form_open($page_base . '#results'); ?>
	<div id="results"></div>
	<?php
	if ($valid_search !== TRUE) {
		?>
		<div class="alert alert-info">
			<i class="far fa-info-circle"></i>
			Enter some search terms to search.
		</div>
		<?php
	} else 	if (count($users) == 0) {
		?>
		<div class="alert alert-info">
			<i class="far fa-info-circle"></i>
			No users found.
		</div>
		<?php
	} else {
		?>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<div class='scrollable-area'>
					<table class='table table-striped table-bordered'>
						<thead>
							<tr>
								<th>
									Type
								</th>
								<?php if(isset($search_fields['type']) && ($search_fields['type'] == "schools" || $search_fields['type'] == "organisations")){ ?>
									<th>
										Name
									</th>
								<?php }else{ ?>
									<th>
										First Name
									</th>
									<th>
										Last Name
									</th>
								<?php } ?>
								<th>
									Email
								</th>
								<th>
									Account
								</th>
								<th>
									Permission Level
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($users as $row) {
								?>
								<tr>
									<td>
										<?php echo ucwords($row['type']); ?>
									</td>
									<?php if(isset($search_fields['type']) && ($search_fields['type'] == "schools" || $search_fields['type'] == "organisations")){ ?>
										<td class="name">
											<?php echo $row['name']; ?>
										</td>
									<?php }else{ ?>
										<td class="name">
											<?php echo $row['first']; ?>
										</td>
										<td>
											<?php echo $row['last']; ?>
										</td>
									<?php } ?>
									<td>
										<?php
										if (!empty($row['email'])) {
											echo mailto($row['email']);
										}
										?>
									</td>
									<td>
										<?php echo $row['account']; ?>
									</td>
									<td>
										<?php echo $row['level']; ?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
	?>
