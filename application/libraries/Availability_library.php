<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Availability_library {

	private $CI;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	/**
	 * check availability
	 * @param  array $search_vars
	 * @return mixed
	 */
	public function check_availability($search_vars = array()) {

		$this->CI->load->library('reports_library');
		if (!is_array($search_vars) || count($search_vars) == 0) {
			$return = array(
				'errors' => array(
					'No search data'
				)
			);
			return $return;
		}

		// get data
		$startDate = NULL;
		if (array_key_exists('startDate', $search_vars)) {
			$startDate = $search_vars['startDate'];
		}
		$endDate = NULL;
		if (array_key_exists('endDate', $search_vars)) {
			$endDate = $search_vars['endDate'];
		}
		$startTimeH = NULL;
		if (array_key_exists('startTimeH', $search_vars)) {
			$startTimeH = $search_vars['startTimeH'];
		}
		$startTimeM = NULL;
		if (array_key_exists('startTimeM', $search_vars)) {
			$startTimeM = $search_vars['startTimeM'];
		}
		$endTimeH = NULL;
		if (array_key_exists('endTimeH', $search_vars)) {
			$endTimeH = $search_vars['endTimeH'];
		}
		$endTimeM = NULL;
		if (array_key_exists('endTimeM', $search_vars)) {
			$endTimeM = $search_vars['endTimeM'];
		}
		$bookingID = NULL;
		if (array_key_exists('booking', $search_vars)) {
			$bookingID = $search_vars['booking'];
		}
		$day = NULL;
		if (array_key_exists('day', $search_vars)) {
			$day = $search_vars['day'];
		}
		$lessonID = NULL;
		if (array_key_exists('lesson', $search_vars)) {
			$lessonID = $search_vars['lesson'];
		}
		$activityID = NULL;
		if (array_key_exists('activityID', $search_vars)) {
			$activityID = $search_vars['activityID'];
		}
		$staffType = NULL;
		if (array_key_exists('staffType', $search_vars)) {
			$staffType = $search_vars['staffType'];
		}

		// validate
		$errors = array();

		if (empty($startDate)) {
			$errors[] = 'Start Date is required';
		} else if (!check_uk_date($startDate)) {
			$errors[] = 'Start Date is invalid';
		}
		if (empty($endDate)) {
			$errors[] = 'End Date is required';
		} else if (!check_uk_date($endDate)) {
			$errors[] = 'End Date is invalid';
		}
		if (empty($startTimeH)) {
			$errors[] = 'Start Time - Hour is required';
		}
		if (empty($startTimeM)) {
			$errors[] = 'Start Time - Minutes is required';
		}
		if (empty($endTimeH)) {
			$errors[] = 'End Time - Hour is required';
		}
		if (empty($endTimeM)) {
			$errors[] = 'End Time - Minutes is required';
		}
		if (empty($bookingID)) {
			$errors[] = 'Booking is required';
		}
		if (empty($day)) {
			$errors[] = 'Day is required';
		}
		if (!empty($lessonID)) {
			$current_lesson_info = $this->CI->db->select('lesson_types.hourly_rate, lesson_types.typeID, bookings_lessons.addressID')->from('bookings_lessons')->
			join('lesson_types', 'lesson_types.typeID=bookings_lessons.typeID', 'left')->
			where(array('bookings_lessons.lessonID' => $lessonID))->
			get();
		}

		// check end time after start
		if (count($errors) == 0) {
			// work out start and end times without buffer
			$startTimeNoBuffer = date('H:i', strtotime($startTimeH . ':' . $startTimeM));
			$endTimeNoBuffer = date('H:i', strtotime($endTimeH . ':' . $endTimeM));

			if (strtotime($endTimeNoBuffer) <= strtotime($startTimeNoBuffer)) {
				$errors[] = 'End time must be after start time';
			}
		}

		// errors, stop
		if (count($errors) > 0) {
			$return = array(
				'errors' => $errors
			);
			return $return;
		}

		// work out start and end time, add 30 mins either side for travel
		$startTime = date('H:i', strtotime('-30 minutes', strtotime($startTimeNoBuffer)));
		$endTime = date('H:i', strtotime('+30 minutes', strtotime($endTimeNoBuffer)));

		// reformat dates
		$startDate = uk_to_mysql_date($startDate);
		$endDate = uk_to_mysql_date($endDate);

		// look up booking
		$where = array(
			'bookingID' => $bookingID
		);
		$res = $this->CI->db->from('bookings')->where($where)->get();

		if ($res->num_rows() == 0) {
			$return = array(
				'errors' => array(
					'Booking not found'
				)
			);
			return $return;
		}

		foreach ($res->result() as $booking_info) {}


        //if it is not web session get account from booking
        if (!$this->CI->auth->user) {
            $accountId = $booking_info->accountID;
        } else {
            $accountId = $this->CI->auth->user->accountID;
        }

        $res = $this->CI->db->from('accounts')->where([
            'accountID' => $accountId
        ])->get();

        foreach ($res->result() as $account_info) {}

		// cache session counts
		$org_lessons = array();
		$activity_lessons = array();

		// get org id from booking
		$orgID = $booking_info->orgID;

		// if session id set, look up block for orgID override
		if (!empty($lessonID)) {
			$where_override = array(
				'bookings_lessons.lessonID' => $lessonID,
				'bookings_lessons.accountID' => $accountId
			);
			$res = $this->CI->db->select('bookings_blocks.orgID')->from('bookings_lessons')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where_override)->limit(1)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if (!empty($row->orgID)) {
						$orgID = $row->orgID;
					}
				}
			}
		}

		// get approx sessions at this org - doesn't account for exceptions
		$where = array(
			'bookings.orgID' => $orgID,
			'bookings.accountID' => $accountId
		);

		$res = $this->CI->db->select('bookings_lessons_staff.staffID, bookings_lessons.day, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end')->from('bookings')->join('bookings_lessons_staff', 'bookings.bookingID = bookings_lessons_staff.bookingID', 'inner')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!array_key_exists($row->staffID, $org_lessons)) {
					$org_lessons[$row->staffID] = 0;
				}
				// switch start and end dates depending on if session has them, else default to block
				if (!empty($row->lesson_start) && !empty($row->lesson_end)) {
					$date = $row->lesson_start;
					$end_date = $row->lesson_end;
				} else {
					$date = $row->block_start;
					$end_date = $row->block_end;
				}

				// skip those not started
				if (strtotime($date) > time()) {
					continue;
				}

				// if end date in future, only count until now
				if (strtotime($end_date) > time()) {
					$end_date = date('Y-m-d');
				}

				// loop through dates to see how many times day occurs
				while (strtotime($date) <= strtotime($end_date)) {
					if (strtolower(date('l', strtotime($date))) == $row->day) {
						$org_lessons[$row->staffID]++;
					}
					$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
				}
			}
		}

		// get approx sessions for this activity - doesn't account for exceptions
		if (!empty($activityID) && $activityID != 'other') {
			$where = array(
				'bookings_lessons.activityID' => $activityID,
				'bookings_lessons.accountID' => $accountId
			);
			$res = $this->CI->db->select('bookings_lessons_staff.staffID, bookings_lessons.day, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if (!array_key_exists($row->staffID, $activity_lessons)) {
						$activity_lessons[$row->staffID] = 0;
					}
					// switch start and end dates depending on if session has them, else default to block
					if (!empty($row->lesson_start) && !empty($row->lesson_end)) {
						$date = $row->lesson_start;
						$end_date = $row->lesson_end;
					} else {
						$date = $row->block_start;
						$end_date = $row->block_end;
					}

					// skip those not started
					if (strtotime($date) > time()) {
						continue;
					}

					// if end date in future, only count until now
					if (strtotime($end_date) > time()) {
						$end_date = date('Y-m-d');
					}

					// loop through dates to see how many times day occurs
					while (strtotime($date) <= strtotime($end_date)) {
						if (strtolower(date('l', strtotime($date))) == $row->day) {
							$activity_lessons[$row->staffID]++;
						}
						$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
					}
				}
			}
		}

		// get staff activities
		$staff_activities = array();
		$where = array(
			'accountID' => $accountId
		);
		$res = $this->CI->db->from('staff_activities')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->head == 1) {
					$staff_activities[$row->staffID][$row->activityID][] = 'head';
				}
				if ($row->lead == 1) {
					$staff_activities[$row->staffID][$row->activityID][] = 'lead';
				}
				if ($row->assistant == 1) {
					$staff_activities[$row->staffID][$row->activityID][] = 'assistant';
				}
			}
		}

		// get active staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $accountId
		);

		$res = $this->CI->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {

			foreach ($res->result() as $row) {

				$conflicts = array();

				// check for conflicts with another lesson
				$where = array(
					'bookings_lessons.day' => $day,
					'bookings_lessons_staff.startDate <=' => $endDate,
					'bookings_lessons_staff.endDate >=' => $startDate,
					'bookings_lessons.startTime <=' => $endTime,
					'bookings_lessons.endTime >=' => $startTime,
					'bookings_lessons_staff.staffID' => $row->staffID
				);

				// if session id set, exclude from check
				if (!empty($lessonID)) {
					$where['bookings_lessons.lessonID !='] = $lessonID;
				}

				$where_custom = $this->CI->db->dbprefix('bookings_lessons_staff') . '.startDate >= SUBDATE(' . $this->CI->db->escape($startDate) . ',  INTERVAL DATEDIFF(' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.`endDate`, ' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.`startDate`) DAY)';
				$where_custom .= ' AND ' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.endDate <= ADDDATE(' . $this->CI->db->escape($endDate) . ',  INTERVAL DATEDIFF(' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.`endDate`, ' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.`startDate`) DAY)';
				$where_custom .= ' AND ' . $this->CI->db->dbprefix('bookings_lessons') . '.startTime >= SUBTIME(CAST(' . $this->CI->db->escape($startTime) . ' AS TIME),  SUBTIME(' . $this->CI->db->dbprefix('bookings_lessons') . '.`endTime`, ' . $this->CI->db->dbprefix('bookings_lessons') . '.`startTime`))';
				$where_custom .= ' AND ' . $this->CI->db->dbprefix('bookings_lessons') . '.endTime <= ADDTIME(CAST(' . $this->CI->db->escape($endTime) . ' AS TIME),  SUBTIME(' . $this->CI->db->dbprefix('bookings_lessons') . '.`endTime`, ' . $this->CI->db->dbprefix('bookings_lessons') . '.`startTime`))';

				$res_check = $this->CI->db->select('bookings_lessons.bookingID, bookings_lessons.startTime as lesson_start, bookings_lessons.endTime as lesson_end,
				 bookings_lessons.lessonID, bookings_lessons_staff.startDate as staffStartDate, bookings_lessons_staff.endDate as staffEndDate,
				 bookings.orgID, bookings.type as booking_type, bookings.name as event_name,  bookings_blocks.provisional,
				 bookings_blocks.orgID as block_orgID, orgs.name as org_name, block_orgs.name as block_org_name,
				 bookings.addressID as booking_address, bookings_blocks.addressID as block_address, bookings_lessons.addressID as lesson_address')
					->from('bookings_lessons')
					->join('bookings', 'bookings.bookingID = bookings_lessons.bookingID', 'inner')
					->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
					->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
					->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
					->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID')
					->where($where)
					->where($where_custom, NULL, FALSE)
					->group_by('bookings_lessons.lessonID')->get();

				if ($res_check->num_rows() > 0) {

					foreach ($res_check->result() as $row_check) {

						// if at same org, check without buffer
						$orgID_compare = $row_check->orgID;
						if (!empty($row_check->block_orgID)) {
							$orgID_compare = $row_check->block_orgID;
						}
						if ($orgID_compare == $orgID) {
							$db_start = strtotime($row_check->lesson_start);
							$db_end = strtotime($row_check->lesson_end);
							$input_start = strtotime($startTimeNoBuffer);
							$input_end = strtotime($endTimeNoBuffer);

							if ($db_start < $input_end && $input_start < $db_end) {
								// conflict
							} else {
								// no conflict, skip
								continue;
							}
						}

						// add conflict
						$conflicts[$row_check->lessonID] = array(
							'bookingID' => $row_check->bookingID,
							'lessonID' => $row_check->lessonID,
							'exception' => FALSE,
							'dates' => mysql_to_uk_date($row_check->staffStartDate),
							'provisional' => $row_check->provisional,
							'booking_type' => $row_check->booking_type,
							'lesson_times' => " (" . substr($row_check->lesson_start, 0, 5) . "-" . substr($row_check->lesson_end, 0, 5) . ")",
							'event_name' => $row_check->event_name,
							'org_name' => $row_check->org_name
						);

						// check if block belong to different org
						if (!empty($row_check->block_orgID)) {
							$conflicts[$row_check->lessonID]['org_name'] = $row_check->block_org_name;
						}

						if (strtotime($row_check->staffEndDate) > strtotime($row_check->staffStartDate)) {
							$conflicts[$row_check->lessonID]['dates'] .= ' to ' . mysql_to_uk_date($row_check->staffEndDate);
						}

						// if only checking one day, check for exceptions which may make this staff member free
						if ($startDate == $endDate) {
							// check for cancelled session exception, if so, ok to carry on
							$where = array(
								'lessonID' => $row_check->lessonID,
								'date' => $startDate,
								'type' => 'cancellation',
								'accountID' => $accountId
							);

							$res_exception_check = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

							if ($res_exception_check->num_rows() > 0) {
								if (array_key_exists($row_check->lessonID, $conflicts)) {
									unset($conflicts[$row_check->lessonID]);
								}
							}

							// check for staffchange session exception, if so, ok to carry on
							$where['type'] = 'staffchange';
							$where['fromID'] = $row->staffID;

							$res_exception_check = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

							if ($res_exception_check->num_rows() > 0) {
								if (array_key_exists($row_check->lessonID, $conflicts)) {
									unset($conflicts[$row_check->lessonID]);
								}
							}
						}
					}
				}

				$reasons = array();

				// if no conflicts, continue
				if (count($conflicts) == 0) {

					$available = array();
					$unavailable = array();

					// loop dates to check availability
					$date = $startDate;
					$end_date = $endDate;
					while (strtotime($date) <= strtotime($end_date)) {
						if (strtolower(date('l', strtotime($date))) == $day) {
							$staffStartTime = date('H:i', strtotime('+30 minutes', strtotime($startTime)));
							$staffEndTime = date('H:i', strtotime('-30 minutes', strtotime($endTime)));

							// check availability
							$where = array(
								'staffID' => $row->staffID,
								'day' => $day,
								'week' => $this->CI->crm_library->week_number_from_shift_pattern($date),
								'from <=' => $staffStartTime,
								'to >=' => $staffEndTime,
								'accountID' => $accountId
							);

							$result_check = $this->CI->db->from('staff_availability')->where($where)->limit(1)->get();

							if ($result_check->num_rows() == 1) {
								$available[] = mysql_to_uk_date($date);
							} else {
								$unavailable[] = mysql_to_uk_date($date);
							}
						}

						// next date
						$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
					}

					if (count($available) == 0) {
						// not available throughout
						$label = 'Unavailable (Working Hours)';
						$priority = -50;
						$reasons[] = "<li>Session time is not within the hours this person is set to work</li>";
					} else if (count($unavailable) > 0) {
						// unavailable some dates
						$label = 'Unavailable (Working Hours Some Weeks)';
						$priority = -40;
						$reasons[] = "<li>Available on: " . implode(', ', $available) . "</li>";
						$reasons[] = "<li>Unavailable on: " . implode(', ', $unavailable) . "</li>";
					} else {
						// available for all dates

						// check if suitable
						if (empty($activityID)) {
							$label = "Available (No or other activity specified in lesson)";
							$reasons[] = "<li>Session time is within the hours this person is set to work</li>";
							$reasons[] = "<li>No or other activity specified in lesson</li>";
							$priority = 10;
						} else {
							if (array_key_exists($row->staffID, $staff_activities) && array_key_exists($activityID, $staff_activities[$row->staffID])) {
								if (empty($staffType) || in_array($staffType, $staff_activities[$row->staffID][$activityID]) || in_array($staffType, array('participant', 'observer'))) {
									$label = "Available";
									$priority = 50;
									$reasons[] = "<li>Session time is within the hours this person is set to work</li>";
									$reasons[] = "<li>Can deliver this activity";
								} else {
									$label = "Available (Wrong Role)";
									$priority = 40;
									$reasons[] = "<li>Session time is within the hours this person is set to work";
									$reason = "<li>Can deliver this activity, but isn't marked as able to deliver as a";
									if ($staffType == 'assistant') {
										$reason .= 'n';
									}
									$reason .= ' ' . $staffType;
									$reason .= " coach</li>";
									$reasons[] = $reason;
								}
							} else {
								$label = "Available (Wrong activity)";
								$priority = 10;
								$reasons[] = "<li>Session activity is not one this person usually delivers</li>";
							}
						}

					}

				} else {
					// process and categorise conflicts
					$provisional_conflicts = array();
					$confirmed_conflicts = array();
					foreach ($conflicts as $conflict) {
						$conflict_item = "<li>";
						if ($conflict['booking_type'] == "event") {
							$conflict_item .= $conflict['event_name'];
						} else {
							$conflict_item .= $conflict['org_name'];
						}
						$conflict_item .= " - " . $conflict['dates'];
						if (!empty($conflict['lesson_times'])) {
							$conflict_item .= $conflict['lesson_times'];
						}
						$conflict_item .= "</li>";
						if ($conflict['provisional'] == 1) {
							$provisional_conflicts[] = $conflict_item;
						} else {
							$confirmed_conflicts[] = $conflict_item;
						}
					}

					if (count($provisional_conflicts) > 0) {
						$label = 'Unavailable (Conflicting Provisional Session';
						if (count($conflicts) > 1) {
							$label .= 's';
						}
						$label .= ')';
						$priority = -10;
					} else {
						$label = 'Unavailable (Conflicting Session';
						if (count($conflicts) > 1) {
							$label .= 's';
						}
						$label .= ')';
						$priority = -20;
					}

					// show provisional conflicts
					if (count($provisional_conflicts) > 0) {
						$reason = "<li>Person has ";
						if (count($provisional_conflicts) == 1) {
							$reason .= 'a ';
						}
						$reason .= " provisional conflicting session";
						if (count($provisional_conflicts) != 1) {
							$reason .= 's';
						}
						$reason .= "<ul>";
						foreach ($provisional_conflicts as $conflict) {
							$reason .= $conflict;
						}
						$reason .= "</li></ul>";
						$reasons[] = $reason;
					}

					// show confirmed conflicts
					if (count($confirmed_conflicts) > 0) {
						$reason = "<li>Person has ";
						if (count($confirmed_conflicts) == 1) {
							$reason .= 'a ';
						}
						$reason .= " conflicting session";
						if (count($confirmed_conflicts) != 1) {
							$reason .= 's';
						}
						$reason .= "<ul>";
						foreach ($confirmed_conflicts as $conflict) {
							$reason .= $conflict;
						}
						$reason .= "</li></ul>";
						$reasons[] = $reason;
					}
				}

				// look up any availabiliy exceptions that may conflict
				$where = array(
					'from <=' => $endDate." ".$endTime.":00",
					'to >=' =>$startDate." ".$startTime.":00",
					'staffID' => $row->staffID,
					'accountID' => $accountId
				);
				$res_check = $this->CI->db->from('staff_availability_exceptions')->where($where)->order_by('from asc, to asc')->get();

				// if found
				if ($res_check->num_rows() > 0) {
					$reason = "<li>Person has ";
					if ($res_check->num_rows() == 1) {
						$reason .= 'an ';
					}
					$reason .= "availability exception";
					if ($res_check->num_rows() != 1) {
						$reason .= 's';
					}
					$reason .= "<ul>";
					foreach ($res_check->result() as $row_check) {
						$reason .= "<li>" . date("d/m/Y H:i", strtotime($row_check->from)) . " to " . date("d/m/Y H:i", strtotime($row_check->to)) . " - " . htmlspecialchars($row_check->reason) . "</li>";
					}
					$reason .= "</ul></li>";
					$reasons[] = $reason;
					if ($priority > 0) {
						$label = 'Unavailable (Availability Exception)';
						$priority = -30;
					}
				}

				// if is available, run more checks
				if ($priority > 0) {

					$selected_qual = $this->CI->db->select('mandatory_quals.*')
						->from('mandatory_quals')
						->join('staff_quals_mandatory',
							'mandatory_quals.qualID=staff_quals_mandatory.qualID AND staff_quals_mandatory.preferred_for_pay_rate=1',
							'left')
						->where([
							'mandatory_quals.accountID' => $accountId,
							'staff_quals_mandatory.staffID' => $row->staffID
						])->limit(1)->get()->result();

					if (!empty($selected_qual)) {
						$selected_qual = $selected_qual[0];
					}

					// check the session hourly rate too
					$hourly_rate = null;
					$session_override_rate = 0;
					if(!empty($lessonID))
					{
						$res_lesson = $this->CI->db->select('lesson_types.hourly_rate, lesson_types.typeID')->from('bookings_lessons')->
						join('lesson_types', 'lesson_types.typeID=bookings_lessons.typeID', 'left')->
						where(array('bookings_lessons.lessonID' => $lessonID))->
						get();
						foreach($res_lesson->result() as $row_lesson) {
							$hourly_rate = (float)$row_lesson->hourly_rate;
							if ($row->system_pay_rates && !empty($selected_qual)) {

								$session_rates = $this->CI->db->from('session_qual_rates')
									->where([
										'accountID' => $accountId,
										'lessionTypeID' => $row_lesson->typeID,
										'qualTypeID' => $selected_qual->qualID,
									])->limit(1)->get()->result();

								if (!empty($session_rates)) {
									$session_override_rate = $this->CI->reports_library->get_qualification_rate_by_session($row, $selected_qual, $session_rates[0], $staffType);
								}
							}
						}
					}

					// work out session length
					$d1 = new DateTime(date('Y-m-d') . ' ' . $endTimeNoBuffer);
					$d2 = new DateTime(date('Y-m-d') . ' ' . $startTimeNoBuffer);
					$diff = $d2->diff($d1);
					$lesson_length = $diff->h * 60;
					$lesson_length += $diff->i;
					$decimal_length = $lesson_length / 60;

					// cost based on session length
					if($hourly_rate > 0)
					{
						$rate = $decimal_length * $hourly_rate;
						$reason = '<li>Cost of this person\'s time per session: ' . currency_symbol() . number_format($rate, 2) . '</li>';

						$reasons []= $reason;

					} else {
						if ($session_override_rate > 0) {
							$reason = '<li>Cost of this person\'s time per session: ' . currency_symbol() . number_format($session_override_rate, 2) . '</li>';

							$reasons []= $reason;
						} else {
							if(!$row->system_pay_rates &&  (float)$row->hourly_rate > 0){
								// for this staff member only hourly_rate is set

								$reason = '<li>Cost of this person\'s time per session: ' . currency_symbol() . number_format($row->hourly_rate, 2) . '</li>';

								$reasons []= $reason;
							} else {
								if($row->system_pay_rates && !empty($selected_qual)) {
									$per_hour = $this->CI->reports_library->get_qualification_rate($row, $selected_qual);

									$reason = '<li>Cost of this person\'s time per session: ' . currency_symbol() . number_format($per_hour, 2) . '</li>';

									$reasons []= $reason;
								} else if (!empty($row->payments_scale_head) || !empty($row->payments_scale_assist)) {
									$head_rate = 0;
									$assist_rate = 0;
									$lowest_rate = 1000000;
									if (!empty($row->payments_scale_head)) {
										$head_rate = $decimal_length * $row->payments_scale_head;
										if ($head_rate < $lowest_rate) {
											$lowest_rate = $head_rate;
										}
									}
									if (!empty($row->payments_scale_assist)) {
										$assist_rate = $decimal_length * $row->payments_scale_assist;
										if ($assist_rate < $lowest_rate) {
											$lowest_rate = $assist_rate;
										}
									}
									if ($head_rate > 0 || $assist_rate > 0) {

									    if ($this->CI->auth->user) {
                                            if (in_array($this->CI->auth->user->department, array('directors', 'management'))) {
                                                $reason = '<li>Cost of this person\'s time per session:<ul>';
                                                if ($head_rate > 0) {
                                                    $reason .= '<li>' . $this->CI->settings_library->get_staffing_type_label('head') .  ': ' . currency_symbol() . number_format($head_rate, 2) . '</li>';
                                                }
                                                if ($assist_rate > 0) {
                                                    $reason .= '<li>' . $this->CI->settings_library->get_staffing_type_label('assistant') .  ': ' . currency_symbol() . number_format($assist_rate, 2) . '</li>';
                                                }
                                                $reason .= '</ul></li>';
                                                $reasons[] = $reason;
                                            }
                                        }

										// decrease priority
										$priority -= 0.05 * $lowest_rate;
									}
								}
							}
						}
					}

					// work out length of service
					if (!empty($row->employment_start_date)) {
						$d1 = new DateTime();
						$d2 = new DateTime($row->employment_start_date);
						$diff = $d2->diff($d1);
						$reason = '<li>With ' . $account_info->company . ' for ' . $diff->y . ' year';
						if ($diff->y != 1) {
							$reason .= 's';
						}
						$reason .= '</li>';
						if ($diff->y < 1) {
							$reason = '<li>With ' . $account_info->company . ' for ' . $diff->m . ' month';
							if ($diff->m != 1) {
								$reason .= 's';
							}
							$reason .= '</li>';
						}
						$reasons[] = $reason;

						// increase priority
						if ($diff->y >= 1) {
							for ($i=0; $i < $diff->y; $i++) {
								$priority += 0.03;
							}
						} else {
							// if less than a year, increase by total months
							$priority += (0.03/12) * $diff->m;
						}
					}

					// sessions at org
					if (array_key_exists($row->staffID, $org_lessons) && $org_lessons[$row->staffID] > 0) {
						$reason = '<li>Around ' . $org_lessons[$row->staffID] . ' session';
						if ($org_lessons[$row->staffID] != 1) {
							$reason .= 's';
						}
						$reason .= ' delivered for this ' . strtolower($this->CI->settings_library->get_label('customer')) . '</li>';
						$reasons[] = $reason;
						// increase priority
						$priority += 0.02 * $org_lessons[$row->staffID];
					}

					// activity sessions
					if (array_key_exists($row->staffID, $activity_lessons) && $activity_lessons[$row->staffID] > 0) {
						$reason = '<li>Around ' . $activity_lessons[$row->staffID] . ' session';
						if ($activity_lessons[$row->staffID] != 1) {
							$reason .= 's';
						}
						$reason .= ' delivered for this activity</li>';
						$reasons[] = $reason;
						// increase priority
						$priority += 0.01 * $activity_lessons[$row->staffID];
					}
				}

				// determine status
				$priority_status = 'amber';
				if ($priority > 30) {
					$priority_status = 'green';
				} else if ($priority < 0) {
					$priority_status = 'red';
				}

				$return[] = array(
					'staffID' => $row->staffID,
					'option' => '<option value="'.$row->staffID.'" data-priority="' . $priority . '">'.htmlspecialchars($row->first . " " . $row->surname).' - '.$label.'</option>',
					'reason' => implode("\n", $reasons),
					'priority' => $priority,
					'status' => $priority_status
				);

			}
		}

		// order by priority desc
		$return = array_orderby($return, 'priority', SORT_DESC);

		return $return;
	}


	public function checkAvailabilityById($lessonId, $staffId, $startDate, $endDate) {
		$res = $this->CI->db->from('bookings_lessons')->where([
			'lessonID' => $lessonId
		])->limit(1)->get();
		if ($res->num_rows() == 0) {
			return false;
		}

		foreach ($res->result() as $lesson_info);


		// look up booking
		$where = array(
			'bookingID' => $lesson_info->bookingID
		);
		$res = $this->CI->db->from('bookings')->where($where)->get();

		if ($res->num_rows() == 0) {
			return false;
		}

		foreach ($res->result() as $booking_info) {}

		$orgID = $booking_info->orgID;

		// if session id set, look up block for orgID override
		$where = array(
			'bookings_lessons.lessonID' => $lessonId
		);
		$res = $this->CI->db->select('bookings_blocks.orgID')
			->from('bookings_lessons')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->where($where)->limit(1)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!empty($row->orgID)) {
					$orgID = $row->orgID;
				}
			}
		}

		$startTimeNoBuffer = date('H:i', strtotime(substr($lesson_info->startTime, 0, 2) . ':' . substr($lesson_info->startTime, 3, 2)));
		$endTimeNoBuffer = date('H:i', strtotime(substr($lesson_info->endTime, 0, 2) . ':' . substr($lesson_info->endTime, 3, 2)));

		// work out start and end time, add 30 mins either side for travel
		$startTime = date('H:i', strtotime('-30 minutes', strtotime($startTimeNoBuffer)));
		$endTime = date('H:i', strtotime('+30 minutes', strtotime($endTimeNoBuffer)));

		// check for conflicts with another lesson
		$where = array(
			'bookings_lessons.day' => $lesson_info->day,
			'bookings_lessons_staff.startDate <=' => $endDate,
			'bookings_lessons_staff.endDate >=' => $startDate,
			'bookings_lessons.startTime <=' => $endTime,
			'bookings_lessons.endTime >=' => $startTime,
			'bookings_lessons_staff.staffID' => $staffId,
			'bookings_lessons.lessonID !=' => $lessonId
		);

		$res_check = $this->CI->db->select('bookings_lessons.bookingID, bookings_lessons.startTime as lesson_start, bookings_lessons.endTime as lesson_end,
				 bookings_lessons.lessonID, bookings_lessons_staff.startDate as staffStartDate, bookings_lessons_staff.endDate as staffEndDate,
				 bookings.orgID, bookings.type as booking_type, bookings.name as event_name,  bookings_blocks.provisional,
				 bookings_blocks.orgID as block_orgID, orgs.name as org_name, block_orgs.name as block_org_name,
				 bookings.addressID as booking_address, bookings_blocks.addressID as block_address, bookings_lessons.addressID as lesson_address')
			->from('bookings_lessons')
			->join('bookings', 'bookings.bookingID = bookings_lessons.bookingID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID')
			->where($where)
			->group_by('bookings_lessons.lessonID')->get();


		$conflicts = [];

		if ($res_check->num_rows() > 0) {

			foreach ($res_check->result() as $row_check) {

				// if at same org, check without buffer
				$orgID_compare = $row_check->orgID;
				if (!empty($row_check->block_orgID)) {
					$orgID_compare = $row_check->block_orgID;
				}

				if ($orgID_compare == $orgID) {
					$db_start = strtotime($row_check->lesson_start);
					$db_end = strtotime($row_check->lesson_end);
					$input_start = strtotime($startTimeNoBuffer);
					$input_end = strtotime($endTimeNoBuffer);

					if ($db_start < $input_end && $input_start < $db_end) {
						// conflict
					} else {
						// no conflict, skip
						continue;
					}
				}

				$conflicts[$row_check->lessonID] = array(
					'bookingID' => $row_check->bookingID,
					'lessonID' => $row_check->lessonID,
					'exception' => FALSE,
					'dates' => mysql_to_uk_date($row_check->staffStartDate),
					'provisional' => $row_check->provisional,
					'booking_type' => $row_check->booking_type,
					'lesson_times' => " (" . substr($row_check->lesson_start, 0, 5) . "-" . substr($row_check->lesson_end, 0, 5) . ")",
					'event_name' => $row_check->event_name,
					'org_name' => $row_check->org_name
				);


				// if only checking one day, check for exceptions which may make this staff member free
				if ($startDate == $endDate) {
					// check for cancelled session exception, if so, ok to carry on
					$where = array(
						'lessonID' => $row_check->lessonID,
						'date' => $startDate,
						'type' => 'cancellation'
					);

					$res_exception_check = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

					if ($res_exception_check->num_rows() > 0) {
						if (array_key_exists($row_check->lessonID, $conflicts)) {
							unset($conflicts[$row_check->lessonID]);
						}
					}

					// check for staffchange session exception, if so, ok to carry on
					$where['type'] = 'staffchange';
					$where['fromID'] = $staffId;

					$res_exception_check = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

					if ($res_exception_check->num_rows() > 0) {
						if (array_key_exists($row_check->lessonID, $conflicts)) {
							unset($conflicts[$row_check->lessonID]);
						}
					}
				}
			}
		}

		return $conflicts;

	}
}
