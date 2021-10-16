<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Reports_library {

	private $CI;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
		$this->CI->load->model('Lessons/LessonsModel');
		$this->CI->load->model('LessonsExceptionsModel');
		$this->CI->load->model('Lessons/LessonsStaffModel');
		$this->CI->load->model('Staff/StaffModel');
	}

	/**
	 * calculate staff utilisation
	 * @param string $type all or averages
	 * @param array $search_fields
	 * @return array
	 */
	public function calc_utilisation($type = 'all', $search_fields = array()) {

		// if dates empty, add default
		if (!isset($search_fields['date_from']) || empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-6 days'));
		}
		if (!isset($search_fields['date_from']) || empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// set dates
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		// build search query
		$search_where = array();

		if (isset($search_fields['search']) && $search_fields['search'] == 'true') {
			if (isset($search_fields['staff_id']) && $search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
			}

			if (isset($search_fields['is_active']) && $search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` = 1';
				} else {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` != 1';
				}
			}
		}

		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// set where
		$where = array(
			'staff.accountID' => $this->CI->auth->user->accountID,
			'staff.target_utilisation >' => 0
		);

		// get staff
		$staff = $this->CI->db->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		// get session staff
		$lesson_staff = array();
		$where = array(
			'bookings_lessons_staff.startDate <=' => $date_to,
			'bookings_lessons_staff.endDate >=' => $date_from,
			'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID
		);
		$res_staff = $this->CI->db->select('bookings_lessons_staff.*, bookings_lessons.day')
			->from('bookings_lessons_staff')
			->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')
			->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				// don't include observers or participants
				if (in_array($row->type, array('observer', 'participant'))) {
					continue;
				}
				$lesson_staff[$row->lessonID][$row->staffID] = array(
					'type' => $row->type,
					'startDate' => $row->startDate,
					'endDate' => $row->endDate,
					'startTime' => $row->startTime,
					'endTime' => $row->endTime
				);
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$lesson_exceptions_where = array(
			'date <=' => $date_to,
			'date >=' => $date_from,
			'accountID' => $this->CI->auth->user->accountID
		);
		$res_exceptions = $this->CI->db->from('bookings_lessons_exceptions')->where($lesson_exceptions_where)->get();

		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $row) {
				$lesson_exceptions[$row->lessonID][$row->date][] = array(
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type
				);
			}
		}

		// get utilisation
		$where = array(
			$this->CI->db->dbprefix('bookings') . '.cancelled !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.startDate <=' => $date_to,
			$this->CI->db->dbprefix('bookings_blocks') . '.endDate >=' => $date_from,
			$this->CI->db->dbprefix('bookings') . '.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd,
		 	bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, bookings.brandID,
		  	bookings_blocks.provisional, bookings_lessons.startTime, bookings_lessons.endTime,
		   	bookings_lessons.lessonID, bookings_lessons.day')->from('bookings')
			->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->where($where)->group_by('bookings_lessons.lessonID')->get();

		$utilisation_data = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				// if no brand id, skip
				if (empty($row->brandID)) {
					continue;
				}

				// loop through dates
				$current_date = date('Y-m-d', strtotime('-1 day', strtotime($date_from)));
				while (strtotime($current_date) <= strtotime($date_to)) {
					// next day
					$current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));

					// if session not on this day, skip
					if ($row->day != strtolower(date('l', strtotime($current_date)))) {
						continue;
					}

					$lesson_details = array(
						'staff_ids' => array(),
						'startTime' => $row->startTime,
						'endTime' => $row->endTime,
						'brand_id' => $row->brandID
					);

					// double check is within search dates and block dates and session dates (if set)
					if (strtotime($current_date) >= strtotime($date_from) && strtotime($current_date) <= strtotime($date_to) && strtotime($current_date) >= strtotime($row->blockStart) && strtotime($current_date) <= strtotime($row->blockEnd) && ((empty($row->lessonStart) && empty($row->lessonEnd)) || (strtotime($current_date) >= strtotime($row->lessonStart) && strtotime($current_date) <= strtotime($row->lessonEnd)))) {
						// all ok
					} else {
						// skip
						continue;
					}

					// get staff
					if (array_key_exists($row->lessonID, $lesson_staff) && is_array($lesson_staff[$row->lessonID])) {
						foreach ($lesson_staff[$row->lessonID] as $staffID => $staffDetails) {
							if (strtotime($staffDetails['startDate']) <= strtotime($current_date) && strtotime($staffDetails['endDate']) >= strtotime($current_date)) {
								$lesson_details['staff_ids'][$staffID] = $staffDetails['type'];
							}
						}
					}

					// check for session exceptions
					if (array_key_exists($row->lessonID, $lesson_exceptions) && array_key_exists($current_date, $lesson_exceptions[$row->lessonID]) && is_array($lesson_exceptions[$row->lessonID][$current_date])) {
						foreach ($lesson_exceptions[$row->lessonID][$current_date] as $exception_info) {
							// if cancellation, skip
							if ($exception_info['type'] == 'cancellation') {
								continue 2;
							}

							// staff change
							if (array_key_exists($exception_info['fromID'], $lesson_details['staff_ids'])) {
								// swap if moved to another staff
								if (!empty($exception_info['staffID'])) {
									$lesson_details['staff_ids'][$exception_info['staffID']] = $lesson_details['staff_ids'][$exception_info['fromID']];
								}
								if (isset($lesson_details['staff_ids'][$exception_info['fromID']])) {
									unset($lesson_details['staff_ids'][$exception_info['fromID']]);
								}
							}
						}
					}

					if (count($lesson_details['staff_ids']) > 0) {
						foreach ($lesson_details['staff_ids'] as $staff_id => $staff_type) {
							// check for time overrides
							if (isset($lesson_staff[$row->lessonID][$staff_id])) {
								if (!empty($lesson_staff[$row->lessonID][$staff_id]['startTime'])) {
									$lesson_details['startTime'] = $lesson_staff[$row->lessonID][$staff_id]['startTime'];
								}
								if (!empty($lesson_staff[$row->lessonID][$staff_id]['endTime'])) {
									$lesson_details['endTime'] = $lesson_staff[$row->lessonID][$staff_id]['endTime'];
								}
							}
							$lesson_length = (strtotime($lesson_details['endTime']) - strtotime($lesson_details['startTime']))/(60*60);
							if (!isset($utilisation_data[$staff_id][$lesson_details['brand_id']])) {
								$utilisation_data[$staff_id][$lesson_details['brand_id']] = 0;
							}
							$utilisation_data[$staff_id][$lesson_details['brand_id']] += $lesson_length;
							if ($row->provisional == 1) {
								if (!isset($utilisation_data[$staff_id]['provisional'])) {
									$utilisation_data[$staff_id]['provisional'] = 0;
								}
								$utilisation_data[$staff_id]['provisional'] += $lesson_length;
							}
						}
					}
				}
			}
		}


		// work out how many days shown
		$date_from = new DateTime(uk_to_mysql_date($search_fields['date_from']));
		$date_to = new DateTime(uk_to_mysql_date($search_fields['date_to']));
		$days = intval($date_to->diff($date_from)->format("%a") + 1);

		// switch return type
		switch ($type) {
			case 'averages':
				$average_data = array(
					'hours' => 0,
					'target_hours' => 0,
					'target_utilisation' => 0,
					'utilisation' => 0
				);
				$staff->data_seek(0);
				foreach ($staff->result() as $item) {
					$hours = 0;
					if (array_key_exists($item->staffID, $utilisation_data) && count($utilisation_data[$item->staffID]) > 0) {
						foreach ($utilisation_data[$item->staffID] as $brandID => $brand_hours) {
							$hours += $utilisation_data[$item->staffID][$brandID];
						}
					}
					$item->target_hours = ($item->target_hours/7)*$days;
					$average_data['hours'] += $hours;
					$average_data['target_hours'] += $item->target_hours;
					$average_data['target_utilisation'] += $item->target_utilisation;
					$average_data['utilisation'] += (($hours/$item->target_hours)*100);
				}
				$averages = array();
				foreach ($average_data as $key => $value) {
					if ($staff->num_rows() > 0) {
						$averages[$key] = $value/$staff->num_rows();
					} else {
						$averages[$key] = 0;
					}
				}
				return $averages;
				break;
			case 'all':
			default:
				return $utilisation_data;
				break;
		}
	}

	/**
	 * calculate staff performance
	 * @param string $type all or top
	 * @param array $search_fields
	 * @return array
	 */
	public function calc_performance($type = 'all', $search_fields = array()) {

		// if dates empty, add default
		if (!isset($search_fields['date_from']) || empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-12 weeks'));
		}
		if (!isset($search_fields['date_from']) || empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// set dates
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		// build search query
		$search_where = array();

		if (isset($search_fields['search']) && $search_fields['search'] == 'true') {
			if (isset($search_fields['staff_id']) && $search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
			}

			if (isset($search_fields['brand_id']) && $search_fields['brand_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`brandID` = " . $this->CI->db->escape($search_fields['brand_id']);
			}

			if (isset($search_fields['teamleader_id']) && $search_fields['teamleader_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff_recruitment_approvers") . "`.`approverID` = " . $this->CI->db->escape($search_fields['teamleader_id']);
			}

			if (isset($search_fields['exclude_non_delivery']) && $search_fields['exclude_non_delivery'] == 'yes') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`non_delivery` != 1";
			}

			if (isset($search_fields['is_active']) && $search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` = 1';
				} else {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` != 1';
				}
			}
		}

		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// track performance_data
		$performance_data = array();

		// set where
		$where = array(
			'staff.accountID' => $this->CI->auth->user->accountID
		);

		// get staff
		$staff = $this->CI->db->select("staff.*")
		->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->where($search_where, NULL, FALSE)
		->order_by('staff.first asc, staff.surname asc')->get();

		// track staff
		$interested_staff = array();
		$staff_names = array();

		if ($staff->num_rows() > 0) {
			foreach ($staff->result() as $row) {
				$interested_staff[] = $row->staffID;
				$staff_names[$row->staffID] = $row->first . ' ' . $row->surname;
			}
		} else {
			// if no staff, add fake id
			$interested_staff[] = -1;
		}

		// look up brand settings, if set
		$exclude_session_evaluations = FALSE;
		$exclude_pupil_assessments = FALSE;
		if (isset($search_fields['brand_id']) && $search_fields['brand_id'] != '') {
			$where = array(
				'accountID' => $this->CI->auth->user->accountID,
				'brandID' => $search_fields['brand_id']
			);
			$brand_res = $this->CI->db->from('brands')->where($where)->limit(1)->get();
			if ($brand_res->num_rows() > 0) {
				foreach ($brand_res->result() as $row) {
					if ($row->staff_performance_exclude_session_evaluations == 1) {
						$exclude_session_evaluations = TRUE;
					}
					if ($row->staff_performance_exclude_pupil_assessments == 1) {
						$exclude_pupil_assessments = TRUE;
					}
				}
			}
		}

		// get notes
		$where_in = array(
			'feedbacknegative',
			'feedbackpositive',
			'late',
			'observation'
		);
		// if not excluding pupil assessments, include in where
		if ($exclude_pupil_assessments !== TRUE) {
			$where_in[] = 'pupilassessment';
		}
		$where = array(
			'staff_notes.accountID' => $this->CI->auth->user->accountID,
			'staff_notes.date <=' => $date_to,
			'staff_notes.date >=' => $date_from
		);
		$res = $this->CI->db->select('staff_notes.staffID, staff_notes.type, staff_notes.observation_score, staff.target_observation_score')->from('staff_notes')->join('staff', 'staff_notes.staffID = staff.staffID', 'inner')->where($where)->where_in('staff_notes.staffID', $interested_staff)->where_in('staff_notes.type', $where_in)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$val = 0;
				switch ($row->type) {
					case 'feedbacknegative':
						$row->type = 'feedback';
						$val -= 2;
						break;
					case 'feedbackpositive':
						$row->type = 'feedback';
						$val += 2;
						break;
					case 'late':
						$val -= 1;
						break;
					case 'pupilassessment':
						$val += 5;
						break;
				}
				if ($val != 0) {
					if (!isset($performance_data[$row->staffID][$row->type])) {
						$performance_data[$row->staffID][$row->type] = 0;
					}
					$performance_data[$row->staffID][$row->type] += $val;
				} else if ($row->type == 'observation' && $row->observation_score > 0) {
					if (!isset($performance_data[$row->staffID]['observation_averages'])) {
						$performance_data[$row->staffID]['observation_averages'] = array();
					}
					$performance_data[$row->staffID]['observation_target'] = $row->target_observation_score;
					$performance_data[$row->staffID]['observation_averages'][] = $row->observation_score;
				}
			}
		}

		// get average of observations and work out scores
		if (count($performance_data) > 0) {
			foreach ($performance_data as $staffID => &$items) {
				if (isset($items['observation_averages']) && is_array($items['observation_averages']) && count($items['observation_averages']) > 0 && $items['observation_target'] > 0) {
					$average = round(array_sum($items['observation_averages']) / count($items['observation_averages']));
					if ($average > $items['observation_target']) {
						$items['observation'] = ($average - $items['observation_target'])*2;
					}
					unset($items['observation_averages']);
					unset($items['observation_target']);
				}
			}
		}

		// timetable confirmations
		$begin = new DateTime( $date_from );
		$end = new DateTime( $date_to );
		$interval = DateInterval::createFromDateString('1 week');
		$period = new DatePeriod($begin, $interval, $end);
		foreach ($period as $dt) {
			$where = array(
				'week' => $dt->format("W"),
				'year' => $dt->format("Y"),
				'accountID' => $this->CI->auth->user->accountID,
			);
			$res = $this->CI->db->from('timetable_read')->where($where)->where_in('staffID', $interested_staff)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// exclude those confirmed by someone else
					if ($row->byID != $row->staffID) {
						continue;
					}
					if (!isset($performance_data[$row->staffID]['timetable'])) {
						$performance_data[$row->staffID]['timetable'] = 0;
					}
					$performance_data[$row->staffID]['timetable']++;
				}
			}
		}

		// sickness
		$where = array(
			'type' => 'unauthorised',
			'from <=' => $date_to,
			'to >=' => $date_from,
			'accountID' => $this->CI->auth->user->accountID,
		);
		$res = $this->CI->db->from('staff_availability_exceptions')->where($where)->where_in('staffID', $interested_staff)->get();
		if ($res->num_rows() > 0) {
			$staff_exceptions = array();
			foreach ($res->result() as $row) {
				$from = $row->from;
				$to = $row->to;
				// if absense overflows interested period, limit
				if (strtotime($from) < strtotime($date_from)) {
					$from = $date_from;
				}
				if (strtotime($to) > strtotime($date_to)) {
					$to = $date_to;
				}

				// loop dates
				$begin = new DateTime($from);
				$end = new DateTime($to);
				$interval = DateInterval::createFromDateString('1 day');
				$period = new DatePeriod($begin, $interval, $end);
				foreach ( $period as $dt ) {
					if (!isset($staff_exceptions[$row->staffID])) {
						$staff_exceptions[$row->staffID] = array();
					}
					$date = $dt->format("Y-m-d");;
					$staff_exceptions[$row->staffID][$date] = $date;
				}
			}
			if (count($staff_exceptions) > 0) {
				foreach ($staff_exceptions as $staffID => $dates) {
					$performance_data[$staffID]['sickness'] = count($dates)*-2;
				}
			}
		}

		// session evaluations, if not excluding
		if ($exclude_session_evaluations !== TRUE) {
			$where = array(
				'type' => 'evaluation',
				'date <=' => $date_to,
				'date >=' => $date_from,
				'accountID' => $this->CI->auth->user->accountID,
			);
			$res = $this->CI->db->from('bookings_lessons_notes')->where($where)->where_in('byID', $interested_staff)->get();
			if ($res->num_rows() > 0) {
				$session_evaluations = array();
				foreach ($res->result() as $row) {
					$week_no = date('W', strtotime($row->date));
					$submit_before = strtotime('+2 days', strtotime($row->date));
					// assume all ok
					if (!isset($session_evaluations[$row->byID])) {
						$session_evaluations[$row->byID] = array();
					}
					if (!isset($session_evaluations[$row->byID][$week_no])) {
						$session_evaluations[$row->byID][$week_no] = 'true';
					}
					// check if submitted on time
					if ($row->status == 'submitted' && strtotime($row->added) < $submit_before) {
						// all ok, do nothing
					} else {
						// mark week as missing
						$session_evaluations[$row->byID][$week_no] = 'false';
					}
				}
				if (count($session_evaluations) > 0) {
					foreach ($session_evaluations as $staffID => $weeks) {
						$week_result = array_count_values($weeks);
						if (array_key_exists('true', $week_result)) {
							$performance_data[$staffID]['sessionevaluations'] = $week_result['true']*2;
						}
					}
				}
			}
		}

		// work out totals
		if (count($performance_data) > 0) {
			foreach ($performance_data as $staffID => &$items) {
				$items['total'] = array_sum($items);
			}
		}

		// switch return type
		switch ($type) {
			case 'top':
				uasort($performance_data, function($a, $b) {
					return $a['total'] < $b['total'];
				});
				// only top x
				$performance_data = array_slice($performance_data, 0, 5, TRUE);
				$top = array();
				foreach ($performance_data as $staffID => $items) {
					$top[$staff_names[$staffID]] = $items['total'];
				}
				return $top;
				break;
			case 'all':
			default:
				return $performance_data;
				break;
		}
	}

	/**
	 * calculate timesheets report
	 * @param string $type all or averages
	 * @param array $search_fields
	 * @return array
	 */
	public function calc_timesheets($type = 'all', $search_fields = array()) {

		// if dates empty, add default
		if (!isset($search_fields['date_from']) || empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-6 days'));
		}
		if (!isset($search_fields['date_from']) || empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// set dates
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		// build search query
		$search_where = array();

		if (isset($search_fields['search']) && $search_fields['search'] == 'true') {
			if (isset($search_fields['staff_id']) && $search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
			}

			if (isset($search_fields['department']) && $search_fields['department'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`department` = " . $this->CI->db->escape($search_fields['department']);
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// set where
		$where = array(
			'staff.accountID' => $this->CI->auth->user->accountID
		);

		// get staff
		$staff = $this->CI->db->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		// get utilisation
		$where = array(
			$this->CI->db->dbprefix('timesheets_items') . '.status' => 'approved',
			$this->CI->db->dbprefix('timesheets_items') . '.date <=' => $date_to,
			$this->CI->db->dbprefix('timesheets_items') . '.date >=' => $date_from,
			$this->CI->db->dbprefix('timesheets_items') . '.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('timesheets_items.*, timesheets.staffID')
			->join('timesheets', 'timesheets_items.timesheetID = timesheets.timesheetID', 'inner')
			->from('timesheets_items')->where($where)->get();

		$timesheet_data = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				// work out length in seconds
				$seconds = (strtotime('2000-01-01 ' . $row->total_time) - strtotime(date('Y-m-d H:i', strtotime('2000-01-01 00:00'))))/(60*60);

				// process brand
				if (!empty($row->brandID)) {
					if (!isset($timesheet_data[$row->staffID][$row->brandID])) {
						$timesheet_data[$row->staffID][$row->brandID] = 0;
					}
					$timesheet_data[$row->staffID][$row->brandID] += $seconds;
				}

				// process role
				if (!empty($row->role)) {
					if (!isset($timesheet_data[$row->staffID][$row->role])) {
						$timesheet_data[$row->staffID][$row->role] = 0;
					}
					$timesheet_data[$row->staffID][$row->role] += $seconds;
				} else {
					// process reason
					if (!isset($timesheet_data[$row->staffID][$row->reason])) {
						$timesheet_data[$row->staffID][$row->reason] = 0;
					}
					$timesheet_data[$row->staffID][$row->reason] += $seconds;
				}

				if (!empty($row->activityID)) {
					if (!isset($timesheet_data[$row->staffID]['activity'][$row->activityID])) {
						$timesheet_data[$row->staffID]['activity'][$row->activityID] = 0;
					}
					$timesheet_data[$row->staffID]['activity'][$row->activityID] += $seconds;
				}
			}
		}

		// work out how many days shown
		$days = (strtotime(uk_to_mysql_date($search_fields['date_to'])) - strtotime(uk_to_mysql_date($search_fields['date_from'])))/(24*60*60) + 1;

		// switch return type
		switch ($type) {
			case 'averages':
				$average_data = array(
					'hours' => 0,
					'target_hours' => 0,
					'target_utilisation' => 0,
					'utilisation' => 0
				);
				$staff->data_seek(0);
				foreach ($staff->result() as $item) {
					$hours = 0;
					if (array_key_exists($item->staffID, $timesheet_data) && count($timesheet_data[$item->staffID]) > 0) {
						foreach ($timesheet_data[$item->staffID] as $brandID => $brand_hours) {
							$hours += $timesheet_data[$item->staffID][$brandID];
						}
					}
					$item->target_hours = ($item->target_hours/7)*$days;
					$average_data['hours'] += $hours;
					$average_data['target_hours'] += $item->target_hours;
					$average_data['target_utilisation'] += $item->target_utilisation;
					$average_data['utilisation'] += (($hours/$item->target_hours)*100);
				}
				$averages = array();
				foreach ($average_data as $key => $value) {
					$averages[$key] = $value/$staff->num_rows();
				}
				return $averages;
				break;
			case 'all':
			default:
				return $timesheet_data;
				break;
		}
	}
	
	/**
	 * calculate project delivery stats
	 * @param array $search_fields
	 * @return array
	 */
	public function calc_mileage($search_fields = array()) {
		
		// if dates empty, add default
		if (!isset($search_fields['date_from']) || empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-6 days'));
		}
		if (!isset($search_fields['date_from']) || empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// set dates
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		// build search query
		$search_where = array();

		
		if (isset($search_fields['staff_id']) && $search_fields['staff_id'] != '') {
			$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}
		
		// Check Exclude Mileage Define
		$where_in = array(
			"excluded_mileage",
			"excluded_mileage_without_fuel_card"
		);
		$where = array("accountID" => $this->CI->auth->user->accountID);
		$exclude_mileage = $excluded_mileage_without_fuel_card = 0;
		$exclude_m = $this->CI->db->select("*")->from("accounts_settings")->where($where)->where_in("key", $where_in)->get();
		foreach($exclude_m->result() as $result){
			if($result->value != NULL && $result->value != "" && $result->key == "excluded_mileage")
				$exclude_mileage = $result->value;
			if($result->value != NULL && $result->value != "" && $result->key == "excluded_mileage_without_fuel_card")
				$excluded_mileage_without_fuel_card = $result->value;
		}
		
		//Transport Modes Price
		$where = array("accountID" => $this->CI->auth->user->accountID);
		$priceArray = array();
		$transport = $this->CI->db->from("mileage")->where($where)->get();
		foreach($transport->result() as $transports){
			$priceArray[$transports->mileageID] = $transports->rate;
		}

		// get utilisation
		$where = array(
			$this->CI->db->dbprefix('timesheets_mileage') . '.status' => 'approved',
			$this->CI->db->dbprefix('timesheets_mileage') . '.date <=' => $date_to,
			$this->CI->db->dbprefix('timesheets_mileage') . '.date >=' => $date_from,
			$this->CI->db->dbprefix('timesheets_mileage') . '.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('timesheets_mileage.*, timesheets.staffID, staff.mileage_activate_fuel_cards')
			->join('timesheets', 'timesheets_mileage.timesheetID = timesheets.timesheetID', 'inner')
			->join('staff', 'staff.staffID = timesheets.staffID', 'left')
			->from('timesheets_mileage')->where($where)->get();

		$timesheet_mileage_data = array();
		$dateArray = array();
		foreach($res->result() as $result){
			$exclude_mile = 0;
			if(!isset($dateArray[$result->staffID])){
				$dateArray[$result->staffID] = array();
			}
			if(!in_array($result->date, $dateArray[$result->staffID]) && $result->total_mileage != 0){
				if($result->mileage_activate_fuel_cards == 1 && $exclude_mileage != NULL)
					$exclude_mile = $exclude_mileage;
				else if($result->mileage_activate_fuel_cards != 1 && $excluded_mileage_without_fuel_card != NULL)
					$exclude_mile = $excluded_mileage_without_fuel_card;
				$dateArray[$result->staffID][] = $result->date;
			}
			if($search_fields["filter_by_mode_of_transport"] == 1){
				if (!isset($timesheet_mileage_data[$result->staffID][$result->mode]["amount"])) {
					$timesheet_mileage_data[$result->staffID][$result->mode]["amount"] = 0;
				}
				$timesheet_mileage_data[$result->staffID][$result->mode]["amount"] += $result->total_cost - ($exclude_mile * $priceArray[$result->mode] / 100);
				if (!isset($timesheet_mileage_data[$result->staffID][$result->mode]["mileage"])) {
					$timesheet_mileage_data[$result->staffID][$result->mode]["mileage"] = 0;
				}
				$timesheet_mileage_data[$result->staffID][$result->mode]["mileage"] += $result->total_mileage - $exclude_mile;
			}else{
				if (!isset($timesheet_mileage_data[$result->staffID]["amount"])) {
					$timesheet_mileage_data[$result->staffID]["amount"] = 0;
				}
				$timesheet_mileage_data[$result->staffID]["amount"] += $result->total_cost - ($exclude_mile * $priceArray[$result->mode] / 100);
				if (!isset($timesheet_mileage_data[$result->staffID]["mileage"])) {
					$timesheet_mileage_data[$result->staffID]["mileage"] = 0;
				}
				$timesheet_mileage_data[$result->staffID]["mileage"] += $result->total_mileage - $exclude_mile;
			}
		}
		return $timesheet_mileage_data;
		
	}
	
	
	/**
	 * calculate project delivery stats
	 * @param array $search_fields
	 * @param string $type
	 * @return array
	 */
	public function calc_project_delivery($search_fields = array(), $type = 'full') {

		// if dates empty, add default
		if (!isset($search_fields['date_from']) || empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-6 days'));
		}
		if (!isset($search_fields['date_from']) || empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// set dates
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		// build search query
		$search_where = array();

		if (isset($search_fields['search']) && $search_fields['search'] == 'true') {
			if (isset($search_fields['staff_id']) && $search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
			}

			if (isset($search_fields['is_active']) && $search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` = 1';
				} else {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` != 1';
				}
			}
		}

		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// set where
		$where = array(
			'staff.accountID' => $this->CI->auth->user->accountID
		);

		// get staff
		$staff = $this->CI->db->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		// get session staff
		$lesson_staff = array();
		$where = array(
			'bookings_lessons_staff.startDate <=' => $date_to,
			'bookings_lessons_staff.endDate >=' => $date_from,
			'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID
		);
		$res_staff = $this->CI->db->select('bookings_lessons_staff.*, bookings_lessons.day, staff.payments_scale_salaried as salaried')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				// don't include observers or participants
				if (in_array($row->type, array('observer', 'participant'))) {
					continue;
				}
				$lesson_staff[$row->lessonID][$row->staffID] = array(
					'type' => $row->type,
					'startDate' => $row->startDate,
					'endDate' => $row->endDate,
					'startTime' => $row->startTime,
					'endTime' => $row->endTime,
					'salaried' => $row->salaried
				);
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$lesson_exceptions_where = array(
			'bookings_lessons_exceptions.date <=' => $date_to,
			'bookings_lessons_exceptions.date >=' => $date_from,
			'bookings_lessons_exceptions.accountID' => $this->CI->auth->user->accountID
		);
		$res_exceptions = $this->CI->db->select('bookings_lessons_exceptions.*, staff.payments_scale_salaried as salaried ')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')->where($lesson_exceptions_where)->get();

		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $row) {
				$lesson_exceptions[$row->lessonID][$row->date][] = array(
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type,
					'salaried' => $row->salaried
				);
			}
		}

		// get non-provisional project sessions
		$where = array(
			$this->CI->db->dbprefix('bookings') . '.project' => 1,
			$this->CI->db->dbprefix('bookings') . '.cancelled !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.provisional !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.startDate <=' => $date_to,
			$this->CI->db->dbprefix('bookings_blocks') . '.endDate >=' => $date_from,
			$this->CI->db->dbprefix('bookings') . '.accountID' => $this->CI->auth->user->accountID
		);
		if (isset($search_fields['search']) && $search_fields['search'] == 'true') {
			if ($search_fields['project_code_id'] != '') {
				$where[$this->CI->db->dbprefix("bookings") . '.project_codeID'] = $search_fields['project_code_id'];
			}
		}
		$res = $this->CI->db->select('bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, bookings.bookingID, bookings_blocks.provisional, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.lessonID, bookings_lessons.day, bookings_lessons.typeID, bookings_lessons.activityID, lesson_types.name as type, activities.name as activity, bookings.project_typeID')->from('bookings')->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->group_by('bookings_lessons.lessonID')->get();

		$report_data = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				// loop through dates
				$current_date = date('Y-m-d', strtotime('-1 day', strtotime($date_from)));
				while (strtotime($current_date) <= strtotime($date_to)) {
					// next day
					$current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));

					// if session not on this day, skip
					if ($row->day != strtolower(date('l', strtotime($current_date)))) {
						continue;
					}

					$lesson_details = array(
						'staff_ids' => array(),
						'startTime' => $row->startTime,
						'endTime' => $row->endTime,
						'booking_id' => $row->bookingID,
						'activityID' => $row->activityID,
						'typeID' => $row->typeID,
						'activity' => $row->activity,
						'type' => $row->type,
						'project_typeID' => $row->project_typeID,
					);

					// add value for activity and type if empty
					if (empty($lesson_details['activity'])) {
						$lesson_details['activity'] = 'Other';
					}
					if (empty($lesson_details['type'])) {
						$lesson_details['type'] = 'Other';
					}

					// double check is within search dates and block dates and session dates (if set)
					if (strtotime($current_date) >= strtotime($date_from) && strtotime($current_date) <= strtotime($date_to) && strtotime($current_date) >= strtotime($row->blockStart) && strtotime($current_date) <= strtotime($row->blockEnd) && ((empty($row->lessonStart) && empty($row->lessonEnd)) || (strtotime($current_date) >= strtotime($row->lessonStart) && strtotime($current_date) <= strtotime($row->lessonEnd)))) {
						// all ok
					} else {
						// skip
						continue;
					}

					// get staff
					if (array_key_exists($row->lessonID, $lesson_staff) && is_array($lesson_staff[$row->lessonID])) {
						foreach ($lesson_staff[$row->lessonID] as $staffID => $staffDetails) {
							if (strtotime($staffDetails['startDate']) <= strtotime($current_date) && strtotime($staffDetails['endDate']) >= strtotime($current_date)) {
								$lesson_details['staff_ids'][$staffID] = $staffDetails['type'];
							}
						}
					}

					// check for session exceptions
					if (array_key_exists($row->lessonID, $lesson_exceptions) && array_key_exists($current_date, $lesson_exceptions[$row->lessonID]) && is_array($lesson_exceptions[$row->lessonID][$current_date])) {
						foreach ($lesson_exceptions[$row->lessonID][$current_date] as $exception_info) {
							// if cancellation, skip
							if ($exception_info['type'] == 'cancellation') {
								continue 2;
							}

							// staff change
							if (array_key_exists($exception_info['fromID'], $lesson_details['staff_ids'])) {
								// swap if moved to another staff
								if (!empty($exception_info['staffID'])) {
									$lesson_details['staff_ids'][$exception_info['staffID']] = $lesson_details['staff_ids'][$exception_info['fromID']];
								}
								if (isset($lesson_details['staff_ids'][$exception_info['fromID']])) {
									unset($lesson_details['staff_ids'][$exception_info['fromID']]);
								}
							}
						}
					}

					if (count($lesson_details['staff_ids']) > 0) {
						foreach ($lesson_details['staff_ids'] as $staff_id => $staff_type) {
							// if search for single staff member, only show projects for that staff member
							if (!empty($search_fields['staff_id']) && $staff_id != $search_fields['staff_id']) {
								continue;
							}

							// check for time overrides
							if (isset($lesson_staff[$row->lessonID][$staff_id])) {
								if (!empty($lesson_staff[$row->lessonID][$staff_id]['startTime'])) {
									$lesson_details['startTime'] = $lesson_staff[$row->lessonID][$staff_id]['startTime'];
								}
								if (!empty($lesson_staff[$row->lessonID][$staff_id]['endTime'])) {
									$lesson_details['endTime'] = $lesson_staff[$row->lessonID][$staff_id]['endTime'];
								}
							}
							$lesson_length = (strtotime($lesson_details['endTime']) - strtotime($lesson_details['startTime']))/(60*60);

							// change data depending on report type
							switch ($type) {
								case 'full':
									if (!isset($report_data[$staff_id][$lesson_details['booking_id']])) {
										$report_data[$staff_id][$lesson_details['booking_id']] = 0;
									}
									$report_data[$staff_id][$lesson_details['booking_id']] += $lesson_length;
									break;
								case 'alt':
									if (!isset($report_data[$lesson_details['booking_id']][$lesson_details['activity']][$lesson_details['type']][$staff_id])) {
										$report_data[$lesson_details['booking_id']][$lesson_details['activity']][$lesson_details['type']][$staff_id] = 0;
									}
									$report_data[$lesson_details['booking_id']][$lesson_details['activity']][$lesson_details['type']][$staff_id] += $lesson_length;
									break;
								default:
									// contracted or sessional
									$hours_type = 'sessional';
									if (isset($lesson_staff[$row->lessonID][$staff_id]) && $lesson_staff[$row->lessonID][$staff_id]['salaried'] == 1) {
										// staff member is salaried
										$hours_type = 'contracted';
									} else if (isset($lesson_exceptions[$row->lessonID][$current_date])) {
										foreach ($lesson_exceptions[$row->lessonID][$current_date] as $exception) {
											if ($exception['staffID'] == $staff_id && $exception['salaried'] == 1) {
												// staff member is salaried and added as an exception
												$hours_type = 'contracted';
											}
										}
									}

									$id_field = 'project_typeID';
									switch ($type) {
										case 'session-type':
											$id_field = 'typeID';
											break;
										case 'activity-type':
											$id_field = 'activityID';
											break;
									}

									// get id
									$id = $lesson_details[$id_field];
									if (empty($id)) {
										$id = 'other';
									}

									if (!isset($report_data[$id][$hours_type])) {
										$report_data[$id][$hours_type] = 0;
									}
									$report_data[$id][$hours_type] += $lesson_length;
									break;
							}
						}
					}
				}
			}
		}

		// return data
		return $report_data;
	}

	/**
	 * calculate activity type stats
	 * @param array $search_fields
	 * @param string $type
	 * @return array
	 */
	public function calc_activity_type($search_fields = array()) {

		// if dates empty, add default
		if (!isset($search_fields['date_from']) || empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-6 days'));
		}
		if (!isset($search_fields['date_from']) || empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// set dates
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		// build search query
		$search_where = array();

		if (isset($search_fields['search']) && $search_fields['search'] == 'true') {
			if (isset($search_fields['staff_id']) && $search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
			}

			if (isset($search_fields['is_active']) && $search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` = 1';
				} else {
					$search_where['is_active'] = '`' . $this->CI->db->dbprefix("staff") . '`.`active` != 1';
				}
			}
		}

		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// set where
		$where = array(
			'staff.accountID' => $this->CI->auth->user->accountID
		);

		// get staff
		$staff = $this->CI->db->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		// get session staff
		$lesson_staff = array();
		$where = array(
			'bookings_lessons_staff.startDate <=' => $date_to,
			'bookings_lessons_staff.endDate >=' => $date_from,
			'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID
		);
		$res_staff = $this->CI->db->select('bookings_lessons_staff.*, bookings_lessons.day, staff.payments_scale_salaried as salaried')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				// don't include observers or participants
				if (in_array($row->type, array('observer', 'participant'))) {
					continue;
				}
				$lesson_staff[$row->lessonID][$row->staffID] = array(
					'type' => $row->type,
					'startDate' => $row->startDate,
					'endDate' => $row->endDate,
					'startTime' => $row->startTime,
					'endTime' => $row->endTime,
					'salaried' => $row->salaried
				);
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$lesson_exceptions_where = array(
			'bookings_lessons_exceptions.date <=' => $date_to,
			'bookings_lessons_exceptions.date >=' => $date_from,
			'bookings_lessons_exceptions.accountID' => $this->CI->auth->user->accountID
		);
		$res_exceptions = $this->CI->db->select('bookings_lessons_exceptions.*, staff.payments_scale_salaried as salaried ')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')->where($lesson_exceptions_where)->get();

		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $row) {
				$lesson_exceptions[$row->lessonID][$row->date][] = array(
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type,
					'salaried' => $row->salaried
				);
			}
		}

		// get non-provisional sessions
		$where = array(
			$this->CI->db->dbprefix('bookings') . '.cancelled !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.provisional !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.startDate <=' => $date_to,
			$this->CI->db->dbprefix('bookings_blocks') . '.endDate >=' => $date_from,
			$this->CI->db->dbprefix('bookings') . '.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, bookings.bookingID, bookings_blocks.provisional, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.lessonID, bookings_lessons.day, bookings_lessons.typeID, bookings_lessons.activityID, bookings.project_typeID')->from('bookings')->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->group_by('bookings_lessons.lessonID')->get();

		$report_data = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				// loop through dates
				$current_date = date('Y-m-d', strtotime('-1 day', strtotime($date_from)));
				while (strtotime($current_date) <= strtotime($date_to)) {
					// next day
					$current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));

					// if session not on this day, skip
					if ($row->day != strtolower(date('l', strtotime($current_date)))) {
						continue;
					}

					$lesson_details = array(
						'staff_ids' => array(),
						'startTime' => $row->startTime,
						'endTime' => $row->endTime,
						'booking_id' => $row->bookingID,
						'activityID' => $row->activityID,
						'typeID' => $row->typeID,
						'project_typeID' => $row->project_typeID,
					);

					if (empty($lesson_details['activityID'])) {
						$lesson_details['activityID'] = 'other';
					}

					// double check is within search dates and block dates and session dates (if set)
					if (strtotime($current_date) >= strtotime($date_from) && strtotime($current_date) <= strtotime($date_to) && strtotime($current_date) >= strtotime($row->blockStart) && strtotime($current_date) <= strtotime($row->blockEnd) && ((empty($row->lessonStart) && empty($row->lessonEnd)) || (strtotime($current_date) >= strtotime($row->lessonStart) && strtotime($current_date) <= strtotime($row->lessonEnd)))) {
						// all ok
					} else {
						// skip
						continue;
					}

					// get staff
					if (array_key_exists($row->lessonID, $lesson_staff) && is_array($lesson_staff[$row->lessonID])) {
						foreach ($lesson_staff[$row->lessonID] as $staffID => $staffDetails) {
							if (strtotime($staffDetails['startDate']) <= strtotime($current_date) && strtotime($staffDetails['endDate']) >= strtotime($current_date)) {
								$lesson_details['staff_ids'][$staffID] = $staffDetails['type'];
							}
						}
					}

					// check for session exceptions
					if (array_key_exists($row->lessonID, $lesson_exceptions) && array_key_exists($current_date, $lesson_exceptions[$row->lessonID]) && is_array($lesson_exceptions[$row->lessonID][$current_date])) {
						foreach ($lesson_exceptions[$row->lessonID][$current_date] as $exception_info) {
							// if cancellation, skip
							if ($exception_info['type'] == 'cancellation') {
								continue 2;
							}

							// staff change
							if (array_key_exists($exception_info['fromID'], $lesson_details['staff_ids'])) {
								// swap if moved to another staff
								if (!empty($exception_info['staffID'])) {
									$lesson_details['staff_ids'][$exception_info['staffID']] = $lesson_details['staff_ids'][$exception_info['fromID']];
								}
								if (isset($lesson_details['staff_ids'][$exception_info['fromID']])) {
									unset($lesson_details['staff_ids'][$exception_info['fromID']]);
								}
							}
						}
					}

					if (count($lesson_details['staff_ids']) > 0) {
						foreach ($lesson_details['staff_ids'] as $staff_id => $staff_type) {
							// if search for single staff member, only show projects for that staff member
							if (!empty($search_fields['staff_id']) && $staff_id != $search_fields['staff_id']) {
								continue;
							}

							// check for time overrides
							if (isset($lesson_staff[$row->lessonID][$staff_id])) {
								if (!empty($lesson_staff[$row->lessonID][$staff_id]['startTime'])) {
									$lesson_details['startTime'] = $lesson_staff[$row->lessonID][$staff_id]['startTime'];
								}
								if (!empty($lesson_staff[$row->lessonID][$staff_id]['endTime'])) {
									$lesson_details['endTime'] = $lesson_staff[$row->lessonID][$staff_id]['endTime'];
								}
							}
							$lesson_length = (strtotime($lesson_details['endTime']) - strtotime($lesson_details['startTime']))/(60*60);

							// save
							if (!isset($report_data[$staff_id][$lesson_details['activityID']])) {
								$report_data[$staff_id][$lesson_details['activityID']] = 0;
							}
							$report_data[$staff_id][$lesson_details['activityID']] += $lesson_length;

						}
					}
				}
			}
		}

		// return data
		return $report_data;
	}
	
	/** Calculate Personal Mileage Cost
	 * @param array $search_fields
	 * @param $staffID
	 * @return array
	 */
	 
	public function calc_personal_mileage_cost($search_fields = array(), $staffID = NULL) {
		// check start date is starting of week date or not
		if (!empty($search_fields['date_from'])) {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}
		if (!empty($search_fields['date_to'])) {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}
		
		$weekday_start = date("w",strtotime($date_from));
		$weekno_start = date("W",strtotime($date_from));
		$weekday_end = date("w",strtotime($date_to));
		$weekno_end = date("W",strtotime($date_to));
		
		$startdate = $date_from;
		$enddate = $date_to;
		if($weekday_start != 1){
			$startdate = date("Y-m-d",strtotime("previous monday". $date_from));
		}
		if($weekday_end != 0){
			$enddate = date("Y-m-d",strtotime("next sunday". $date_to));
		}
		
		// Mode of transport from mileage table
		$carID = '';
		$where = array($this->CI->db->dbprefix('mileage') . '.accountID' => $this->CI->auth->user->accountID);
		$query = $this->CI->db->from("mileage")->where($where)->get();
		$priceArray = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){				
				$priceArray[$result->mileageID] = $result->rate;
				if(strtolower($result->name) == 'car'){
					$carID = $result->mileageID;
				}
			}
		}
		
		//excluded mileage
		$accounts_settings = array();
		$where = array($this->CI->db->dbprefix('accounts_settings') . '.accountID' => $this->CI->auth->user->accountID);
		$query = $this->CI->db->from("accounts_settings")->where($where)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result)
				$accounts_settings[$result->key] = $result->value;
		}
		
		
		$day = "Monday";
		$count = 0;
		$total_cost = 0;
		$overall_mileage = 0;
		for($i = strtotime($day, strtotime($startdate)); $i <= strtotime($enddate); $i = strtotime('+1 week', $i)){
			$total_mileage = 0;
			$count++;
			$new_date = date('Y-m-d', $i);
			$end_date = date('Y-m-d', strtotime("+6 day". $new_date));
			// get total mileage from mileage table
			$where = array($this->CI->db->dbprefix('timesheets_mileage') . '.accountID' => $this->CI->auth->user->accountID,
			$this->CI->db->dbprefix('timesheets') .".staffID " => $staffID,
			$this->CI->db->dbprefix('timesheets_mileage') .".status " => 'approved',
			$this->CI->db->dbprefix('timesheets_mileage') .".date >= " => $new_date,
			$this->CI->db->dbprefix('timesheets_mileage') .".date <= " => $end_date);
			
			$query = $this->CI->db->select("timesheets_mileage.*, staff.mileage_activate_fuel_cards")
			->from("timesheets_mileage")
			->join("timesheets", "timesheets.timesheetID = timesheets_mileage.timesheetID", "left")
			->join("staff", "staff.staffID = timesheets.staffID", "left")
			->where($where)
			->get();
			$mileage_activate_fuel_cards = 0;
			if($query->num_rows() > 0){
				$timesheetID = 0;
				$dateArray = array();
				foreach($query->result() as $result){
					if($result->mode == $carID && $date_from <= $result->date && $date_to >= $result->date){
						$total_mileage += $result->total_mileage;
						$timesheetID = $result->timesheetID;
						$mileage_activate_fuel_cards = $result->mileage_activate_fuel_cards;
						if(!in_array($result->date, $dateArray) && $result->total_mileage != 0)
							$dateArray[] = $result->date;
					}
				}
				
				$exclude_mileage = 0;
				if(isset($accounts_settings['excluded_mileage']) && $accounts_settings['excluded_mileage'] != null && count($dateArray) > 0 && $mileage_activate_fuel_cards == 1)
					$exclude_mileage = count($dateArray) * $accounts_settings['excluded_mileage'];
				
				$new_mileage = 0;
				$where = array($this->CI->db->dbprefix('timesheets_fuel_card') . '.accountID' => $this->CI->auth->user->accountID,
				$this->CI->db->dbprefix('timesheets_fuel_card') .".timesheetID " => $timesheetID,
				$this->CI->db->dbprefix('timesheets_fuel_card') .".status " => 'approved');
				$query1 = $this->CI->db->select("*")->from("timesheets_fuel_card")->where($where)->get();
				if($query1->num_rows() > 0){
					foreach($query1->result() as $result1){
						if($result1->end_mileage != 0){
							$start_mileage = $result1->start_mileage;
							$end_mileage = $result1->end_mileage;
							$fraction = $end_mileage - $start_mileage;
							if($count == 1 && $weekday_start != 1){
								$fraction = ($weekday_start/7) * $fraction;
							}
							if(strtotime($end_date) > strtotime($date_to) && $weekday_end != 0){
								$fraction = ($weekday_end/7) * $fraction;
							}
							$new_mileage = $fraction - $total_mileage + $exclude_mileage;
						}
					}
				}
				$overall_mileage += $new_mileage;
				
			}		
			
		}
		
		$overall_personal_cost = $overall_mileage * $priceArray[$carID];
		if($overall_personal_cost != 0)
			$overall_personal_cost = $overall_personal_cost/100;
		
		$array = array("overall_mileage" => number_format($overall_mileage,2,".",""),
		"overall_personal_cost" => number_format($overall_personal_cost,2,".",""));
		
		return $array;
	}
	
	/**
	 * calculate payroll report
	 * @param array $search_fields
	 * @param string $report_type
	 * @param array $staffResult
	 * @return array
	 */
	public function calc_payroll(
		$search_fields = array(),
		$report_type = 'payroll',
		array $staffResult = []
	) {

		$where = [];
		if (!empty($search_fields['date_from'])) {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
			$where[$this->CI->db->dbprefix('timesheets_items') . '.date >='] = $date_from;
		}
		if (!empty($search_fields['date_to'])) {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
			$where[$this->CI->db->dbprefix('timesheets_items') . '.date <='] = $date_to;
		}

		// get utilisation
		$where = array_merge(
			$where,
			[
				$this->CI->db->dbprefix('timesheets_items') . '.status' => 'approved',
				$this->CI->db->dbprefix('timesheets_items') . '.accountID' => $this->CI->auth->user->accountID
			]
		);

		if (!empty($search_fields['staff_id'])) {
			 $where[$this->CI->db->dbprefix('timesheets') . '.staffID'] = $search_fields['staff_id'];
		}

		if (!empty($search_fields['department'])) {
			$where[$this->CI->db->dbprefix('timesheets') . '.department'] = $search_fields['department'];
		}
		$res = $this->CI->db->select('timesheets_items.*, '.
			'staff.payments_scale_head,
			 staff.payments_scale_assist,
			 staff.payments_scale_lead,
			 staff.payments_scale_observer,
			 staff.payments_scale_participant,
			 staff.payments_scale_salaried, staff.payments_scale_salary, '.
			'timesheets.staffID, lesson_types.hourly_rate, bookings_lessons.typeID, bookings_lessons_staff.salaried')->
		from('timesheets_items')->
		join('timesheets', 'timesheets_items.timesheetID = timesheets.timesheetID', 'inner')->
		join('staff', 'timesheets.staffID = staff.staffID', 'inner')->
		join('bookings_lessons', 'timesheets_items.lessonID = bookings_lessons.lessonID', 'left')->
		join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID and timesheets.staffID = bookings_lessons_staff.staffID', 'left')->
		join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left');

		if ($report_type == 'project_code') {
			$res->select('project_codes.code as project_code')->
			join('bookings', 'bookings.bookingID = bookings_lessons.bookingID', 'left')->
			join('project_codes', 'bookings.project_codeID = project_codes.codeID', 'left');
			if ($search_fields['project_code'] > 0) {
				$where['bookings.project_codeID'] = $search_fields['project_code'];
			} else {
				$where['bookings.project_codeID >'] = 0;
			}
		}

		if ($staffResult) {
			$res->where_in('timesheets.staffID', array_keys($staffResult));
		}

		$res = $res->where($where)->group_by('timesheets_items.itemID')->get();

		$quals = $this->get_qualifications($this->CI->auth->user->accountID);
		if (!$staffResult) {
			$staff = $this->get_staff($this->CI->auth->user->accountID, NULL, TRUE, TRUE);
		} else {
			$staff = $staffResult;
		}

		$data = array();

		switch ($report_type) {
			case 'project_code':
				$result_key = 'project_code';
				break;
			default:
				$result_key = 'staffID';
				break;
		}

		//no need to get extra time here, it is already set in total time of the timesheets
		if ($res->num_rows() > 0) {

			foreach ($res->result() as $row) {

				if(!isset($data[$row->{$result_key}])) {
					$data[$row->{$result_key}] = array(
						'hours' => 0,
						'quals' => '',
						'total_pay' => 0,
						'staff_hourly_pay' => 0,
						'session_hourly_pay' => 0,
						'qualified_pay' => 0,
						'standard_pay' => 0,
						'salaried' => 0,
						'nonsalaried' => 0
					);
				}


				$selected_qual = [];
				if (isset($quals[$staff[$row->staffID]->qualToDisplay])) {
					$selected_qual = $quals[$staff[$row->staffID]->qualToDisplay];
				}

				$session_override_rate = 0;
				if ($staff[$row->staffID]->system_pay_rates && !empty($selected_qual)) {
					$session_rates = $this->CI->db->from('session_qual_rates')
						->where([
							'accountID' => $this->CI->auth->user->accountID,
							'lessionTypeID' => $row->typeID,
							'qualTypeID' => $selected_qual->qualID,
						])->limit(1)->get()->result();

					if (!empty($session_rates)) {
						$session_override_rate = $this->get_qualification_rate_by_session($staff[$row->staffID], $selected_qual, $session_rates[0], $row->role);
					}
				}

				$hours = (strtotime('2000-01-01 ' . $row->total_time) - strtotime(date('Y-m-d H:i', strtotime('2000-01-01 00:00'))))/(60*60);
				
				if ($row->salaried == 1){
					$data[$row->{$result_key}]['salaried'] += $hours;
				}else{
					$data[$row->{$result_key}]['nonsalaried'] += $hours;
				}

				// work out length in seconds
				$data[$row->{$result_key}]['hours'] += $hours;
				if(!isset($data[$row->{$result_key}]['session_type_hours'][$row->typeID][$row->role])) {
					$data[$row->{$result_key}]['session_type_hours'][$row->typeID][$row->role] = 0;
				}
				$data[$row->{$result_key}]['session_type_hours'][$row->typeID][$row->role] += $hours;

				if(!isset($data[$row->{$result_key}]['total_pay_by_role'][$row->typeID][$row->role])) {
					$data[$row->{$result_key}]['total_pay_by_role'][$row->typeID][$row->role] = 0;
				}

				if(!isset($data[$row->{$result_key}]['rates_by_role'][$row->typeID][$row->role])) {
					$data[$row->{$result_key}]['rates_by_role'][$row->typeID][$row->role] = 0;
				}

				$rate = 0;

				if ($row->salaried == 1 && $this->CI->settings_library->get('salaried_sessions') == 1 && $this->CI->auth->has_features('payroll')) {
					$value = 0;
				} else if ((float)$row->hourly_rate > 0) {
					$value = $hours * (float)$row->hourly_rate;
					$data[$row->{$result_key}]['total_pay_by_role'][$row->typeID][$row->role] += $value;
					$data[$row->{$result_key}]['total_pay'] += $value;
					$data[$row->{$result_key}]['session_hourly_pay'] += $value;
				}
				else
				{
					if ($session_override_rate > 0) {
						$value = $hours * $session_override_rate;
						$data[$row->{$result_key}]['total_pay'] += $value;
						$data[$row->{$result_key}]['session_hourly_pay'] += $value;
					} else {
						if(!$staff[$row->staffID]->system_pay_rates &&  (float)$staff[$row->staffID]->hourly_rate > 0){
							// for this staff member only hourly_rate is set
							$value = $hours * (float)$staff[$row->staffID]->hourly_rate;
							$data[$row->{$result_key}]['total_pay'] += $value;
							$data[$row->{$result_key}]['staff_hourly_pay'] += $value;

						} else
						{
							// if there is valid mandatory qualification, count according to this rate (get rate level at the fly)
							if ($staff[$row->staffID]->system_pay_rates && isset($staff[$row->staffID]->qualToDisplay))
							{
								$staff_row = $staff[$row->staffID];
								$qual_row = $quals[$staff[$row->staffID]->qualToDisplay];
								$rate = $this->get_qualification_rate($staff_row, $qual_row);
								$value = $hours * (float)$rate;
								$data[$row->{$result_key}]['total_pay'] += $value;
								$data[$row->{$result_key}]['qualified_pay'] += $value;
							}
							else
							{

								// now, it depends on role
								if ($row->role === 'head')
								{
									$value = $hours * (float)$row->payments_scale_head;
									// head rate
									$data[$row->{$result_key}]['total_pay'] += $value;
									$data[$row->{$result_key}]['standard_pay'] += $value;

								}
								else
								{
									$this->CI->load->library('settings_library');
									$roles = $this->CI->settings_library->get_staff_for_payroll();

									if (isset($roles[$row->role])) {
										$role = $row->role;
										if ($row->role == 'assistant') {
											$role = 'assist';
										}
										$value = $hours * (float)$row->{'payments_scale_' . $role};
									} else {
										$value = $hours * (float)$row->payments_scale_assist;
									}

									// assistant rate
									$data[$row->{$result_key}]['total_pay'] += $value;
									$data[$row->{$result_key}]['standard_pay'] += $value;
								}
							}
						}
					}
				}

				$data[$row->{$result_key}]['total_pay_by_role'][$row->typeID][$row->role] += $value;
				$data[$row->{$result_key}]['rates_by_role'][$row->typeID][$row->role] = $rate;
			}
		}

		//process filters
		$result = [];
		foreach ($data as $key => $item) {
			if (!empty($search_fields['type_id']) && empty($item['session_type_hours'][$search_fields['type_id']])) {
				continue;
			}
			$result[$key] = $item;
		}

		return $result;
	}

	public function get_staff(
		$account_id,
		$search_fields = NULL,
		$is_assoc = FALSE,
		$is_append_qualification = FALSE,
		&$pagination = null
	) {
		$where = array(
			'staff.accountID' => $account_id
		);

		$search_where = [];

		if (!is_null($search_fields)) {
			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`staffID` = " . $this->CI->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['department'] != '') {
				$search_where[] = '`' . $this->CI->db->dbprefix("staff") . "`.`department` = " . $this->CI->db->escape($search_fields['department']);
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		$resultTotalAmount = $this->CI->db->select('COUNT(staffID) as staff_total_amount')->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		if (!is_null($pagination) && !$pagination->is_search) {
			$pagination->calc(current($resultTotalAmount->result())->staff_total_amount);
		}

		$query = $this->CI->db
			->select('staff.*')
			->from('staff')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('staff.first asc, staff.surname asc')
			->group_by('staff.staffID');
		if(!is_null($pagination) && !$pagination->is_search) {
			$query->limit($pagination->amount, $pagination->start);
		}

		$result = $query->get();

		foreach ($result->result() as $item) {
			$item->qualToDisplay = $this->get_qualification_to_display($item->staffID);
		}

		if ($is_append_qualification) {

			$quals = $this->get_qualifications($account_id);
			$preferred_quals = $this->_get_staff_preferred_qualifications($account_id, $quals);

			foreach ($result->result() as $row) {

				if (isset($preferred_quals[$row->staffID])) {
					$row->rateQualID = $preferred_quals[$row->staffID];
				} else {
					$row->rateQualID = NULL;
				}
			}
		}

		// convert staff to assoc array
		if ($is_assoc === TRUE) {
			$staff_assoc = array();
			foreach ($result->result() as $row) {
				$staff_assoc[$row->staffID] = $row;
			}

			return $staff_assoc;

		} else {
			return $result;
		}
	}

	public function get_qualification_to_display($staffId) {
		$res = $this->CI->db->select('staff_quals_mandatory.qualID')->from('staff_quals_mandatory')->
			where([
				'staff_quals_mandatory.staffId' => $staffId,
				'staff_quals_mandatory.preferred_for_pay_rate' => 1
			])->get();

		if ($res->num_rows() > 0) {
			return $res->row()->qualID;
		}

		return null;
	}

	public function get_qualifications($account_id) {
		$res = $this->CI->db->from('mandatory_quals')->where(array('accountID' => $account_id))->get();

		$assoc = array();
		foreach ($res->result() as $row)
		{
			$assoc[$row->qualID] = $row;
		}

		return $assoc;
	}

	protected function _get_staff_valid_qualifications($account_id) {
		// collecting a valid mandatory qualifications
		$res = $this->CI->db->select('staff.staffID, staff_quals_mandatory.qualID')->from('staff')->
		join('staff_quals_mandatory', 'staff.staffID=staff_quals_mandatory.staffID AND staff_quals_mandatory.valid=1', 'left')->
		where(array('staff.accountID' => $account_id))->get();

		$valid_quals = array();

		foreach($res->result() as $row) {
			if(!isset($valid_quals[$row->staffID])) {
				$valid_quals[$row->staffID] = array();
			}

			if($row->qualID)
			{
				$valid_quals[$row->staffID][] = $row->qualID;
			}
		}

		return $valid_quals;
	}


	protected function _get_staff_preferred_qualifications($account_id, $quals) {

		$valid_quals = $this->_get_staff_valid_qualifications($account_id);

		$res = $this->CI->db->select('staff.staffID, staff_quals_mandatory.qualID')->from('staff')->
		join('staff_quals_mandatory', 'staff.staffID=staff_quals_mandatory.staffID AND staff_quals_mandatory.preferred_for_pay_rate=1', 'left')->
		where(array('staff.accountID' => $account_id))->get();

		$result = array();

		foreach($res->result() as $row){

			if($row->qualID && in_array($row->qualID, $valid_quals[$row->staffID])) {
				$result[$row->staffID] = $row->qualID;

			} else {

				if( isset($valid_quals[$row->staffID])) {
					// get qual with highest rate
					$result[$row->staffID] = $this->_get_qualification_with_highest_rate(array_intersect_key($quals, array_flip($valid_quals[$row->staffID])));
				} else {
					// no valid quals found at all :(
					$result[$row->staffID] = NULL;
				}
			}
		}

		return $result;
	}

	protected function _get_qualification_with_highest_rate($quals) {
		$top_qual = NULL;
		$rate = -1;
		foreach($quals as $row) {
			if((float)$row->hourly_rate > $rate ) {
				$rate = (float)$row->hourly_rate;
				$top_qual = $row->qualID;
			}
		}

		return $top_qual;
	}

	public function get_qualification_rate($staff_row, $qual_row) {

		if (time() >= strtotime('+' . $qual_row->length_increment . ' month', strtotime($staff_row->employment_start_date))
			&& $qual_row->length_increment > 0 && $qual_row->incremental_rate > 0) {
			return $qual_row->incremental_rate;
		} else {
			return $qual_row->hourly_rate;
		}
	}

	public function get_qualification_rate_by_session($staff_row, $qual_row, $session_rate, $role) {
		$pay_rate = json_decode($session_rate->pay_rate, true);
		$increased_pay_rate = json_decode($session_rate->increased_pay_rate, true);

		if (!isset($pay_rate[$role]) || !isset($increased_pay_rate[$role])) {
			return 0;
		}

		if (time() >= strtotime('+' . $qual_row->length_increment . ' month', strtotime($staff_row->employment_start_date))
			&& $qual_row->length_increment > 0 && $increased_pay_rate[$role] > 0) {
			return $increased_pay_rate[$role];
		} else {
			return $pay_rate[$role];
		}
	}

	public function get_contracts_data($where, $search_where = []) {
		$res = $this->CI->db->select($this->CI->db->dbprefix('bookings') . '.bookingID,
			' . $this->CI->db->dbprefix('bookings') . '.type,
			' . $this->CI->db->dbprefix('bookings') . '.name,
			' . $this->CI->db->dbprefix('bookings') . '.confirmed,
			' . $this->CI->db->dbprefix('bookings') . '.invoiced,
			' . $this->CI->db->dbprefix('bookings') . '.riskassessed,
			' . $this->CI->db->dbprefix('bookings') . '.startDate,
			' . $this->CI->db->dbprefix('bookings') . '.endDate,
			' . $this->CI->db->dbprefix('bookings') . '.name as event,
			' . $this->CI->db->dbprefix('bookings') . '.orgID,
			' . $this->CI->db->dbprefix('bookings') . '.project,
			' . $this->CI->db->dbprefix('bookings') . '.register_type,
			' . $this->CI->db->dbprefix('brands')   . '.name as department,
			' . $this->CI->db->dbprefix('orgs') . '.name as org,
			' . $this->CI->db->dbprefix('brands') . '.colour as brand_colour', FALSE)
			->from('bookings')
			->where($where)
			->join('orgs', 'bookings.orgID = orgs.orgID', 'left')
			->join('brands', 'bookings.brandID = brands.brandID', 'left')
			->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'left')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'left')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->join('bookings_lessons_staff', 'bookings.bookingID = bookings_lessons_staff.bookingID', 'left')
			->group_by('bookings.bookingID')->order_by('bookings.startDate asc, bookings.endDate asc, org asc');

		if (!empty($search_where)) {
			$res->where($search_where, NULL, FALSE);
		}

		return $res->get();
	}

	public function getSessionDeliveryData($searchArray) {
		$date_from_search = $where['bookings_blocks.endDate >='] = $searchArray['date_from'];
		$date_to_search = $where['bookings_blocks.startDate <='] = $searchArray['date_to'];
		$accountID = $where['bookings_lessons.accountID'] = $searchArray['accountID'];

		$customQuery = [];
		$like = [];

		if (!empty($searchArray['org'])) {
			$customQuery[] = "(`app_orgs`.`name` LIKE '%" . $this->CI->db->escape_like_str($searchArray['org']) .
				"%' OR `block_org`.`name` LIKE '%" . $this->CI->db->escape_like_str($searchArray['org']) . "%')";
		}

		if (!empty($searchArray['type_id'])) {
			if ($searchArray['type_id'] == 'other') {
				$where['bookings_lessons.typeID'] = null;
			} else {
				$where['bookings_lessons.typeID'] = $searchArray['type_id'];
			}
		}

		if (!empty($searchArray['name'])) {
			$like['bookings.name'] = $searchArray['name'];
		}

		if (!empty($searchArray['activity_id'])) {
			if ($searchArray['activity_id'] == 'other') {
				$where['bookings_lessons.activityID'] = null;
			} else {
				$where['bookings_lessons.activityID'] = $searchArray['activity_id'];
			}
		}

		if (!empty($searchArray['day'])) {
			$where['bookings_lessons.day'] = $searchArray['day'];
		}

		if (!empty($searchArray['brand_id'])) {
			$where['bookings.brandID'] = $searchArray['brand_id'];
		}

		if (!empty($searchArray['postcode'])) {
			$customQuery[] = '(`app_orgs_addresses`.`postcode` = ' . $this->CI->db->escape($searchArray['postcode']) .
				' OR `event_address`.`postcode` = ' . $this->CI->db->escape($searchArray["postcode"]) . ')';
		}

		if (!empty($searchArray['class_size'])) {
			$where['bookings_lessons.class_size'] = $searchArray['class_size'];
		}

		if (!empty($searchArray['main_contact'])) {
			$customQuery[] = '(`event_contacts`.`contactID` = ' . $this->CI->db->escape($searchArray['main_contact']) .
				' OR `block_contacts`.`contactID` = ' . $this->CI->db->escape($searchArray["main_contact"]) . ')';
		}

		$lessons = $this->CI->LessonsModel->getListWithDetails($where, $like, $customQuery);

		$list_data = [];

		$lessonExceptions = [];
		$lessonStaff = [];
		$staffNames = [];
		if (count($lessons) > 0) {
			$exceptions = $this->CI->LessonsExceptionsModel->getListByDates($date_from_search, $date_to_search, $accountID);

			foreach ($exceptions as $exception) {
				$lessonExceptions[$exception->lessonID][] = $exception;
			}

			$staff = $this->CI->LessonsStaffModel->getListByDates($date_from_search, $date_to_search, $accountID);

			foreach ($staff as $member) {
				$lessonStaff[$member->lessonID][] = $member;
			}

			$allStaff = $this->CI->StaffModel->getList($accountID, 1);

			foreach ($allStaff as $staff) {
				$staffNames[$staff->staffID] = $staff->first . ' ' . $staff->surname;
			}
		}

		foreach ($lessons as $lesson) {
			$lesson->startDate = $lesson->block_start;
			$lesson->endDate = $lesson->block_end;
			if (!empty($lesson->lesson_start)){
				$lesson->startDate = $lesson->lesson_start;
			}

			if (!empty($lesson->lesson_end)) {
				$lesson->endDate = $lesson->lesson_end;
			}

			$datesBetween = generateDatesForSearch($lesson, $date_from_search, $date_to_search);

			if (isset($datesBetween[$lesson->day])) {
				foreach ($datesBetween[$lesson->day] as $date) {
					$skip = false;
					$lesson->staff = [];
					$lesson->date = $date;

					if (strtotime($date) >= strtotime($date_from_search)
						&& strtotime($date) <= strtotime($date_to_search)
						&& strtotime($date) >= strtotime($lesson->startDate)
						&& strtotime($date) <= strtotime($lesson->endDate)) {
						// all ok

					} else {
						continue;
					}

					if (!empty($lessonStaff[$lesson->id])) {
						foreach ($lessonStaff[$lesson->id] as $item) {
							if (strtotime($date) >= strtotime($item->startDate)
								&& strtotime($date) <= strtotime($item->endDate)) {
								$lesson->staff[$item->staffID] = $item->type;
							}
						}
					}

					//check exceptions
					if (array_key_exists($lesson->id, $lessonExceptions)) {
						foreach ($lessonExceptions[$lesson->id] as $exceptionInfo) {
							if ($exceptionInfo->date == $date) {
								// if cancellation, remove
								if ($exceptionInfo->type == 'cancellation') {
									$skip = true;
									continue;
								}

								// staff change
								if (array_key_exists($exceptionInfo->fromID, $lesson->staff)) {
									// swap if moved to another staff
									if (!empty($exceptionInfo->staffID)) {
										$lesson->staff[$exceptionInfo->staffID] = $lesson->staff[$exceptionInfo->fromID];
									}
									if (isset($lesson->staff[$exceptionInfo->fromID])) {
										unset($lesson->staff[$exceptionInfo->fromID]);
									}
								}
							}
						}
					}

					if ($skip) {
						continue;
					}


					$lesson->headcoaches = [];
					$lesson->leadcoaches = [];
					$lesson->assistantcoaches = [];
					$lesson->observers = [];
					$lesson->participants = [];

					//rewrite headcoaches, etc.
					foreach ($lesson->staff as $staffId => $type) {

						// map staff ids to staff names and add to head coach, etc arrays
						if (!array_key_exists($staffId, $staffNames)) {
							unset($lesson->staff[$staffId]);
							continue;
						}

						switch ($type) {
							case 'head':
								$lesson->headcoaches[] = $staffNames[$staffId];
								break;
							case 'lead':
								$lesson->leadcoaches[] = $staffNames[$staffId];
								break;
							case 'assistant':
							default:
								$lesson->assistantcoaches[] = $staffNames[$staffId];
								break;
							case 'observer':
								$lesson->observers[] = $staffNames[$staffId];
								break;
							case 'participant':
								$lesson->participants[] = $staffNames[$staffId];
								break;
						}
					}

					if (!empty($searchArray['staffing_type']) && !empty($searchArray['staff_id'])) {
						$staffRoles = [];

						foreach ($lesson->staff as $id => $role) {
							$staffRoles[$role][] = $id;
						}

						if (empty($staffRoles[$searchArray['staffing_type']]) || !in_array($searchArray['staff_id'], $staffRoles[$searchArray['staffing_type']])) {
							continue;
						}
					}

					if (!empty($searchArray['staff_id'])) {
						if (!array_key_exists($searchArray['staff_id'], $lesson->staff)) {
							continue;
						}
					}

					// filter by region
					if (!empty($searchArray['region_id'])) {
						// if has block org
						if (!empty($lesson->block_orgID)) {
							if ($lesson->block_regionID != $searchArray['region_id']) {
								continue;
							}
						} else {
							if ($lesson->regionID != $searchArray['region_id']) {
								continue;
							}
						}
					}

					// filter by area
					if (!empty($searchArray['area_id'])) {
						// if has block org
						if (!empty($lesson->block_orgID)) {
							if ($lesson->block_areaID != $searchArray['area_id']) {
								continue;
							}
						} else {
							if ($lesson->areaID != $searchArray['area_id']) {
								continue;
							}
						}
					}

					$list_data[] = [
						'id' => $lesson->id,
						'org' => !empty($lesson->block_org) ? $lesson->block_org : $lesson->booking_org,
						'date' => date('d/m/Y', strtotime($lesson->date)),
						'time' => substr($lesson->startTime, 0, 5) . '-' . substr($lesson->endTime, 0 ,5),
						'day' => $lesson->day,
						'activity_name' => $lesson->activity,
						'type_name' => $lesson->type_name,
						'post_code' => !empty($lesson->lesson_postcode) ? $lesson->lesson_postcode : $lesson->event_postcode,
						'class_size' => $lesson->class_size,
						'headcoaches' => $lesson->headcoaches,
						'assistantcoaches' => $lesson->assistantcoaches,
						'leadcoaches' => $lesson->leadcoaches,
						'main_contact' => !empty($lesson->block_main_contact) ? $lesson->block_main_contact : $lesson->event_main_contact,
						'main_tel' => !empty($lesson->block_tel) ? $lesson->block_tel : $lesson->event_tel,
						'date_int' => strtotime($lesson->date)
					];

				}
			}
		}

//		print_r($list_data);
//		die();

		return $list_data;
	}
}
