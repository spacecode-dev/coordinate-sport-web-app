<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<li class="nav-item">
				<a href='<?php echo site_url('bookings/sessions/' . $bookingID . '/' . $blockID); ?>' class="nav-link">Sessions</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('bookings/sessions/edit/' . $lessonID); ?>' class="nav-link<?php if ($tab == 'details') { echo ' active'; } ?>">
					Details
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('sessions/staff/' . $lessonID); ?>' class="nav-link<?php if ($tab == 'staff') { echo ' active'; } ?>">
					Staff
				</a>
			</li>
			<?php
			if ($this->auth->has_features('bookings_exceptions')) {
				?><li class='nav-item'>
					<a href='<?php echo site_url('sessions/exceptions/' . $lessonID); ?>' class="nav-link<?php if ($tab == 'exceptions') { echo ' active'; } ?>">
						Exceptions
					</a>
				</li><?php
			}
			?>
			<li class='nav-item'>
				<a href='<?php echo site_url('sessions/notes/' . $lessonID); ?>' class="nav-link<?php if ($tab == 'notes') { echo ' active'; } ?>">
					Notes<?php
					if ($this->auth->has_features('session_evaluations') && $lesson_info->session_evaluations == 1) {
						echo '/Evaluations';
					}
					?>
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('sessions/attachments/' . $lessonID); ?>' class="nav-link<?php if ($tab == 'attachments') { echo ' active'; } ?>">
					Attachments
				</a>
			</li>
		</ul>
	</div>
</div>
