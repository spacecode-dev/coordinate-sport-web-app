<!--begin::Main-->
<!--begin::Header Mobile-->
<div id="kt_header_mobile" class="header-mobile align-items-center header-mobile-fixed<?php echo $this->auth->account_overridden === TRUE || $this->auth->user_overridden === TRUE ? " account_overridden" : ""; ?>">
	<!--begin::Logo-->
	<a href="/">
		<?php
		$logo_data = @unserialize($this->settings_library->get('logo'));
		if ($logo_data !== FALSE) {
			$args = array(
				'alt' => 'Image',
				'src' => 'attachment/setting/logo/' . $this->auth->user->accountID,
				'class' => 'logo',
				'height' => 40,
				'alt' => $this->auth->account->company
			);
			echo img($args);
		} else {
			echo $this->auth->account->company;
		}
		?>
	</a>
	<!--end::Logo-->
	<!--begin::Toolbar-->
	<div class="d-flex align-items-center">
		<!--begin::Aside Mobile Toggle-->
		<button class="btn p-0 burger-icon" id="kt_aside_mobile_toggle">
			<span></span>
		</button>
		<!--end::Aside Mobile Toggle-->
		<!--begin::Topbar Mobile Toggle-->
		<button class="btn btn-hover-text-primary p-0 ml-2" id="kt_header_mobile_topbar_toggle">
			<span class="svg-icon svg-icon-xl">
				<!--begin::Svg Icon | path:assets/media/svg/icons/General/User.svg-->
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
					<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<polygon points="0 0 24 0 24 24 0 24" />
						<path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
						<path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000" fill-rule="nonzero" />
					</g>
				</svg>
				<!--end::Svg Icon-->
			</span>
		</button>
		<!--end::Topbar Mobile Toggle-->
		<?php if ($this->auth->has_features('settings')) { ?>
			<button class="btn btn-hover-text-primary p-0 ml-2" onclick="openNav()">
				<i class='far fa-cog text-contrast'></i>
			</button>
		<?php }?>
	</div>
	<!--end::Toolbar-->
