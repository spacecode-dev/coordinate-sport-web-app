<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<?php
			if ($is_project == 1 && !in_array($register_type, array('numbers', 'names', 'bikeability', 'shapeup'))) {
				?><li class="nav-item">
					<a href='<?php echo site_url('bookings/messaging/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'email_sms') { echo ' active'; } ?>">
						Participants
					</a>
				</li><?php
			}
			?>
			<li class="nav-item">
				<a href='<?php echo site_url('bookings/confirmation/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'customer') { echo ' active'; } ?>">
					Customers
				</a>
			</li>
			<li class="nav-item">
				<a href='<?php echo site_url('bookings/history/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'history') { echo ' active'; } ?>">
					History
				</a>
			</li>
		</ul>
	</div>
</div>
