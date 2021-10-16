<?php
display_messages();
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
					<strong><label for="field_name">Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_name',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => $search_fields['name']
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
<?php
if ($plans->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No plans found. Do you want to <?php echo anchor('accounts/plans/new/', 'create one'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th class="min">
							Bookings: Timetable
						</th>
						<th class="min">
							Bookings: Your Timetable
						</th>
						<th class="min">
							Bookings: Contracts
						</th>
						<th class="min">
							Bookings: Projects
						</th>
						<th class="min">
							Bookings: Exceptions
						</th>
						<th class="min">
							<?php echo $this->settings_library->get_label('customers', TRUE); ?>: Schools
						</th>
						<th class="min">
							<?php echo $this->settings_library->get_label('customers', TRUE); ?>: Prospective Schools
						</th>
						<th class="min">
							<?php echo $this->settings_library->get_label('customers', TRUE); ?>: Organisations
						</th>
						<th class="min">
							<?php echo $this->settings_library->get_label('customers', TRUE); ?>: Prospective Organisations
						</th>
						<th class="min">
							<?php echo $this->settings_library->get_label('participants', TRUE); ?>
						</th>
						<th class="min">
							Staff Management
						</th>
						<th class="min">
							Settings
						</th>
						<th class="min">
							All Addons
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($plans->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('accounts/plans/edit/' . $row->planID, $row->name); ?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->bookings_timetable == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_timetable/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_timetable/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->bookings_timetable_own == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_timetable_own/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_timetable_own/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->bookings_bookings == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_bookings/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_bookings/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->bookings_projects == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_projects/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_projects/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->bookings_exceptions == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_exceptions/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/bookings_exceptions/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->customers_schools == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_schools/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_schools/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->customers_schools_prospects == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_schools_prospects/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_schools_prospects/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->customers_orgs == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_orgs/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_orgs/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->customers_orgs_prospects == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_orgs_prospects/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/customers_orgs_prospects/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->participants == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/participants/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/participants/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->staff_management == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/staff_management/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/staff_management/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->settings == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/settings/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/settings/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if($row->addons_all == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/addons_all/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/plans/feature/' . $row->planID); ?>/addons_all/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('accounts/plans/edit/' . $row->planID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<?php
									if ($this->auth->account->planID == $row->planID) {
										?><span class='btn btn-danger btn-sm no-action'>
											<i class='far fa-lock'></i>
										</span><?php
									} else {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('accounts/plans/remove/' . $row->planID); ?>' title="Remove">
											<i class='far fa-times'></i>
										</a><?php
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
	echo $this->pagination_library->display($page_base);
}