</div>
<!--end::Header Mobile-->
<div class="d-flex flex-column flex-root">
	<!--begin::Page-->
	<div class="d-flex flex-row flex-column-fluid page">
		<!--begin::Aside-->
		<div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto" id="kt_aside">
			<!--begin::Brand-->
			<div class="brand flex-column-auto" id="kt_brand">
				<!--begin::Logo-->
				<a href="/" class="brand-logo">
					<?php
					$logo_data = @unserialize($this->settings_library->get('logo'));
					if ($logo_data !== FALSE) {
						$args = array(
							'alt' => 'Image',
							'src' => 'attachment/setting/logo/' . $this->auth->user->accountID,
							'class' => 'logo',
							'height' => 40,
							'alt' => $this->auth->account->company
						);
						echo img($args);
					} else {
						echo $this->auth->account->company;
					}
					?>
				</a>
				<!--end::Logo-->
				<!--begin::Toggle-->
				<button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
					<span class="svg-icon svg-icon svg-icon-xl">
						<!--begin::Svg Icon | path:assets/media/svg/icons/Navigation/Angle-double-left.svg-->
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
							<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<polygon points="0 0 24 0 24 24 0 24" />
								<path d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z" fill="#000000" fill-rule="nonzero" transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999)" />
								<path d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999)" />
							</g>
						</svg>
						<!--end::Svg Icon-->
					</span>
				</button>
				<!--end::Toolbar-->
			</div>
			<!--end::Brand-->
			<?php $this->load->view('templates/master/nav'); ?></div>
		<!--end::Aside-->
		<?php
		if ($this->auth->account_overridden === TRUE || $this->auth->user_overridden === TRUE) {
			?><div id="account_overridden"><?php
			if ($this->auth->user->accountID == $this->session->userdata('account_id')) {
				echo 'You are logged in as ';
			} else {
				echo 'You are accessing the account <strong>' . $this->auth->account->company . '</strong> with the user ';
			}
			?><strong><?php echo $this->auth->user->first . ' ' . $this->auth->user->surname; ?></strong> - <a href="<?php echo site_url('removeoverride'); ?>"<?php if ($this->auth->user_overridden !== TRUE) { echo ' class="confirm" data-message= "Are you sure? If you are now hosting a screen sharing session, please click Cancel"'; } ?>>Return to <?php
				if ($this->auth->user_overridden === TRUE) {
					echo 'staff';
				} else {
					echo 'accounts';
				}
				?></a></div><?php
		}
		?>
		<!--begin::Wrapper-->
		<div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
			<!--begin::Header-->
			<div id="kt_header" class="header header-fixed">
				<!--begin::Container-->
				<?php
				$header_class = 'justify-content-end';
				if ($this->uri->segment(1) == 'bookings'){
					$header_class = 'justify-content-between';
				}?>
				<div class="container-fluid d-flex align-items-stretch <?php echo $header_class; ?>">
					<!--begin::Topbar-->
					<?php if ($this->uri->segment(1) == 'bookings'){ ?>
						<div class="topbar">
							<div class="topbar-item">
								<div class="btn btn-icon btn-clean btn-lg mr-1">
									<a href='<?php echo $this->auth->get_bookings_site(); ?>' target="_blank">
										<i class="flaticon2-shopping-cart"></i>
									</a>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="topbar">
						<?php
						if ($this->agent->is_mobile() && $this->uri->segment(1) == 'bookings'){ ?>
							<div class="topbar-item">
								<div class="btn btn-icon btn-clean btn-lg mr-1">
									<a href='<?php echo $this->auth->get_bookings_site(); ?>' target="_blank">
										<i class="flaticon2-shopping-cart"></i>
									</a>
								</div>
							</div>
						<?php }
						if ($this->crm_library->get_contact_cart()) {
							?><div class="topbar-item">
							<div class="btn btn-icon w-auto btn-clean d-flex align-items-center btn-lg px-2">
								<a href='<?php echo site_url('booking/cart'); ?>'>
										<span class="symbol shopping-cart">
											<i class='far fa-<?php
											if ($this->cart_library->cart_type == 'booking') {
												echo 'calendar-alt';
											} else {
												echo 'shopping-cart';
											}
											?>'></i>
											<?php
											if ($this->cart_library->count > 0) {
												?><span class="counter"><?php echo intval($this->cart_library->count); ?></span><?php
											}
											?>
										</span>
									<span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">
											<?php echo $this->cart_library->contact_name; ?>
										</span>
								</a>
							</div>
							</div><?php
						}
						if (!empty($this->settings_library->get('website'))) {
							?><div class="topbar-item">
							<div class="btn btn-icon btn-clean btn-lg mr-1">
								<a href='<?php echo $this->settings_library->get('website'); ?>' target="_blank">
									<i class='far fa-globe'></i>
								</a>
							</div>
							</div><?php
						}
						if (!empty($this->settings_library->get('company_support_link', 'default'))) {
							?><div class="topbar-item">
							<div class="btn btn-icon btn-clean btn-lg mr-1">
								<a href='<?php echo $this->settings_library->get('company_support_link', 'default'); ?>' target="_blank">
									<i class='far fa-question-circle'></i>
								</a>
							</div>
							</div><?php
						}
						?>
						<!--begin::User-->
						<div class="topbar-item">
							<div class="btn btn-icon w-auto btn-clean d-flex align-items-center btn-lg px-2" id="kt_quick_user_toggle">
								<span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Hi,</span>
								<span class="text-dark-50 font-weight-bolder font-size-base d-none d-md-inline mr-3"><?php echo htmlspecialchars($this->auth->user->first); ?></span>
								<span class="symbol symbol-35 symbol-light-success">
									<span class="symbol-label font-size-h5 font-weight-bold"><?php echo strtoupper(substr($this->auth->user->first, 0, 1)); ?></span>
								</span>
							</div>
						</div>
						<!--end::User-->
						<?php if ($this->auth->has_features('settings') && in_array($this->auth->user->department, array('directors', 'management'))) {?>
							<!--begin::Settings-->
							<div class="topbar-item">
								<div class="btn btn-icon w-auto btn-clean d-flex align-items-center btn-lg px-2">
									<a href='javascript:void(0);' class="d-flex" onclick="openNav()">
										<span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Settings</span>
										<i class='ml-1 fas fa-cog'></i>
									</a>
								</div>
							</div>
							<!--end::Settings-->
						<?php }?>
					</div>
					<!--end::Topbar-->
				</div>
				<!--end::Container-->
			</div>
			<!--end::Header-->
			<?php if ($this->auth->has_features('settings') && in_array($this->auth->user->department, array('directors', 'management'))) { ?>
				<!-- begin::Settings Content -->
				<div id="settings-overlay-container" class="overlay-settings-content overlay-hide">
					<!-- Button to close the overlay navigation -->
					<a href="#" class="btn btn-sm btn-icon btn-primary closebtn" onclick="closeNav()">
						<i class="far fa-times"></i>
					</a>
					<!-- Overlay content -->
					<div id="overlay-content">
						<?php
						// Settings
						$settings_items = array();
						if ($this->auth->has_features('settings')) {
							// directors and management only
							if (in_array($this->auth->user->department, array('directors', 'management'))) {
								$settings_items['setup']['sub_items'] = [
									'general' => [
										'title' => 'General',
										'url' => 'settings/listing/general'
									],
									'emailsms' => [
										'title' => 'Email &amp; SMS',
										'url' => 'settings/listing/emailsms'
									]
								];
								$settings_items['setup']['sub_items']['areas'] = [
									'title' => 'Areas',
									'url' => 'settings/areas'
								];
								$settings_items['setup']['sub_items']['regions'] = [
									'title' => 'Regions',
									'url' => 'settings/regions'
								];
								if ($this->auth->user->department == 'directors') {
									$settings_items['setup']['sub_items']['termsprivacy'] = [
										'title' => 'Terms &amp; Privacy',
										'url' => 'settings/termsprivacy'
									];
								}
								$settings_items['setup']['sub_items']['integrations'] = [
									'title' => 'Integrations',
									'url' => 'settings/integrations'
								];
								if ($this->auth->has_features('whitelabel')) {
									$settings_items['setup']['sub_items']['styling'] = [
										'title' => 'Styling',
										'url' => 'settings/styling'
									];
								}
								// directors only
								if ($this->auth->user->department == 'directors') {
									$settings_items['setup']['sub_items']['fields'] = [
										'title' => 'Display',
										'url' => 'settings/fields/display/staff'
									];
								}
								$settings_items['staff_users']['sub_items']['dashboard'] = [
									'title' => 'Dashboard',
									'url' => 'settings/dashboard'
								];
								$settings_items['staff_users']['sub_items']['settings_dashboard'] = [
									'title' => 'Dashboard Triggers',
									'url' => 'settings/dashboardtriggers'
								];
								$settings_items['staff_users']['sub_items']['mandatory_quals'] = [
									'title' => 'Mandatory Qualifications',
									'url' => 'settings/mandatoryquals'
								];
								$settings_items['staff_users']['sub_items']['permissionlevels'] = [
									'title' => 'Permission Levels',
									'url' => 'settings/permissionlevels'
								];
								$settings_items['staff_users']['sub_items']['staffingtypes'] = [
									'title' => 'Staffing Types',
									'url' => 'settings/staffingtypes'
								];
								$settings_items['bookings']['sub_items']['activities'] = [
									'title' => 'Activities',
									'url' => 'settings/activities'
								];
								$settings_items['bookings']['sub_items']['brands'] = [
									'title' => $this->settings_library->get_label('brands'),
									'url' => 'settings/departments'
								];
								$settings_items['bookings']['sub_items']['safety'] = [
									'title' => 'Health &amp; Safety',
									'url' => 'settings/safety'
								];
								if ($this->auth->has_features('bookings_projects')) {
									$settings_items['bookings']['sub_items']['projecttypes'] = [
										'title' => 'Project Types',
										'url' => 'settings/projecttypes'
									];
								}
								$settings_items['bookings']['sub_items']['resources'] = [
									'title' => 'Resources',
									'url' => 'settings/resources'
								];
								$settings_items['bookings']['sub_items']['sessiontypes'] = [
									'title' => 'Session Types',
									'url' => 'settings/sessiontypes'
								];
								$settings_items['bookings']['sub_items']['vouchers'] = [
									'title' => 'Vouchers',
									'sub_items' => [
										'vouchers' => [
											'title' => 'Vouchers',
											'url' => 'settings/vouchers'
										],
										'vouchers_childcare' => [
											'title' => 'Childcare Voucher Providers',
											'url' => 'settings/childcarevoucherproviders'
										]
									]
								];
								if ($this->auth->has_features('availability_cals')) {
									$settings_items['other']['sub_items']['availabilitycals'] = [
										'title' => 'Availability Calendars',
										'url' => 'settings/availabilitycals'
									];
								}
								$settings_items['other']['sub_items']['groups'] = [
									'title' => 'Groups',
									'url' => 'settings/groups'
								];
								if ($this->auth->has_features('bookings_projects')) {
									if ($this->auth->has_features('projectcode')) {
										$settings_items['other']['sub_items']['projectcodes'] = [
											'title' => 'Project Codes',
											'url' => 'settings/projectcodes'
										];
									}
								}
								$settings_items['other']['sub_items']['tags'] = [
									'title' => 'Tags',
									'url' => 'settings/tags'
								];
							}
						}
						?>
						<div class="row" id="kt_aside_menu_settings" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
							<?php
							$key_change_flag = '';
							foreach ($settings_items as $key => $item) {
							if($key_change_flag !== $key){
							if($key === 'setup'){ ?>
							<div class="col-md-6 col-lg-3 col-xl-3 aside-menu-settings">
								<h4 class="text-light text-left pt-3"><?php echo ucfirst(str_replace("_", " ", $key)); ?></h4>
								<?}else{?>
							</div>
							<div class="col-md-6 col-lg-3 col-xl-3 aside-menu-settings">
								<h4 class="text-light text-left pt-3"><?php echo ucfirst(str_replace("_", " ", $key)); ?></h4>
								<?php }
								$key_change_flag = $key;
								}
								if (array_key_exists('sub_items', $item)) {
									?>
									<ul class="menu-nav">
										<?php
										foreach ($item['sub_items'] as $sub_key => $sub_item) {
											$tag_open = 'span';
											$tag_close = 'span';
											if (array_key_exists('url', $sub_item)) {
												if (substr($sub_item['url'], 0, 4) !== 'http') {
													$sub_item['url'] = site_url($sub_item['url']);
												}
												$tag_open = 'a href="' . $sub_item['url'] . '"';
												if (array_key_exists('target', $sub_item)) {
													$tag_open .= ' target="' . $sub_item['target'] . '"';
												}
												$tag_close = 'a';
											}
											?><li class="menu-item<?php
											if (array_key_exists('sub_items', $sub_item)) {
												echo ' menu-item-submenu';
												if (isset($current_page, $section) && $section == 'settings' && substr($current_page, 0, strlen($sub_key)) == $sub_key) {
													echo ' menu-item-open';
												}
											} else if (isset($current_page, $section) && $section == 'settings' && $current_page == $sub_key) {
												echo ' menu-item-active';
											}
											?>"<?php if (array_key_exists('sub_items', $sub_item)) { ?> aria-haspopup="true" data-menu-toggle="hover"<?php } ?>>
											<<?php echo $tag_open; ?> class="menu-link<?php if (array_key_exists('sub_items', $sub_item)) { ?>  menu-toggle<?php } ?>">
											<span class="menu-text"><?php echo $sub_item['title']; ?></span>
											<?php if (array_key_exists('sub_items', $sub_item)) { ?><i class="menu-arrow"></i><?php } ?>
											</<?php echo $tag_close; ?>><?php
											if (array_key_exists('sub_items', $sub_item)) {
												?><div class="menu-submenu">
												<i class="menu-arrow"></i>
												<ul class="menu-subnav">
													<?php
													foreach ($sub_item['sub_items'] as $sub_sub_key => $sub_sub_item) {
														$tag_open = 'span';
														$tag_close = 'span';
														if (array_key_exists('url', $sub_sub_item)) {
															if (substr($sub_sub_item['url'], 0, 4) !== 'http') {
																$sub_sub_item['url'] = site_url($sub_sub_item['url']);
															}
															$tag_open = 'a href="' . $sub_sub_item['url'] . '"';
															if (array_key_exists('target', $sub_sub_item)) {
																$tag_open .= ' target="' . $sub_sub_item['target'] . '"';
															}
															$tag_close = 'a';
														}
														?><li class="menu-item<?php
														if (isset($current_page, $section) && $section == 'settings' && $current_page == $sub_sub_key) {
															echo ' menu-item-active';
														}
														?>">
														<<?php echo $tag_open; ?> class="menu-link">
														<span class="menu-text"><?php echo $sub_sub_item['title']; ?></span>
														</<?php echo $tag_close; ?>>
														</li><?php
													}
													?></ul>
												</div><?php
											}
											?></li><?php
										}
										?>
									</ul>
									<?php
								}
								?><?php
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<!-- end::Settings Content -->
			<?php } ?>
			<!--begin::Content-->
			<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
				<!--begin::Subheader-->
				<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
					<div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
						<!--begin::Info-->
						<div class="d-flex align-items-center flex-wrap mr-1">
							<!--begin::Page Heading-->
							<div class="d-flex align-items-baseline mr-5">
								<?php
								if (isset($title) && !empty($title)) {
								?><!--begin::Page Title-->
								<h5 class="text-dark font-weight-bold my-2 mr-5"><?php echo $title; ?></h5>
								<!--end::Page Title--><?php
								}
								if (isset($breadcrumb_levels) && count($breadcrumb_levels) > 0) {
								?><!--begin::Breadcrumb--><?php
								echo breadcrumb($breadcrumb_levels);
								?><!--end::Breadcrumb--><?php
								}
								?>
							</div>
							<!--end::Page Heading-->
						</div>
						<!--end::Info-->
						<!--begin::Toolbar-->
						<div class="d-flex align-items-center">
							<!--begin::Actions-->
							<?php
							if (isset($buttons)) {
								// set button size
								$buttons = str_replace('btn ', 'btn font-weight-bold btn-sm ', $buttons);
								// add styling to buttons with none
								$buttons = str_replace('"btn"', '"btn btn-light font-weight-bold btn-sm"', $buttons);
								echo $buttons;
							}
							?>
							<!--end::Actions-->
						</div>
						<!--end::Toolbar-->
					</div>
				</div>
				<!--end::Subheader-->
				<?php
				if ($this->auth->account->status == 'trial') {
					?><div id="trial_mode">Your account is currently in trial mode <?php if (!empty($this->auth->account->trial_until)) {
						$days_remaining = ceil((strtotime($this->auth->account->trial_until) - strtotime(date('Y-m-d')))/(24*60*60));
						if ($days_remaining < 0) {
							$days_remaining = 0;
						}
						echo ' with ' . $days_remaining . ' day';
						if ($days_remaining != 1) {
							echo 's';
						}
						echo ' remaining';
					} ?> - <a href="mailto:hello@coordinate.team?subject=Upgrade%20Account">Contact us to upgrade</a></div><?php
				}
				?>
				<!--begin::Entry-->
				<div class="d-flex flex-column-fluid">
					<!--begin::Container-->
					<div class="container-fluid">
