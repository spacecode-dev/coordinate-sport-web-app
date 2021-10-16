<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class='nav-item'>
				<a href='<?php echo site_url('reports/payments'); ?>' class="nav-link<?php if ($tab == 'billing') { echo ' active'; } ?>">
					<?php echo $this->settings_library->get_label('participant'); ?> Billing
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('reports/payments/bookings'); ?>' class="nav-link<?php if ($tab == 'bookings') { echo ' active'; } ?>">
					Booking Payments
				</a>
			</li>
		</ul>
	</div>
</div>
