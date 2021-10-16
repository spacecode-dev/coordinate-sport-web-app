<div class="card card-custom card-custom-tabs">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/edit/' . $staffID); ?>' class="nav-link<?php if ($tab == 'details') { echo ' active'; } ?>">
					Personal
				</a>
			</li>
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/addresses/' . $staffID); ?>' class="nav-link<?php if ($tab == 'addresses') { echo ' active'; } ?>">
					Addresses &amp; Contacts
				</a>
			</li>
			<?php
			if ($this->auth->user->department !== 'office') {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/availability/' . $staffID); ?>' class="nav-link<?php if ($tab == 'availability') { echo ' active'; } ?>">
						Availability
					</a>
				</li><?php
			}
			if ($this->auth->has_features('staff_management')) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/quals/' . $staffID); ?>' class="nav-link<?php if ($tab == 'quals') { echo ' active'; } ?>">
						Qualifications
					</a>
				</li><?php
			}
			if ($this->auth->user->department !== 'office' && check_tab_availability('staff_recruitment')) {
				if ($this->auth->has_features('staff_management')) {
					?><li class='nav-item flex-shrink-0'>
						<a href='<?php echo site_url('staff/recruitment/' . $staffID); ?>' class="nav-link<?php if ($tab == 'recruitment') { echo ' active'; } ?>">
							Recruitment
						</a>
					</li><?php
				}
			}
			if ($this->auth->has_features(array('staff_management', 'staff_id'))) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/id/' . $staffID); ?>' class="nav-link<?php if ($tab == 'id') { echo ' active'; } ?>">
						Coach ID
					</a>
				</li><?php
			}
			if ($this->auth->has_features('staff_management')) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/notes/' . $staffID); ?>' class="nav-link<?php if ($tab == 'notes') { echo ' active'; } ?>">
						Development
					</a>
				</li>
				<li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/attachments/' . $staffID); ?>' class="nav-link<?php if ($tab == 'attachments') { echo ' active'; } ?>">
						Attachments
					</a>
				</li><?php
			}
			?><li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/privacy/' . $staffID); ?>' class="nav-link<?php if ($tab == 'privacy') { echo ' active'; } ?>">
					Data &amp; Privacy
				</a>
			</li><?php
			if ($this->auth->has_features('safety')) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/safety/' . $staffID); ?>' class="nav-link<?php if ($tab == 'safety') { echo ' active'; } ?>">
						H&amp;S
					</a>
				</li><?php
			}
			if ($this->auth->has_features('bookings_timetable')) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/timetable/' . $staffID); ?>' class="nav-link<?php if ($tab == 'Timetable') { echo ' active'; } ?>">
						Timetable
					</a>
				</li><?php
			}
			if ($this->auth->has_features('equipment')) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/equipment/' . $staffID); ?>' class="nav-link<?php if ($tab == 'Equipment') { echo ' active'; } ?>">
						Equipment
					</a>
				</li><?php
			}
			if ($this->auth->has_features('lesson_checkins')) {
				?><li class='nav-item flex-shrink-0'>
					<a href='<?php echo site_url('staff/checkins/' . $staffID); ?>' class="nav-link<?php if ($tab == 'checkins') { echo ' active'; } ?>">
						Check-in
					</a>
				</li><?
			}
			?>
		</ul>
	</div>
</div>
