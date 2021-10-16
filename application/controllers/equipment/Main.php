<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('equipment'));
	}

	/**
	 * show list of equipment
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'futbol';
		$current_page = 'equipment';
		$section = 'equipment';
		$type = 'equipment';
		$page_base = 'equipment';
		$title = 'Equipment';
		$tab = 'equipment';
		$buttons = '<a class="btn btn-success" href="' . site_url('equipment/new') . '"><i class="far fa-plus"></i> Create New</a>';
		if ($this->auth->user->department == 'coaching') {
			$buttons = NULL;
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-equipment'))) {

			foreach ($this->session->userdata('search-equipment') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-equipment', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->from('equipment')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('equipment')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'type' => $type,
			'equipment' => $res,
			'page_base' => $page_base,
			'ci' => $this,
			'search_fields' => $search_fields,
			'tab' => $tab,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('equipment/main', $data);
	}

	/**
	 * edit equipment
	 * @param  int $equipmentID
	 * @return void
	 */
	public function edit($equipmentID = NULL)
	{

		if ($this->auth->user->department == 'coaching') {
			show_404();
		}

		$equipment_info = new stdClass;

		// check if editing
		if ($equipmentID != NULL) {

			// check if numeric
			if (!ctype_digit($equipmentID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'equipmentID' => $equipmentID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('equipment')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$equipment_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Equipment';
		$submit_to = 'equipment/new/';
		$return_to = 'equipment';
		if ($equipmentID != NULL) {
			$title = $equipment_info->name;
			$submit_to = 'equipment/edit/' . $equipmentID;
		}
		$icon = 'futbol';
		$tab = 'details';
		$current_page = 'equipment';
		$section = 'equipment';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'equipment' => 'Equipment'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean|required');
			$this->form_validation->set_rules('quantity', 'Quantity', 'trim|xss_clean|required|greater_than[0]|integer');
			$this->form_validation->set_rules('notes', 'Notes', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'location' => set_value('location'),
					'quantity' => set_value('quantity'),
					'notes' => set_value('notes'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				// if new
				if ($equipmentID === NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($equipmentID == NULL) {
						// insert id
						$query = $this->db->insert('equipment', $data);
					} else {
						$where = array(
							'equipmentID' => $equipmentID
						);

						// update
						$query = $this->db->update('equipment', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($equipmentID == NULL) {

							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');

						} else {

							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
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
			'equipment_info' => $equipment_info,
			'equipmentID' => $equipmentID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('equipment/equipment', $data);
	}

	/**
	 * delete equipment
	 * @param  int $equipmentID
	 * @return mixed
	 */
	public function remove($equipmentID = NULL) {

		if ($this->auth->user->department == 'coaching') {
			show_404();
		}

		// check params
		if (empty($equipmentID)) {
			show_404();
		}

		$where = array(
			'equipmentID' => $equipmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('equipment')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$equipment_info = $row;

			// all ok, delete
			$query = $this->db->delete('equipment', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $equipment_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $equipment_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'equipment';

			redirect($redirect_to);
		}
	}

	/**
	 * return how many items booked out
	 * @param  int $equipmentID
	 * @return mixed
	 */
	public function items_available($equipmentID) {

		// check params
		if (empty($equipmentID)) {
			return FALSE;
		}

		$where = array(
			'equipmentID' => $equipmentID,
			'status' => 1,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('SUM(quantity) AS taken')->from('equipment_bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return 0;
		} else {
			foreach ($query->result() as $row) {
				return $row->taken;
			}
		}
	}

}

/* End of file main.php */
/* Location: ./application/controllers/equipment/main.php */
