<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Departments extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
	}

	/**
	 * show list of brands
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'brands';
		$page_base = 'settings/departments';
		$section = 'settings';
		$title = $this->settings_library->get_label('brands');
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/departments/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings'
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where['is_active'] = "`active` = 1";
		$search_fields = array(
			'search' => NULL,
			'name' => NULL,
			'is_active' => 'yes'
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-brands'))) {

			foreach ($this->session->userdata('search-brands') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-brands', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`active` = 1';
				} else {
					$search_where['is_active'] = '`active` != 1';
				}
			}
		}

		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->from('brands')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('brands')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'page_base' => $page_base,
			'brands' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/departments', $data);
	}

	/**
	 * edit a brand
	 * @param  int $brandID
	 * @return void
	 */
	public function edit($brandID = NULL)
	{

		$brand_info = new stdClass();

		// check if editing
		if ($brandID != NULL) {

			// check if numeric
			if (!ctype_digit($brandID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'brandID' => $brandID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('brands')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$brand_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New ' . $this->settings_library->get_label('brand');
		if ($brandID != NULL) {
			$submit_to = 'settings/departments/edit/' . $brandID;
			$title = $brand_info->name;
		} else {
			$submit_to = 'settings/departments/new/';
		}
		$return_to = 'settings/departments';
		$icon = 'cog';
		$current_page = 'brands';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/departments' => $this->settings_library->get_label('brands')
		);

		$quals = [];
		$res = $this->db->from('mandatory_quals')
			->where(['accountID' => $this->auth->user->accountID])
			->order_by('name asc')->get();

		foreach ($res->result() as $row) {
			$quals[] = $row;
		}

		// get list of activities
		$activities = [];
		$res = $this->db->from('activities')
			->where([
				'accountID' => $this->auth->user->accountID,
				'active' => 1
			])
			->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$activities[$row->activityID] = $row->name;
			}
		}

		$brand_quals = [];
		$res = $this->db->from('brands_quals')
			->where(['brandID' => $brandID])
			->get();

		foreach ($res->result() as $row) {
			$brand_quals[$row->qualID] = $row;
		}

		$brand_activities = [];
		$res = $this->db->from('brands_activities')
			->where(['brandID' => $brandID])
			->get();

		foreach ($res->result() as $row) {
			$brand_activities[$row->activityID] = $row;
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('colour', 'Colour', 'trim|xss_clean|required');
			$this->form_validation->set_rules('website', 'Web Site', 'trim|xss_clean|valid_url');
			$this->form_validation->set_rules('mailchimp_id', 'Newsletter Audience ID', 'trim|xss_clean');
			$this->form_validation->set_rules('staff_performance_exclude_session_evaluations', 'Staff Performance - Exclude Session Evaluations', 'trim|xss_clean');
			$this->form_validation->set_rules('staff_performance_exclude_pupil_assessments', 'Staff Performance - Exclude Pupil Assessments', 'trim|xss_clean');
			$this->form_validation->set_rules('hide_online', 'Hide from Search Dropdown on Bookings Site', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'colour' => set_value('colour'),
					'website' => NULL,
					'mailchimp_id' => NULL,
					'staff_performance_exclude_session_evaluations' => intval(set_value('staff_performance_exclude_session_evaluations')),
					'staff_performance_exclude_pupil_assessments' => intval(set_value('staff_performance_exclude_pupil_assessments')),
					'hide_online' => intval(set_value('hide_online')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($brandID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				if (set_value('website') != '') {
					$data['website'] = set_value('website');
				}

				if (set_value('mailchimp_id') != '') {
					$data['mailchimp_id'] = set_value('mailchimp_id');
				}

				// update logo
				$upload_res = $this->crm_library->handle_image_upload('logo');

				if ($upload_res !== NULL) {
					$data['logo_path'] = $upload_res['raw_name'];
					$data['logo_type'] = $upload_res['file_type'];
					$data['logo_size'] = $upload_res['file_size']*1024;
					$data['logo_ext'] = substr($upload_res['file_ext'], 1);
				}

				// final check for errors
				if (count($errors) == 0) {

					$quals_posted = $this->input->post('quals');
					if (!is_array($quals_posted)) {
						$quals_posted = array();
					}

					if ($brandID !== NULL) {
						$remove_where = [
							'brandID' => $brandID
						];

						$this->db->delete('brands_quals', $remove_where);
					}

					$activities_posted = $this->input->post('activities');
					if (!is_array($activities_posted)) {
						$activities_posted = array();
					}

					if ($brandID !== NULL) {
						$remove_where = [
							'brandID' => $brandID
						];

						$this->db->delete('brands_activities', $remove_where);
					}

					if ($brandID == NULL) {
						// insert
						$query = $this->db->insert('brands', $data);

						$brandID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'brandID' => $brandID
						);

						// update
						$query = $this->db->update('brands', $data, $where);
					}

					foreach ($quals_posted as $qual_id => $value) {
						$data = [
							'brandID' => $brandID,
							'qualID' => $qual_id
						];

						$this->db->insert('brands_quals', $data);
					}

					foreach ($activities_posted as $id => $value) {
						$data = [
							'brandID' => $brandID,
							'activityID' => $id
						];

						$this->db->insert('brands_activities', $data);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (isset($just_added)) {
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
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'brand_info' => $brand_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'mandatory_quals' => $quals,
			'brand_quals' => $brand_quals,
			'activities' => $activities,
			'brand_activities' => $brand_activities
		);

		// load view
		$this->crm_view('settings/department', $data);
	}

	/**
	 * delete a brand
	 * @param  int $brandID
	 * @return mixed
	 */
	public function remove($brandID = NULL) {

		// check params
		if (empty($brandID)) {
			show_404();
		}

		$where = array(
			'brandID' => $brandID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('brands')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$brand_info = $row;

			// all ok, delete
			$query = $this->db->delete('brands', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $brand_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $brand_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/departments';

			redirect($redirect_to);
		}
	}

	/**
	 * toggle active status
	 * @param  int $brandID
	 * @param string $value
	 * @return mixed
	 */
	public function active($brandID = NULL, $value = NULL) {

		// check params
		if (empty($brandID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'brandID' => $brandID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('brands')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$brand_info = $row;

			$data = array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['active'] = 1;
			}

			// run query
			$query = $this->db->update('brands', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}
	}

	/**
	 * check a url is valid format
	 * @param  string $str
	 * @return bool
	 */
	function valid_url($str) {

		// trim whitespace
		$str = trim($str);

		// skip if valud
		if (empty($str)) {
			return TRUE;
		}

		// auto add http if missing
		if (substr($str, 0, 4) != 'http') {
			$str = 'http://' . $str;
		}

		// check
		$pattern = "|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i";
		if (!preg_match($pattern, $str)){
			return FALSE;
		}

		return $str;
	}

}

/* End of file departments.php */
/* Location: ./application/controllers/settings/departments.php */
