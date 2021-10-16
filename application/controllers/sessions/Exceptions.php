<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exceptions extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('bookings_exceptions'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of exceptions
	 * @return void
	 */
	public function index($lessonID = NULL) {

		if ($lessonID == NULL) {
			show_404();
		}

		// look up lesson
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
		$icon = 'calendar-alt';
		$tab = 'exceptions';
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
		$page_base = 'sessions/exceptions/' . $lessonID;
		$section = 'bookings';
		$title = 'Exceptions';
		$buttons = '<a class="btn btn-success" href="' . site_url('sessions/exceptions/' . $lessonID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'lessonID' => $lessonID,
			'bookings_lessons_exceptions.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
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
			$this->form_validation->set_rules('search_reason', 'Reason', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['reason'] = set_value('search_reason');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-lessons-exceptions'))) {

			foreach ($this->session->userdata('search-lessons-exceptions') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-lessons-exceptions', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`date` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['reason'] != '') {
				$search_where[] = "`reason` LIKE '%" . $this->db->escape_like_str($search_fields['reason']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('bookings_lessons_exceptions.*, staff.first, staff.surname, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('date asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_lessons_exceptions.*, staff.first, staff.surname, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('date asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

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
			'booking_type' => $booking_info->type,
			'staff' => $res,
			'staff_list'=> $staff_list,
			'lesson_info' => $lesson_info,
			'search_fields' => $search_fields,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('sessions/exceptions', $data);
	}

	/**
	 * edit exception
	 * @param  int $exceptionID
	 * @param int $lessonID
	 * @return void
	 */
	public function edit($exceptionID = NULL, $lessonID = NULL)
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
				'exceptionID' => $exceptionID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$exception_info = $row;
				$lessonID = $exception_info->lessonID;
			}

		}

		// required
		if ($lessonID == NULL) {
			show_404();
		}

		// look up lesson
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
		$title = 'New Exception';
		if ($exceptionID != NULL) {
			$submit_to = 'sessions/exceptions/edit/' . $exceptionID;
			$title = 'Edit Exception';
		} else {
			$submit_to = 'sessions/exceptions/' . $lessonID . '/new/';
		}
		$return_to = 'sessions/exceptions/' . $lessonID;
		$icon = 'calendar-alt';
		$tab = 'exceptions';
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
		$breadcrumb_levels['sessions/exceptions/' . $lessonID] = 'Exceptions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date|callback_within_block[' . $lesson_info->blockID . ']|callback_correct_day[' . $lesson_info->day . ']');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			if ($this->input->post('type') == 'staffchange') {
				$this->form_validation->set_rules('fromID', 'Staff', 'trim|xss_clean|required');
				$this->form_validation->set_rules('staffID', 'Replacement Staff', 'trim|xss_clean');
			}
			$this->form_validation->set_rules('assign_to', 'Assign To', 'trim|xss_clean|required');
			$this->form_validation->set_rules('reason_select', 'Reason', 'trim|xss_clean|required');
			$this->form_validation->set_rules('reason', 'Reason - Other', 'trim|xss_clean|callback_required_if_other[' . $this->input->post('reason_select') . ']');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'date' => uk_to_mysql_date(set_value('date')),
					'type' => set_value('type'),
					'assign_to' => set_value('assign_to'),
					'reason_select' => set_value('reason_select'),
					'reason' => set_value('reason'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($this->input->post('type') == 'staffchange') {
					$data['fromID'] = set_value('fromID');
					if (set_value('staffID') != '' && set_value('staffID') != 0) {
						$data['staffID'] = set_value('staffID');
					} else {
						$data['staffID'] = NULL;
					}
				} else {
					$data['fromID'] = NULL;
					$data['staffID'] = NULL;
				}

				if ($exceptionID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['bookingID'] = $bookingID;
					$data['lessonID'] = $lessonID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($exceptionID == NULL) {
						// insert
						$query = $this->db->insert('bookings_lessons_exceptions', $data);

						$exceptionID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'exceptionID' => $exceptionID
						);

						// update
						$query = $this->db->update('bookings_lessons_exceptions', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (isset($just_added)) {
							$success = 'The exception has now been created';
						} else {
							$success = 'The exception has now been updated';
						}

						if ($this->settings_library->get('send_staff_cancelled_sessions') == 1 && set_value('notify_staff') == 1 && $this->crm_library->notify_staff_new_exceptions(array($exceptionID))) {
							$success .= ' and staff notified';
						}

						$redirect_to = 'sessions/exceptions/' . $lesson_info->lessonID;
						if ($this->settings_library->get('send_exceptions') == 1) {
							$success .= '. Review and send the customer notification below.';
							$redirect_to = 'sessions/exceptions/notify/' . $exceptionID;
						}

						$this->session->set_flashdata('success', $success);
						$this->session->set_userdata('notify_exceptions_lessons', array($lesson_info->lessonID));
						$this->session->set_userdata('notify_exceptions', array($exceptionID));

						if ($this->input->post('type') == 'staffchange' && !$data['staffID']) {
							$redirect_to = 'sessions/exceptions/' . $lesson_info->lessonID;
						}

						redirect($redirect_to);

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
			'lessonID' => $lessonID,
			'staff.accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->select('staff.*, bookings_lessons_staff.type as staffType')
			->from('staff')
			->join('bookings_lessons_staff', 'staff.staffID = bookings_lessons_staff.staffID', 'inner')
			->where($where)
			->order_by('first asc, surname asc')
			->get();
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$replacement_staff = $this->db->from('staff')
			->where($where)
			->order_by('first asc, surname asc')
			->get();

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
			'staff' => $staff,
			'replacement_staff' => $replacement_staff,
			'lesson_info' => $lesson_info,
			'block_info' => $block_info,
			'exceptionID' => $exceptionID,
			'booking_type' => $booking_info->type,
			'lessonID' => $lessonID,
			'bookingID' => $bookingID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('sessions/exception', $data);
	}

	/**
	 * remove a record
	 * @param  int $exceptionID
	 * @return mixed
	 */
	public function remove($exceptionID = NULL) {

		// check params
		if (empty($exceptionID)) {
			show_404();
		}

		$where = array(
			'exceptionID' => $exceptionID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$families_to_recalculate = array();
			$exception_info = $row;

			$refunds_reverted = true;

			// get any refunds which need to be reverted
			$refunds = $this->db->from('bookings_lessons_exceptions_refund')->where($where)->get();
			if ($refunds->num_rows()>0) {
				foreach ($refunds->result() as $refund) {
					//Find payment associated with refund for exception
					$payment = array(
						'paymentID' => $refund->paymentID,
						'accountID' => $this->auth->user->accountID,
						'method' => "credit note"
					);

					//Get balance and remove the partial exception refund (or remove if the exception refund was the complete payment)
					$balance = $this->db->select("amount")->from('family_payments')->where($payment)->limit(1)->get();
					if ($balance->num_rows()>0) {
						if (!in_array($refund->familyID,$families_to_recalculate)) {
							$families_to_recalculate[] = $refund->familyID;
						}
						if (($balance->result()[0]->amount - $refund->amount)<=0) {
							$this->db->delete('family_payments', $payment, 1);
						}
						else {
							$this->db->update('family_payments', array("amount" => $balance->result()[0]->amount - $refund->amount), $payment);
						}
					}

					if ($this->db->affected_rows() == 0 && $balance->num_rows()>0) {
						//Refund was not reverted (removed from payment) but the payment was found (e.g. it hasnt already been removed).
						//Flag the failure, and keep the record.
						$refunds_reverted = false;
					}
					else {
						//Refund was reverted. Delete it.
						$this->db->delete('bookings_lessons_exceptions_refund', $where, 1);
					}
				}
			}

			if ($refunds_reverted) {
				// all ok, delete
				$query = $this->db->delete('bookings_lessons_exceptions', $where);

				if ($this->db->affected_rows() == 1) {
					$this->session->set_flashdata('success', 'The exception has been removed successfully.');
				} else {
					$this->session->set_flashdata('error', 'The exception could not be removed.');
				}
			}
			else {
				$this->session->set_flashdata('error', 'The exception could not be removed as one or more of the refunds originally applied could not be reverted.');
			}

			//Recalculate all family balances which have been effected by the removal of the exception
			foreach ($families_to_recalculate as $familyID) {
				$this->crm_library->recalc_family_balance($familyID);
			}

			// determine which page to send the user back to
			$redirect_to = 'sessions/exceptions/' . $exception_info->lessonID;

			redirect($redirect_to);
		}
	}

	/**
	 * refund all participants
	 * @return void
	 */
	public function refund($exceptionID = NULL) {
		// check if allowed
		if ($this->settings_library->get('send_exceptions') != 1) {
			show_403();
		}

		//Get exception IDs, lessonIDs and the contacts to refund
		$exception_IDs = $this->session->userdata('notify_exceptions');
		$lessonIDs = $this->session->userdata('notify_exceptions_lessons');
		$data_contact = unserialize(base64_decode($this->input->post('data')));

		//Validate all contact IDs exist
		if (!$this->valid_participant_contact(array_keys($data_contact))) {
			echo "NOTOK";
			return;
		}

		$where_in = array();

		// use exception ID from URL if set, else look up in session
		if (!empty($exceptionID)) {
			$where_in[] = $exceptionID;
		} else if (is_array($this->session->userdata('notify_exceptions'))) {
			$where_in = $this->session->userdata('notify_exceptions');
		}

		// at least one required
		if (count($where_in) == 0) {
			show_404();
		}

		// run query
		$where = array(
			'bookings_lessons_exceptions.accountID' => $this->auth->user->accountID
		);

		$exceptions = $this->db->select('bookings_lessons_exceptions.*, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname, replacement_staff.qual_fsscrb, replacement_staff.qual_fsscrb_issue_date, replacement_staff.qual_fsscrb_expiry_date, replacement_staff.qual_fsscrb_ref, replacement_staff.qual_othercrb, replacement_staff.qual_othercrb_issue_date, replacement_staff.qual_othercrb_expiry_date, replacement_staff.qual_othercrb_ref, bookings_lessons.day, bookings_lessons.group, bookings_lessons.group_other, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.blockID')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where_in('exceptionID', $where_in)->order_by('date asc')->get();
		$exceptionDates = array();
		foreach ($exceptions->result() as $ex) {
			$exceptionDates[$ex->exceptionID] = $ex->date;
		}

		$payments = array();
		foreach($data_contact as $contactID => $data) {
			$where = array(
				'contactID' => $contactID,
				'accountID' => $this->auth->user->accountID
			);

			$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				echo "NOTOK";
				return;
			}

			foreach ($query->result() as $contact_info) {
			}

			// all ok, prepare data
			$payment_data = array(
				'accountID' => $this->auth->user->accountID,
				'familyID' => $contact_info->familyID,
				'contactID' => $contactID,
				'byID' => $this->auth->user->staffID,
				'internal' => '1',
				'amount' => ($data_contact[$contactID]['is_sub'] == '0') ? $data_contact[$contactID]['session_price'] : "0",
				'method' => 'credit note',
				'transaction_ref' => 'Exception Cancellation',
				'note' => 'Exception Cancellation',
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->db->insert('family_payments', $payment_data);
			$paymentID = $this->db->insert_id();
			$this->crm_library->recalc_family_balance($contact_info->familyID);

			//Store exception refunded data in case user wants to revert
			foreach ($lessonIDs as $key => $lessonID) {
				if (isset($data_contact[$contactID]['lesson_id'][$lessonID]) || (!isset($data_contact[$contactID]['lesson_id'][$lessonID]) && $data_contact[$contactID]['is_sub'] !== '0')) {
					if (!isset($data_contact[$contactID]['lesson_id'][$lessonID][$exceptionDates[$exception_IDs[$key]]]) && $data_contact[$contactID]['is_sub'] == '0') {
						continue;
					}
					if (isset($refund_data[$exception_IDs[$key]][$contact_info->familyID]['amount'])) {
						$payments[$exception_IDs[$key]][$contact_info->familyID]['amount'] += ($data_contact[$contactID]['is_sub'] == '0') ? $data_contact[$contactID]['lesson_id'][$lessonID][$exceptionDates[$exception_IDs[$key]]] : 0;
					} else {
						$payments[$exception_IDs[$key]][$contact_info->familyID]['amount'] = ($data_contact[$contactID]['is_sub'] == '0') ? $data_contact[$contactID]['lesson_id'][$lessonID][$exceptionDates[$exception_IDs[$key]]] : 0;
						$payments[$exception_IDs[$key]][$contact_info->familyID]['paymentID'] = $paymentID;
					}
				}
			}
		}

		//Once total refunds have been calculated, store refund data in exception refund table
		if(count($payments) > 0){
			foreach($payments as $exceptionID => $families){
				foreach ($families as $familyID => $data) {
					// save family refund amount into the exceptions table
					$familyRefund = array(
						'accountID' => $this->auth->user->accountID,
						'exceptionID' => $exceptionID,
						'paymentID' => $data["paymentID"],
						'familyID' => $familyID,
						'amount' => $data["amount"],
					);

					$this->db->insert('bookings_lessons_exceptions_refund', $familyRefund);
				}
			}
		}

		// update all exception IDs (whether or not there are any actual refunds) to mark that refunds have been assessed and processed
		$this->db->where_in("exceptionID",$where_in)->limit(count($where_in))->update('bookings_lessons_exceptions', array("refunded_participants" => 1));

		echo "OK";
	}

	/**
	 * notify customer of change
	 * @return void
	 */
	public function notify($exceptionID = NULL)
	{
		// check if allowed
		if ($this->settings_library->get('send_exceptions') != 1) {
			show_403();
		}

		$where_in = array();

		// use exception ID from URL if set, else look up in session
		if (!empty($exceptionID)) {
			$where_in[] = $exceptionID;
		} else if (is_array($this->session->userdata('notify_exceptions'))) {
			$where_in = $this->session->userdata('notify_exceptions');
		}

		// at least one required
		if (count($where_in) == 0) {
			show_404();
		}

		// look up exception

		// run query
		$where = array(
			'bookings_lessons_exceptions.accountID' => $this->auth->user->accountID
		);
		$exceptions = $this->db->select('bookings_lessons_exceptions.*, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname, replacement_staff.qual_fsscrb, replacement_staff.qual_fsscrb_issue_date, replacement_staff.qual_fsscrb_expiry_date, replacement_staff.qual_fsscrb_ref, replacement_staff.qual_othercrb, replacement_staff.qual_othercrb_issue_date, replacement_staff.qual_othercrb_expiry_date, replacement_staff.qual_othercrb_ref, bookings_lessons.day, bookings_lessons.group, bookings_lessons.group_other, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.blockID')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where_in('exceptionID', $where_in)->order_by('date asc')->get();
		$exceptionDates = array();
		$all_refunds_processed = true;
		foreach ($exceptions->result() as $ex) {
			$exceptionDates[$ex->exceptionID] = $ex->date;
			if ($ex->refunded_participants==0) {
				$all_refunds_processed = false;
			}
		}
		$bulk_data = $this->session->flashdata('bulk_data');
		if(isset($bulk_data)){
			$this->session->unset_userdata('bulk_data_temp');
			$this->session->set_userdata('bulk_data_temp', $bulk_data);
		}else{
			$bulk_data = $this->session->userdata('bulk_data_temp');
		}

		// no match
		if ($exceptions->num_rows() == 0) {
			show_404();
		}

		foreach ($exceptions->result() as $exception_info) {
			// only want first to get booking
			break;
		}

		// look up booking
		$where = array(
			'bookingID' => $exception_info->bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $booking_info) {}

		// look up block
		$where = array(
			'bookings_lessons.lessonID' => $exception_info->lessonID,
			'bookings_lessons.bookingID' => $exception_info->bookingID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_blocks.*')->from('bookings_lessons')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $block_info) {}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Send Exception Notification';
		$submit_to = 'sessions/exceptions/notify/' . $exceptionID;
		$refund_callback = site_url('sessions/exceptions/refund/'.$exceptionID);
		$return_to = 'sessions/exceptions/' . $exception_info->lessonID;
		if ($exceptions->num_rows() > 0) {
			$return_to = 'bookings/sessions/' . $exception_info->bookingID . '/' . $exception_info->blockID;
		}
		$icon = 'envelope';
		$current_page = $booking_info->type . 's';
		if ($booking_info->project == 1) {
			$current_page = 'projects';
		}
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$error = NULL;
		$success = NULL;
		$info = NULL;
		$participant_subject = NULL;
		$participant_content = NULL;
		$form_status = TRUE;

		// if posted
		if ($this->input->post()) {
			if($this->input->post('section') !== "participants") {

				// set validation rules
				$this->form_validation->set_rules('contactID', 'Contact', 'trim|xss_clean|required|callback_valid_contact');
				$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
				$this->form_validation->set_rules('content', 'Content', 'trim|required');

				if ($this->form_validation->run() == FALSE) {
					$errors = $this->form_validation->error_array();
				} else {

					// look up contact
					$where = array(
						'contactID' => set_value('contactID'),
						'orgID' => $booking_info->orgID,
						'accountID' => $this->auth->user->accountID
					);
					if (!empty($block_info->orgID) && $block_info->orgID != $booking_info->orgID) {
						$where['orgID'] = $block_info->orgID;
					}

					// run query
					$query = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

					// no match
					if ($query->num_rows() == 0) {
						return false;
					}

					foreach ($query->result() as $contact_info) {
					}

					// get vars
					$subject = set_value('subject', NULL, FALSE);
					$email_html = $this->input->post('content', FALSE);

					// replace contact name
					$smart_tags = array(
						'main_contact' => $contact_info->name,
						'contact_name' => $contact_info->name
					);

					foreach ($smart_tags as $key => $value) {
						$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
						$email_html = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $email_html);
					}

					// convert to plain text
					$this->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), FALSE, $booking_info->accountID, $booking_info->brandID)) {

						// save
						$data = array(
							'orgID' => $booking_info->orgID,
							'contactID' => $contact_info->contactID,
							'byID' => $this->auth->user->staffID,
							'type' => 'email',
							'destination' => $contact_info->email,
							'subject' => $subject,
							'contentHTML' => $email_html,
							'contentText' => $email_plain,
							'status' => 'sent',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						$this->db->insert('orgs_notifications', $data);

						$notificationID = $this->db->insert_id();
						$form_status = FALSE;

						$this->session->set_flashdata('success', 'The Customer notification has been sent successfully.');
					} else {
						$error = 'Notification could not be sent';
					}

				}
			}else{
				$error_flag = FALSE;
				// set validation rules
				$data_contact = unserialize(base64_decode($this->input->post('data')));
				$contacts = array();
				if(is_array($this->input->post('participant_contactID')) && in_array("all", $this->input->post('participant_contactID'))){
					if(count($data_contact) > 0){
						foreach($data_contact as $key => $item){
							$contacts[] = $key;
						}
					}else{
						$error_flag = TRUE;
						$errors[] = 'There is an error in processing data. Please contact support team.';
					}
					$response = $this->valid_participant_contact($contacts);
					if($response == FALSE){
						$error_flag = TRUE;
						$errors[] = 'Invalid contact detail passed. Please contact support team.';
					}
					$this->form_validation->set_rules('participant_contactID', 'Contact', 'trim|xss_clean|required');
				}else {
					$this->form_validation->set_rules('participant_contactID', 'Contact', 'trim|xss_clean|required|callback_valid_participant_contact');
				}
				$this->form_validation->set_rules('participant_subject', 'Subject', 'trim|xss_clean|required');
				$this->form_validation->set_rules('participant_content', 'Content', 'trim|required');

				if ($this->form_validation->run() == FALSE && $error_flag == FALSE) {
					$errors = $this->form_validation->error_array();
				} else {
					if($this->input->post('participant_contactID') && count($this->input->post('participant_contactID')) > 0) {
						$participant_contacts = in_array("all", $this->input->post('participant_contactID'))?$data_contact:$this->input->post('participant_contactID');
						foreach($participant_contacts as $key => $value) {
							$smart_tags = array(
								'org_name' => ''
							);

							$participant_contactID = in_array("all", $this->input->post('participant_contactID'))?$key:$value;
							// look up contact
							$where = array(
								'contactID' => $participant_contactID,
								'accountID' => $this->auth->user->accountID
							);

							if (!empty($block_info->orgID) && $block_info->orgID != $booking_info->orgID) {
								$where_add = array('orgID' => $block_info->orgID);
								// run query
								$query = $this->db->from('orgs_contacts')->where($where)->where($where_add)->limit(1)->get();

								// no match
								if ($query->num_rows() > 0) {
									foreach ($query->result() as $contact_info) {}
									$smart_tags['org_name'] = $contact_info->name;
								}
							}

							$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();

							// no match
							if ($query->num_rows() == 0) {
								return false;
							}

							foreach ($query->result() as $contact_info) {
							}

							// get vars
							$subject = set_value('participant_subject', NULL, FALSE);
							$email_html = $this->input->post('participant_content', FALSE);

							// replace contact name
							$smart_tags['participant_first'] = strtok($data_contact[$participant_contactID]['name'],  ' ');

							foreach ($smart_tags as $key => $value) {
								$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
								$email_html = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $email_html);
							}

							// convert to plain text
							$this->load->helper('html2text');
							$html2text = new \Html2Text\Html2Text($email_html);
							$email_plain = $html2text->get_text();

							if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), FALSE, $booking_info->accountID, $booking_info->brandID)) {

								// save
								$data = array(
									'familyID' => $contact_info->familyID,
									'byID' => $this->auth->user->staffID,
									'type' => 'email',
									'destination' => $contact_info->email,
									'subject' => $subject,
									'contentHTML' => $email_html,
									'contentText' => $email_plain,
									'status' => 'sent',
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);

								$this->db->insert('family_notifications', $data);

								$notificationID = $this->db->insert_id();
								$form_status = FALSE;

								$this->session->set_flashdata('success', 'The Participant Customer notification has been sent successfully.');
							} else {
								$error = 'Notification could not be sent';
							}
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
		}

		// set defaults
		switch ($exception_info->type) {
			case 'staffchange':
				if ($exceptions->num_rows() > 1) {
					$subject = $this->settings_library->get('email_exception_bulk_staffchange_subject');
					$content = $this->settings_library->get('email_exception_bulk_staffchange');
				} else {
					switch ($exception_info->assign_to) {
						case 'staff':
						case 'company':
							$subject = $this->settings_library->get('email_exception_company_staffchange_subject');
							$content = $this->settings_library->get('email_exception_company_staffchange');
							break;
						case 'customer':
							$subject = $this->settings_library->get('email_exception_customer_staffchange_subject');
							$content = $this->settings_library->get('email_exception_customer_staffchange');
							break;
					}
				}
				break;
			case 'cancellation':
				$participant_subject = $this->settings_library->get('email_exception_participant_cancellation_subject');
				$participant_content = $this->settings_library->get('email_exception_participant_cancellation');
				if ($exceptions->num_rows() > 1) {
					$subject = $this->settings_library->get('email_exception_bulk_cancellation_subject');
					$content = $this->settings_library->get('email_exception_bulk_cancellation');
				} else {
					switch ($exception_info->assign_to) {
						case 'staff':
						case 'company':
							$subject = $this->settings_library->get('email_exception_company_cancellation_subject');
							$content = $this->settings_library->get('email_exception_company_cancellation');
							break;
						case 'customer':
							$subject = $this->settings_library->get('email_exception_customer_cancellation_subject');
							$content = $this->settings_library->get('email_exception_customer_cancellation');
							break;
					}
				}
				break;
		}

		// if subject or content not set, redirect
		if (!isset($subject) || !isset($content) || empty($subject) || empty($content)) {
			redirect($return_to);
		}

		// set smart tags
		$smart_tags = array(
			'details' => NULL,
			'dbs_details' => NULL
		);

		// get details
		if ($exception_info->type == 'staffchange') {
			$smart_tags['details'] .= '<table width="100%" border="1">
			<tr>
				<th scope="col">Date</th>
				<th scope="col">Start</th>
				<th scope="col">End</th>
				<th scope="col">Group</th>
				<th scope="col">Replacement</th>
			</tr>';
		} else {
			$smart_tags['details'] .= '<table width="100%" border="1">
			<tr>
				<th scope="col">Date</th>
				<th scope="col">Start</th>
				<th scope="col">End</th>
				<th scope="col">Group</th>
			</tr>';
		}

		$replacement_staff = array();

		$all_valid = TRUE; $lessonIDs = array();
		foreach ($exceptions->result() as $exception) {
			$lessonIDs[] = $exception->lessonID;
			$group = 'Unknown';
			$activity = 'Unknown';

			if ($exception->group == 'other') {
				$group = $exception->group_other;
			} else if (!empty($exception->group)) {
				$group = $this->crm_library->format_lesson_group($exception->group);
			}

			if ($exception_info->type == 'staffchange') {
				$replacementLabel = 'N/A';
				if (!empty($exception->replacement_first)) {
					$replacementLabel = $exception->replacement_first . ' ' . $exception->replacement_surname;
				}
				$smart_tags['details'] .= '<tr>
					<td>' . mysql_to_uk_date($exception->date) . '</td>
					<td>' . substr($exception->startTime, 0, 5) . '</td>
					<td>' . substr($exception->endTime, 0, 5) . '</td>
					<td>' . $group . '</td>
					<td>' . $replacementLabel . '</td>
				</tr>';
			} else {
				$smart_tags['details'] .= '<tr>
					<td>' . mysql_to_uk_date($exception->date) . '</td>
					<td>' . substr($exception->startTime, 0, 5) . '</td>
					<td>' . substr($exception->endTime, 0, 5) . '</td>
					<td>' . $group . '</td>
				</tr>';
			}

			$replacement_staff[$exception->staffID] = $exception;
		}

		$smart_tags['details'] .= '</table>';

		if (count($replacement_staff) > 0) {
			$smart_tags['dbs_details'] .= '<table width="100%" border="1">
			<tr>
				<th scope="col">Staff Name</th>
				<th scope="col">DBS No.</th>
				<th scope="col">Issue Date</th>
				<th scope="col">Expiry Date</th>
			</tr>';

			foreach ($replacement_staff as $replacement) {
				$dbs_name = $replacement->replacement_first . ' ' . $replacement->replacement_surname;
				$dbs_no = 'Unknown';
				if (empty($replacement->replacement_first)) {
					$dbs_no = 'N/A';
					$dbs_name = 'N/A';
				}
				$dbs_expiry_date = 'Unknown';
				$dbs_issue_date = 'Unknown';

				if ($replacement->qual_fsscrb == 1) {
					if (!empty($replacement->qual_fsscrb_ref)) {
						$dbs_no = $replacement->qual_fsscrb_ref;
					}
					if (!empty($replacement->qual_fsscrb_issue_date)) {
						$dbs_issue_date = mysql_to_uk_date($replacement->qual_fsscrb_issue_date);
					}
					if (!empty($replacement->qual_fsscrb_expiry_date)) {
						$dbs_expiry_date = mysql_to_uk_date($replacement->qual_fsscrb_expiry_date);
						// check expired
						if (strtotime($replacement->qual_fsscrb_expiry_date) < time()) {
							$all_valid = FALSE;
						}
					}
				} else if ($replacement->qual_othercrb == 1) {
					if (!empty($replacement->qual_othercrb_ref)) {
						$dbs_no = $replacement->qual_othercrb_ref;
					}
					if (!empty($replacement->qual_othercrb_issue_date)) {
						$dbs_issue_date = mysql_to_uk_date($replacement->qual_othercrb_issue_date);
					}
					if (!empty($replacement->qual_othercrb_expiry_date)) {
						$dbs_expiry_date = mysql_to_uk_date($replacement->qual_othercrb_expiry_date);
						// check expired
						if (strtotime($replacement->qual_othercrb_expiry_date) < time()) {
							$all_valid = FALSE;
						}
					}
				}

				$smart_tags['dbs_details'] .= '<tr>
					<td>' . $dbs_name . '</td>
					<td>' . $dbs_no . '</td>
					<td>' . $dbs_issue_date . '</td>
					<td>' . $dbs_expiry_date . '</td>
				</tr>';
			}

			$smart_tags['dbs_details'] .= '</table>';

		}

		// don't need dbs details for cancellations
		if ($exception_info->type == 'cancellation') {
			unset($smart_tags['dbs_details']);
		}

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$content = str_replace('<p>{' . $key . '}</p>', $value, $content);
			$content = str_replace('{' . $key . '}', $value, $content);
			if(!empty($participant_content)){
				$participant_content = str_replace('<p>{' . $key . '}</p>', $value, $participant_content);
			}
		}

		// replace smart tags in subject
		unset($smart_tags['details']);
		unset($smart_tags['dbs_details']);
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
		}

		if (!$this->input->post() && $all_valid !== TRUE) {
			$info = 'Some DBSs are missing or expired. Please check before sending.';
		}

		// contacts
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		if (!empty($block_info->orgID) && $block_info->orgID != $booking_info->orgID) {
			$where['orgID'] = $block_info->orgID;
		}
		$contacts = $this->db->from('orgs_contacts')->where($where)->order_by('isMain desc, name asc')->get();

		//get All booked session's contacts
		$participant_contacts = array();
		if($exception_info->type === "cancellation"){
			if($this->auth->has_features('online_booking_subscription_module') == 1) {
				$where = array(
					'bookings_cart.accountID' => $this->auth->user->accountID,
					'bookings_cart_subscriptions.bookingID' => $exception_info->bookingID,
					'participant_subscriptions.status' => 'active',
					'bookings_cart.type' => 'booking'
				);

				$where_date = NULL;
				if(!empty($bulk_data['from']) && !empty($bulk_data['to'])){
					$date_from = uk_to_mysql_date($bulk_data['from']);
					$date_to = uk_to_mysql_date($bulk_data['to']);
					$where_date = ' ('.$this->db->dbprefix("bookings_cart_sessions").'.date >= "'.$date_from.'" AND '.$this->db->dbprefix("bookings_cart_sessions").'.date <= "'.$date_to.'")';
				}

				//Fetch only subscription bookings
				$participant_subscription_contacts = $this->db
					->select('bookings_cart.contactID, family_contacts.first_name, family_contacts.last_name, family_contacts.email')
					->from('bookings_cart')
					->join('bookings_cart_subscriptions', 'bookings_cart.cartID = bookings_cart_subscriptions.cartID', 'inner')
					->join('bookings_cart_sessions', 'bookings_cart_subscriptions.cartID = bookings_cart_sessions.cartID', 'inner')
					->join('participant_subscriptions','bookings_cart_subscriptions.childID = participant_subscriptions.childID OR bookings_cart_subscriptions.contactID = `'.$this->db->dbprefix("participant_subscriptions").'`.`contactID`', "LEFT")
					->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'left')
					->where($where)
					->where_in('bookings_cart_sessions.lessonID',$lessonIDs)
					->where_in('bookings_cart_sessions.date',$exceptionDates)
					->group_by('bookings_cart.contactID');

				if (!is_null($where_date)) {
					$participant_subscription_contacts = $participant_subscription_contacts->where($where_date, NULL, FALSE);
				}

				$participant_subscription_contacts = $participant_subscription_contacts->get();

				if($participant_subscription_contacts->num_rows() > 0) {
					foreach ($participant_subscription_contacts->result() as $row) {
						$participant_contacts[$row->contactID]['name'] = $row->first_name . ' ' . $row->last_name;
						$participant_contacts[$row->contactID]['email'] = $row->email;
						$participant_contacts[$row->contactID]['is_sub'] = 1;
						$participant_contacts[$row->contactID]['session_price'] = 0;
					}
				}
			}
			
			$where = array(
				'bookings_cart.accountID' => $this->auth->user->accountID,
				'bookings_cart_sessions.bookingID' => $exception_info->bookingID,
				'bookings_cart.type' => 'booking'
			);
			
			if($booking_info->subscriptions_only == 0 && $this->auth->has_features('online_booking_subscription_module') == 1) {
				$where["bookings_cart_sessions.price >"] = 0;
			}
			
			//Fetch non subscription data
			$where_discount = '(('.$this->db->dbprefix("bookings_cart_sessions").'.price = 0 AND '.$this->db->dbprefix("bookings_cart_sessions").'.discount > 0) OR ('.$this->db->dbprefix("bookings_cart_sessions").'.price > 0))';

			$where_date = null;
			if(!empty($bulk_data['from']) && !empty($bulk_data['to'])){
				$date_from = uk_to_mysql_date($bulk_data['from']);
				$date_to = uk_to_mysql_date($bulk_data['to']);
				$where_date = ' ('.$this->db->dbprefix("bookings_cart_sessions").'.date >= "'.$date_from.'" AND '.$this->db->dbprefix("bookings_cart_sessions").'.date <= "'.$date_to.'")';
			}

			$participant_session_contacts = $this->db
				->select('bookings_cart.contactID, bookings_cart_sessions.sessionID, family_contacts.first_name, family_contacts.last_name, family_contacts.email, bookings_cart_sessions.childID, bookings_cart_sessions.total, bookings_cart_sessions.balance, bookings_cart_sessions.lessonID, bookings_cart_sessions.date')
				->from('bookings_cart')
				->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
				->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'left')
				->where($where)
				->where($where_discount)
				->where_in('bookings_cart_sessions.lessonID',$lessonIDs)
				->where_in('bookings_cart_sessions.date',$exceptionDates);

			if (!is_null($where_date)) {
				$participant_session_contacts = $participant_session_contacts->where($where_date, NULL, FALSE);
			}

			$participant_session_contacts = $participant_session_contacts->get();

			if($participant_session_contacts->num_rows() > 0) {
				foreach ($participant_session_contacts->result() as $row) {
					if (isset($participant_contacts[$row->contactID])) {
						$participant_contacts[$row->contactID]['session_price'] += ($row->total - $row->balance);
						$participant_contacts[$row->contactID]['sessionID'] = $row->sessionID;
					}else {
						$participant_contacts[$row->contactID]['name'] = $row->first_name . ' ' . $row->last_name;
						$participant_contacts[$row->contactID]['email'] = $row->email;
						$participant_contacts[$row->contactID]['is_sub'] = 0;
						$participant_contacts[$row->contactID]['session_price'] = ($row->total - $row->balance);
						$participant_contacts[$row->contactID]['sessionID'] = $row->sessionID;
					}
					if (isset($participant_contacts[$row->contactID]['lesson_id'][$row->lessonID][$row->date])) {
						$participant_contacts[$row->contactID]['lesson_id'][$row->lessonID][$row->date] += ($row->total - $row->balance);
					}
					else {
						$participant_contacts[$row->contactID]['lesson_id'][$row->lessonID][$row->date] = ($row->total - $row->balance);
					}
				}
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'form_status' => $form_status,
			'refund_callback' => $refund_callback,
			'return_to' => $return_to,
			'booking_info' => $booking_info,
			'exception_info' => $exception_info,
			'all_refunds_processed' => $all_refunds_processed,
			'contacts' => $contacts,
			'subject' => $subject,
			'content' => $content,
			'participant_subject' => $participant_subject,
			'participant_content' => $participant_content,
			'participant_contacts' => $participant_contacts,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('email', $data);
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
	 * check if either this field filled in if value of another field is other
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_other($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if ($value2 == 'other' && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if date is on correct day
	 * @param  string $date
	 * @param  string $day
	 * @return boolean
	 */
	public function correct_day($date, $day) {

		// check
		if (strtolower(date('l', strtotime(uk_to_mysql_date($date)))) != $day) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if a contact is valid
	 * @param  string $contactID
	 * @return boolean
	 */
	public function valid_contact($contactID = NULL) {

		if (empty($contactID)) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return false;
		}

		foreach ($query->result() as $contact_info) {}

		if (!empty($contact_info->email) && filter_var($contact_info->email, FILTER_VALIDATE_EMAIL)) {
			return TRUE;
		}


		return FALSE;

	}

	/**
	 * check if a contact is valid
	 * @param  string $contactIDs
	 * @return boolean
	 */
	public function valid_participant_contact($contactIDs = NULL) {

		if($this->input->post('participant_contactID') && !in_array("all", $this->input->post('participant_contactID'))){
			$contactIDs = $this->input->post('participant_contactID');
		}

		if (!is_array($contactIDs) || count($contactIDs) == 0) {
			return FALSE;
		}

		foreach($contactIDs as $contactID) {
			// look up contact
			$where = array(
				'accountID' => $this->auth->user->accountID,
				'family_contacts.contactID' => $contactID
			);

			// run query
			$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				return false;
			}

			foreach ($query->result() as $contact_info) {
			}

			if (empty($contact_info->email) || !filter_var($contact_info->email, FILTER_VALIDATE_EMAIL)) {
				return FALSE;
			}
		}

		return TRUE;
	}

}

/* End of file exceptions.php */
/* Location: ./application/controllers/sessions/exceptions.php */
