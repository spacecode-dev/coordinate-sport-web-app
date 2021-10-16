<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class='nav-item'>
				<a href='<?php echo site_url('messages/inbox/'.$group); ?>' class="nav-link<?php if ($folder == 'inbox') { echo ' active'; } ?>">
					Inbox
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('messages/sent/'.$group); ?>' class="nav-link<?php if ($folder == 'sent') { echo ' active'; } ?>">
					Sent
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('messages/archive/'.$group); ?>' class="nav-link<?php if ($folder == 'archive') { echo ' active'; } ?>">
					Archive
				</a>
			</li>
		</ul>
	</div>
</div>
