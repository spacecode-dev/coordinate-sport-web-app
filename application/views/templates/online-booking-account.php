<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('templates/online-booking/meta'); ?>
</head>
<body class="<?php if (isset($body_class)) { echo $body_class; } if (isset($lightbox) && $lightbox == TRUE) { echo ' lightbox'; } ?>">
	<?php
	if (!isset($lightbox) || $lightbox != TRUE) {
		$this->load->view('templates/online-booking/header');
	}
	?><h1>Welcome <?php echo $this->online_booking->user->first_name; ?></h1>
	<?php
	// get next booking/block
	$where = array(
		'bookings_cart_sessions.accountID' => $this->cart_library->accountID,
		'bookings_cart.familyID' => $this->cart_library->familyID,
		'bookings_cart.type' => 'booking',
		'bookings_blocks.endDate >=' => date('Y-m-d'),
		'bookings_cart_sessions.date >=' => date('Y-m-d')
	);
	$res = $this->db->select('bookings_cart_sessions.cartID, bookings_cart_sessions.blockID, GROUP_CONCAT(DISTINCT CONCAT_WS(" ", `' . $this->db->dbprefix('bookings_cart_sessions') . '`.`date`, `' . $this->db->dbprefix('bookings_lessons') . '`.`startTime`)) AS sessions, GROUP_CONCAT(DISTINCT CONCAT_WS(" ", `' . $this->db->dbprefix('family_contacts') . '`.`first_name`, `' . $this->db->dbprefix('family_contacts') . '`.`last_name`)) as contact_participants, GROUP_CONCAT(DISTINCT CONCAT_WS(" ", `' . $this->db->dbprefix('family_children') . '`.`first_name`, `' . $this->db->dbprefix('family_children') . '`.`last_name`)) as child_participants')
	->from('bookings_cart_sessions')
	->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
	->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID', 'inner')
	->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
	->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
	->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
	->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
	->where($where)
	->order_by('bookings_blocks.startDate asc')
	->group_by('bookings_cart_sessions.blockID')
	->limit(1)
	->get();

	if ($res->num_rows() > 0) {
		foreach ($res->result() as $row) {
			// look up block
			$where = array(
				'bookings_blocks.blockID' => $row->blockID
			);
			$blocks = $this->cart_library->get_blocks($where);
			if (count($blocks) == 0) {
				break;
			}
			foreach ($blocks as $block) {
				break;
			}
			// work out next session date
			$next_session_date = NULL;
			$sessions = explode(',', $row->sessions);
			if (!is_array($sessions)) {
				break;
			}
			$sessions = array_filter($sessions);
			if (count($sessions) == 0) {
				break;
			}
			natsort($sessions);
			foreach ($sessions as $session) {
				// if already in past, skip
				if (strtotime($session) < time()) {
					continue;
				}
				$next_session_date = $session;
				break;
			}
			if (empty($next_session_date)) {
				break;
			}
			?><p class="intro">Below are details of your...</p>
			<h2 class="with-line">Next Booking</h2>
			<div class="row next-booking">
				<div class="col-sm-3 col-md-2">
					<?php
					if (count($block->images) > 0) {
						$data = array(
							'src' => $block->images[0]['thumb'],
							'alt' => $block->booking
						);
						echo img($data);
					}
					// show participants
					$row->participants = $row->contact_participants;
					if (empty($row->participants)) {
						$row->participants = $row->child_participants;
					}
					$participants = explode(",", $row->participants);
					$participants = array_filter($participants);
					if (count($participants) > 0) {
						?><h3 class="with-line h4 light">Participants</h3>
						<ul><?php
							foreach ($participants as $participant) {
								?><li><?php echo $participant; ?></li><?php
							}
						?></ul><?php
					}
					?>
					<a href="<?php echo site_url('account/booking/' . $row->cartID); ?>#details" class="btn btn-block btn-hollow">View Details</a>
				</div>
				<div class="col-sm-6 col-md-7">
					<div class="countdown" data-countdown-to="<?php echo date('Y/m/d H:i:00', strtotime($next_session_date)); ?>"></div>
					<p class="event-specs">
						<span><i class="fas fa-map-marker-alt"></i> <?php
						if (!empty($block->block_org)) {
							echo $block->block_org;
						} else {
							echo $block->org;
						}
						?></span>
						<span><i class="fas fa-arrow-circle-right"></i> <?php echo $block->block; ?></span>
						<span><i class="fas fa-calendar-alt"></i> <?php echo mysql_to_uk_date($block->startDate); ?> to <?php echo mysql_to_uk_date($block->endDate); ?></span>
					</p>
					<?php
					if (!empty($block->website_description)) {
						echo "<p>" . substr($block->website_description, 0, strpos($block->website_description, "\n")) . ".</p>";
					}
					?>
				</div>
				<div class="col-sm-3">
					<?php
					if (is_array($block->coordinates)) {
						$markers = array(
							array(
								'label' => $block->booking,
								'link' => '',
								'color' => $block->colour,
								'lat' => $block->coordinates[0],
								'lng' => $block->coordinates[1]
							)
						);
						?><script>
							var map_markers = <?php echo json_encode($markers); ?>;
						</script>
						<div id="map"></div>
						<a href="https://maps.google.co.uk/maps?f=d&amp;daddr=<?php echo implode(',', $block->coordinates); ?>" class="btn btn-block" target="_blank">Get Directions</a>
						<?php
					}
					?>
				</div>
			</div><?php

		}
	}
	?>
	<div id="details">
		<div class="balance">
			<p><i class="fas fa-arrow-circle-right"></i> Your current account balance is <?php echo currency_symbol($this->online_booking->accountID) . number_format($this->cart_library->get_family_account_balance(), 2); ?></p>
		</div>

		<div id="tabs">
			<div class="row flex">
			    <ul class="nav nav-pills nav-stacked col-xs-12 col-sm-3">
			        <li<?php if ($tab == 'bookings') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account'); ?>#details">Bookings</a></li>
			        <li<?php if ($tab == 'payments') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/payments'); ?>#details">Payments</a></li>
					<li<?php if ($tab == 'payment-plans') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/payment-plans'); ?>#details">Payment Plans</a></li>
					<li<?php if ($tab == 'subscriptions') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/subscriptions'); ?>#details">Subscriptions</a></li>
			        <li<?php if ($tab == 'profile') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/profile'); ?>#details">Profile</a></li>
					<li<?php if ($tab == 'participants') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/participants'); ?>#details">Participants</a></li>
					<?php
					if ($this->online_booking->account->addon_shapeup == 1 || (isset($this->online_booking->account->addons_all) && $this->online_booking->account->addons_all == 1)) {
						?><li<?php if ($tab == 'shapeup') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/shapeup'); ?>#details">Shape Up</a></li><?php
					}
					?>
					<li<?php if ($tab == 'privacy') { echo ' class="active"'; } ?>><a href="<?php echo site_url('account/privacy'); ?>#details">Data &amp; Privacy</a></li>
			    </ul>
			    <div class="tab-content col-xs-12 col-sm-9">
					<?php echo $content; ?>
			    </div>
			</div>
		</div>
	</div><?php
	if (!isset($lightbox) || $lightbox != TRUE) {
		$this->load->view('templates/online-booking/footer');
	}
	$this->load->view('templates/online-booking/footer-meta');
	?>
</body>
</html>
