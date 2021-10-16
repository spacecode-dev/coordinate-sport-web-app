<?php
// check permission
if ($this->auth->has_features(array('bookings_timetable_own', 'bookings_timetable_confirmation')) && $this->auth->user->non_delivery != 1) {
	$show_confirm = FALSE;
	$timetable_confirm_weeks = intval($this->settings_library->get('timetable_confirm_weeks'));
	if ($timetable_confirm_weeks < 1) {
		$timetable_confirm_weeks = 1;
	}

	// if is current week
	if ($week == date("W") && $year == date("Y")) {
		// if before switch day, show link
		if (date("N") < $switch_day) {
			$show_confirm = TRUE;
		} else {
			?><h3 id="confirmation">Confirmation</h3><?php
			?><p>Next week's timetable is now available to confirm</p><?php
		}
	} else if (strtotime(get_date_from_week($week, $year)) <= strtotime(get_date_from_week((date("W")+$timetable_confirm_weeks), date('Y')))) {
		// check if should show for future weeks
		$show_confirm = TRUE;
	}

	if ($show_confirm === TRUE) {
		?><h3 id="confirmation">Confirmation</h3><?php
		$where = array(
			'week' => $week,
			'year' => $year,
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('timetable_read')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			?><p><a href="<?php echo site_url($timetable_base . '/confirm/' . $year . '/' . $week); ?>" class="confirm">Confirm timetable</a></p><?php
		} else {
			?><p>Timetable confirmed</p><?php
		}
	}
}