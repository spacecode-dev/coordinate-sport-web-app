<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<li class='nav-item'>
				<a href='<?php echo site_url('customers/edit/' . $orgID); ?>' class="nav-link<?php if ($tab == 'details') { echo ' active'; } ?>">
					Details
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('customers/addresses/' . $orgID); ?>' class="nav-link<?php if ($tab == 'addresses') { echo ' active'; } ?>">
					Addresses
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('customers/contacts/' . $orgID); ?>' class="nav-link<?php if ($tab == 'contacts') { echo ' active'; } ?>">
					Contacts
				</a>
			</li>
			<?php
			if ($this->auth->has_features('safety')) {
				?><li class='nav-item'>
					<a href='<?php echo site_url('customers/safety/' . $orgID); ?>' class="nav-link<?php if ($tab == 'safety') { echo ' active'; } ?>">
						Health &amp; Safety
					</a>
				</li><?php
			}
			?>
			<li class='nav-item'>
				<a href='<?php echo site_url('customers/attachments/' . $orgID); ?>' class="nav-link<?php if ($tab == 'attachments') { echo ' active'; } ?>">
					Attachments
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('customers/notes/' . $orgID); ?>' class="nav-link<?php if ($tab == 'notes') { echo ' active'; } ?>">
					Notes
				</a>
			</li>
			<li class='nav-item'>
				<a href='<?php echo site_url('customers/notifications/' . $orgID); ?>' class="nav-link<?php if ($tab == 'notifications') { echo ' active'; } ?>">
					Notifications
				</a>
			</li>
		</ul>
	</div>
</div>
