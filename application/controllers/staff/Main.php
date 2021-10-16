<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
		$this->switch_day = 4;
	}

	/**
	 * show list of staff
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'sitemap';
		$current_page = 'staff';
		$section = 'staff';
		$page_base = 'staff';
		$title = 'Staff';
		$buttons = '<a class="btn btn-success" href="' . site_url('staff/new') . '"><i class="far fa-plus"></i> Create New</a>';
		if ($this->limit_reached() === TRUE) {
			$buttons = '<span title="Staff limit reached. Contact us to upgrade or deactivate another user"><a class="btn btn-success disabled" href="' . site_url('staff/new') . '"><i class="far fa-plus"></i> Create New</a></span>';
		}
		if ($this->auth->user->department === 'headcoach') {
			$buttons = NULL;
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'staff.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where['is_active'] = '`' . $this->db->dbprefix("staff") . "`.`active` = 1";
		$search_fields = array(
			'first_name' => NULL,
			'last_name' => NULL,
			'job_title' => NULL,
			'department' => NULL,
			'brand_id' => NULL,
			'is_active' => 'yes',
			'activity_id' => NULL,
			'min_age' => NULL,
			'max_age' => NULL,
			'gender' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_first_name', 'First Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_last_name', 'Last Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_job_title', 'Job Title', 'trim|xss_clean');
			$this->form_validation->set_rules('search_department', 'Permission Level', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand_id', 'Primary ' . $this->settings_library->get_label('brand'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search_activity_id', 'Activity', 'trim|xss_clean');
			$this->form_validation->set_rules('search_min_age', 'Min Age', 'trim|xss_clean');
			$this->form_validation->set_rules('search_max_age', 'Max Age', 'trim|xss_clean');
			$this->form_validation->set_rules('search_gender', 'Gender', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['first_name'] = set_value('search_first_name');
			$search_fields['last_name'] = set_value('search_last_name');
			$search_fields['job_title'] = set_value('search_job_title');
			$search_fields['department'] = set_value('search_department');
			$search_fields['brand_id'] = set_value('search_brand_id');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['activity_id'] = set_value('search_activity_id');
			$search_fields['min_age'] = set_value('search_min_age');
			$search_fields['max_age'] = set_value('search_max_age');
			$search_fields['gender'] = set_value('search_gender');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-staff'))) {

			foreach ($this->session->userdata('search-staff') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-staff', $search_fields);

			if ($search_fields['first_name'] != '') {
				$search_where[] = "`first` LIKE '%" . $this->db->escape_like_str($search_fields['first_name']) . "%'";
			}

			if ($search_fields['last_name'] != '') {
				$search_where[] = "`surname` LIKE '%" . $this->db->escape_like_str($search_fields['last_name']) . "%'";
			}

			if ($search_fields['job_title'] != '') {
				$search_where[] = "`jobTitle` LIKE '%" . $this->db->escape_like_str($search_fields['job_title']) . "%'";
			}

			if ($search_fields['department'] != '') {
				$search_where[] = "`department` = " . $this->db->escape($search_fields['department']);
			}

			if ($search_fields['brand_id'] != '') {
				$search_where[] = "`brandID` = " . $this->db->escape($search_fields['brand_id']);
			}

			if ($search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`active` = 1';
				} else {
					$search_where['is_active'] = '`active` != 1';
				}
			}

			if ($search_fields['activity_id'] != '') {
				$search_where[] = $this->db->dbprefix('staff_activities').".`activityID` = " . $this->db->escape($search_fields['activity_id']);
			}

			if ($search_fields['min_age'] != '') {
				$search_fields['min_age'] = intval($search_fields['min_age']);
				$minDOB = date("Y-m-d", strtotime('-' . $search_fields['min_age'] . ' years'));
				$search_where[] = "`dob` <= " . $this->db->escape_str($minDOB);
			}

			if ($search_fields['max_age'] != '') {
				$search_fields['max_age'] = intval($search_fields['max_age']);
				$maxDOB = date("Y-m-d", strtotime('-' . $search_fields['max_age'] . ' years'));
				$search_where[] = "`dob` > " . $this->db->escape_str($maxDOB);
			}

			if ($search_fields['gender'] != '') {
				if ($search_fields['gender'] == 'male') {
					$search_where[] = '`title` = "mr"';
				} else {
					$search_where[] = '`title` IN("mrs", "ms", "miss")';
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
		$res = $this->db->select('staff.*, staff_addresses.*')->from('staff')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND ' . $this->db->dbprefix('staff_addresses') . '.type = "main"', 'left')->join('staff_activities', 'staff.staffID = staff_activities.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->group_by('staff.staffID')->order_by('staff.first asc, staff.surname asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('staff.*, staff_addresses.*')->from('staff')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND ' . $this->db->dbprefix('staff_addresses') . '.type = "main"', 'left')->join('staff_activities', 'staff.staffID = staff_activities.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->group_by('staff.staffID')->order_by('staff.first asc, staff.surname asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// activities
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$activities = $this->db->from('activities')->where($where)->order_by('name asc')->get();

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
			'search_fields' => $search_fields,
			'staff' => $res,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'activities' => $activities,
			'brands' => $brands,
			'limit_reached' => $this->limit_reached()
		);

		// load view
		$this->crm_view('staff/main', $data);
	}

	/**
	 * edit staff
	 * @param  int $staffID
	 * @return void
	 */
	public function edit($staffID = NULL)
	{

		// deny from head coach
		if ($this->auth->user->department == 'headcoach') {
			show_404();
		}

		$staff_info = new stdClass;
		$contact_info = new stdClass;
		$edit_level_and_login = TRUE;

		// get fields
		$fields = get_fields('staff');

		// check if editing
		if ($staffID != NULL) {

			// check if numeric
			if (!ctype_digit($staffID)) {
				show_404();
			}

			// if so, check user exists
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
				// check permissions
				switch ($this->auth->user->department) {
					case 'office':
						if (in_array($staff_info->department, array('directors', 'management'))) {
							$edit_level_and_login = FALSE;
						}
						break;
					case 'management':
						if (in_array($staff_info->department, array('directors'))) {
							$edit_level_and_login = FALSE;
						}
						break;
				}
			}

		} else if ($this->limit_reached() === TRUE) {
			show_403();
		}

		// if proxying into account, allow level and login editing
		if ($this->auth->account_overridden === TRUE) {
			$edit_level_and_login = TRUE;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Staff';
		$submit_to = 'staff/new/';
		$return_to = 'staff';
		if ($staffID != NULL) {
			$title = $staff_info->first . ' ' . $staff_info->surname;
			$submit_to = 'staff/edit/' . $staffID;
		}
		$icon = 'user';
		$tab = 'details';
		$current_page = 'staff';
		$section = 'staff';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
 		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			// basic info
			$this->form_validation->set_rules('title', field_label('title', $fields, TRUE), 'trim|xss_clean' . required_field('title', $fields, 'validation'));
			$this->form_validation->set_rules('first', field_label('first', $fields, TRUE), 'trim|xss_clean' . required_field('first', $fields, 'validation'));
			$this->form_validation->set_rules('middle', field_label('middle', $fields, TRUE), 'trim|xss_clean' . required_field('middle', $fields, 'validation'));
			$this->form_validation->set_rules('surname', field_label('surname', $fields, TRUE), 'trim|xss_clean' . required_field('surname', $fields, 'validation'));
			$this->form_validation->set_rules('jobTitle', field_label('jobTitle', $fields, TRUE), 'trim|xss_clean' . required_field('jobTitle', $fields, 'validation'));
			if ($edit_level_and_login === TRUE) {
				$this->form_validation->set_rules('department', field_label('department', $fields, TRUE), 'trim|xss_clean' . required_field('department', $fields, 'validation') . '|callback_check_permission');
			}
			$this->form_validation->set_rules('non_delivery', field_label('non_delivery', $fields, TRUE), 'trim|xss_clean' . required_field('non_delivery', $fields, 'validation'));
			$this->form_validation->set_rules('brandID', 'Primary ' . $this->settings_library->get_label('brand'), 'trim|xss_clean' . required_field('brandID', $fields, 'validation'));
			$this->form_validation->set_rules('nationalInsurance', field_label('nationalInsurance', $fields, TRUE), 'trim|xss_clean' . required_field('nationalInsurance', $fields, 'validation'));
			$this->form_validation->set_rules('dob', field_label('dob', $fields, TRUE), 'trim|xss_clean' . required_field('dob', $fields, 'validation') . '|callback_check_date');

			// login info
			if ($edit_level_and_login === TRUE) {
				$this->form_validation->set_rules('email', field_label('email', $fields, TRUE), 'trim|xss_clean' . required_field('email', $fields, 'validation') . '|valid_email|callback_check_email[' . $staffID . ']');

				// only allow editing password if proxied in to account
				if ($this->auth->account_overridden === TRUE) {
					if ($staffID === NULL) {
						// new
						$this->form_validation->set_rules('password', 'Password', 'trim|xss_clean|required|min_length[8]|matches[password_confirm]');
						$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean|required');
					} else {
						// existing
						$this->form_validation->set_rules('password', 'Password', 'trim|xss_clean|min_length[8]|matches[password_confirm]');
						$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean');
					}
				}
				$this->form_validation->set_rules('notify', 'Notify', 'trim|xss_clean' . required_field('title', $fields, 'validation'));
			}

			// contact info
			$this->form_validation->set_rules('address1', field_label('address1', $fields, TRUE), 'trim|xss_clean' . required_field('address1', $fields, 'validation'));
			$this->form_validation->set_rules('address2', field_label('address2', $fields, TRUE), 'trim|xss_clean' . required_field('address2', $fields, 'validation'));
			$this->form_validation->set_rules('town', field_label('town', $fields, TRUE), 'trim|xss_clean' . required_field('town', $fields, 'validation'));
			$this->form_validation->set_rules('county', localise('county'), 'trim|xss_clean' . required_field('county', $fields, 'validation'));
			$this->form_validation->set_rules('postcode', field_label('postcode', $fields, TRUE), 'trim|xss_clean' . required_field('postcode', $fields, 'validation') . '|callback_check_postcode');
			$this->form_validation->set_rules('fromM', 'From Month', 'trim|xss_clean' . required_field('fromM', $fields, 'validation'));
			$this->form_validation->set_rules('fromY', 'From Year', 'trim|xss_clean' . required_field('fromM', $fields, 'validation'));
			$this->form_validation->set_rules('phone', field_label('phone', $fields, TRUE), 'trim|xss_clean' . required_field('phone', $fields, 'validation'));
			$this->form_validation->set_rules('mobile', field_label('mobile', $fields, TRUE), 'trim|xss_clean' . required_field('mobile', $fields, 'validation'));
			$this->form_validation->set_rules('mobile_work', field_label('mobile_work', $fields, TRUE), 'trim|xss_clean' . required_field('work_mobile', $fields, 'validation'));

			// emergency contact
			if ($staffID === NULL) {
				$this->form_validation->set_rules('eName', field_label('eName', $fields, TRUE), 'trim|xss_clean' . required_field('eName', $fields, 'validation'));
				$this->form_validation->set_rules('eRelationship', 'Emergency Contact - ' . field_label('eRelationship', $fields, TRUE), 'trim|xss_clean' . required_field('eRelationship', $fields, 'validation'));
				$this->form_validation->set_rules('eAddress1', 'Emergency Contact - ' . field_label('eAddress1', $fields, TRUE), 'trim|xss_clean' . required_field('eAddress1', $fields, 'validation'));
				$this->form_validation->set_rules('eAddress2', 'Emergency Contact - ' . field_label('eAddress2', $fields, TRUE), 'trim|xss_clean' . required_field('eAddress2', $fields, 'validation'));
				$this->form_validation->set_rules('eTown', 'Emergency Contact -' . field_label('eTown', $fields, TRUE), 'trim|xss_clean' . required_field('eTown', $fields, 'validation'));
				$this->form_validation->set_rules('eCounty', 'Emergency Contact - ' . localise('county'), 'trim|xss_clean' . required_field('eCounty', $fields, 'validation'));
				$this->form_validation->set_rules('ePostcode', 'Emergency Contact - ' . field_label('ePostcode', $fields, TRUE), 'trim|xss_clean' . required_field('ePostcode', $fields, 'validation') . '|callback_check_postcode');
				$this->form_validation->set_rules('ePhone', 'Emergency Contact - ' . field_label('ePhone', $fields, TRUE), 'trim|xss_clean' . required_field('ePhone', $fields, 'validation'));
				$this->form_validation->set_rules('eMobile', 'Emergency Contact - ' . field_label('eMobile', $fields, TRUE), 'trim|xss_clean' . required_field('eMobile', $fields, 'validation'));
			}

			# equal opportunities
			$this->form_validation->set_rules('equal_ethnic', field_label('equal_ethnic', $fields, TRUE), 'trim|xss_clean' . required_field('equal_ethnic', $fields, 'validation'));
			$this->form_validation->set_rules('equal_disability', field_label('equal_disability', $fields, TRUE), 'trim|xss_clean' . required_field('equal_disability', $fields, 'validation'));
			$this->form_validation->set_rules('equal_source', field_label('equal_source', $fields, TRUE), 'trim|xss_clean' . required_field('equal_source', $fields, 'validation'));

			# misc info
			$this->form_validation->set_rules('medical', field_label('medical', $fields, TRUE), 'trim|xss_clean' . required_field('medical', $fields, 'validation'));
			$this->form_validation->set_rules('tshirtSize', field_label('tshirtSize', $fields, TRUE), 'trim|xss_clean' . required_field('tshirtSize', $fields, 'validation'));
			$this->form_validation->set_rules('onsite', field_label('onsite', $fields, TRUE), 'trim|xss_clean' . required_field('onsite', $fields, 'validation'));

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'title' => set_value('title'),
					'first' => set_value('first'),
					'middle' => set_value('middle'),
					'surname' => set_value('surname'),
					'jobTitle' => set_value('jobTitle'),
					'department' => set_value('department'),
					'brandID' => NULL,
					'nationalInsurance' => set_value('nationalInsurance'),
					'dob' => NULL,
					'email' => set_value('email'),
					'equal_ethnic' => set_value('equal_ethnic'),
					'equal_disability' => $this->input->post('equal_disability')
,
					'equal_source' => set_value('equal_source'),
					'medical' => $this->input->post('medical')
,
					'tshirtSize' => set_value('tshirtSize'),
					'onsite' => 0,
					'non_delivery' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($edit_level_and_login !== TRUE) {
					unset($data['department']);
					unset($data['email']);
				}

				if (!empty(set_value('dob'))) {
					$data['dob'] = uk_to_mysql_date(set_value('dob'));
				}

				if (set_value('brandID') > 0) {
					$data['brandID'] = set_value('brandID');
				}

				if (set_value('onsite') == 1) {
					$data['onsite'] = 1;
				}

				if (set_value('non_delivery') == 1) {
					$data['non_delivery'] = 1;
				}

				$password = NULL;

				// if sending details, set new random password
				if ($edit_level_and_login === TRUE && $this->settings_library->get('send_new_staff') == 1 && set_value('notify') == 1) {
					$password = random_string();
					$data['password'] = $this->auth->encrypt_password($password);
					$data['last_password_change'] = NULL;
				}

				// if setting password manually
				if ($this->auth->account_overridden === TRUE && set_value('password') != '') {
					$password = set_value('password');
					$data['password'] = $this->auth->encrypt_password($password);
					$data['last_password_change'] = NULL;
				}

				// update profile picture
				$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->auth->user->accountID, 500, 500, 50, 50, TRUE, TRUE);

				if ($upload_res !== NULL && $upload_res != 1) {
					$image_data = array(
						'name' => $upload_res['client_name'],
						'path' => $upload_res['raw_name'],
						'type' => $upload_res['file_type'],
						'size' => $upload_res['file_size']*1024,
						'ext' => substr($upload_res['file_ext'], 1)
					);
					$data['profile_pic'] = serialize($image_data);
				}else if($upload_res == 1){
					$error = 'Image size is less than 200px by 200px';
				}

				// if new
				if ($staffID === NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// work out from date for address
				$fromM = set_value('fromM');
				$fromY = set_value('fromY');
				if (!empty($fromM) && !empty($fromY)) {
					$from = $fromY . "-" . $fromM . "-1";
				} else {
					$from = NULL;
				}

				// contact data
				$contact_data = array(
					'type' => 'main',
					'address1' => set_value('address1'),
					'address2' => set_value('address2'),
					'town' => set_value('town'),
					'county' => set_value('county'),
					'postcode' => set_value('postcode'),
					'phone' => set_value('phone'),
					'mobile' => set_value('mobile'),
					'mobile_work' => set_value('mobile_work'),
					'from' => $from,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				// final check for errors
				if (count($errors) == 0 && $error == null) {

					if ($staffID == NULL) {
						// insert id
						$query = $this->db->insert('staff', $data);
					} else {
						$where = array(
							'staffID' => $staffID
						);

						// update
						$query = $this->db->update('staff', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($staffID == NULL) {
							$staffID = $this->db->insert_id();

							// insert contact info
							$contact_data['staffID'] = $staffID;
							$contact_data['byID'] = $this->auth->user->staffID;
							$contact_data['added'] = mdate('%Y-%m-%d %H:%i:%s');

							$this->db->insert('staff_addresses', $contact_data);

							// insert emergency contact if some data
							if (!empty(set_value('eName')) || !empty(set_value('eRelationship')) || !empty(set_value('eAddress1')) || !empty(set_value('eAddress2')) || !empty(set_value('eTown')) || !empty(set_value('eCounty')) || !empty(set_value('ePostcode')) || !empty(set_value('ePhone')) || !empty(set_value('eMobile'))) {
								$emergency_contact_data = array(
									'staffID' => $staffID,
									'byID' => $this->auth->user->staffID,
									'type' => 'emergency',
									'name' => set_value('eName'),
									'relationship' => set_value('eRelationship'),
									'address1' => set_value('eAddress1'),
									'address2' => set_value('eAddress2'),
									'town' => set_value('eTown'),
									'county' => set_value('eCounty'),
									'postcode' => set_value('ePostcode'),
									'phone' => set_value('ePhone'),
									'mobile' => set_value('eMobile'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);

								$this->db->insert('staff_addresses', $emergency_contact_data);
							}

							$success .= set_value('first') . ' ' . set_value('surname') . ' has been created';

						} else {

							// update contact info
							$where = array(
								'staffID' => $staffID,
								'type' => 'main'
							);

							$this->db->update('staff_addresses', $contact_data, $where);


							$success .= set_value('first') . ' ' . set_value('surname') . ' has been updated';
						}

						if ($edit_level_and_login === TRUE && $this->settings_library->get('send_new_staff') == 1 && set_value('notify') == 1 && $this->crm_library->send_staff_welcome_email($staffID, $password)) {
							$success .= ' and notified';
						}

						$success .=  ' successfully.';

						$this->session->set_flashdata('success', $success);

						redirect('staff/edit/' . $staffID);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}else if($error != null){
					$this->session->set_flashdata('error', $error);
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// contact info
		if ($staffID != NULL) {
			$where = array(
				'staffID' => $staffID,
				'type' => 'main',
				'accountID' => $this->auth->user->accountID
			);
			$contact_res = $this->db->from('staff_addresses')->where($where)->limit(1)->get();
			if ($contact_res->num_rows() == 1) {
				$contact_info = $contact_res->result();
				$contact_info = $contact_info[0];
			}
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($staffID != NULL) {
			$or_where[] = '`brandID` = ' . $this->db->escape($staff_info->brandID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$brands = $this->db->from('brands')->where($where, NULL, FALSE)->order_by('name asc')->get();

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
			'contact_info' => $contact_info,
			'brands' => $brands,
			'staffID' => $staffID,
			'edit_level_and_login' => $edit_level_and_login,
			'fields' => $fields,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/staff', $data);
	}

	/**
	 * delete staff
	 * @param  int $staffID
	 * @return mixed
	 */
	public function remove($staffID = NULL) {

		// deny from head coach
		if ($this->auth->user->department == 'headcoach') {
			show_404();
		}

		// check params
		if (empty($staffID)) {
			show_404();
		}

		// can't delete self
		if ($this->auth->user->staffID == $staffID) {
			show_404();
		}

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

			// all ok, delete
			$query = $this->db->delete('staff', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $staff_info->first . ' ' . $staff_info->surname . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $staff_info->first . ' ' . $staff_info->surname . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'staff';

			redirect($redirect_to);
		}
	}

	/**
	 * toggle active status
	 * @param  int $staffID
	 * @param string $value
	 * @return mixed
	 */
	public function active($staffID = NULL, $value = NULL) {

		// deny from head coach
		if ($this->auth->user->department == 'headcoach') {
			show_404();
		}

		// check params
		if (empty($staffID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		// check limit
		if ($value == 'yes' && $this->limit_reached() === TRUE) {
			show_403();
		}

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

			$data = array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['active'] = 1;
			}

			// run query
			$query = $this->db->update('staff', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

	/**
	 * show outstanding equipment bookings
	 * @param  int $staffID
	 * @return mixed
	 */
	public function equipment($staffID = NULL) {

		// deny from head coach
		if ($this->auth->user->department == 'headcoach') {
			show_404();
		}

		// check params
		if (empty($staffID)) {
			show_404();
		}

		// if so, check user exists
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

		$icon = 'futbol';
		$current_page = 'equipment-staff';
		$section = 'staff';
		$type = 'staff';
		$page_base = 'staff/equipment';
		$title = 'Equipment';
		$tab = 'Equipment';
		$buttons = '<a class="btn btn-primary" href="' . site_url('equipment/bookings/recall') . '"> View All Equipment</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
 		);

		// set where
		$where = array(
			'equipment.accountID' => $this->auth->user->accountID,
			'equipment_bookings.staffID' => $staffID,
			'equipment_bookings.status' => 1
		);

		// build search
		$search_fields['staff_id'] = $staffID;
		$search_fields['search'] = 'true';

		// store search fields
		$this->session->set_userdata('search-equipment-bookings', $search_fields);

		// run query
		$res = $this->db->select('equipment.name, equipment_bookings.*')
			->from('equipment_bookings')
			->join('equipment', 'equipment_bookings.equipmentID = equipment.equipmentID', 'inner')
			->where($where)
			->order_by('status desc, added asc')
			->group_by('equipment_bookings.bookingID')
			->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('equipment.name, equipment_bookings.*, CONCAT_WS(\' \', ' . $this->db->dbprefix('staff') . '.first, ' . $this->db->dbprefix('staff') . '.surname) AS staff_label, orgs.name AS org_label, CONCAT_WS(\' \', ' . $this->db->dbprefix('family_contacts') . '.first_name, ' . $this->db->dbprefix('family_contacts') . '.last_name) AS contact_label, CONCAT_WS(\' \', ' . $this->db->dbprefix('family_children') . '.first_name, ' . $this->db->dbprefix('family_children') . '.last_name) AS child_label')
			->from('equipment_bookings')
			->join('equipment', 'equipment_bookings.equipmentID = equipment.equipmentID', 'inner')
			->join('staff', 'equipment_bookings.staffID = staff.staffID', 'left')
			->join('orgs', 'equipment_bookings.orgID = orgs.orgID', 'left')
			->join('family_contacts', 'equipment_bookings.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'equipment_bookings.childID = family_children.childID', 'left')
			->where($where)
			->order_by('status desc, added asc')
			->group_by('equipment_bookings.bookingID')
			->limit($this->pagination_library->amount, $this->pagination_library->start)
			->get();

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
			'staffID' => $staffID,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/equipment', $data);

	}

	/**
	 * jump to timetable for staff
	 * @param  int $staffID
	 * @return mixed
	 */
	public function timetable($staffID = NULL, $show_year = NULL, $show_week = NULL, $only_own = FALSE) {

		// deny from head coach
		if ($this->auth->user->department == 'headcoach') {
			show_404();
		}

		// check params
		if (empty($staffID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select("surname,first")->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// set defaults
		$icon = 'calendar-alt';
		$type = 'staff';
		$current_page = 'timetable';
		$section = 'staff';
		$page_base = 'staff/timetable/'.$staffID;
		$timetable_base = $page_base;
		$title = $staff_info->first . ' ' . $staff_info->surname;
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$lessons = array();
		$list_lessons = [];
		$lesson_count = 0;
		$lesson_hours = '00:00';
		$lesson_seconds = 0;
		$day_seconds = array();
		$day_hours = array();
		$week = date('W');
		$year = date('Y');
		$switch_day = $this->switch_day;
		$view = 'standard';
		$tab = "Timetable";
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
 		);

		// get possible views
		$possible_views = [
			'standard' => [
				'label' => 'Standard',
				'icon' => 'calendar'
			]
		];

		// build search
		$search_fields['staff_id'] = $staffID;
		$search_fields['search'] = 'true';

		// store search fields
		$this->session->set_userdata('search-timetable-bookings', $search_fields);
		$this->session->set_userdata('search-timetable-staff', $staffID);

		// check for view - first param
		if (array_key_exists($show_year, $possible_views)) {
			$view = $show_year;
		}

		// use logged in accountID if not set
		if (empty($this->accountID)) {
			$this->accountID = $this->auth->user->accountID;
		}

		// use logged in staffID if not set
		if (empty($this->staffID)) {
			$this->staffID = $this->auth->user->staffID;
		}

		// if coach or full time, always shown just own
		if (!isset($this->auth->user->department) || in_array($this->auth->user->department, array('fulltimecoach', 'coaching'))) {
			$only_own = 'true';
		}


		// check if view valid
		if (!array_key_exists($view, $possible_views)) {
			show_404();
		}

		$buttons = '<a class="btn btn-primary" href="' . site_url('bookings/timetable/recall') . '" title="Timetable View">Timetable</a>';

		// check if jumping
		if ($this->input->post('week') != '' && $this->input->post('year') != '') {
			$show_week = $this->input->post('week');
			$show_year = $this->input->post('year');
		}

		// In ISO-8601 specification, it says that December 28th is always in the last week of its year
		$max_weeks = gmdate("W", strtotime("28 December " . $show_year));

		// check for override from next/prev
		if (is_numeric($show_week) && $show_week >= 1 && $show_week <= $max_weeks) {
			$week = $show_week;
		}

		if (is_numeric($show_year) && strlen($show_year) == 4) {
			$year = $show_year;
		}

		// double check max weeks if above valid from override
		$max_weeks = gmdate("W", strtotime("28 December " . $year));

		$dt = new DateTime;
		if ($view == 'standard') {
			//$title .= ' - Week ' . $week . ' ' . $year . ' (' . $dt->setISODate($year, $week, 1)->format('jS M') . ')';
			$page_base .= '/' . $year . '/' . $week;
		}

		$dto = new DateTime();
		$dto->setISODate($year, $week);
		$start_date = $dto->format('Y-m-d');
		$dto->modify('+6 days');
		$end_date = $dto->format('Y-m-d');

		// time slots
		$time_slots = array(
			6 => '06:00',
			7 => '07:00',
			8 => '08:00',
			9 => '09:00',
			10 => '10:00',
			11=> '11:00',
			12 => '12:00',
			13 => '13:00',
			14 => '14:00',
			15 => '15:00',
			16 => '16:00',
			17 => '17:00',
			18 => '18:00',
			19 => '19:00',
			20 => '20:00',
			21 => '21:00',
			22 => '22:00',
			23 => '23:00'
		);

		// get day numbers
		$day_numbers = array(
			'monday' => 1,
			'tuesday' => 2,
			'wednesday' => 3,
			'thursday' => 4,
			'friday' => 5,
			'saturday' => 6,
			'sunday' => 7,
		);

		// what week number is next + prev
		if ($week == $max_weeks) {
			$next_week = 1;
			$next_year = $year + 1;
		} else {
			$next_week = $week + 1;
			$next_year = $year;
		}
		if ($week == 1) {
			$prev_week = gmdate("W", strtotime("28 December " . ($year - 1)));;
			$prev_year = $year - 1;
		} else {
			$prev_week = $week - 1;
			$prev_year = $year;
		}

		// work out dates
		$dates = array();
		$days = array(
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
			'sunday'
		);
		$day_date = $start_date;
		foreach ($days as $day) {
			$dates[$day] = $day_date;
			$day_date = date("Y-m-d", strtotime($day_date) + 60*60*24);

			// add base value for times
			$day_seconds[$day] = '0';
			$day_hours[$day] = '00:00';
		}

		// get staff names
		$staff_names = array();
		$where = array(
			'accountID' => $this->accountID,
			'staffID' => $staffID
		);
		$staff = $this->db->from('staff')->where($where)->get();

		if ($staff->num_rows() > 0) {
			foreach ($staff->result() as $s) {
				$staff_names[$s->staffID] = $s->first . ' ' . $s->surname;
			}
		}

		$search_fields = array(
			'staff_id' => $staffID,
			'org' => NULL,
			'type_id' => NULL,
			'name' => NULL,
			'region_id' => NULL,
			'area_id' => NULL,
			'activity_id' => NULL,
			'day' => NULL,
			'staffing_type' => NULL,
			'brand_id' => NULL,
			'search' => NULL,
			'date_from' => date('d/m/Y', strtotime("this week")),
			'date_to' => date('d/m/Y', strtotime("this week + 6 days")),
			'postcode' => NULL,
			'class_size' => NULL,
			'main_contact' => NULL,
			'checkin_status' => NULL,
			'bookings_site' => NULL,
			'search' => NULL
		);

		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_start_from', 'Start From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_start_to', 'Start To', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();


			$search_fields['date_from'] = set_value('search_start_from');
			$search_fields['date_to'] = set_value('search_start_to');
		}

		$date_from_search = $dates['monday'];
		$date_to_search = $dates['sunday'];

		// get session staff
		$lesson_staff = array();
		$where = array(
			'bookings_lessons_staff.startDate <=' => $date_to_search,
			'bookings_lessons_staff.endDate >=' => $date_from_search,
			'bookings_lessons_staff.accountID' => $this->accountID
		);
		$res_staff = $this->db->select('bookings_lessons_staff.*, bookings_lessons.day')->from('bookings_lessons_staff')
			->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			if ($view == 'standard') {
				foreach ($res_staff->result() as $row) {
					// verify actual on session as above just got all staff for whole week
					if (strtotime($row->startDate) <= strtotime($dates[$row->day]) && strtotime($row->endDate) >= strtotime($dates[$row->day])) {
						$lesson_staff[$row->lessonID][$row->staffID] = $row->type;
					}
				}
			} else {
				// custom dates
				foreach ($res_staff->result() as $row) {
					$lesson_staff[$row->lessonID][$row->staffID] = $row->type;
				}
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$where = array(
			'date >=' => $date_from_search,
			'date <=' => $date_to_search,
			'accountID' => $this->accountID
		);
		$res_staff = $this->db->from('bookings_lessons_exceptions')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				$lesson_exceptions[$row->lessonID][] = array(
					'date' => $row->date,
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type
				);
			}
		}


		// set where
		$where = array(
			$this->db->dbprefix('bookings') . '.cancelled !=' => 1,
			$this->db->dbprefix('bookings_blocks') . '.startDate <=' => $date_to_search,
			$this->db->dbprefix('bookings_blocks') . '.endDate >=' => $date_from_search,
			$this->db->dbprefix('bookings') . '.accountID' => $this->accountID
		);

		// if only own, exclude provisional unless turned on at account level
		if ($this->settings_library->get('provisional_own_timetable') != 1 && $only_own === TRUE) {
			$where[$this->db->dbprefix('bookings_blocks') . '.provisional !='] = 1;
		}

		// run query
		$res = $this->db->select('orgs.name as org, orgs.type as org_type, block_orgs.name as block_org,
		 	block_orgs.type as block_org_type, orgs_addresses.*, bookings_lessons.*, bookings.name, bookings.project,
		  	bookings_blocks.orgID as block_orgID, bookings_blocks.provisional, bookings_blocks.name as block,
		   	bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd,
			bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, bookings.brandID,
		 	brands.colour as brand_colour, bookings.type as booking_type, event_address.address1 as event_address1,
		  	event_address.address2 as event_address2, event_address.address3 as event_address3,
		   	event_address.town as event_town, event_address.county as event_county,
			event_address.postcode as event_postcode, orgs.regionID, orgs.areaID,
		 	block_orgs.regionID as block_regionID, block_orgs.areaID as block_areaID, activities.name as activity,
		  	types.name as type_name, contacts.name as main_contact, contacts.tel as main_tel, bookings_lessons.bookingID')
			->from('bookings')
			->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->join('orgs_contacts as contacts', 'orgs.orgID = contacts.orgID and contacts.isMain = 1', 'left')
			->join('brands', 'bookings.brandID = brands.brandID', 'left')
			->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types as types', 'types.typeID = bookings_lessons.typeID', 'left')
			->where($where)
			->group_by('bookings_lessons.lessonID')->get();


		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				$gendate = new DateTime();
				$gendate->setISODate($year, $week, $day_numbers[$row->day]);
				$lesson_date = $gendate->format('Y-m-d');

				// add to array
				$lessons[$row->lessonID] = array(
					'id' => $row->lessonID,
					'day' => $row->day,
					'date' => $dates[$row->day],
					'block' => $row->block,
					'startDate' => $row->blockStart,
					'endDate' => $row->blockEnd,
					'booking_type' => $row->booking_type,
					'project' => $row->project,
					'link' => site_url('coach/session/' . $row->lessonID . '/' . $lesson_date),
					'event' => $row->name,
					'org' => $row->org,
					'org_type' => $row->org_type,
					'address' => NULL,
					'label_classes' => '',
					'colour' => $row->brand_colour,
					'brandID' => $row->brandID,
					'region' => $row->regionID,
					'area' => $row->areaID,
					'activityID' => $row->activityID,
					'activity_group' => NULL,
					'time' => NULL,
					'time_start' => NULL,
					'time_end' => NULL,
					'length' => NULL,
					'has_block_org' => FALSE,
					'staff_ids' => array(),
					'headcoaches' => array(),
					'leadcoaches' => array(),
					'assistantcoaches' => array(),
					'observers' => array(),
					'participants' => array(),
					'offer_accept_status' => NULL,
					'offered_to' => NULL,
					'participants_actual' => 0,
					'participants_target' => $row->target_participants,
					'type_name' => $row->type_name,
					'activity_name' => $row->activity,
					'post_code' => $row->postcode,
					'class_size' => $row->class_size,
					'main_contact' => $row->main_contact,
					'main_tel' => $row->main_tel,
					'booking_id' => $row->bookingID,
					'lesson_start' => $row->lessonStart,
					'lesson_end' => $row->lessonEnd
				);

				// override org if set on block level
				if (!empty($row->block_orgID)) {
					$lessons[$row->lessonID]['has_block_org'] = TRUE;
					$lessons[$row->lessonID]['org'] = $row->block_org;
					$lessons[$row->lessonID]['org_type'] = $row->block_org_type;
				}

				// not checking week dates because of custom date range
				if ($view == 'standard') {
					// double check is within week dates and block dates and session dates (if set)
					if (strtotime($dates[$row->day]) >= strtotime($start_date) && strtotime($dates[$row->day]) <= strtotime($end_date) && strtotime($dates[$row->day]) >= strtotime($row->blockStart) && strtotime($dates[$row->day]) <= strtotime($row->blockEnd) && ((empty($row->lessonStart) && empty($row->lessonEnd)) || strtotime($dates[$row->day]) >= strtotime($row->lessonStart) && strtotime($dates[$row->day]) <= strtotime($row->lessonEnd))) {
						// all ok
					} else {
						unset($lessons[$row->lessonID]);
						continue;
					}
				}

				// get staff
				if (array_key_exists($row->lessonID, $lesson_staff) && is_array($lesson_staff[$row->lessonID])) {
					foreach ($lesson_staff[$row->lessonID] as $staffID => $staffType) {
						$lessons[$row->lessonID]['staff_ids'][$staffID] = $staffType;
					}
				}

				// check for session exceptions (for custom dates we need to handle exceptions in other way)
				if ($view == 'standard') {
					if (array_key_exists($row->lessonID, $lesson_exceptions) && is_array($lesson_exceptions[$row->lessonID])) {
						foreach ($lesson_exceptions[$row->lessonID] as $exception_info) {
							// if cancellation, remove
							if ($exception_info['type'] == 'cancellation') {
								unset($lessons[$row->lessonID]);
								continue 2;
							}

							// staff change
							if (array_key_exists($exception_info['fromID'], $lessons[$row->lessonID]['staff_ids'])) {
								// swap if moved to another staff
								if (!empty($exception_info['staffID'])) {
									$lessons[$row->lessonID]['staff_ids'][$exception_info['staffID']] = $lessons[$row->lessonID]['staff_ids'][$exception_info['fromID']];
								}
								if (isset($lessons[$row->lessonID]['staff_ids'][$exception_info['fromID']])) {
									unset($lessons[$row->lessonID]['staff_ids'][$exception_info['fromID']]);
								}
							}
						}
					}
				}


				// if only showing own, skip, if not in lesson
				if ($only_own === TRUE && !array_key_exists($this->staffID, $lessons[$row->lessonID]['staff_ids'])) {
					unset($lessons[$row->lessonID]);
					continue;
				}

				// loop through staff
				foreach ($lessons[$row->lessonID]['staff_ids'] as $staff_id => $type) {

					// map staff ids to staff names and add to head coach, etc arrays
					if (!array_key_exists($staff_id, $staff_names)) {
						unset($lessons[$row->lessonID]['staff_ids'][$staff_id]);
						continue;
					}

					switch ($type) {
						case 'head':
							$lessons[$row->lessonID]['headcoaches'][] = $staff_names[$staff_id];
							break;
						case 'lead':
							$lessons[$row->lessonID]['leadcoaches'][] = $staff_names[$staff_id];
							break;
						case 'assistant':
						default:
							$lessons[$row->lessonID]['assistantcoaches'][] = $staff_names[$staff_id];
							break;
						case 'observer':
							$lessons[$row->lessonID]['observers'][] = $staff_names[$staff_id];
							break;
						case 'participant':
							$lessons[$row->lessonID]['participants'][] = $staff_names[$staff_id];
							break;
					}
				}

				// sort all staff
				sort($lessons[$row->lessonID]['headcoaches']);
				sort($lessons[$row->lessonID]['leadcoaches']);
				sort($lessons[$row->lessonID]['assistantcoaches']);
				sort($lessons[$row->lessonID]['observers']);
				sort($lessons[$row->lessonID]['participants']);

				// check for min staff
				$staff_reqs_met = TRUE;
				$staff_type_map = [
					'head' => 'headcoaches',
					'lead' => 'leadcoaches',
					'assistant' => 'assistantcoaches',
					'observer' => 'observers',
					'participant' => 'participants'
				];
				$required_staff_for_session = $this->settings_library->get_required_staff_for_session();
				foreach ($required_staff_for_session as $type => $label) {
					$staff_count = 0;
					$field = 'staff_required_' . $type;
					if (isset($lessons[$row->lessonID][$staff_type_map[$type]])) {
						$staff_count = count($lessons[$row->lessonID][$staff_type_map[$type]]);
					}
					if ($row->$field > 0 && $staff_count < $row->$field) {
						$staff_reqs_met = FALSE;
					}
				}
				// min 1 staff
				if (count($lessons[$row->lessonID]['staff_ids']) == 0) {
					$staff_reqs_met = FALSE;
				}
				// if not, add class
				if ($staff_reqs_met !== TRUE) {
					$lessons[$row->lessonID]['label_classes'] .= ' label-nostaff nostaff';
					$lessons[$row->lessonID]['colour'] = "pink";
				}

				// filtering by staff ID
				// able to filter only on standard view, because of exceptions (we can not remove the whole lesson from search in case of custom dates)
				if (!empty($search_fields['staff_id']) && $view == 'standard') {
					if (!array_key_exists($search_fields['staff_id'], $lessons[$row->lessonID]['staff_ids'])) {
						unset($lessons[$row->lessonID]);
						continue;
					}
				}

				// address
				if ($row->booking_type == 'booking') {

					// booking address (from lesson)
					$address_parts = array();
					if (!empty($row->address1)) {
						$address_parts[] = $row->address1;
					}
					if (!empty($row->address2)) {
						$address_parts[] = $row->address2;
					}
					if (!empty($row->address3)) {
						$address_parts[] = $row->address3;
					}
					if (!empty($row->town)) {
						$address_parts[] = $row->town;
					}
					if (!empty($row->county)) {
						$address_parts[] = $row->county;
					}
					if (!empty($row->postcode)) {
						$address_parts[] = $row->postcode;
					}

					if (count($address_parts)) {
						$lessons[$row->lessonID]['address'] = implode(', ', $address_parts);
					}

				} else {

					// event address (from event)
					$event_address_parts = array();
					if (!empty($row->event_address1)) {
						$event_address_parts[] = $row->event_address1;
					}
					if (!empty($row->event_address2)) {
						$event_address_parts[] = $row->event_address2;
					}
					if (!empty($row->event_address3)) {
						$event_address_parts[] = $row->event_address3;
					}
					if (!empty($row->town)) {
						$event_address_parts[] = $row->town;
					}
					if (!empty($row->county)) {
						$event_address_parts[] = $row->county;
					}
					if (!empty($row->postcode)) {
						$event_address_parts[] = $row->postcode;
					}

					if (count($event_address_parts)) {
						$lessons[$row->lessonID]['address'] = implode(', ', $event_address_parts);
					}

				}

				// activity and group
				$activity_group_parts = array();

				if (!empty($row->activity)) {
					$activity_group_parts['activity'] = $row->activity;
				} else if (!empty($row->activity_other)) {
					$activity_group_parts['activity'] = $row->activity_other;
				}
				if (!empty($row->activity_desc)) {
					if (!array_key_exists('activity', $activity_group_parts)) {
						$activity_group_parts['activity'] = NULL;
					}
					$activity_group_parts['activity'] .= ' - ' . $row->activity_desc;
				}

				if (!empty($row->group)) {
					if ($row->group == 'other') {
						$activity_group_parts['group'] = $row->group_other;
					} else {
						$activity_group_parts['group'] = $this->crm_library->format_lesson_group($row->group);
					}
				}

				if (count($activity_group_parts)) {
					$lessons[$row->lessonID]['activity_group'] = implode(', ', $activity_group_parts);
				}

				// if label empty, set to group
				if (empty($lessons[$row->lessonID]['colour'])) {
					$lessons[$row->lessonID]['colour'] = 'light-blue';
				}

				// if viewing own, or searching for specific, change times shown to match theirs
				if ($only_own || !empty($search_fields['staff_id'])) {
					if (!empty($search_fields['staff_id'])) {
						$staffID = $search_fields['staff_id'];
					} else {
						$staffID = $this->staffID;
					}

					// if on this lesson, look up
					if (array_key_exists($staffID, $lessons[$row->lessonID]['staff_ids'])) {
						$where_times = array(
							'lessonID' => $row->lessonID,
							'staffID' => $staffID,
							'startDate <=' => $date_to_search,
							'endDate >=' => $date_from_search,
							'accountID' => $this->accountID
						);
						$res_times = $this->db->from('bookings_lessons_staff')->where($where_times)->get();
						if ($res_times->num_rows() > 0) {
							foreach ($res_times->result() as $row_time) {
								$row->startTime = $row_time->startTime;
								$row->endTime = $row_time->endTime;
							}
						}
					}
				}

				// if provisional, add stripe class
				if ($row->provisional == 1) {
					$lessons[$row->lessonID]['label_classes'] .= ' striped';
				}

				// show offer accept status
				if ($this->auth->has_features('offer_accept') || $this->auth->has_features('offer_accept_manual')) {
					$offer_accept_status = $row->offer_accept_status;
					switch ($offer_accept_status) {
						case 'offering':
							$offer_accept_status = 'offered';
							if (array_key_exists($row->lessonID, $offered_to)) {
								sort($offered_to[$row->lessonID]);
								$lessons[$row->lessonID]['offered_to'] = implode(', ', $offered_to[$row->lessonID]);
							}
							break;
						case 'exhausted':
							$offer_accept_status = 'declined';
							break;
					}
					$lessons[$row->lessonID]['offer_accept_status'] = ucwords($offer_accept_status);
					if (!empty($row->offer_accept_reason)) {
						$lessons[$row->lessonID]['offer_accept_status'] .= ' (' . $row->offer_accept_reason . ')';
					}
				}

				$lessons[$row->lessonID]['time'] = substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0 ,5);
				$lessons[$row->lessonID]['time_start'] = substr($row->startTime, 0, 5);
				$lessons[$row->lessonID]['time_end'] = substr($row->endTime, 0 ,5);
				$lessons[$row->lessonID]['length'] = strtotime($row->endTime) - strtotime($row->startTime);

				// get participants
				if (isset($lesson_participants[$row->lessonID][$dates[$row->day]])) {
					$lessons[$row->lessonID]['participants_actual'] = $lesson_participants[$row->lessonID][$dates[$row->day]];
				}

				// work out times
				$lesson_seconds += $lessons[$row->lessonID]['length'];
				$day_seconds[$row->day] += $lessons[$row->lessonID]['length'];

				// re-arrange keys for session so can add to correct places and sort
				$slot = intval(substr($row->startTime, 0, 2));
				$startTime = substr($row->startTime, 0, 5);
				$list_lessons[$row->lessonID] = $lessons[$row->lessonID];
				$lessons[$row->day][$slot][$startTime . '-' . $row->lessonID] = $lessons[$row->lessonID];
				unset($lessons[$row->lessonID]);

				// increase session count
				$lesson_count++;

			}
		}

		// convert session seconds into time format
		$lesson_hours = sprintf("%02d%s%02d%s", floor($lesson_seconds/3600), 'h', ($lesson_seconds/60)%60, 'm');
		foreach ($days as $day) {
			$day_hours[$day] = sprintf("%02d%s%02d%s", floor($day_seconds[$day]/3600), 'h', ($day_seconds[$day]/60)%60, 'm');
		}

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
			'accountID' => $this->accountID,
			'non_delivery !=' => 1
		);
		// include single inactive staff if searching from staff Timetable tab
		$where_or = [
			"`active` = 1"
		];
		if (isset($search_fields['staff_id']) && !empty($search_fields['staff_id'])) {
			$where_or[] = "`staffID` = " . $this->db->escape($search_fields['staff_id']);
		}
		$where2 = '(' . implode(' OR ', $where_or) . ')';
		$staff = $this->db->from('staff')->where($where)->where($where2, NULL, FALSE)->order_by('first asc, surname asc')->get();

		// regions and areas
		$where = array(
			'accountID' => $this->accountID
		);
		$regions = $this->db->from('settings_regions')->where($where)->order_by('name asc')->get();
		$areas = $this->db->from('settings_areas')->where($where)->order_by('name asc')->get();

		// brands
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// activities
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$activities = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// session types
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$lesson_types = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();

		$markers = [];
		$details_data = [];
		switch ($view) {
			case 'map':
			case 'details':
				if ($only_own) {
					show_404();
				}
				if (!$this->auth->has_features('lesson_checkins')) {
					show_404();
				}
				$search_where = [];
				if (count($list_lessons) > 0) {

					foreach ($list_lessons as $key => $lesson) {
						$dates_between = $this->generateDatesForSearch($lesson, $date_from_search, $date_to_search);

						if (isset($dates_between[$lesson['day']])) {
							foreach ($dates_between[$lesson['day']] as $date) {
								$lesson['date'] = $date;

								if (strtotime($date) >= strtotime($date_from_search) && strtotime($date) <= strtotime($date_to_search) && strtotime($date) >= strtotime($lesson['startDate']) && strtotime($date) <= strtotime($lesson['endDate']) && ((empty($lesson['lesson_start']) && empty($lesson['lesson_end'])) || strtotime($date) >= strtotime($lesson['lesson_start']) && strtotime($date) <= strtotime($lesson['lesson_end']))) {
									// all ok
								} else {
									unset($list_lessons[$key]);
									continue;
								}

								//check exceptions
								if (array_key_exists($lesson['id'], $lesson_exceptions) && is_array($lesson_exceptions[$lesson['id']])) {
									foreach ($lesson_exceptions[$lesson['id']] as $exception_info) {
										// if cancellation, remove
										if ($exception_info['type'] == 'cancellation' && $exception_info['date'] == $date) {
											unset($list_lessons[$key]);
											continue;
										}

										// staff change
										if (array_key_exists($exception_info['fromID'], $lesson['staff_ids'])) {
											// swap if moved to another staff
											if (!empty($exception_info['staffID'])) {
												$lesson['staff_ids'][$exception_info['staffID']] = $lesson['staff_ids'][$exception_info['fromID']];
											}
											if (isset($lesson['staff_ids'][$exception_info['fromID']])) {
												unset($lesson['staff_ids'][$exception_info['fromID']]);
											}
										}
									}

									$lesson['headcoaches'] = [];
									$lesson['leadcoaches'] = [];
									$lesson['assistantcoaches'] = [];
									$lesson['observers'] = [];
									$lesson['participants'] = [];

									//rewrite headcoaches, etc.
									foreach ($lesson['staff_ids'] as $staff_id => $type) {

										// map staff ids to staff names and add to head coach, etc arrays
										if (!array_key_exists($staff_id, $staff_names)) {
											unset($lesson['staff_ids'][$staff_id]);
											continue;
										}

										switch ($type) {
											case 'head':
												$lesson['headcoaches'][] = $staff_names[$staff_id];
												break;
											case 'lead':
												$lesson['leadcoaches'][] = $staff_names[$staff_id];
												break;
											case 'assistant':
											default:
												$lesson['assistantcoaches'][] = $staff_names[$staff_id];
												break;
											case 'observer':
												$lesson['observers'][] = $staff_names[$staff_id];
												break;
											case 'participant':
												$lesson['participants'][] = $staff_names[$staff_id];
												break;
										}
									}
								}
							}
						}
					}


					$ids = array_keys($list_lessons);

					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`lessonID`
					IN (" . implode(',', $ids) . ")";

					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`date` >= " . $this->db->escape($date_from_search);

					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`date` <= " . $this->db->escape($date_to_search);

					$search_where = '(' . implode(' AND ', $search_where) . ')';

					$markers = $this->crm_library->get_checkins([
						'bookings_lessons_checkins.accountID' => $this->accountID
					], $search_where, [
						'date_from' => $date_from_search,
						'date_to' => $date_to_search
					], false);

					$markers = array_values($markers);

					$markers = $this->crm_library->prepare_markers($markers, [
						'date_from' => $date_from_search,
						'date_to' => $date_to_search
					]);

					foreach ($markers as $key => $marker) {
						if (!empty($search_fields['staffing_type'])) {
							if ($marker['role'] != $search_fields['staffing_type']) {
								unset($markers[$key]);
								continue;
							}
						}
						if (!empty($search_fields['staff_id'])) {
							if ($marker['staff_id'] != $search_fields['staff_id']) {
								unset($markers[$key]);
								continue;
							}
						}

						//filtering by checkin status
						if (!empty($search_fields['checkin_status'])) {
							if ($marker['colour'] != $search_fields['checkin_status']) {
								continue;
							}
						}

						$details_data[] = [
							'staff' => $marker['staff'],
							'first_lesson_org' => $marker['orgs'][0],
							'first_lesson_time' => $marker['lesson_times'][0],
							'check_in_times' => $marker['checkin_times'],
							'check_in_status' => $marker['colour'],
							'last_lesson_ord' => array_pop($marker['orgs']),
							'last_lesson_time' => array_pop($marker['lesson_times']),
							'check_out_times' => $marker['checkout_times'],
							'not_checked_in' => $marker['not_checked_in']
						];
					}
				}
				break;
		}

		//get Exceptions Data for staff only
		$availability_exceptions = array();
		if($search_fields['staff_id'] != "" && $search_fields['staff_id'] != NULL){
			$where = array("accountID" => $this->auth->user->accountID,
			"staffID" => $search_fields['staff_id']);
			$res = $this->db->from("staff_availability_exceptions")->where($where)->get();
			if($res->num_rows() > 0){
				foreach($res->result() as $row){
					$availability_exceptions[$row->exceptionsID] = $row;
				}
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'section' => $section,
			'current_page' => $current_page,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'timetable_base' => $timetable_base,
			'lessons' => $lessons,
			'week' => $week,
			'year' => $year,
			'days' => $days,
			'time_slots' => $time_slots,
			'next_week' => $next_week,
			'next_year' => $next_year,
			'prev_week' => $prev_week,
			'prev_year' => $prev_year,
			'lesson_count' => $lesson_count,
			'lesson_hours' => $lesson_hours,
			'day_hours' => $day_hours,
			'staff' => $staff,
			'regions' => $regions,
			'areas' => $areas,
			'brands' => $brands,
			'activities' => $activities,
			'switch_day' => $switch_day,
			'only_own' => $only_own,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'lesson_types' => $lesson_types,
			'view' => $view,
			'markers' => $markers,
			'details_data' => $details_data,
			'max_weeks' => $max_weeks,
			'breadcrumb_levels' => $breadcrumb_levels,
			'tab' => $tab,
			'availability_exceptions' => $availability_exceptions,
			'search' => true
		);
		// load view
		$this->crm_view('staff/timetable', $data);


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
	 * check if either this or another field is filled in
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function phone_or_mobile($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// if both empty, not valid
		if (empty($value) && empty($value2)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		if (empty($postcode)) {
			return TRUE;
		}

		return $this->crm_library->check_postcode($postcode);

	}

	/**
	 * validation function for checking email is unique, except in specified user record
	 * @param  string $email
	 * @param  int $user_id
	 * @return bool
	 */
	public function check_email($email = NULL, $staffID = NULL) {
		// check if parameters
		if (empty($email)) {
			return TRUE;
		}

		// check email not in use with anyone on any account
		$where = array(
			'email' => $email
		);

		// exclude current user, if set
		if (!empty($staffID)) {
			$where['staffID !='] = $staffID;
		}

		// check
		$query = $this->db->get_where('staff', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * validation function for checking if user has permission to set permission level
	 * @param  string $level
	 * @return bool
	 */
	public function check_permission($level = NULL) {
		// check if parameters
		if (empty($level)) {
			return TRUE;
		}

		// check
		switch ($this->auth->user->department) {
			case 'office':
				if (in_array($level, array('directors', 'management'))) {
					return FALSE;
				}
				break;
			case 'management':
				if (in_array($level, array('directors'))) {
					return FALSE;
				}
				break;
		}

		return TRUE;
	}

	/**
	 * check not reached account limit
	 * @return bool
	 */
	public function limit_reached() {

		$limit = $this->auth->account->organisation_size;

		// if 0 or less, is unlimited
		if ($limit <= 0 || empty($limit)) {
			return FALSE;
		}

		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$current = $this->db->from('staff')->where($where)->get()->num_rows();

		if ($current >= $limit) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * log in as staff
	 * @param  int $accountID
	 * @return mixed
	 */
	public function access($staffID = NULL) {

		// check permission
		if (!in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}

		// check params and not self
		if (empty($staffID) || $staffID == $this->auth->user->staffID) {
			show_404();
		}

		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// only allow directors to log in as directors
		if ($staff_info->department == 'directors' && $this->auth->user->department != 'directors') {
			show_404();
		}

		// override user and account id
		$this->session->unset_userdata('search-timetable');
		$this->session->set_userdata('user_id_override', $staffID);
		$this->session->set_userdata('search-timetable-staff',"");

		// go to
		$redirect_to = '/';

		redirect($redirect_to);
	}
}

/* End of file main.php */
/* Location: ./application/controllers/staff/main.php */
