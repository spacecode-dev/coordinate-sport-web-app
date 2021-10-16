<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bookings extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('equipment'));
	}

	/**
	 * show list of equipment bookings
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'folder-open';
		$current_page = 'equipment-bookings';
		$section = 'equipment';
		$type = 'equipment';
		$page_base = 'equipment/bookings';
		$title = 'Equipment Bookings';
		$tab = 'bookings';
		$buttons = '<a class="btn btn-success" href="' . site_url('equipment/bookings/new') . '"><i class="far fa-plus"></i> Create New</a>';
		if ($this->auth->user->department == 'coaching') {
			$buttons = NULL;
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'equipment' => 'Equipment'
		);

		// set where
		$where = array(
			'equipment.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'type' => NULL,
			'staff_id' => NULL,
			'org_id' => NULL,
			'contact_id' => NULL,
			'child_id' => NULL,
			'checked_in' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_org_id', $this->settings_library->get_label('customer'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_contact_id', 'Parent/Contact', 'trim|xss_clean');
			$this->form_validation->set_rules('search_child_id', 'Child', 'trim|xss_clean');
			$this->form_validation->set_rules('search_checked_in', 'Checked In', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['type'] = set_value('search_type');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['org_id'] = set_value('search_org_id');
			$search_fields['contact_id'] = set_value('search_contact_id');
			$search_fields['child_id'] = set_value('search_child_id');
			$search_fields['checked_in'] = set_value('search_checked_in');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-equipment-bookings'))) {

			foreach ($this->session->userdata('search-equipment-bookings') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-equipment-bookings', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = $this->db->dbprefix('equipment') . ".`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['type'] != '') {
				$search_where[] = $this->db->dbprefix('equipment_bookings') . ".`type` = " . $this->db->escape($search_fields['type']);
			}

			if (($search_fields['type'] == '' || $search_fields['type'] == 'staff') && $search_fields['staff_id'] != '') {
				$search_where[] = $this->db->dbprefix('equipment_bookings') . ".`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if (($search_fields['type'] == '' || $search_fields['type'] == 'org') && $search_fields['org_id'] != '') {
				$search_where[] = $this->db->dbprefix('equipment_bookings') . ".`orgID` = " . $this->db->escape($search_fields['org_id']);
			}

			if (($search_fields['type'] == '' || $search_fields['type'] == 'contact') && $search_fields['contact_id'] != '') {
				$search_where[] = $this->db->dbprefix('equipment_bookings') . ".`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if (($search_fields['type'] == '' || $search_fields['type'] == 'child') && $search_fields['child_id'] != '') {
				$search_where[] = $this->db->dbprefix('equipment_bookings') . ".`childID` = " . $this->db->escape($search_fields['child_id']);
			}

			if ($search_fields['checked_in'] != '') {
				if ($search_fields['checked_in'] == 'yes') {
					$status = 0;
				} else {
					$status = 1;
				}
				$search_where[] = "`status` = " . $this->db->escape($status);
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('equipment.name, equipment_bookings.*')->from('equipment_bookings')->join('equipment', 'equipment_bookings.equipmentID = equipment.equipmentID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('status desc, added asc')->group_by('equipment_bookings.bookingID')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('equipment.name, equipment_bookings.*, CONCAT_WS(\' \', ' . $this->db->dbprefix('staff') . '.first, ' . $this->db->dbprefix('staff') . '.surname) AS staff_label, orgs.name AS org_label, CONCAT_WS(\' \', ' . $this->db->dbprefix('family_contacts') . '.first_name, ' . $this->db->dbprefix('family_contacts') . '.last_name) AS contact_label, CONCAT_WS(\' \', ' . $this->db->dbprefix('family_children') . '.first_name, ' . $this->db->dbprefix('family_children') . '.last_name) AS child_label')->from('equipment_bookings')->join('equipment', 'equipment_bookings.equipmentID = equipment.equipmentID', 'inner')->join('staff', 'equipment_bookings.staffID = staff.staffID', 'left')->join('orgs', 'equipment_bookings.orgID = orgs.orgID', 'left')->join('family_contacts', 'equipment_bookings.contactID = family_contacts.contactID', 'left')->join('family_children', 'equipment_bookings.childID = family_children.childID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('status desc, added asc')->group_by('equipment_bookings.bookingID')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
		$staff = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// orgs
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$orgs = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// contacts
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('first_name asc, last_name asc')->get();

		// children
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$children = $this->db->from('family_children')->where($where)->order_by('first_name asc, last_name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'type' => $type,
			'equipment' => $res,
			'staff' => $staff,
			'orgs' => $orgs,
			'contacts' => $contacts,
			'children' => $children,
			'page_base' => $page_base,
			'search_fields' => $search_fields,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('equipment/bookings', $data);
	}

	/**
	 * edit equipment
	 * @param  int $bookingID
	 * @return void
	 */
	public function edit($bookingID = NULL)
	{

		if ($this->auth->user->department == 'coaching') {
			show_404();
		}

		$booking_info = new stdClass;

		// check if editing
		if ($bookingID != NULL) {

			// check if numeric
			if (!ctype_digit($bookingID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'bookingID' => $bookingID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('equipment_bookings')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$booking_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Booking';
		$submit_to = 'equipment/bookings/new/';
		$return_to = 'equipment/bookings';
		if ($bookingID != NULL) {
			$title = 'Edit Booking';
			$submit_to = 'equipment/bookings/edit/' . $bookingID;
		}
		$icon = 'folder-open';
		$tab = 'details';
		$current_page = 'bookings';
		$section = 'equipment';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'equipment' => 'Equipment',
			'equipment/bookings' => 'Bookings'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('equipmentID', 'Equipment', 'trim|xss_clean|required');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('staffID', 'Staff', 'trim|xss_clean|callback_required_if_type[staff]');
			$this->form_validation->set_rules('orgID', $this->settings_library->get_label('customer'), 'trim|xss_clean|callback_required_if_type[org]');
			$this->form_validation->set_rules('contactID', 'Parent/Contact', 'trim|xss_clean|callback_required_if_type[contact]');
			$this->form_validation->set_rules('childID', 'Child', 'trim|xss_clean|callback_required_if_type[child]');
			$this->form_validation->set_rules('dateIn', 'Return Date', 'trim|xss_clean|required|callback_check_date|callback_on_or_after_today');
			$this->form_validation->set_rules('timeH', 'Return Time - Hours', 'trim|xss_clean|required');
			$this->form_validation->set_rules('timeM', 'Return Time - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('quantity', 'Quantity', 'trim|xss_clean|required|greater_than[0]|callback_is_available[' . $bookingID . ']');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				$to = uk_to_mysql_date(set_value('dateIn')) . ' ' . set_value('timeH') . ':' . set_value('timeM');

				// all ok, prepare data
				$data = array(
					'equipmentID' => set_value('equipmentID'),
					'type' => set_value('type'),
					'staffID' => NULL,
					'orgID' => NULL,
					'contactID' => NULL,
					'childID' => NULL,
					'quantity' => set_value('quantity'),
					'dateOut' => mdate('%Y-%m-%d %H:%i:%s'),
					'dateIn' => $to,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($data['type'] == 'staff' && set_value('staffID') != '') {
					$data['staffID'] = set_value('staffID');
				}

				if ($data['type'] == 'org' && set_value('orgID') != '') {
					$data['orgID'] = set_value('orgID');
				}

				if ($data['type'] == 'contact' && set_value('contactID') != '') {
					$data['contactID'] = set_value('contactID');
				}

				if ($data['type'] == 'child' && set_value('childID') != '') {
					$data['childID'] = set_value('childID');
				}

				// if new
				if ($bookingID === NULL) {
					$data['status'] = 1;
					$data['byID'] = $bookingID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($bookingID == NULL) {
						// insert id
						$query = $this->db->insert('equipment_bookings', $data);
					} else {
						$where = array(
							'bookingID' => $bookingID
						);

						// update
						$query = $this->db->update('equipment_bookings', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($bookingID == NULL) {

							$this->session->set_flashdata('success', 'Booking has been created successfully.');

						} else {

							$this->session->set_flashdata('success', 'Booking has been updated successfully.');
						}

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

		// equipment + staff
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$equipment = $this->db->from('equipment')->where($where)->order_by('name asc')->get();
		$where['active'] = 1;
		$staff = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// orgs
		unset($where['active']);
		$orgs = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// contacts
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('first_name asc, last_name asc')->get();

		// children
		$children = $this->db->from('family_children')->where($where)->order_by('first_name asc, last_name asc')->get();

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
			'booking_info' => $booking_info,
			'bookingID' => $bookingID,
			'equipment' => $equipment,
			'staff' => $staff,
			'orgs' => $orgs,
			'contacts' => $contacts,
			'children' => $children,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('equipment/booking', $data);
	}

	/**
	 * delete equipment
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function remove($bookingID = NULL) {

		if ($this->auth->user->department == 'coaching') {
			show_404();
		}

		// check params
		if (empty($bookingID)) {
			show_404();
		}

		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('equipment_bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;

			// all ok, delete
			$query = $this->db->delete('equipment_bookings', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Booking has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'Booking could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'equipment/bookings';

			redirect($redirect_to);
		}
	}

	/**
	 * check in equipment
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function checkin($bookingID = NULL) {

		if ($this->auth->user->department == 'coaching') {
			show_404();
		}

		// check params
		if (empty($bookingID)) {
			show_404();
		}

		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('equipment_bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;

			$data = array(
				'status' => 0,
				'dateIn' => mdate('%Y-%m-%d %H:%i:%s')
			);

			// all ok, check in
			$query = $this->db->update('equipment_bookings', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Booking has been checked in successfully.');
			} else {
				$this->session->set_flashdata('error', 'Booking could not be checked in.');
			}

			// determine which page to send the user back to
			$redirect_to = 'equipment/bookings/recall';

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
	 * checks if enough items available
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function is_available($quantity = NULL, $bookingID = NULL) {

		$equipmentID = $this->input->post('equipmentID');

		// check params
		if (empty($quantity) || empty($equipmentID)) {
			return TRUE;
		}

		// check if numeric
		if (!ctype_digit($equipmentID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'equipmentID' => $equipmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('equipment')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// match
		foreach ($query->result() as $row) {
			$equipment_info = $row;
		}

		// get items taken out
		$where = array(
			'equipmentID' => $equipmentID,
			'status' => 1,
			'accountID' => $this->auth->user->accountID
		);

		if ($bookingID != NULL) {
			$where['bookingID !='] = $bookingID;
		}

		$taken = 0;

		// run query
		$query = $this->db->select('SUM(quantity) AS taken')->from('equipment_bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$taken = $row->taken;
			}
		}

		$available = (intval($equipment_info->quantity) - intval($taken));

		// if requested more than available
		if ($quantity > $available) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check a date is on or after today
	 * @param  string $date
	 * @return boolean
	 */
	public function on_or_after_today($date) {

		$date = strtotime(uk_to_mysql_date($date));

		if ($date >= strtotime(date('Y-m-d'))) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check if fields required because of specific type
	 * @return boolean
	 */
	public function required_if_type($var, $type) {

		// get form post as it's array
		if ($this->input->post('type') == $type && empty($var)) {
			return FALSE;
		}

		return TRUE;
	}
}

/* End of file bookings.php */
/* Location: ./application/controllers/equipment/bookings.php */
