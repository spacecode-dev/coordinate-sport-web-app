<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Groups extends MY_Controller
{

	public function __construct()
	{
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
		$this->load->library('form_validation');
	}

	/**
	 * show list of session types
	 * @return void
	 */
	public function index()
	{
		// set defaults
		$icon = 'users-cog';
		$current_page = 'groups';
		$page_base = 'settings/groups';
		$section = 'settings';
		$title = 'Groups';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/groups/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings'
		);

		$search_fields = array(
			'name' => NULL,
			'search' => NULL
		);

		$str = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : NULL;

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		if ($str !== null) {
			parse_str($str, $url_query_array);

			$this->load->library('form_validation');
			$this->form_validation->set_data($url_query_array);

			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			if( $this->form_validation->run() == FALSE ) {
				$errors = $this->form_validation->error_array();
			} else {
				$search_fields['name'] = $this->input->get('name');
				$search_fields['search'] = $this->input->get('search');
				$is_search = TRUE;
			}
		}


		$where['accountID'] = $this->auth->user->accountID;
		$like_where = [];

		if (!empty($search_fields['name'])) {
			$like_where['name'] = $search_fields['name'];
		}

		$this->pagination_library->calc_by_url(count($this->settings_library->getGroups($where, $like_where)));

		$limit = $this->pagination_library->amount;
		$start = $this->pagination_library->start;


		$groups = $this->settings_library->getGroups($where, $like_where, $start, $limit);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'search_fields' => $search_fields,
			'groups' => $groups
		);

		// load view
		$this->crm_view('settings/groups', $data);
	}

	public function edit($groupId = null) {
		// set defaults
		$icon = 'users-cog';
		$current_page = 'groups';
		$page_base = 'settings/groups/new';
		$section = 'settings';
		$title = 'Create New Group';
		$buttons = '<a class="btn" href="' . site_url('settings/groups') . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$success = NULL;
		$errors = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/groups' => 'Groups'
		);
		$return_to = 'settings/groups';

		$groupInfo = null;

		if ($groupId != NULL) {
			$groupInfo = $this->settings_library->getGroupInfo($groupId);
			$submitTo = 'settings/groups/edit/' . $groupId;
			$title = $groupInfo->name;
		} else {
			$submitTo = 'settings/groups/new/';
		}

		if ($this->input->post()) {

			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('staffId[]', 'Staff', 'trim|xss_clean|numeric');
			$this->form_validation->set_rules('offer_type', 'Offer Type', 'trim|xss_clean');

			$just_added = false;
			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				$staffIds = set_value('staffId');

				$data = array(
					'name' => set_value('name'),
					'offer_type' => set_value('offer_type'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);
				if ($groupId) {
					$this->settings_library->updateGroup($groupId, $data, $staffIds);
				} else {
					$just_added = true;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
					$groupId = $this->settings_library->createGroup($data, $staffIds);
				}

				if (isset($just_added)) {
					$this->session->set_flashdata('success', set_value('name') . ' Group has been created successfully.');
				} else {
					$this->session->set_flashdata('success', set_value('name') . ' Group has been updated successfully.');
				}

				redirect($return_to);
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		$this->load->library('staff_library');

		$staffList = $this->staff_library->getAllStaff($this->auth->user->accountID, true);

		$groupStaffList = $this->settings_library->getStaffByGroup($groupId);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'submit_to' => $submitTo,
			'return_to' => $return_to,
			'staff_list' => $staffList,
			'group_staff_list' => $groupStaffList,
			'group_info' => $groupInfo
		);

		// load view
		$this->crm_view('settings/group', $data);
	}

	public function remove($groupId) {
		if (empty($groupId)) {
			show_404();
		}

		$groupInfo = $this->settings_library->getGroupInfo($groupId);

		if (!$groupInfo) {
			show_404();
		}

		$removed = $this->settings_library->removeGroup($groupId);

		if ($removed > 0) {
			$this->session->set_flashdata('success', $groupInfo->name . ' Group has been removed successfully.');
		} else {
			$this->session->set_flashdata('error', $groupInfo->name . ' Group could not be removed.');
		}

		$redirect_to = 'settings/groups';

		redirect($redirect_to);
	}
}
