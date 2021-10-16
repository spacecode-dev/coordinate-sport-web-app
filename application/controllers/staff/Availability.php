<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Availability extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach + office
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach', 'office'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * edit availability
	 * @param  int $staffID
	 * @return void
	 */
	public function index($staffID = NULL)
	{

		$staff_info = new stdClass;

		// check
		if ($staffID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($staffID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Availability';
		$submit_to = 'staff/availability/' . $staffID;
		$return_to = $submit_to;
		$buttons = NULL;
		$icon = 'calendar-alt';
		$tab = 'availability';
		$current_page = 'staff';
		$section = 'staff';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
 		);

		// get number of weeks in shift pattern
		$weeks = intval($this->settings_library->get('shift_pattern_weeks'));
		if ($weeks < 1) {
			$weeks = 1;
		}

		// set available hours for table (must be half hour slots) - also set in coach.php
		$available_hours = array(
			'06:00',
			'06:30',
			'07:00',
			'07:30',
			'08:00',
			'08:30',
			'09:00',
			'09:30',
			'10:00',
			'10:30',
			'11:00',
			'11:30',
			'12:00',
			'12:30',
			'13:00',
			'13:30',
			'14:00',
			'14:30',
			'15:00',
			'15:30',
			'16:00',
			'16:30',
			'17:00',
			'17:30',
			'18:00',
			'18:30',
			'19:00',
			'19:30',
			'20:00',
			'20:30',
			'21:00',
			'21:30',
			'22:00',
			'22:30',
			'23:00',
			'23:30'
		);

		// copy available slots and add one at the end so we can process the last slot successfully when adding to the db
		$available_slots = $available_hours;
		$available_slots[] = '23:59';

		// look up availability
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->get_where('staff_availability', $where);

		$range = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$from = substr($row->from, 0, 5);
				$to = substr($row->to, 0, 5);

				if (strtotime($to) == strtotime($from) + 60*30) {
					// just for half hour
					$range[$row->week][$row->day][$from][$to] = 1;
				} else {
					// multiple hours
					$from_time = strtotime($from);
					$to_time = strtotime($to);
					while ($from_time < $to_time) {
						$to = date("H:i", $from_time + 60*30);
						$from = date("H:i", $from_time);
						$range[$row->week][$row->day][$from][$to] = 1;
						$from_time += 60*30;
					}
				}
			}
		}

		$availability_info = array();
		// loop weeks
		for ($week=1; $week <= $weeks; $week++) {
			foreach ($available_hours as $start) {
				// if in half hour
				if (substr($start, 3, 2) == "30") {
					// set end to next hour
					$end = sprintf("%02d", substr($start, 0, 2)+1).":00";
					if($end == "24:00"){
						$end = "00:00";
					}
				} else {
					// else set to next half hour
					$end = (substr($start, 0, 2)).":30";
				}
				if (isset($range[$week]['monday'][$start][$end]) && $range[$week]['monday'][$start][$end] == 1) {
					$availability_info[$week][0][$start] = 1;
				} else {
					$availability_info[$week][0][$start] = null;
				}
				if (isset($range[$week]['tuesday'][$start][$end]) && $range[$week]['tuesday'][$start][$end] == 1) {
					$availability_info[$week][1][$start] = 1;
				} else {
					$availability_info[$week][1][$start] = null;
				}
				if (isset($range[$week]['wednesday'][$start][$end]) && $range[$week]['wednesday'][$start][$end] == 1) {
					$availability_info[$week][2][$start] = 1;
				} else {
					$availability_info[$week][2][$start] = null;
				}
				if (isset($range[$week]['thursday'][$start][$end]) && $range[$week]['thursday'][$start][$end] == 1) {
					$availability_info[$week][3][$start] = 1;
				} else {
					$availability_info[$week][3][$start] = null;
				}
				if (isset($range[$week]['friday'][$start][$end]) && $range[$week]['friday'][$start][$end] == 1) {
					$availability_info[$week][4][$start] = 1;
				} else {
					$availability_info[$week][4][$start] = null;
				}
				if (isset($range[$week]['saturday'][$start][$end]) && $range[$week]['saturday'][$start][$end] == 1) {
					$availability_info[$week][5][$start] = 1;
				} else {
					$availability_info[$week][5][$start] = null;
				}
				if (isset($range[$week]['sunday'][$start][$end]) && $range[$week]['sunday'][$start][$end] == 1) {
					$availability_info[$week][6][$start] = 1;
				} else {
					$availability_info[$week][6][$start] = null;
				}
			}
		}

		// if posted
		if ($this->input->post()) {

			$availability_info = $this->input->post('availability_info');

			// work out availability ranges
			$range = array();
			for ($week=1; $week <= $weeks; $week++) {
				$day_index = 0;
				while ($day_index <= 6) {
					switch ($day_index) {
						case 0:
							$day = "monday";
							break;
						case 1:
							$day = "tuesday";
							break;
						case 2:
							$day = "wednesday";
							break;
						case 3:
							$day = "thursday";
							break;
						case 4:
							$day = "friday";
							break;
						case 5:
							$day = "saturday";
							break;
						case 6:
							$day = "sunday";
							break;
					}
					$from = null;
					$to = null;

					foreach ($available_slots as $start) {
						if (isset($availability_info[$week][$day_index][$start]) && $availability_info[$week][$day_index][$start] == 1 && $from == null) {
							$from = $start;
							if($from == "23:30"){
								$to = "23:59";
							}
						} else if (((!isset($availability_info[$week][$day_index][$start]) || $availability_info[$week][$day_index][$start] != 1) || $start == "23:59") && $from != null) {
							$to = $start;
							if($from == "23:30"){
								$to = "23:59";
							}
							$range[] = array("week" => $week, "day" => $day, "from" => $from, "to" => $to);
							$from = null;
							$to = null;
						}
					}
					$day_index++;
				}
			}

			// clear current availability ranges
			$where = array(
				'staffID' => $staffID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->delete('staff_availability', $where);

			// insert availability ranges
			if (count($range) > 0) {
				foreach ($range as $r) {
					$data = array(
						'staffID' => $staffID,
						'byID' => $this->auth->user->staffID,
						'week' => $r['week'],
						'day' => $r['day'],
						'from' => $r['from'],
						'to' => $r['to'],
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					$query = $this->db->insert('staff_availability', $data);
				}
			}

			$this->session->set_flashdata('success', 'Availability data has been updated successfully.');

			redirect($return_to);

		}


		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'staff_info' => $staff_info,
			'available_hours' => $available_hours,
			'availability_info' => $availability_info,
			'weeks' => $weeks,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/availability', $data);
	}

	/**
	 * show list of exceptions
	 * @return void
	 */
	public function exceptions($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('staff')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$staff_info = $row;
		}

		// set defaults
		$icon = 'calendar-alt';
		$tab = 'availability';
		$current_page = 'staff';
		$page_base = 'staff/availability/' . $staffID . '/exceptions';
		$section = 'staff';
		$title = 'Availability Exceptions';
		$buttons = '<a class="btn" href="' . site_url('staff/availability/' . $staffID) . '"><i class="far fa-angle-left"></i> Return to Availability</a> <a class="btn btn-success" href="' . site_url('staff/availability/' . $staffID . '/exceptions/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/availability/' . $staffID => 'Availability'
 		);

		// set where
		$where = array(
			'staff_availability_exceptions.staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'type' => NULL,
			'reason' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_reasont', 'Reason', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['type'] = set_value('search_type');
			$search_fields['reason'] = set_value('search_reason');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-staff-exceptions'))) {

			foreach ($this->session->userdata('search-staff-exceptions') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-staff-exceptions', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`from` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`to` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['type'] != '') {
				$search_where[] = "`type` = " . $this->db->escape($search_fields['type']);
			}

			if ($search_fields['reason'] != '') {
				$search_where[] = "`reason` LIKE '%" . $this->db->escape_like_str($search_fields['reason']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('staff_availability_exceptions')->where($where)->where($search_where, NULL, FALSE)->order_by('to desc, from desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('staff_availability_exceptions')->where($where)->where($search_where, NULL, FALSE)->order_by('to desc, from desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'exceptions' => $res,
			'staff_info' => $staff_info,
			'search_fields' => $search_fields,
			'staffID' => $staffID,
			'success' => $success,
			'breadcrumb_levels' => $breadcrumb_levels,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/exceptions', $data);
	}

	/**
	 * edit an exception
	 * @param  int $staffID
	 * @param int $exceptionID
	 * @return void
	 */
	public function edit_exception($staffID = NULL, $exceptionID = NULL)
	{

		$exception_info = new stdClass();

		// check if editing
		if ($exceptionID != NULL) {

			// check if numeric
			if (!ctype_digit($exceptionID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'exceptionsID' => $exceptionID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('staff_availability_exceptions')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$exception_info = $row;
				$staffID = $exception_info->staffID;
			}

		}

		// required
		if ($staffID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Exception';
		if ($exceptionID != NULL) {
			$submit_to = 'staff/availability/' . $staffID . '/exceptions/edit/' . $exceptionID;
			$title = empty($exception_info->reason)?$exception_info->note:ucfirst($exception_info->reason);
		} else {
			$submit_to = 'staff/availability/' . $staffID . '/exceptions/new/';
		}
		$return_to = 'staff/availability/' . $staffID . '/exceptions';
		$icon = 'calendar-alt';
		$tab = 'availability';
		$current_page = 'staff';
		$section = 'staff';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/availability/' . $staffID => 'Availability',
			'staff/availability/' . $staffID . '/exceptions' => 'Availability Exceptions'
 		);
		
		$message = "";
		$replace = 0;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('from', 'Date From', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('fromH', 'Time From - Hours', 'trim|xss_clean|required');
			$this->form_validation->set_rules('fromM', 'Time From - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('to', 'Date To', 'trim|xss_clean|required|callback_check_date|callback_exception_datetime');
			$this->form_validation->set_rules('toH', 'Time To - Hours', 'trim|xss_clean|required');
			$this->form_validation->set_rules('toM', 'Time To - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('reason', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('note', 'Note', 'trim|xss_clean');

			$upload_res = array();
			if (!empty($_FILES['file']['name'])) {
				$upload_res = $this->crm_library->handle_upload();

				if ($upload_res === NULL) {
					$errors[] = 'A valid file is required';
				}
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				
				// work out from and to
				$from = uk_to_mysql_date(set_value('from')) . ' ' . set_value('fromH') . ':' . set_value('fromM');
				$to = uk_to_mysql_date(set_value('to')) . ' ' . set_value('toH') . ':' . set_value('toM');

				// all ok, prepare data
				$data = array(
					'from' => $from,
					'to' => $to,
					'type' => set_value('type'),
					'note' => set_value('note'),
					'reason' => set_value('reason'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);
				
				if(count($upload_res)> 0){
					$data['attachment_name'] = $upload_res['client_name'];
					$data['path'] = $upload_res['raw_name'];
					$data['file_type'] = $upload_res['file_type'];
					$data['size'] = $upload_res['file_size']*1024;
					$data['ext'] = substr($upload_res['file_ext'], 1);

					if (!empty($exception_info->path)) {
						// delete previous file, if exists
						$path = UPLOADPATH;
						if (file_exists($path . $exception_info->path)) {
							unlink($path . $exception_info->path);
						}
					}
				}

				if ($exceptionID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['staffID'] = $staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}
				
				// final check for errors
				if (count($errors) == 0) {
					if ($exceptionID == NULL) {
						// insert
						$query = $this->db->insert('staff_availability_exceptions', $data);
						$exceptionID = $this->db->insert_id();
					} else {
						$where = array(
							'exceptionsID' => $exceptionID
						);
						// update
						$query = $this->db->update('staff_availability_exceptions', $data, $where);
					}
					
					// if inserted/updated
					if ($this->db->affected_rows() == 1) {
					
						// check if session is available for staff for that time period
						$where = array("bookings_lessons_staff.staffID" => $staffID,
						"bookings_lessons_staff.accountID" => $this->auth->user->accountID);
						
						$to_datetime = uk_to_mysql_date(set_value('to'))." ".set_value('toH') . ':' . set_value('toM').":00";
						$from_datetime = uk_to_mysql_date(set_value('from'))." ".set_value('fromH') . ':' . set_value('fromM').":00";
						
						$where_to = "concat_ws(' ',".$this->db->dbprefix('bookings_lessons_staff').".startDate".",".$this->db->dbprefix('bookings_lessons_staff').".startTime) <= '".$to_datetime."' AND concat_ws(' ',".$this->db->dbprefix('bookings_lessons_staff').".endDate".",".$this->db->dbprefix('bookings_lessons_staff').".endTime) >= '".$from_datetime."'";
					
						$query = $this->db->select("bookings_lessons_staff.*, bookings_lessons.day, bookings.name as project_name, bookings_blocks.name as block_name, bookings_lessons.startTime as lesson_start_time, bookings_lessons.endTime as lesson_end_time, bookings_blocks.blockID")->from("bookings_lessons_staff")
						->join("bookings_lessons"," bookings_lessons.lessonID = bookings_lessons_staff.lessonID ", "left")
						->join("bookings_blocks"," bookings_blocks.blockID = bookings_lessons.blockID ", "left")
						->join("bookings"," bookings.bookingID = bookings_blocks.bookingID ", "left")
						->where($where)->where($where_to)->get();
						
						if($query->num_rows() > 0){
							foreach($query->result() as $result){
								$endDate = strtotime(uk_to_mysql_date(set_value('to')));
								for($i = strtotime(ucfirst($result->day), strtotime(uk_to_mysql_date(set_value('from')))); $i <= $endDate; $i = strtotime('+1 week', $i)){
									$replace = 1;
								}
							}
						}
						
						if($replace == 1){
							$return_to = 'staff/staff_replacement/'.$staffID.'/'.$exceptionID;
							redirect($return_to);
						}
						
						$success_statement = " has been updated successfully.";
						if ($staffID == NULL) {
							$success_statement = ' has been created successfully.';
						}

						$this->session->set_flashdata('success', ucfirst(set_value('reason')) . $success_statement);

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'exception_info' => $exception_info,
			'staffID' => $staffID,
			'exceptionID' => $exceptionID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/exception', $data);
	}

	/**
	 * delete an exception
	 * @param  int $staffID
	 * @param  int $exceptionID
	 * @return mixed
	 */
	public function remove_exception($staffID = NULL, $exceptionID = NULL) {

		// check params
		if (empty($staffID) || empty($exceptionID)) {
			show_404();
		}

		$where = array(
			'staffID' => $staffID,
			'exceptionsID' => $exceptionID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff_availability_exceptions')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$exception_info = $row;

			// all ok, delete
			$query = $this->db->delete('staff_availability_exceptions', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $exception_info->reason . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $exception_info->reason . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'staff/availability/' . $exception_info->staffID . '/exceptions';

			redirect($redirect_to);
		}
	}

	/**
	 * check date is correct
	 * @param  string $date
	 * @return bool
	 */
	public function check_date($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check
		if (check_uk_date($date)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check date is after start
	 * @param  string $date
	 * @return bool
	 */
	public function exception_datetime($date = NULL) {

		// check fields - all required
		if ($this->input->post('from') == '' || $this->input->post('fromH') == '' || $this->input->post('fromM') == '' || $this->input->post('to') == '' || $this->input->post('toH') == '' || $this->input->post('toM') == '') {
			return TRUE;
		}

		// work out from and to
		$from = uk_to_mysql_date($this->input->post('from')) . ' ' . $this->input->post('fromH') . ':' . $this->input->post('fromM');
		$to = uk_to_mysql_date($this->input->post('to')) . ' ' . $this->input->post('toH') . ':' . $this->input->post('toM');

		if (strtotime($to) > strtotime($from)) {
			return TRUE;
		}

		return FALSE;

	}
	
	/*
	When Exception is added in staff profile 
	Replacement staff while staff exception apply */
	
	public function staff_replacement($staffID = NULL, $exception = NULL){
		// check params
		if (empty($staffID) || empty($exception)) {
			// dont show error
			show_404();
			return FALSE;
		}
		$type = "staffchange";
		
		// look up
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		
		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
			return false;
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}
		
		// set defaults
		$icon = 'calendar-alt';
		$tab = 'availability';
		$current_page = 'staff';
		$page_base = 'staff/staff_replacement/' . $staffID . '/'.$exception;
		$return_to = 'staff/availability/' . $staffID.'/exceptions';
		$section = 'staff';
		$title = 'Staff Replacement';
		$buttons = '<a class="btn" href="' . site_url('staff/availability/' . $staffID) . '"><i class="far fa-angle-left"></i> Return to Availability</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/availability/' . $staffID => 'Availability'
 		);
		
		// staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();
		
		//get Exception data from Exception ID
		$where = array("exceptionsID" => $exception,
		"accountID" => $this->auth->user->accountID);
		
		$query = $this->db->from("staff_availability_exceptions")->where($where)->get();
		if($query->num_rows() == 0){
			show_404();
			return false;
		}
		foreach($query->result() as $result){
			$start_date = date("Y-m-d",strtotime($result->from));
			$end_date = date("Y-m-d",strtotime($result->to));
			$start_time = date("H:i:s",strtotime($result->from));
			$end_time = date("H:i:s",strtotime($result->to));
		}
		
		$to_datetime = $end_date." ".$end_time;
		$from_datetime = $start_date." ".$start_time;
		
		$where_to = "concat_ws(' ',".$this->db->dbprefix('bookings_lessons_staff').".startDate".",".$this->db->dbprefix('bookings_lessons_staff').".startTime) <= '".$to_datetime."' AND concat_ws(' ',".$this->db->dbprefix('bookings_lessons_staff').".endDate".",".$this->db->dbprefix('bookings_lessons_staff').".endTime) >= '".$from_datetime."'";
	
		// check if session is available for staff for that time period
		$where = array("bookings_lessons_staff.staffID" => $staffID,
		"bookings_lessons_staff.accountID" => $this->auth->user->accountID);
	
		$query = $this->db->select("bookings_lessons_staff.*, bookings_lessons.day, bookings.name as project_name, bookings_blocks.name as block_name, bookings_lessons.startTime as lesson_start_time, bookings_lessons.endTime as lesson_end_time, bookings_blocks.blockID, bookings.bookingID")->from("bookings_lessons_staff")
		->join("bookings_lessons"," bookings_lessons.lessonID = bookings_lessons_staff.lessonID ", "left")
		->join("bookings_blocks"," bookings_blocks.blockID = bookings_lessons.blockID ", "left")
		->join("bookings"," bookings.bookingID = bookings_blocks.bookingID ", "left")
		->where($where)->where($where_to)->get();
		
		$lesson_list = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$endDate = strtotime($end_date);
				for($i = strtotime(ucfirst($result->day), strtotime($start_date)); $i <= $endDate; $i = strtotime('+1 week', $i)){
					$date = date('Y-m-d', $i);
					$lesson_list[$result->lessonID][$date] = $result;
				}
			}
		}else{
			$return_to = 'staff/availability/' . $staffID . '/exceptions';
			redirect($return_to);
			return TRUE;
		}
		
		// if posted
		if ($this->input->post('process') == 1) {
			// set validation rules
			$this->load->library('form_validation');
			
			$this->form_validation->set_rules('staffID', 'Replacement', 'callback_check_required_data[staffID]');
			$this->form_validation->set_rules('assign_to', 'Assign To', 'callback_check_required_data[assign_to]');
			$this->form_validation->set_rules('reason_select', 'Reason', 'callback_check_required_data[reason_select]');
			$this->form_validation->set_rules('reason', 'Reason - Other (Please specify)', 'callback_check_required_data[reason]');
			
			
			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				$exception_added = 0;
				$exception_no_replacement_count = 0;
				$exceptionIDs = array();
				
				//remove already exisiting
				$where = array("accountID" => $this->auth->user->accountID, "staff_exceptionID" => $exception, "staff_exceptionID != " => NULL);
				$query = $this->db->delete('bookings_lessons_exceptions', $where);
				
				$assign_to = $this->input->post('assign_to');
				$reason_select = $this->input->post('reason_select');
				$reason = $this->input->post('reason');
				$staffID_data = $this->input->post('staffID');
				foreach ($lesson_list as $lessonID => $dates) {
					foreach($dates as $date => $lesson_info){
						$bookingID = $lesson_info->bookingID;
						$blockID = $lesson_info->blockID;
						// all ok, prepare data
						$data = array(
							'bookingID' => $bookingID,
							'lessonID' => $lessonID,
							'date' => $date,
							'type' => $type,
							'assign_to' => $assign_to[$lessonID][$date],
							'reason_select' => $reason_select[$lessonID][$date],
							'reason' => $reason[$lessonID][$date],
							'byID' => $this->auth->user->staffID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'staff_exceptionID' => $exception,
							'accountID' => $this->auth->user->accountID
						);

						if ($type == 'staffchange') {
							$data['fromID'] = $staffID;
							if ($staffID_data[$lessonID][$date] != '' && $staffID_data[$lessonID][$date] != 0) {
								$data['staffID'] = $staffID_data[$lessonID][$date];
							} else {
								$data['staffID'] = NULL;
							}
						} 
						
						// final check for errors
						if (count($errors) == 0) {

							// insert
							$query = $this->db->insert('bookings_lessons_exceptions', $data);

							if ($type == 'staffchange' && empty($data['staffID'])) {
								$exception_no_replacement_count++;
							}

							if ($this->db->affected_rows() == 1) {
								$exception_added++;
								$exceptionIDs[] = $this->db->insert_id();
							}
							
							// calc targets
							$this->crm_library->calc_targets($blockID);
						}
					}
				}
				if ($exception_added == 0) {

					$this->session->set_flashdata('error', 'No exceptions could be added to any lessons.');

				} else {
					$staff_text = $exception_added . ' exception';
					if($exception_added == 1) {
						$staff_text .= ' has';
					} else {
						$staff_text .= 's have';
					}
					$staff_text .= ' have been added successfully';
					
					$this->session->set_flashdata('success', $staff_text);
					$redirect_to = 'staff/availability/'.$staffID.'/exceptions';
					redirect($redirect_to);
				}
			}
		}
		
		// All ready Exist
		
		$already_exceptions = array();
		$where = array("accountID" => $this->auth->user->accountID, "staff_exceptionID" => $exception);
		
		$query = $this->db->from("bookings_lessons_exceptions")->where($where)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$already_exceptions[$result->lessonID][$result->date] = $result;
			}
		}
		
		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'return_to' => $return_to,
			'lesson_list' => $lesson_list,
			'staff' => $staff_list,
			'staff_info' => $staff_info,
			'already_exceptions' => $already_exceptions,
			'staffID' => $staffID,
			'success' => $success,
			'breadcrumb_levels' => $breadcrumb_levels,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'type' => $type
		);
		
		// load view
		$this->crm_view('staff/staff_replacement', $data);
		
	}
	
	public function check_required_data($value, $field){
		$assign = $this->input->post($field);
		foreach($assign as $lessonID => $dates) {
			foreach($dates as $date => $info){
				if($field == 'reason'){
					$reason_select = $this->input->post("reason_select");
					if($reason_select[$lessonID][$date] == 'other' && empty($info) ){
						return false;
					}
				}else{
					if(empty($info)){
						return false;
					}
				}
			}
		}
		return true;
		
	}


}

/* End of file availability.php */
/* Location: ./application/controllers/staff/availability.php */
