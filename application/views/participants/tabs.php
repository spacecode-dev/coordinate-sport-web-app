<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<li class='nav-item'>
				<a href='<?php echo site_url('participants/view/' . $familyID); ?>' class="nav-link<?php if ($tab == 'details') { echo ' active'; } ?>">
					Details
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('participants/notes/' . $familyID); ?>' class="nav-link<?php if ($tab == 'notes') { echo ' active'; } ?>">
					Notes
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('participants/bookings/' . $familyID); ?>' class="nav-link<?php if ($tab == 'bookings') { echo ' active'; } ?>">
					Bookings
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('participants/payments/' . $familyID); ?>' class="nav-link<?php if ($tab == 'payments') { echo ' active'; } ?>">
					Payments
				</a>
			</li>
			<?php
			if ($this->auth->has_features('online_booking')) {
				?><li class='nav-item'>
					<a href='<?php echo site_url('participants/payment-plans/' . $familyID); ?>' class="nav-link<?php if ($tab == 'payment-plans') { echo ' active'; } ?>">
						Payment Plans
					</a>
				</li>
			<?php }if ($this->auth->has_features('online_booking_subscription_module')) {?>
				<li class='nav-item'>
					<a href='<?php echo site_url('participants/subscriptions/' . $familyID); ?>' class="nav-link<?php if ($tab == 'subscriptions') { echo ' active'; } ?>">
						Subscriptions
					</a>
				</li><?php
			}
			?>
			<li class='nav-item'>
				<a href='<?php echo site_url('participants/privacy/' . $familyID); ?>' class="nav-link<?php if ($tab == 'privacy') { echo ' active'; } ?>">
					Data &amp; Privacy
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('participants/notifications/' . $familyID); ?>' class="nav-link<?php if ($tab == 'notifications') { echo ' active'; } ?>">
					Notifications
				</a>
			</li>
		</ul>
	</div>
</div>
