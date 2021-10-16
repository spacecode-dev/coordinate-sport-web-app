<div id="top"></div>
<?php echo $this->settings_library->get('online_booking_header', $this->online_booking->accountID); ?>
<header class="main">
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?php echo $this->online_booking->account->default_view; ?>">
					<?php
					$logo_data = @unserialize($this->settings_library->get('logo', $this->online_booking->accountID));
					if ($logo_data !== FALSE) {
						$args = array(
							'alt' => 'Image',
							'src' => 'attachment/setting/logo/' . $this->online_booking->accountID,
							'class' => 'logo',
							'height' => 40,
							'alt' => $this->online_booking->account->company
						);
						echo img($args);
					} else {
						echo $this->online_booking->account->company;
					}
					?>
				</a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
					<li><a href="<?php echo $this->online_booking->account->default_view; ?>">Event Search</a></li>
					<?php
					if ($this->online_booking->user !== FALSE) {
						?><li class="dropdown">
							<a href="<?php echo site_url('account'); ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Account <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo site_url('account'); ?>">Overview</a></li>
								<li><a href="<?php echo site_url('account/logout'); ?>">Logout</a></li>
							</ul>
						</li><?php
					} else {
						?><li><a href="<?php echo site_url('account/login'); ?>">Login</a></li>
						<li><a href="<?php echo site_url('account/register'); ?>">Register</a></li><?php
					}
					?>
					<li><a href="<?php echo site_url('cart'); ?>"><span class="shopping-cart">
						<i class="fas fa-shopping-cart"></i>
						<?php
						if ($this->online_booking->user !== FALSE && $this->cart_library->count > 0) {
							?><span class="counter"><?php echo intval($this->cart_library->count); ?></span><?php
						}
						?>
					</span></a></li>
				</ul>
			</div>
		</div>
	</nav>
</header>
<div id="content">
	<?php
	$banner_data = @unserialize($this->settings_library->get('online_booking_header_image', $this->online_booking->accountID));
	if ($banner_data !== FALSE) {
		?><img id="banner" src="<?php echo site_url('attachment/setting/online_booking_header_image/' . $this->online_booking->accountID); ?>" alt="<?php echo $this->online_booking->account->company; ?>"><?php
	}
	?>
	<div class="container">
