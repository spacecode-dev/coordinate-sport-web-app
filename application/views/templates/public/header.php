<!--begin::Main-->
<div class="d-flex flex-column flex-root">
	<!--begin::Login-->
	<div class="login login-4 login-signin-on d-flex flex-row-fluid" id="kt_login">
		<div class="d-flex flex-center flex-row-fluid">
			<div class="login-form text-center p-7 position-relative overflow-hidden">
				<!--begin::Login Header-->
				<div class="d-flex flex-center mb-10">
					<a href="/">
						<?php
						// get acocunt ID from custom domain
						$accountID = resolve_custom_domain();
						if (empty($accountID)) {
							$accountID = 'default';
						}
						$logo_data = @unserialize($this->settings_library->get('logo', $accountID));
						if ($logo_data !== FALSE) {
							$args = array(
								'alt' => $this->settings_library->get('company', $accountID),
								'src' => 'attachment/setting/logo/' . $accountID
							);
							echo img($args);
						}
						?>
					</a>
				</div>
				<!--end::Login Header-->
				<!--begin::Login Form-->
				<div class="login-signin">
					<div class="mb-5">
						<h3><?php echo $title; ?></h3>
						<?php
						if (isset($instruction) && !empty($instruction)) {
							?><div class="text-muted font-weight-bold"><?php echo $instruction; ?></div><?php
						}
						?>
					</div>
