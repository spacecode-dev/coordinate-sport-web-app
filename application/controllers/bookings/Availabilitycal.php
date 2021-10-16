<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Availabilitycal extends MY_Controller {

	private $booking_info;

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('availability_cals'));
	}

	/**
	 * show cal
	 * @return void
	 */
	public function index($calID = NULL) {

		if ($calID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'availability_cals.calID' => $calID,
			'availability_cals.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('availability_cals.*, GROUP_CONCAT(' . $this->db->dbprefix('availability_cals_activities') . '.activityID) as activities')->from('availability_cals')->join('availability_cals_activities', 'availability_cals.calID = availability_cals_activities.calID', 'left')->where($where)->group_by('availability_cals.calID')->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$cal_info = $row;
		}

		$cal_info->activities = explode(',', $cal_info->activities);

		// get slots
		$slots = array();
		$where = array(
			'calID' => $calID,
			'accountID' => $this->auth->user->accountID
		);
		// run query
		$query = $this->db->from('availability_cals_slots')->where($where)->order_by('startTime asc, endTime asc, name asc')->get();

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$slots[$row->slotID] = array(
					'name' => $row->name,
					'startTime' => substr($row->startTime, 0, 5),
					'endTime' => substr($row->endTime, 0, 5)
				);
			}
		}

		// get days
		$days = array(
			'monday' => 'Mondays',
			'tuesday' => 'Tuesdays',
			'wednesday' => 'Wednesdays',
			'thursday' => 'Thursdays',
			'friday' => 'Fridays',
			'saturday' => 'Saturdays',
			'sunnday' => 'Sundays',
		);

		// set defaults
		$icon = 'calendar-alt';
		$current_page = 'availability_cals_' . $calID;
		$page_base = 'bookings/availabilitycal/' . $calID;
		$section = 'bookings';
		$title = $cal_info->name;
		$buttons = NULL;
		if (in_array($this->auth->user->department, array('directors', 'management'))) {
			$buttons = '<a class="btn" href="' . site_url('settings/availabilitycals/edit/' . $calID) . '">Edit</a>';
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// default to this week
		$date_from = date('Y-m-d', strtotime('monday this week'));
		$date_to = date('Y-m-d', strtotime('sunday this week'));

		$search_fields = array(
			'from' => NULL,
			'to' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_to', 'Date To', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['from'] = set_value('search_from');
			$search_fields['to'] = set_value('search_to');

			// store search fields
			$this->session->set_userdata('search-availability_cal', $search_fields);

		} else if (is_array($this->session->userdata('search-availability_cal'))) {
			// retrieve search fields from session
			foreach ($this->session->userdata('search-availability_cal') as $key => $value) {
				$search_fields[$key] = $value;
			}
		}

		// always searching
		if ($search_fields['from'] != '') {
			$from = uk_to_mysql_date($search_fields['from']);
			if ($from !== FALSE) {
				$date_from = $from;
			}
		}

		if ($search_fields['to'] != '') {
			$to = uk_to_mysql_date($search_fields['to']);
			if ($to !== FALSE) {
				$date_to = $to;
			}
		}

		// check to after from
		if (strtotime($date_from) > strtotime($date_to)) {
			// reset to this week
			$date_from = date('Y-m-d', strtotime('monday this week'));
			$date_to = date('Y-m-d', strtotime('sunday this week'));
		}

		// if less than 1 week difference, unset days
		$dStart = new DateTime($date_from);
		$dEnd  = new DateTime($date_to);
		$dDiff = $dStart->diff($dEnd);
		$days_searched = $dDiff->days;
		if ($days_searched <= 6) {
			$days_to_show = array();
			$date = $date_from;
			while (strtotime($date) <= strtotime($date_to)) {
				$day = strtolower(date('l', strtotime($date)));
				$days_to_show[] = $day;
				// update label
				$days[$day] = date('D jS M', strtotime($date));
				$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
			}
			foreach ($days as $day => $label) {
				if (!in_array($day, $days_to_show)) {
					unset($days[$day]);
				}
			}
		}

		// append dates to title
		$title .= ' (' . mysql_to_uk_date($date_from) . ' - ' . mysql_to_uk_date($date_to) . ')';

		// get activities
		$activities = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res_activities = $this->db->from('activities')->where($where)->where_in('activityID', $cal_info->activities)->order_by('name asc')->get();
		if ($res_activities->num_rows() > 0) {
			foreach ($res_activities->result() as $row) {
				$activities[$row->activityID] = $row->name;
			}
		}

		// get staff with matching activities
		$staff = array();
		$staff_ids = array();
		$where = array(
			'staff.accountID' => $this->auth->user->accountID,
			'non_delivery !=' => 1,
			'staff.active' => 1
		);
		$res_staff = $this->db->select('staff.staffID, staff.first, staff.surname, GROUP_CONCAT(' . $this->db->dbprefix('staff_activities') . '.activityID) as activities')->from('staff')->join('staff_activities', 'staff.staffID = staff_activities.staffID', 'inner')->where($where)->where_in('staff_activities.activityID', $cal_info->activities)->group_by('staff.staffID')->order_by('staff.first asc, staff.surname asc')->get();
		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				$row->activities = explode(',', $row->activities);
				$staff[$row->staffID] = $row;
				$staff_ids[] = $row->staffID;
			}
		} else {
			// add one, if none to avoid errors
			$staff_ids[] = -1;
		}

		// get staff hours
		$staff_hours = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res_hours = $this->db->from('staff_availability')->where($where)->where_in('staffID', $staff_ids)->get();
		if ($res_hours->num_rows() > 0) {
			foreach ($res_hours->result() as $row) {
				$staff_hours[$row->staffID][$row->day][] = array(
					'from' => $row->from,
					'to' => $row->to
				);
			}
		}

		// get staff exceptions - holidays, etc
		$staff_exceptions = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$where_custom = "((`from` <= " . $this->db->escape($date_to . ' 23:59:59') . ") AND (" . $this->db->escape($date_from . ' 00:00:00') . " <= `to`))";
		$res_exceptions = $this->db->from('staff_availability_exceptions')->where($where)->where($where_custom, NULL, FALSE)->where_in('staffID', $staff_ids)->get();
		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $row) {
				$staff_exceptions[$row->staffID][] = array(
					'from' => $row->from,
					'to' => $row->to,
					'name' => $row->reason
				);
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$where = array(
			'date >=' => $date_from,
			'date <=' => $date_to,
			'accountID' => $this->auth->user->accountID
		);
		$res_exceptions = $this->db->from('bookings_lessons_exceptions')->where($where)->get();
		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $row) {
				$lesson_exceptions[$row->lessonID][] = array(
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'date' => $row->date,
					'type' => $row->type
				);
			}
		}

		// get session staff matching above and with blocks that interesect search dates
		$lessons = array();
		$where = array(
			'bookings_lessons_staff.accountID' => $this->auth->user->accountID
		);
		$where_custom = "((" . $this->db->dbprefix('bookings_blocks') . ".startDate <= " . $this->db->escape($date_to) . ") AND (" . $this->db->escape($date_from) . " <= " . $this->db->dbprefix('bookings_blocks') . ".endDate))";
		$res_lesson_staff = $this->db->select('bookings_lessons_staff.*, bookings_lessons.day, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end, bookings_blocks.provisional')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where_in('bookings_lessons_staff.staffID', $staff_ids)->where($where)->where($where_custom, NULL, FALSE)->get();

		if ($res_lesson_staff->num_rows() > 0) {
			foreach ($res_lesson_staff->result() as $row) {
				// if staff doesn't teach activties required, skip
				if (count(array_intersect($staff[$row->staffID]->activities, $cal_info->activities)) == 0) {
					continue;
				}

				// use blocks dates
				$lesson_start = $row->block_start;
				$lesson_end = $row->block_end;
				// if session dates set, use them
				if (!empty($row->lesson_start)) {
					$lesson_start = $row->lesson_start;
				}
				if (!empty($row->lesson_end)) {
					$lesson_end = $row->lesson_end;
				}
				// if staff dates within above, use them
				if (strtotime($row->startDate) >= strtotime($lesson_start) && strtotime($row->startDate) <= strtotime($lesson_end)) {
					$lesson_start = $row->startDate;
				}
				if (strtotime($row->endDate) >= strtotime($lesson_start) && strtotime($row->endDate) <= strtotime($lesson_end)) {
					$lesson_end = $row->endDate;
				}
				// if above dates outside search dates, limit
				if (strtotime($lesson_start) < strtotime($date_from)) {
					$lesson_start = $date_from;
				}
				if (strtotime($lesson_end) > strtotime($date_to)) {
					$lesson_end = $date_to;
				}

				// loop through dates on lesson
				$date = $lesson_start;
				while (strtotime($date) <= strtotime($lesson_end)) {
					$day = strtolower(date('l', strtotime($date)));

					// check there is a session on this day
					if ($day != $row->day) {
						$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
						continue;
					}

					// check for exception
					if (array_key_exists($row->lessonID, $lesson_exceptions)) {
						foreach ($lesson_exceptions[$row->lessonID] as $exception) {
							// check if date is relevant
							if ($date !== $exception['date']) {
								// if not, skip this date
								$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
								continue;
							}
							// if cancellation, skip
							if ($exception['type'] == 'cancellation') {
								$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
								continue 2;
							}
							// if staff replaced, swap
							if ($exception['fromID'] == $row->staffID) {
								$row->staffID = $exception['staffID'];
							}
						}
					}

					// store
					$lessons[$row->staffID][$row->day][$row->lessonID][$date] = array(
						'startTime' => $row->startTime,
						'endTime' => $row->endTime,
						'provisional' => boolval($row->provisional)
					);

					$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
				}
			}
		}

		// gather slot data
		$slot_data = array();
		foreach ($slots as $slotID => $slot) {
			foreach ($days as $day => $day_label) {
				foreach ($cal_info->activities as $activityID) {
					$slot_data[$slotID][$day][$activityID] = array(
						'available' => array(),
						'provisional' => array(),
						'conflict' => array()
					);

					// check staff
					foreach ($staff as $staffID => $staff_data) {
						// if staff doesn't teach activity, skip
						if (!in_array($activityID, $staff_data->activities)) {
							continue;
						}

						// check within working hours
						$within_hours = FALSE;
						$only_partially_available = FALSE;
						if (isset($staff_hours[$staffID][$day])) {
							foreach ($staff_hours[$staffID][$day] as $hours) {
								// check if available at least some of the slot
								if ((strtotime($hours['from']) < strtotime($slot['endTime'])) && (strtotime($slot['startTime']) < strtotime($hours['to']))) {
									$within_hours = TRUE;
									// check if available full slot
									if ((strtotime($hours['from']) > strtotime($slot['startTime'])) || strtotime($hours['to']) < (strtotime($slot['endTime']))) {
										$only_partially_available = TRUE;
									}
								}
							}
						}
						// if not, skip
						if ($within_hours !== TRUE) {
							continue;
						}

						// check for session conflicts
						$available = TRUE;
						$conflicts = array();
						$provisional_conflicts = array();
						if (isset($lessons[$staffID][$day])) {
							foreach ($lessons[$staffID][$day] as $lessonID => $lesson_info) {
								foreach ($lesson_info as $date => $lesson_data) {
									if ((strtotime($lesson_data['startTime']) < strtotime($slot['endTime'])) && (strtotime($slot['startTime']) < strtotime($lesson_data['endTime']))) {
										$available = FALSE;
										$conflict_key = $date;
										if ($lesson_data['provisional'] === TRUE) {
											$provisional_conflicts[$conflict_key] = mysql_to_uk_date($date);
										} else {
											$conflicts[$conflict_key] = mysql_to_uk_date($date);
										}
									}
								}
							}
						}

						// check for staff exceptions - holidays, etc
						if (array_key_exists($staffID, $staff_exceptions)) {
							$date = $date_from;
							while (strtotime($date) <= strtotime($date_to)) {
								$day_tmp = strtolower(date('l', strtotime($date)));
								// if day doesn't match, skip
								if ($day_tmp != $row->day) {
									$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
									continue;
								}
								foreach ($staff_exceptions[$staffID] as $exception_info) {
									if ((strtotime($exception_info['from']) < strtotime($date . ' ' . $slot['endTime'])) && (strtotime($date . ' ' . $slot['startTime']) < strtotime($exception_info['to']))) {
										$available = FALSE;
										$conflict_key = $date . '_' . $exception_info['name'];
										$conflicts[$conflict_key] = mysql_to_uk_date($date) . ' (' .$exception_info['name'] . ')';
									}
								}
								$date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
							}
						}

						if ($available === TRUE) {
							$slot_data[$slotID][$day][$activityID]['available'][] = array(
								'name' => $staff_data->first . ' ' . $staff_data->surname,
								'only_partially_available' => $only_partially_available,
								'conflicts' => array()
							);
						} else if (count($provisional_conflicts) > 0) {
							$slot_data[$slotID][$day][$activityID]['provisional'][] = array(
								'name' => $staff_data->first . ' ' . $staff_data->surname,
								'only_partially_available' => $only_partially_available,
								'conflicts' => $provisional_conflicts
							);
						} else {
							$slot_data[$slotID][$day][$activityID]['conflict'][] = array(
								'name' => $staff_data->first . ' ' . $staff_data->surname,
								'only_partially_available' => $only_partially_available,
								'conflicts' => $conflicts
							);
						}
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'calID' => $calID,
			'cal_info' => $cal_info,
			'slots' => $slots,
			'slot_data' => $slot_data,
			'activities' => $activities,
			'staff' => $staff,
			'date_from' => $date_from,
			'date_to' => $date_to,
			'days' => $days,
			'days_searched' => $days_searched,
			'search_fields' => $search_fields,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/availabilitycal', $data);
	}
}

/* End of file Availabilitycal.php */
/* Location: ./application/controllers/bookings/Availabilitycal.php */
