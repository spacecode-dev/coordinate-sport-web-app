<?php echo form_open($page_base); ?>
<div class="row">
<?php if ($this->auth->has_features('dashboard_bookings') && !in_array($this->auth->user->department, array('coaching', 'headcoach', 'fulltimecoach'))) { ?>
	<div class="col-col-xl-3 col-lg-3 col-md-4 col-xs-12">
		<div class="row">
			<div class="col-12">
				<div class="card card-custom card-compact" id="booking_dashboard_alerts" data-url="<?php echo site_url('dashboard/ajax/summary/bookings'); ?>">
					<div class="card-header">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-calendar-alt text-contrast"></i></span>
							<h3 class="card-label"><a href="<?php echo site_url('dashboard/bookings'); ?>">Bookings</a></h3>
						</div>
					</div>
					<div class="card-body no-padding">
						<div class="results">
							<p class="loading">Loading...</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-9 col-lg-9 col-md-8 col-xs-12">
		<div class="row">
			<?php if ($this->auth->has_features('bookings_projects')) { ?>
				<div class="col-xl-4 col-sm-6 col-xs-12 d-flex">
					<div class="card card-custom card-compact flex-fill">
						<div class="card-header">
							<div class="card-title">
								<span class="card-icon"><i class="far fa-calendar-alt text-contrast"></i></span>
								<h3 class="card-label">Projects > Course</h3>
							</div>
						</div>
						<div class="card-body d-flex flex-column justify-content-between">
							<p class="mb-10">A course is a booking that occurs over a long time period. Use this if you have weekly sessions that recur throughout the year.</p>
							<div class="d-block">
								<a class="btn btn-sm btn-success mr-2" href="<?php echo site_url("bookings/course/new");?>"><i class="far fa-plus mr-1"></i> Create New</a>
								<button class='btn btn-sm btn-primary btn-submit' type="submit" name="course" value="course"><i class="fas fa-arrow-right mr-1"></i> See All</button>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-sm-6 col-xs-12 d d-flex">
					<div class="card card-custom card-compact flex-fill">
						<div class="card-header">
							<div class="card-title">
								<span class="card-icon"><i class="far fa-calendar-alt text-contrast"></i></span>
								<h3 class="card-label">Projects > Event</h3>
							</div>
						</div>
						<div class="card-body d-flex flex-column justify-content-between">
							<p class="mb-10">An event is a non-recurring, one-off project. The maximum length a block can be in an event is one week.</p>
							<div class="d-block">
								<a class="btn btn-sm btn-success mr-2" href="<?php echo site_url("bookings/event/new");?>"><i class="far fa-plus mr-1"></i> Create New</a>
								<button class="btn btn-sm btn-primary btn-submit" type="submit" name="event" value="event"><i class="fas fa-arrow-right mr-1"></i> See All</button>
							</div>
						</div>
					</div>
				</div>
			<?php }?>
			<div class="col-xl-4 col-sm-6 col-xs-12 d d-flex">
				<div class="card card-custom card-compact flex-fill">
					<div class="card-header">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-calendar-alt text-contrast"></i></span>
							<h3 class="card-label">Contract</h3>
						</div>
					</div>
					<div class="card-body d-flex flex-column justify-content-between">
						<p class="mb-10">A contract is a booking that is directly set up with a school or organisation. Contract bookings don’t have registers because they are usually handled by the school or organisation themselves.</p>
						<div class="d-block">
							<a class="btn btn-sm btn-success mr-2" href="<?php echo site_url("bookings/contract/new");?>"><i class="far fa-plus mr-1"></i> Create New</a>
							<a class="btn btn-sm btn-primary" href="<?php echo site_url("bookings");?>"><i class="fas fa-arrow-right mr-1"></i> See All</a>
						</div>
					</div>
				</div>
			</div>
			<?php if ($this->auth->has_features('online_booking')) {?>
				<div class="col-xl-4 col-sm-6 col-xs-12 d d-flex">
					<div class="card card-custom card-compact flex-fill">
						<div class="card-header">
							<div class="card-title">
								<span class="card-icon"><i class='far fa-globe text-contrast'></i></span>
								<h3 class="card-label">Booking Site</h3>
							</div>
						</div>
						<div class="card-body d-flex flex-column justify-content-between">
							<div class="d-block">
								<p>The front end is the booking site which can be viewed and accessed by the public.</p>
								<p class="mb-10">The back end is where bookings can be managed internally.</p>
							</div>
							<div class="d-block">
							<?php
								if ($booking_link !== FALSE) { ?>
									<a class="btn btn-sm btn-success mr-2" href="<?php echo $booking_link; ?>" target="_blank"><i class="far fa-plus mr-1"></i> Front End</a>
								<?php } ?>
								<button class='btn btn-sm btn-primary btn-submit' type="submit" name="booking_site" value="booking_site"><i class="fas fa-arrow-right mr-1"></i> Back End</button>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if ($this->auth->has_features('bookings_exceptions')) {?>
				<div class="col-xl-4 col-sm-6 col-xs-12 d d-flex">
					<div class="card card-custom card-compact flex-fill">
						<div class="card-header">
							<div class="card-title">
								<span class="card-icon"><i class='far fa-calendar-alt text-contrast'></i></span>
								<h3 class="card-label">Exceptions</h3>
							</div>
						</div>
						<div class="card-body d-flex flex-column justify-content-between">
							<p class="mb-10">An exception is used when a staff change or cancellation needs to be made to a booking. You'll be able to see them all here.</p>
							<div class="d-block">
								<a class="btn btn-sm btn-primary" href="<?php echo site_url("bookings/exceptions/all"); ?>"><i class="fas fa-arrow-right mr-1"></i> See All</a>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if ($this->auth->has_features('availability_cals')) {?>
				<div class="col-xl-4 col-sm-6 col-xs-12 d d-flex">
					<div class="card card-custom card-compact flex-fill">
						<div class="card-header">
							<div class="card-title">
								<span class="card-icon"><i class='far fa-calendar-alt text-contrast'></i></span>
								<h3 class="card-label">Availability Calendars</h3>
							</div>
						</div>
						<div class="card-body d-flex flex-column justify-content-between">
							<p>The availability calendars show staff members' availability on any given week. These can be customised to suit your specific availability types.</p>
							<?php
							$where = array(
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->from('availability_cals')->where($where)->order_by('name asc')->get();
							$options = array(
								'' => 'No Availability Calendars found'
							);
							if ($res->num_rows() > 0) {
								$options = array(
									'' => 'Select'
								);
								foreach ($res->result() as $row) {
									$options[$row->calID] = $row->name;
								}
							}
							echo form_dropdown('availabilities', $options, NULL, 'id="availabilities" class="select2 form-control"');
							?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>
</div>
<?php echo form_close(); ?>
<!-- begin::Bookings Panel-->
<div id="view-bookings" class="offcanvas offcanvas-right booking p-5">
	<div class="offcanvas-header d-flex justify-content-end mb-3 pl-5 pr-5 pt-5">
		<a href="javascript:void(0);" class="btn btn-xs btn-icon btn-primary" id="view-bookings-toggle-close">
			<i class="fas fa-times text-white"></i>
		</a>
	</div>
	<!--begin::Content-->
	<div class="offcanvas-content h-90 pl-5 pr-5 pr-5 ">
		<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>
	</div>
	<!--end::Content-->
</div>
<!-- end::Bookings Panel-->
