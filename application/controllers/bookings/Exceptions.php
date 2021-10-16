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
	public function index($bookingID = NULL) {

		if ($bookingID == NULL) {
			show_404();
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
		$page_base = 'bookings/exceptions/' . $bookingID;
		$section = 'bookings';
		$title = 'Exceptions';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookings_lessons.bookingID' => $bookingID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'blockID' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'type' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_blockID', 'Block', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Reason', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['blockID'] = set_value('search_blockID');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['type'] = set_value('search_type');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-exceptions'))) {

			foreach ($this->session->userdata('search-bookings-exceptions') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-exceptions', $search_fields);

			if ($search_fields['blockID'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_lessons") . "`.`blockID` = " . $this->db->escape($search_fields['blockID']);
			}

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

			if ($search_fields['type'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_lessons_exceptions") . "`.`type` = " . $this->db->escape($search_fields['type']);
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('bookings_lessons_exceptions.*, bookings_lessons.blockID, bookings_blocks.name as block, staff.first, staff.surname, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('date asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_lessons_exceptions.*, bookings_lessons.blockID, bookings_blocks.name as block, staff.first, staff.surname, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('date asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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

		// blocks
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$block_list = $this->db->from('bookings_blocks')->where($where)->order_by('name asc')->get();

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
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'staff' => $res,
			'staff_list'=> $staff_list,
			'block_list'=> $block_list,
			'search_fields' => $search_fields,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/exceptions', $data);
	}

	/**
	 * show all exceptions
	 * @return void
	 */
	public function all() {

		// set defaults
		$icon = 'calendar-alt';
		$current_page = 'exceptions';
		$page_base = 'bookings/exceptions/all';
		$section = 'bookings';
		$title = 'Exceptions (All)';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookings_lessons_exceptions.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'brandID' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'type' => NULL,
			'completed' => NULL,
			'fromID' => NULL,
			'orgID' => NULL,
			'search' => NULL
		);

		// hide completed
		$search_where['completed'] = '`' . $this->db->dbprefix("bookings_lessons_exceptions") . "`.`date` >= CURDATE()";
		$search_fields['completed'] = 'no';
		$order_by = 'bookings_lessons_exceptions.date asc, bookings_lessons_exceptions.exceptionID asc';

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_brandID', 'Block', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_completed', 'Completed', 'trim|xss_clean');
			$this->form_validation->set_rules('search_fromID', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_orgID', 'Customer', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['brandID'] = set_value('search_brandID');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['type'] = set_value('search_type');
			$search_fields['completed'] = set_value('search_completed');
			$search_fields['fromID'] = set_value('search_fromID');
			$search_fields['orgID'] = set_value('search_orgID');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-exceptions'))) {

			foreach ($this->session->userdata('search-bookings-exceptions') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-exceptions', $search_fields);

			if ($search_fields['brandID'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['brandID']);
			}

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

			if ($search_fields['type'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_lessons_exceptions") . "`.`type` = " . $this->db->escape($search_fields['type']);
			}

			switch ($search_fields['completed']) {
				case 'yes':
					$search_where['completed'] = '`' . $this->db->dbprefix("bookings_lessons_exceptions") . "`.`date` < CURDATE()";
					$order_by = 'bookings_lessons_exceptions.date desc, bookings_lessons_exceptions.exceptionID asc';
					break;
				case 'no':
					$search_where['completed'] = '`' . $this->db->dbprefix("bookings_lessons_exceptions") . "`.`date` >= CURDATE()";
					$order_by = 'bookings_lessons_exceptions.date asc, bookings_lessons_exceptions.exceptionID asc';
					break;
				default:
					unset($search_where['completed']);
					$order_by = 'bookings_lessons_exceptions.date asc, bookings_lessons_exceptions.exceptionID asc';
					break;
			}

			if ($search_fields['fromID'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_lessons_exceptions") . "`.`fromID` = " . $this->db->escape($search_fields['fromID']);
			}

			if ($search_fields['orgID'] != '') {
				$search_where[] = "(`" . $this->db->dbprefix("bookings") . "`.`orgID` = " . $this->db->escape($search_fields['orgID']) . " OR `" . $this->db->dbprefix("bookings_blocks") . "`.`orgID` = " . $this->db->escape($search_fields['orgID']) . ")";
			}

		}

		if (array_key_exists('completed', $search_where)) {
			$search_where[] = $search_where['completed'];
			unset($search_where['completed']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('bookings_lessons_exceptions.*, orgs.name as org, brands.name as brand, brands.colour as brand_colour, bookings.orgID, bookings.type as booking_type, bookings.name as event, bookings_lessons.blockID, bookings_blocks.name as block, staff.first, staff.surname, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('bookings', 'bookings_lessons_exceptions.bookingID = bookings.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('brands', 'bookings.brandID = brands.brandID', 'left')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by($order_by)->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_lessons_exceptions.*, orgs.name as org, brands.name as brand, brands.colour as brand_colour, bookings.orgID, bookings.type as booking_type, bookings.name as event, bookings_lessons.blockID, bookings_blocks.name as block, staff.first, staff.surname, replacement_staff.first as replacement_first, replacement_staff.surname as replacement_surname')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('bookings', 'bookings_lessons_exceptions.bookingID = bookings.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('staff', 'bookings_lessons_exceptions.fromID = staff.staffID', 'left')->join('brands', 'bookings.brandID = brands.brandID', 'left')->join('staff AS replacement_staff', 'bookings_lessons_exceptions.staffID = replacement_staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by($order_by)->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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

		// orgs
		$where = array(
			//'prospect !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$orgs = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// blocks
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$block_list = $this->db->from('bookings_blocks')->where($where)->order_by('name asc')->get();

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'exceptions' => $res,
			'staff' => $res,
			'brands' => $brands,
			'orgs' => $orgs,
			'staff_list'=> $staff_list,
			'block_list'=> $block_list,
			'search_fields' => $search_fields,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/exceptions-all', $data);
	}

	/**
	 * remove a record
	 * @param  int $exceptionID
	 * @param bool $return_all
	 * @return mixed
	 */
	public function remove($exceptionID = NULL, $return_all = FALSE) {

		// check params
		if (empty($exceptionID)) {
			show_404();
		}

		if ($return_all == 'true') {
			$return_all = TRUE;
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
			if ($return_all == TRUE) {
				$redirect_to = 'bookings/exceptions/all';
			} else {
				$redirect_to = 'bookings/exceptions/' . $exception_info->bookingID;
			}

			redirect($redirect_to);
		}
	}
}

/* End of file exceptions.php */
/* Location: ./application/controllers/bookings/exceptions.php */
