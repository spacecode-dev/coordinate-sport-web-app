<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staff extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of staff
	 * @return void
	 */
	public function index($lessonID = NULL) {

		if ($lessonID == NULL) {
			show_404();
		}

		// look up
		$res = $this->db->select('bookings_lessons.*, lesson_types.session_evaluations')->from('bookings_lessons')->where([
			'lessonID' => $lessonID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		])->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$lesson_info = $row;
			$bookingID = $lesson_info->bookingID;
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// set defaults
		$icon = 'user';
		$tab = 'staff';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$breadcrumb_levels['bookings/blocks/' . $bookingID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $lesson_info->blockID] = 'Sessions';
		$breadcrumb_levels['bookings/sessions/edit/' . $lessonID] = ucwords($lesson_info->day) . ' ' . substr($lesson_info->startTime, 0, 5);
		$page_base = 'sessions/staff/' . $lessonID;
		$section = 'bookings';
		$title = 'Staff';
		$buttons = '<a class="btn btn-success" href="' . site_url('sessions/staff/' . $lessonID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'lessonID' => $lessonID,
			'bookings_lessons_staff.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'comment' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_comment', 'Comment', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['comment'] = set_value('search_comment');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-lessons-staff'))) {

			foreach ($this->session->userdata('search-lessons-staff') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-lessons-staff', $search_fields);

			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_staff") . "`.`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['comment'] != '') {
				$search_where[] = "`comment` LIKE '%" . $this->db->escape_like_str($search_fields['comment']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('bookings_lessons_staff.*, staff.first, staff.surname')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('startDate asc, endDate asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_lessons_staff.*, staff.first, staff.surname')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('startDate asc, endDate asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		$offers = [];
		if ($lessonID) {
			$this->load->library('offer_accept_library');
			$offers = $this->offer_accept_library->get_lesson_offers($lessonID);
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
			'lessonID' => $lessonID,
			'bookingID' => $bookingID,
			'staff' => $res,
			'staff_list'=> $staff_list,
			'lesson_info' => $lesson_info,
			'search_fields' => $search_fields,
			'booking_type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'offers' => $offers
		);

		// load view
		$this->crm_view('sessions/staff-list', $data);
	}

	/**
	 * edit staff
	 * @param  int $recordID
	 * @param int $lessonID
	 * @return void
	 */
	public function edit($recordID = NULL, $lessonID = NULL)
	{

		$staff_info = new stdClass();

		// check if editing
		if ($recordID != NULL) {

			// check if numeric
			if (!ctype_digit($recordID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'recordID' => $recordID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('bookings_lessons_staff')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$staff_info = $row;
				$lessonID = $staff_info->lessonID;
			}

		}

		// required
		if ($lessonID == NULL) {
			show_404();
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_lessons.*, lesson_types.session_evaluations')->from('bookings_lessons')->where([
			'lessonID' => $lessonID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		])->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$lesson_info = $row;
			$bookingID = $lesson_info->bookingID;
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Staff';
		if ($recordID != NULL) {
			$submit_to = 'sessions/staff/edit/' . $recordID;
			$title = 'Edit Staff';
		} else {
			$submit_to = 'sessions/staff/' . $lessonID . '/new/';
		}
		$return_to = 'sessions/staff/' . $lessonID;
		$icon = 'user';
		$tab = 'staff';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$breadcrumb_levels['bookings/blocks/' . $bookingID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $lesson_info->blockID] = 'Sessions';
		$breadcrumb_levels['bookings/sessions/edit/' . $lessonID] = ucwords($lesson_info->day) . ' ' . substr($lesson_info->startTime, 0, 5);
		$breadcrumb_levels['sessions/staff/' . $lessonID] = 'Staff';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('from', 'Date From', 'trim|xss_clean|required|callback_check_date|callback_within_block[' . $lesson_info->blockID . ']');
			$this->form_validation->set_rules('fromH', 'Time From - Hours', 'trim|xss_clean|required');
			$this->form_validation->set_rules('fromM', 'Time From - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('to', 'Date To', 'trim|xss_clean|required|callback_check_date|callback_exception_datetime|callback_within_block[' . $lesson_info->blockID . ']');
			$this->form_validation->set_rules('toH', 'Time To - Hours', 'trim|xss_clean|required');
			$this->form_validation->set_rules('toM', 'Time To - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('staffID', 'Staff', 'trim|xss_clean|required|callback_check_staff_not_already_on[' . $lessonID . ', ' . $recordID . ']');
			$this->form_validation->set_rules('comment', 'Comment', 'trim|xss_clean');
			$this->form_validation->set_rules('salaried', 'Salaried Session', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// work out from and to
				$fromTime = set_value('fromH') . ':' . set_value('fromM');
				$toTime = set_value('toH') . ':' . set_value('toM');

				// all ok, prepare data
				$data = array(
					'startDate' => uk_to_mysql_date(set_value('from')),
					'endDate' => uk_to_mysql_date(set_value('to')),
					'startTime' => $fromTime,
					'endTime' => $toTime,
					'staffID' => set_value('staffID'),
					'type' => set_value('type'),
					'comment' => set_value('comment'),
					'salaried' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($this->settings_library->get('salaried_sessions') == 1 && $this->auth->has_features('payroll') && set_value('salaried') == 1) {
					$data['salaried'] = 1;
				}

				if ($recordID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['bookingID'] = $bookingID;
					$data['lessonID'] = $lessonID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($recordID == NULL) {
						// insert
						$query = $this->db->insert('bookings_lessons_staff', $data);
						$recordID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'recordID' => $recordID
						);

						// update
						$query = $this->db->update('bookings_lessons_staff', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// run offer/accept in case still offering
						if ($this->auth->has_features(array('offer_accept'))) {
							// load library
							$this->load->library('offer_accept_library');
							$this->offer_accept_library->offer($lessonID);
						}

						// tell user
						$success = 'The staff member has been ';
						if (isset($just_added)) {
							$success .= 'created';
						} else {
							$success .= 'updated';
						}
						if ($this->settings_library->get('send_staff_new_sessions') == 1 && set_value('notify_staff') == 1 && $this->crm_library->notify_staff_new_sessions(array($recordID))) {
							$success .= ' and notified';
						}
						$success .= ' successfully';
						$this->session->set_flashdata('success', $success);

						// calc targets
						$this->crm_library->calc_targets($lesson_info->blockID);

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

		// staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// get staff on lesson
		$lesson_staff = array();
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);
//		if ($recordID != NULL) {
//			$where['recordID !='] = $recordID;
//		}
		$res_staff = $this->db->from('bookings_lessons_staff')->where($where)->get();
		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				$lesson_staff[$row->type][$row->staffID] = $row->staffID;
			}
		}

		$required_staff_for_session = $this->settings_library->get_required_staff_for_session();

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
			'staff' => $staff_list,
			'lesson_info' => $lesson_info,
			'block_info' => $block_info,
			'recordID' => $recordID,
			'lessonID' => $lessonID,
			'booking_type' => $booking_info->type,
			'bookingID' => $bookingID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'lesson_staff' => $lesson_staff,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'required_staff_for_session' => $required_staff_for_session
		);

		// load view
		$this->crm_view('sessions/staff', $data);
	}

	/**
	 * remove a record
	 * @param  int $recordID
	 * @return mixed
	 */
	public function remove($recordID = NULL) {

		// check params
		if (empty($recordID)) {
			show_404();
		}

		$where = array(
			'recordID' => $recordID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_lessons_staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$record_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_lessons_staff', $where);

			if ($this->db->affected_rows() == 1) {
				// run offer/accept in case still offering
				if ($this->auth->has_features(array('offer_accept'))) {
					// load library
//					$this->load->library('offer_accept_library');
//					$this->offer_accept_library->offer($record_info->lessonID);
				}

				$this->session->set_flashdata('success', 'The staff member has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'The staff member could not be removed.');
			}

			// delete any session evaluations for this staff/session
			$where = array(
				'lessonID' => $record_info->lessonID,
				'byID' => $record_info->staffID,
				'type' => 'evaluation'
			);
			$this->db->delete('bookings_lessons_notes', $where);

			// determine which page to send the user back to
			$redirect_to = 'sessions/staff/' . $record_info->lessonID;

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

	/**
	 * check date is within block
	 * @param  string $date
	 * @param  object $block_info
	 * @return bool
	 */
	public function within_block($date = NULL, $blockID = NULL) {

		// check params
		if (empty($date) || empty($blockID)) {
			return FALSE;
		}

		// look up block
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// convert
		$date = uk_to_mysql_date($date);

		// check within block
		if (strtotime($date) >= strtotime($block_info->startDate) && strtotime($date) <= strtotime($block_info->endDate)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check staff not already on block
	 * @param  integer $staffID
	 * @param  integer $lessonID
	 * @return bool
	 */
	public function check_staff_not_already_on($staffID = NULL, $params = NULL) {

		$date_from = uk_to_mysql_date($this->input->post('from'));
		$date_to = uk_to_mysql_date($this->input->post('to'));

		$lessonID = NULL;
		$recordID = NULL;

		$params_exploded = explode(",", $params);
		if (count($params_exploded) == 2) {
			$lessonID = trim($params_exploded[0]);
			$recordID = trim($params_exploded[1]);
		}

		// check params
		if (empty($staffID) || empty($lessonID) || empty($date_from) || empty($date_to)) {
			// dont show error
			return TRUE;
		}

		// check if already on session
		$where = array(
			'lessonID' => $lessonID,
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		if (!empty($recordID)) {
			$where['recordID !='] = $recordID;
		}

		// run query
		$query = $this->db->from('bookings_lessons_staff')->where($where)->get();

		// no match, no conflicts
		if ($query->num_rows() == 0) {
			return TRUE;
		}

		// check if conflict if already on for part of the sessions
		foreach ($query->result() as $row) {
			// if conflict, stop
			if (strtotime($date_from) <= strtotime($row->endDate) && strtotime($row->startDate) <= strtotime($date_to)) {
				return FALSE;
			}
		}

		return TRUE;
	}

}

/* End of file availability.php */
/* Location: ./application/controllers/staff/availability.php */
