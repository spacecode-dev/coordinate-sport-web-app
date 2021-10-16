<?php
$edit_link = 'bookings/contract/' . $bookingID;
if ($is_project == 1) {
	if ($type == 'event') {
		$edit_link = 'bookings/event/' . $bookingID;
	} else {
		$edit_link = 'bookings/course/' . $bookingID;
	}
}
?>
<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<li class="nav-item">
				<a href='<?php echo site_url($edit_link); ?>' class="nav-link<?php if ($tab == 'details') { echo ' active'; } ?>">
					Details
				</a>
			</li>
			<li class="nav-item">
				<a href='<?php echo site_url('bookings/blocks/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'blocks') { echo ' active'; } ?>">
					Blocks
				</a>
			</li>
			<?php
			// also used on last tab for getting next block to book
			$where = array(
				'bookingID' => $bookingID,
				'accountID' => $this->auth->user->accountID,
				'endDate >=' => mdate('%Y-%m-%d')
			);
			$res_blocks = $this->db->from('bookings_blocks')->where($where)->order_by('startDate asc')->limit(1)->get();
			if ($res_blocks->num_rows() == 0) {
				// rerun without restriction to get last block instead
				unset($where['endDate >=']);
				$res_blocks = $this->db->from('bookings_blocks')->where($where)->order_by('endDate desc')->limit(1)->get();
			}
			if ($res_blocks->num_rows() > 0) {
				?><li class="nav-item">
					<a href='<?php
					if (isset($blockID) && !empty($blockID)) {
						echo site_url('bookings/sessions/' . $bookingID . '/' . $blockID);
					} else {
						echo site_url('bookings/sessions/' . $bookingID);
					}
					?>' class="nav-link<?php if ($tab == 'lessons') { echo ' active'; } ?>">Sessions</a>
				</li>
				<?php
				if ($this->auth->has_features('bookings_exceptions')) {
					?><li class="nav-item">
						<a href='<?php echo site_url('bookings/exceptions/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'exceptions') { echo ' active'; } ?>">
							Exceptions
						</a>
					</li><?php
				}
			}
			if($this->auth->has_features('online_booking_subscription_module')) {
				?><li class="nav-item">
					<a href='<?php echo site_url('bookings/subscriptions/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'subscriptions') { echo ' active'; } ?>">
						Subscriptions
					</a>
				</li><?php
			}
			if ($type == 'event' || $is_project == 1) {
				// check if has a session with birthday type
				$where = array(
					'bookings_lessons.bookingID' => $bookingID,
					'lesson_types.birthday_tab' => 1,
					'bookings_lessons.accountID' => $this->auth->user->accountID
				);
				$birthday_res = $this->db->from('bookings_lessons')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'inner')->where($where)->limit(1)->get();
				if ($birthday_res->num_rows() > 0) {
					?><li class="nav-item">
						<a href='<?php echo site_url('bookings/birthday/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'birthday') { echo ' active'; } ?>">
							Birthday
						</a>
					</li><?php
				}
				if (!in_array($booking_info->register_type, array('numbers', 'names', 'bikeability', 'shapeup'))) {
					?><li class="nav-item">
						<a href='<?php echo site_url('bookings/vouchers/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'vouchers') { echo ' active'; } ?>">
							Vouchers
						</a>
					</li><?php
				}
				?>
				<li class="nav-item">
					<a href='<?php echo site_url('bookings/attachments/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'attachments') { echo ' active'; } ?>">
						Attachments
					</a>
				</li>
				<?php
			}
			?>
			<li class="nav-item">
				<a href='<?php echo site_url('bookings/messaging/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'messaging') { echo ' active'; } ?>">
					Messaging
				</a>
			</li>
			<li class="nav-item">
				<?php
				$finance_url = site_url('bookings/costs/'.$bookingID);
				if (($type == 'event' && $this->auth->has_features('reports') && in_array($this->auth->user->department, array('directors', 'management'))) || $type == 'booking') {
					$finance_url = site_url('bookings/finances/'.($type=="event" ? "profit" : "invoices").'/' . $bookingID);
				}
				?>
				<a href='<?php echo $finance_url; ?>' class="nav-link<?php if ($tab == 'invoices' || $tab == 'profit' || $tab == 'costs') { echo ' active'; } ?>">
					Finances
				</a>
				</li><?php
			if ($this->auth->has_features('reports') && in_array($this->auth->user->department, array('directors', 'management'))) {
				if ($type == 'event' || $is_project == 1) {
					?><li class="nav-item">
						<a href='<?php echo site_url('bookings/report/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'report') { echo ' active'; } ?>">
							Report
						</a>
					</li><?php
				}
			}
			if ($res_blocks->num_rows() > 0 && $this->auth->has_features('participants') && ($type == 'event' || $is_project == 1) && !in_array($booking_info->register_type, array('numbers', 'names', 'bikeability', 'shapeup'))) {
				// get next block with bookable sessions
				$where = array(
					'bookings_blocks.bookingID' => $bookingID
				);
				$blocks = $this->cart_library->get_blocks($where);
				if (count($blocks) > 0) {
					foreach ($blocks as $blockID => $block) {
						?><li class="nav-item">
							<a href='<?php echo site_url('booking/book/' . $blockID); ?>' class="nav-link<?php if ($tab == 'participants') { echo ' active'; } ?>">
								Add <?php echo $this->settings_library->get_label('participant'); ?>
							</a>
						</li><?php
						break;
					}
				}
			}
			?>
		</ul>
	</div>
