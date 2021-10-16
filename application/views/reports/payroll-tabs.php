<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class='nav-item'>
				<a href='<?php echo site_url('reports/payroll'); ?>' class="nav-link<?php if ($tab == 'report') { echo ' active'; } ?>">
					Report
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('reports/payroll-history'); ?>' class="nav-link<?php if ($tab == 'history') { echo ' active'; } ?>">
					History
				</a>
			</li>
		</ul>
	</div>
</div>
