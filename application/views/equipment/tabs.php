<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class='nav-item'>
				<a href='<?php echo site_url('equipment'); ?>' class="nav-link<?php if ($tab == 'equipment') { echo ' active'; } ?>" />
					Equipment
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('equipment/bookings'); ?>' class="nav-link<?php if ($tab == 'bookings') { echo ' active'; } ?>" />
					Bookings
				</a>
			</li>
		</ul>
	</div>
</div>