</div>

<?php
if ($tab == 'details') {
?>
<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line">
			<li class="nav-item">
				<a href='<?php echo site_url($edit_link . '/information'); ?>' class="nav-link<?php if ($this->crm_library->last_segment() == 'information' || $this->crm_library->last_segment() == $bookingID) { echo ' active'; } ?>">
					Information
				</a>
			</li>
			<?php if($is_project == 1){ ?>
			<li class="nav-item hide-for-numbers-and-names">
				<a href='<?php echo site_url($edit_link . '/booking-site'); ?>' class="nav-link<?php if ($this->crm_library->last_segment() == 'booking-site') { echo ' active'; } ?>">
					Booking Site
				</a>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>
<?php
}
elseif ($tab == 'invoices' || $tab == 'profit'|| $tab == 'costs') {
	?>
	<div class="card card-custom">
		<div class="card-header card-header-tabs-line">
			<ul class="nav nav-tabs nav-bold nav-tabs-line">
				<?php
				if ($type == 'booking') {
				?>
				<li class="nav-item">
					<a href='<?php echo site_url('bookings/finances/invoices/' . $bookingID); ?>' class="nav-link<?php if ($tab == 'invoices') { echo ' active'; } ?>">
						Invoices
					</a>
				</li>
				<?php
				}
				if ($this->auth->has_features('reports') && in_array($this->auth->user->department, array('directors', 'management')) && ($type == 'booking' || $type == 'events')) {
					?>
					<li class="nav-item">
						<a href='<?php echo site_url('bookings/finances/profit/' . $bookingID); ?>' title='Profit &amp; Loss' class="nav-link<?php if ($tab == 'profit') { echo ' active'; } ?>">
							P&amp;L
						</a>
					</li>
					<?php
				}
				?>
				<li class="nav-item">
					<a href='<?php echo site_url('bookings/costs/'. $bookingID); ?>' class="nav-link<?php if ($tab == 'costs') { echo ' active'; } ?>">
						Costs
					</a>
				</li>
			</ul>
			<?php if ($this->auth->has_features('reports') && in_array($this->auth->user->department, array('directors', 'management')) && ($type == 'booking' || $type == 'events') && $tab == 'profit') { ?>
				<?php echo form_open($page_base, "id='search-form'"); ?>

					<p>
					<h4>Filter by Week</h4><?php
					$options = array();
					$options[0] = 'Select Week';
					for ($i = 1; $i <= $max_weeks; $i++) {
						$dt = new DateTime;
						$options[$i] = 'Week ' . $i . ' (' . $dt->setISODate($year, $i, 1)->format('jS M') . ')';
					}
					echo form_dropdown('week', $options, $week, 'id="week" class=""');
					$options = array();
					for ($i = $year - 3; $i <= $year + 3; $i++) {
						$options[$i] = $i;
					}
					echo form_dropdown('year', $options, $year, 'id="year" class=""');
					?></p><?php
				echo form_close();
				?>
			<?php } ?>
		</div>
	</div>
<?php
}
?>
