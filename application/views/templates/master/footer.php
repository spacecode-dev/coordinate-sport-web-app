					</div>
					<!--end::Container-->
				</div>
				<!--end::Entry-->
			</div>
			<!--end::Content-->
			<!--begin::Footer-->
			<div class="footer bg-white py-4 d-flex flex-lg-column" id="kt_footer">
				<!--begin::Container-->
				<div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
					<!--begin::Copyright-->
					<div class="text-dark order-2 order-md-1">
						&copy; <?php echo date('Y'); ?> <?php echo $this->settings_library->get('company', 'default'); ?>
					</div>
					<!--end::Copyright-->
					<?php
					if (!empty($this->settings_library->get('company_support_link', 'default'))) {
						?><!--begin::Nav-->
						<div class="nav nav-dark">
							<a href="<?php echo $this->settings_library->get('company_support_link', 'default'); ?>" target="_blank" class="nav-link pl-0 pr-5">Support</a>
						</div>
						<!--end::Nav--><?php
					}
					?>
				</div>
				<!--end::Container-->
			</div>
			<!--end::Footer-->
		</div>
		<!--end::Wrapper-->
	</div>
	<!--end::Page-->
</div>
<!--end::Main-->
<!-- begin::User Panel-->
<div id="kt_quick_user" class="offcanvas offcanvas-right p-10">
	<!--begin::Header-->
	<div class="offcanvas-header d-flex align-items-center justify-content-between pb-5">
		<h3 class="font-weight-bold m-0">User Profile</h3>
		<a href="#" class="btn btn-sm btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
			<i class="ki ki-close icon-xs text-muted"></i>
		</a>
	</div>
	<!--end::Header-->
	<!--begin::Content-->
	<div class="offcanvas-content pr-5 mr-n5">
		<!--begin::Header-->
		<div class="d-flex align-items-center mt-5">
			<div class="symbol symbol-100 mr-5">
				<div class="symbol-label" style="background-image:url(<?php
				if (!empty($this->auth->user->id_photo_path) && file_exists(UPLOADPATH . $this->auth->user->id_photo_path)) {
					echo site_url('attachment/staff-id/' . $this->auth->user->id_photo_path);
				}
				?>)"></div>
				<i class="symbol-badge bg-success"></i>
			</div>
			<div class="d-flex flex-column">
				<a href="<?php echo site_url('profile'); ?>" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary"><?php echo htmlspecialchars($this->auth->user->first . ' ' . $this->auth->user->surname); ?></a>
				<div class="text-muted mt-1"><?php echo htmlspecialchars($this->auth->user->jobTitle); ?></div>
				<div class="navi mt-2">
					<a href="<?php echo site_url('logout'); ?>" class="btn btn-sm btn-light-primary font-weight-bolder py-2 px-5">Sign Out</a>
				</div>
			</div>
		</div>
		<!--end::Header-->
		<!--begin::Separator-->
		<div class="separator separator-dashed mt-8 mb-5"></div>
		<!--end::Separator-->
		<!--begin::Nav-->
		<div class="navi navi-spacer-x-0 p-0">
			<!--begin::Item-->
			<a href="<?php echo site_url('profile'); ?>" class="navi-item">
				<div class="navi-link">
					<div class="symbol symbol-40 bg-light mr-3">
						<div class="symbol-label">
							<span class="svg-icon svg-icon-md svg-icon-success">
								<!--begin::Svg Icon | path:assets/media/svg/icons/General/Notification2.svg-->
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<rect x="0" y="0" width="24" height="24" />
										<path d="M13.2070325,4 C13.0721672,4.47683179 13,4.97998812 13,5.5 C13,8.53756612 15.4624339,11 18.5,11 C19.0200119,11 19.5231682,10.9278328 20,10.7929675 L20,17 C20,18.6568542 18.6568542,20 17,20 L7,20 C5.34314575,20 4,18.6568542 4,17 L4,7 C4,5.34314575 5.34314575,4 7,4 L13.2070325,4 Z" fill="#000000" />
										<circle fill="#000000" opacity="0.3" cx="18.5" cy="5.5" r="2.5" />
									</g>
								</svg>
								<!--end::Svg Icon-->
							</span>
						</div>
					</div>
					<div class="navi-text">
						<div class="font-weight-bold">Profile</div>
						<div class="text-muted">Update your password</div>
					</div>
				</div>
			</a>
			<!--end:Item-->
			<!--begin::Item-->
			<a href="<?php echo site_url('messages'); ?>" class="navi-item">
				<div class="navi-link">
					<div class="symbol symbol-40 bg-light mr-3">
						<div class="symbol-label">
							<span class="svg-icon svg-icon-md svg-icon-warning">
								<!--begin::Svg Icon | path:assets/media/svg/icons/Shopping/Chart-bar1.svg-->
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<rect x="0" y="0" width="24" height="24" />
										<rect fill="#000000" opacity="0.3" x="12" y="4" width="3" height="13" rx="1.5" />
										<rect fill="#000000" opacity="0.3" x="7" y="9" width="3" height="8" rx="1.5" />
										<path d="M5,19 L20,19 C20.5522847,19 21,19.4477153 21,20 C21,20.5522847 20.5522847,21 20,21 L4,21 C3.44771525,21 3,20.5522847 3,20 L3,4 C3,3.44771525 3.44771525,3 4,3 C4.55228475,3 5,3.44771525 5,4 L5,19 Z" fill="#000000" fill-rule="nonzero" />
										<rect fill="#000000" opacity="0.3" x="17" y="11" width="3" height="6" rx="1.5" />
									</g>
								</svg>
								<!--end::Svg Icon-->
							</span>
						</div>
					</div>
					<div class="navi-text">
						<div class="font-weight-bold">Messages</div>
						<div class="text-muted">Inbox</div>
					</div>
				</div>
			</a>
			<!--end:Item-->
		</div>
		<!--end::Nav-->
	</div>
	<!--end::Content-->
</div>
<!-- end::User Panel-->
<script>window.userpilotSettings = {token: "97ku39v4"} </script>
<script src = "https://js.userpilot.io/sdk/latest.js"></script>
<script>
	<?php
	$userpilot_data = array(
		'name' => htmlspecialchars($this->auth->user->first . ' ' . $this->auth->user->surname),
		'email' =>  $this->auth->user->email,
		'created_at' => time(),
		'company' => array('id' => $this->auth->user->accountID,
							'name' => $this->auth->user->accountName,
							'created_at' => time()
						),
		'projectId' => $this->settings_library->get_permission_level_label($this->auth->user->department)
	);  
	?>
	userpilot.identify("<?php echo $this->auth->user->staffID?>", <?php echo json_encode($userpilot_data)?>);
</script>
