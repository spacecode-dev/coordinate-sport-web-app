<div class="card card-custom card-custom-tabs">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line flex-nowrap">
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/quals/' . $staffID); ?>' class="nav-link<?php if ($tab == 'man-qualifications') { echo ' active'; } ?>">
					Mandatory Qualifications
				</a>
			</li>
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/quals/additional/' . $staffID); ?>' class="nav-link<?php if ($tab == 'add-qualifications') { echo ' active'; } ?>">
					Additional Qualifications
				</a>
			</li>
			<li class='nav-item flex-shrink-0'>
				<a href='<?php echo site_url('staff/quals/abl-deliver/' . $staffID); ?>' class="nav-link<?php if ($tab == 'abl-deliver') { echo ' active'; } ?>">
					Able to Deliver
				</a>
			</li>
		</ul>
	</div>
</div>
