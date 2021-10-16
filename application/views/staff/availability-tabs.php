<div class="card card-custom card-custom-tabs">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line flex-nowrap">
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/availability/' . $staffID); ?>' class="nav-link<?php if ($tab == 'availability') { echo ' active'; } ?>">
					Details
				</a>
			</li>
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/availability/' . $staffID.'/exceptions'); ?>' class="nav-link<?php if ($tab == 'exception') { echo ' active'; } ?>">
					Exceptions
				</a>
			</li>
		</ul>
	</div>
</div>
