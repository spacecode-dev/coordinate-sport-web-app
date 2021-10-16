<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('accounts'));
	}

	/**
	 * show list
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'search';
		$current_page = 'users';
		$section = 'accounts';
		$page_base = 'accounts/users';
		$title = 'Search Users';
		$buttons = '<a class="btn btn-secondary" href="' . site_url('accounts') . '"><i class="far fa-angle-left"></i> Return to Accounts</a>';
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
		$search_where = array(
			'staff' => array(),
			'participants' => array(),
			'schools' => array(),
			'organisations' => array()
		);
		$search_fields = array(
			'type' => NULL,
			'first' => NULL,
			'last' => NULL,
			'email' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_first', 'First Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_last', 'Last Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_email', 'Email', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['type'] = set_value('search_type');
			$search_fields['first'] = set_value('search_first');
			$search_fields['last'] = set_value('search_last');
			$search_fields['email'] = set_value('search_email');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-accounts-users'))) {

			foreach ($this->session->userdata('search-accounts-users') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-accounts-users', $search_fields);

			if ($search_fields['first'] != '') {
				$search_where['staff'][] = "`first` LIKE '%" . $this->db->escape_like_str($search_fields['first']) . "%'";
				$search_where['participants'][] = "`first_name` LIKE '%" . $this->db->escape_like_str($search_fields['first']) . "%'";
				$search_where['schools'][] = $this->db->dbprefix('orgs_contacts').".`name` LIKE '%" . $this->db->escape_like_str($search_fields['first']) . "%'";
				$search_where['organisations'][] = $this->db->dbprefix('orgs_contacts').".`name` LIKE '%" . $this->db->escape_like_str($search_fields['first']) . "%'";
			}

			if ($search_fields['last'] != '') {
				$search_where['staff'][] = "`surname` LIKE '%" . $this->db->escape_like_str($search_fields['last']) . "%'";
				$search_where['participants'][] = "`last_name` LIKE '%" . $this->db->escape_like_str($search_fields['last']) . "%'";
				$search_where['schools'][] = $this->db->dbprefix('orgs_contacts').".`name` LIKE '%" . $this->db->escape_like_str($search_fields['last']) . "%'";
				$search_where['organisations'][] = $this->db->dbprefix('orgs_contacts').".`name` LIKE '%" . $this->db->escape_like_str($search_fields['last']) . "%'";
			}

			if ($search_fields['email'] != '') {
				$search_where['staff'][] = $this->db->dbprefix('staff') . ".`email` LIKE '%" . $this->db->escape_like_str($search_fields['email']) . "%'";
				$search_where['participants'][] = $this->db->dbprefix('family_contacts') . ".`email` LIKE '%" . $this->db->escape_like_str($search_fields['email']) . "%'";
				$search_where['schools'][] = $this->db->dbprefix('orgs_contacts') . ".`email` LIKE '%" . $this->db->escape_like_str($search_fields['email']) . "%'";
				$search_where['organisations'][] = $this->db->dbprefix('orgs_contacts') . ".`email` LIKE '%" . $this->db->escape_like_str($search_fields['email']) . "%'";
			}
		}

		if (count($search_where['staff']) > 0) {
			$search_where['staff'] = '(' . implode(' AND ', $search_where['staff']) . ')';
		}

		if (count($search_where['participants']) > 0) {
			$search_where['participants'] = '(' . implode(' AND ', $search_where['participants']) . ')';
		}

		if (count($search_where['schools']) > 0) {
			$search_where['schools'] = '(' . implode(' AND ', $search_where['schools']) . ')';
		}

		if (count($search_where['organisations']) > 0) {
			$search_where['organisations'] = '(' . implode(' AND ', $search_where['organisations']) . ')';
		}

		// track results
		$results = array();

		// search staff
		if (empty($search_fields['type']) || $search_fields['type'] == 'staff') {
			$res = $this->db->select('staff.staffID, staff.accountID, staff.first, staff.surname, staff.email, staff.department, accounts.company as account')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID', 'inner')->where($where)->where($search_where['staff'], NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$key = $row->first . '-' . $row->surname . '-staff-' . $row->accountID . '-' . $row->staffID;
					$results[$key] = array(
						'id' => $row->staffID,
						'accountID' => $row->accountID,
						'account' => $row->account,
						'first' => $row->first,
						'last' => $row->surname,
						'email' => $row->email,
						'type' => 'staff',
						'level' => $this->settings_library->get_permission_level_label($row->department, TRUE)
					);
				}
			}
		}

		// search participants
		if (empty($search_fields['type']) || $search_fields['type'] == 'participants') {
			$res = $this->db->select('family_contacts.contactID, family_contacts.accountID, family_contacts.first_name, family_contacts.last_name, family_contacts.email, accounts.company as account')->from('family_contacts')->join('accounts', 'family_contacts.accountID = accounts.accountID', 'inner')->where($where)->where($search_where['participants'], NULL, FALSE)->order_by('family_contacts.first_name asc, family_contacts.last_name asc')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$key = $row->first_name . '-' . $row->last_name . '-staff-' . $row->accountID . '-' . $row->contactID;
					$results[$key] = array(
						'id' => $row->contactID,
						'accountID' => $row->accountID,
						'account' => $row->account,
						'first' => $row->first_name,
						'last' => $row->last_name,
						'email' => $row->email,
						'type' => 'participant',
						'level' => NULL
					);
				}
			}
		}

		// search schools or organisations
		if (!empty($search_fields['type']) && ($search_fields['type'] == 'schools' || $search_fields['type'] == 'organisations')) {
			$type_key = rtrim($search_fields['type'], 's');
			$where['type'] = $type_key;
			$res = $this->db->select('orgs_contacts.name,orgs_contacts.position, orgs_contacts.contactID, orgs_contacts.accountID, orgs_contacts.email, accounts.company as account')
				->from('orgs_contacts')
				->join('orgs', 'orgs_contacts.orgID = orgs.orgID', 'inner')
				->join('accounts', 'orgs_contacts.accountID = accounts.accountID', 'inner')
				->where($where)
				->where($search_where[$search_fields['type']], NULL, FALSE)
				->order_by('orgs_contacts.name asc')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$key = $row->name. '-staff-' . $row->accountID . '-' . $row->contactID;
					$results[$key] = array(
						'id' => $row->contactID,
						'accountID' => $row->accountID,
						'account' => $row->account,
						'name' => $row->name,
						'email' => $row->email,
						'type' => $type_key,
						'level' => $row->position
					);
				}
			}
		}

		// sort
		ksort($results);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// check if valid search
		$valid_search = FALSE;
		if (!empty($search_fields['first']) || !empty($search_fields['last']) || !empty($search_fields['email'])) {
			$valid_search = TRUE;
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'users' => $results,
			'page_base' => $page_base,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'valid_search' => $valid_search
		);

		// load view
		$this->crm_view('accounts/users', $data);

	}

}

/* End of file users.php */
/* Location: ./application/controllers/accounts/users.php */
