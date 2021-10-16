<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class="nav-item">
				<a href="<?php echo site_url('export'); ?>" class="nav-link<?php if ($tab == '') { echo ' active'; } ?>">
					Data Management
				</a>
			</li>
			<li class="nav-item">
				<a href='<?php echo site_url('dataconflicts'); ?>' class="nav-link<?php if ($tab == 'dataconflicts') { echo ' active'; } ?>">
					Data Conflicts
				</a>
			</li>
			<?php if($this->settings_library->get_permission_level_label($this->auth->user->department) == "Super User"){ ?>
			<li class="nav-item">
				<a href='<?php echo site_url('export/dataprotection'); ?>' class="nav-link<?php if ($tab == 'dataprotection') { echo ' active'; } ?>">
					Data Protection Officer
				</a>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>