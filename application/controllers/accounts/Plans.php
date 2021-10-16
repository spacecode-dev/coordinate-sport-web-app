<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plans extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('accounts'));
	}

	/**
	 * show list
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'folder-open';
		$current_page = 'plans';
		$section = 'accounts';
		$page_base = 'accounts/plans';
		$title = 'Plans';
		$buttons = '<a class="btn btn-success" href="' . site_url('accounts/plans/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'accounts' => 'Accounts'
		);

		// set where
		$where = array(
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-accounts-plans'))) {

			foreach ($this->session->userdata('search-accounts-plans') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-accounts-plans', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('*')->from('accounts_plans')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('*')->from('accounts_plans')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'search_fields' => $search_fields,
			'plans' => $res,
			'page_base' => $page_base,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('accounts/plans', $data);
	}

	/**
	 * edit plan
	 * @param  int $planID
	 * @return void
	 */
	public function edit($planID = NULL)
	{

		$plan_info = new stdClass;

		// check if editing
		if ($planID != NULL) {

			// check if numeric
			if (!ctype_digit($planID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'planID' => $planID,
			);

			// run query
			$query = $this->db->from('accounts_plans')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$plan_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Plan';
		$submit_to = 'accounts/plans/new/';
		$return_to = 'accounts/plans';
		if ($planID != NULL) {
			$title = $plan_info->name;
			$submit_to = 'accounts/plans/edit/' . $planID;
		}
		$icon = 'folder-open';
		$tab = 'details';
		$current_page = 'plans';
		$section = 'accounts';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'accounts' => 'Accounts',
			'accounts/plans' => 'Plans'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('default_project_types', 'Default Project Types', 'trim|xss_clean|required');
			$this->form_validation->set_rules('bookings_timetable', 'Bookings - Timetable', 'trim|xss_clean');
			$this->form_validation->set_rules('bookings_timetable_own', 'Bookings - Your Timetable', 'trim|xss_clean');
			$this->form_validation->set_rules('bookings_bookings', 'Bookings - Contracts', 'trim|xss_clean');
			$this->form_validation->set_rules('bookings_projects', 'Bookings - Projects', 'trim|xss_clean');
			$this->form_validation->set_rules('bookings_exceptions', 'Bookings - Exceptions', 'trim|xss_clean');
			$this->form_validation->set_rules('customers_schools', $this->settings_library->get_label('customers', TRUE) . ' - Schools', 'trim|xss_clean');
			$this->form_validation->set_rules('customers_schools_prospects', $this->settings_library->get_label('customers', TRUE) . ' - Prospective Schools', 'trim|xss_clean');
			$this->form_validation->set_rules('customers_orgs', $this->settings_library->get_label('customers', TRUE) . ' - Organisations', 'trim|xss_clean');
			$this->form_validation->set_rules('customers_orgs_prospects', $this->settings_library->get_label('customers', TRUE) . ' - Prospective Organisations', 'trim|xss_clean');
			$this->form_validation->set_rules('participants', $this->settings_library->get_label('participants', TRUE), 'trim|xss_clean');
			$this->form_validation->set_rules('staff_management', 'Staff Management', 'trim|xss_clean');
			$this->form_validation->set_rules('settings', 'Settings', 'trim|xss_clean');
			$this->form_validation->set_rules('addons_all', 'All Addons', 'trim|xss_clean');

			// dashoard widgets
			$this->form_validation->set_rules('dashboard_bookings', 'Bookings', 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_staff', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_participants', $this->settings_library->get_label('participants', TRUE), 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_health_safety', 'Health & Safety', 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_equipment', 'Equipment', 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_availability', 'Availability', 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_employee_of_month', 'Employee of the Month', 'trim|xss_clean');
			$this->form_validation->set_rules('dashboard_staff_birthdays', 'Staff Birthdays', 'trim|xss_clean');

			// labels
			$this->form_validation->set_rules('label_brand', $this->settings_library->get_label('brand', TRUE) . ' (Singular)', 'trim|xss_clean');
			$this->form_validation->set_rules('label_brands', $this->settings_library->get_label('brands', TRUE) . ' (Plural)', 'trim|xss_clean');
			$this->form_validation->set_rules('label_customer', $this->settings_library->get_label('customer', TRUE) . ' (Singular)', 'trim|xss_clean');
			$this->form_validation->set_rules('label_customers', $this->settings_library->get_label('customers', TRUE) . ' (Plural)', 'trim|xss_clean');
			$this->form_validation->set_rules('label_participant', $this->settings_library->get_label('participant', TRUE) . ' (Singular)', 'trim|xss_clean');
			$this->form_validation->set_rules('label_participants', $this->settings_library->get_label('participants', TRUE) . ' (Plural)', 'trim|xss_clean');


			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'default_project_types' => set_value('default_project_types'),
					'bookings_timetable' => intval(set_value('bookings_timetable')),
					'bookings_timetable_own' => intval(set_value('bookings_timetable_own')),
					'bookings_bookings' => intval(set_value('bookings_bookings')),
					'bookings_projects' => intval(set_value('bookings_projects')),
					'bookings_exceptions' => intval(set_value('bookings_exceptions')),
					'customers_schools' => intval(set_value('customers_schools')),
					'customers_schools_prospects' => intval(set_value('customers_schools_prospects')),
					'customers_orgs' => intval(set_value('customers_orgs')),
					'customers_orgs_prospects' => intval(set_value('customers_orgs_prospects')),
					'participants' => intval(set_value('participants')),
					'staff_management' => intval(set_value('staff_management')),
					'settings' => intval(set_value('settings')),
					'addons_all' => intval(set_value('addons_all')),
					'dashboard_bookings' => intval(set_value('dashboard_bookings')),
					'dashboard_staff' => intval(set_value('dashboard_staff')),
					'dashboard_participants' => intval(set_value('dashboard_participants')),
					'dashboard_health_safety' => intval(set_value('dashboard_health_safety')),
					'dashboard_equipment' => intval(set_value('dashboard_equipment')),
					'dashboard_availability' => intval(set_value('dashboard_availability')),
					'dashboard_employee_of_month' => intval(set_value('dashboard_employee_of_month')),
					'dashboard_staff_birthdays' => intval(set_value('dashboard_staff_birthdays')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				);

				// labels
				$data['label_brand'] = NULL;
				if (set_value('label_brand') != '') {
					$data['label_brand'] = set_value('label_brand');
				}
				$data['label_brands'] = NULL;
				if (set_value('label_brands') != '') {
					$data['label_brands'] = set_value('label_brands');
				}
				$data['label_customer'] = NULL;
				if (set_value('label_customer') != '') {
					$data['label_customer'] = set_value('label_customer');
				}
				$data['label_customers'] = NULL;
				if (set_value('label_customers') != '') {
					$data['label_customers'] = set_value('label_customers');
				}
				$data['label_participant'] = NULL;
				if (set_value('label_participant') != '') {
					$data['label_participant'] = set_value('label_participant');
				}
				$data['label_participants'] = NULL;
				if (set_value('label_participants') != '') {
					$data['label_participants'] = set_value('label_participants');
				}

				// if new
				if ($planID === NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($planID == NULL) {
						// insert id
						$query = $this->db->insert('accounts_plans', $data);
					} else {
						$where = array(
							'planID' => $planID
						);

						// update
						$query = $this->db->update('accounts_plans', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($planID == NULL) {
							$planID = $this->db->insert_id();
							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
						}

						redirect('accounts/plans');

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
			'plan_info' => $plan_info,
			'planID' => $planID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('accounts/plan', $data);
	}

	/**
	 * delete plan
	 * @param  int $planID
	 * @return mixed
	 */
	public function remove($planID = NULL) {

		// check params
		if (empty($planID)) {
			show_404();
		}

		// can't delete own account plan
		if ($this->auth->account->planID == $planID) {
			show_404();
		}

		$where = array(
			'planID' => $planID
		);

		// run query
		$query = $this->db->from('accounts_plans')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$plan_info = $row;

			// all ok, delete
			$query = $this->db->delete('accounts_plans', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $plan_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $plan_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'accounts/plans';

			redirect($redirect_to);
		}
	}

	/**
	 * toggle feature status
	 * @param  int $planID
	 * @param string $value
	 * @return mixed
	 */
	public function feature($planID = NULL, $feature = NULL, $value = NULL) {
		// check params
		if (empty($planID) || empty($feature) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		// check feature exists
		if (!$this->db->field_exists($feature, 'accounts_plans')) {
			return FALSE;
		}

		$where = array(
			'planID' => $planID
		);

		// run query
		$query = $this->db->from('accounts_plans')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$plan_info = $row;

			$data = array(
				$feature => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data[$feature] = 1;
			}

			// run query
			$query = $this->db->update('accounts_plans', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

}

/* End of file plans.php */
/* Location: ./application/controllers/accounts/plans.php */
