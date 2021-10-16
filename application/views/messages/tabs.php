<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class='nav-item'>
				<a href='<?php echo site_url('messages/'.$folder.'/staff'); ?>' class="nav-link<?php if ($group == 'staff') { echo ' active'; } ?>">
					Staff
				</a>
			</li>
			<?php if ($this->auth->user->department != 'headcoach' && $this->auth->user->department != 'fulltimecoach' && $this->auth->user->department != 'coaching') {?>
				<li class='nav-item'>
					<a href='<?php echo site_url('messages/'.$folder.'/schools'); ?>' class="nav-link<?php if ($group == 'schools') { echo ' active'; } ?>">
						Schools
					</a>
				</li>
				<li class='nav-item'>
					<a href='<?php echo site_url('messages/'.$folder.'/organisations'); ?>' class="nav-link<?php if ($group == 'organisations') { echo ' active'; } ?>">
						Organisations
					</a>
				</li>
				<li class='nav-item'>
					<a href='<?php echo site_url('messages/'.$folder.'/participants'); ?>' class="nav-link<?php if ($group == 'participants') { echo ' active'; } ?>">
						Participants
					</a>
				</li>
			<?php } ?>
			<?php
			if ($this->auth->user->department == 'directors' && $this->auth->account->admin == 1 && $folder == "inbox") { ?>
				<li class='nav-item'>
					<a href='<?php echo site_url('messages/templates/'.$folder); ?>' class="nav-link<?php if ($group == 'templates') { echo ' active'; } ?>">
					Templates
					</a>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
