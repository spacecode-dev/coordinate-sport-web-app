<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	private $tabs = [];

	public function __construct() {
		// directors and management only - feature access checked later on as can access default settings via accounts
		parent::__construct(FALSE, array(), array('directors', 'management'));

		// set tabs
		$this->tabs = [
			'emailsms' => [
				'general' => 'General',
				'staff' => 'Staff',
				'customers' => 'Customers',
				'participants' => 'Participants'
			],
			'integrations' => [
				'keys' => 'Keys'
			]
		];
	}

	/**
	 * show list of settings
	 * @return void
	 */
	public function index($type = 'general', $default_section = NULL) {

		// set defaults
		$icon = 'cog';
		$section = 'settings';
		$current_page = $type;
		$title = $type;
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$table = 'accounts_settings';
		$redirect_to = 'settings/' . $type;
		$success_extra = NULL;
		$breadcrumb_levels = array(
			'settings/listing/general' => 'Settings'
		);

		$titles = array(
			'emailsms' => 'Email & SMS',
			'termsprivacy' => 'Terms & Privacy',
			'safety' => 'Health & Safety',
			'dashboard' => 'Dashboard',
			'global' => 'Global',
			'integrations' => 'Integrations',
			'styling' => 'Styling'
		);

		//this is needed for split simple setting page by sections
		$subsections = [
			'participant_privacy' => 'Participant Terms & Privacy',
			'participant_marketing_consent_question' => 'Participant Terms & Privacy',
			'participant_privacy_phone_script' => 'Participant Terms & Privacy',
			'participant_consent_changed_subject' => 'Participant Terms & Privacy Change',
			'participant_consent_changed' => 'Participant Terms & Privacy Change',
			'participant_data_protection_notice' => 'Participant Terms & Privacy',
			'participant_safeguarding' => 'Participant Terms & Privacy',
			'staff_privacy' => 'Staff Terms & Privacy',
			'reconfirm_participant_privacy' => 'Reprompt Terms & Privacy',
			'reconfirm_staff_privacy' => 'Reprompt Terms & Privacy',
			'company' => 'Support',
			'sign_in_page_title' => 'Support',
			'tech_email' => 'Support',
			'company_support_link' => 'Support',
			'company_website' => 'Support',
			'max_invalid_logins' => 'Support',
			'email_from_default' => 'Support',
			'email_reset_password' => 'Support',
			'company_privacy' => 'Coordinate Sport Terms & Privacy Policy',
			'reconfirm_company_privacy' => 'Coordinate Sport Terms & Privacy Policy',
			'cc_processor' => 'Payment',
			'require_full_payment' => 'Payment',
			'gocardless_access_token' => 'GoCardless',
			'gocardless_webhook_secret' => 'GoCardless',
			'gocardless_environment' => 'GoCardless',
			'gocardless_success_redirect' => 'GoCardless',
			'stripe_pk' => 'Stripe',
			'stripe_sk' => 'Stripe',
			'stripe_whs' => 'Stripe',
			'mailchimp_key' => 'Mailchimp',
			'mailchimp_audience_id' => 'Mailchimp',
			'sagepay_environment' => 'Opayo (Sage Pay)',
			'sagepay_vendor' => 'Opayo (Sage Pay)',
			'sagepay_encryption_password' => 'Opayo (Sage Pay)',
			'logo' => 'Styling',
			'body_colour' => 'Styling',
			'contrast_colour' => 'Styling',
			'label_nostaff_colour' => 'Styling',
			'online_booking_header_image' => 'header',
			'online_booking_css' => 'styling_forms',
			'online_booking_header' => 'styling_forms',
			'online_booking_footer' => 'styling_forms',
			'online_booking_meta' => 'styling_forms',
		];

		// if privacy type, only directors have access
		if ($type == 'termsprivacy' && $this->auth->user->department !== 'directors') {
			show_403();
			return;
		}

		// switch type for prettier titles
		if (array_key_exists($title, $titles)) {
			$title = $titles[$title];
		} else {
			$title = ucwords($title);
		}

		// set where
		$where = array(
			'section' => $type,
		);

		// if default settings, check
		if ($this->auth->has_features('accounts') && $type == 'defaults') {
			$where = array(
				'section' => $default_section,
			);
			$title = $titles[$default_section];
			$table = 'settings';
			$redirect_to = 'accounts/defaults/' . $default_section;
			$section = 'accounts';
			$current_page = 'defaults_' . $default_section;
			$breadcrumb_levels = array(
				'accounts' => 'Accounts'
			);
			// else check access to settings or trying to access white label and doesn't have permission
		} else if (!$this->auth->has_features('settings') || ($type == 'styling' && !$this->auth->has_features('whitelabel'))) {
			show_403();
			return;
		}

		// run query
		$settings = $this->db->from('settings')->where($where)->where_not_in('section', [
			'general-main', 'emailsms-main'
		])->order_by('section asc, order asc, title asc')->get();

		$settings_array = [];
		foreach ($settings->result() as $row) {
			$settings_array[$row->key] = $row;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// if posted
		if ($this->input->post()) {

			if ($settings->num_rows() > 0) {
				foreach ($settings->result_array() as $setting) {
					// all ok

					// if read only, skip
					if ($setting['readonly'] == 1) {
						continue;
					}

					// if function, run and continue
					if ($setting['type'] == 'function') {
						if (set_value($setting['key']) == 1) {
							$function = $setting['value'];
							$this->crm_library->$function();
							$success_extra = ' and policy reprompted';
						}
						continue;
					}

					// prepare data
					$data = array(
						'value' => $this->input->post($setting['key'], FALSE),
						'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
					);

					// add blank value if null
					if (is_null($data['value'])) {
						$data['value'] = '';
					}

					switch($setting['type']) {
						case 'date':
						case 'date-monday':
							$data['value'] = uk_to_mysql_date($data['value']);
							break;
						case 'image':
							$shared_path = FALSE;
							if ($type == 'defaults') {
								$shared_path = TRUE;
							}
							if($setting['key'] == 'logo'){
								$setting['max_width'] = 500;
								$setting['max_height'] = 500;
							}
							$upload_res = $this->crm_library->handle_image_upload($setting['key'], $shared_path, FALSE, $setting['max_width'], $setting['max_height']);

							if ($upload_res !== NULL) {
								$image_data = array(
									'name' => $upload_res['client_name'],
									'path' => $upload_res['raw_name'],
									'type' => $upload_res['file_type'],
									'size' => $upload_res['file_size']*1024,
									'ext' => substr($upload_res['file_ext'], 1)
								);
								$data['value'] = serialize($image_data);
							} else if ($this->input->post('remove_' . $setting['key']) == 1) {
								// check if should remove
								$data['value'] = '';
							} else {
								// skip
								continue 2;
							}
							break;
						case 'permission-levels':
							if (is_array($data['value'])) {
								$data['value'] = implode(',', $data['value']);
							}
							break;
					}

					$where = array(
						'key' => $setting['key'],
						'accountID' => $this->auth->user->accountID
					);

					if ($type == 'defaults') {
						unset($where['accountID']);
					}

					// check if exists
					$res = $this->db->from($table)->where($where)->get();

					if ($res->num_rows() > 0) {
						// update
						$query = $this->db->update($table, $data, $where);
					} else {
						// insert
						$data['accountID'] = $this->auth->user->accountID;
						$data['key'] = $setting['key'];
						$data['created_at'] = mdate('%Y-%m-%d %H:%i:%s');
						$query = $this->db->insert($table, $data);
					}
				}
			}

			$this->session->set_flashdata('success', $title . ' settings have been updated successfully' . $success_extra);

			redirect($redirect_to);

			return TRUE;

		}

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		// if default, dont show staff
		if ($this->auth->has_features('accounts') && $type == 'defaults') {
			$where['accountID'] = -1;
		}
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		// if default, dont show staff
		if ($this->auth->has_features('accounts') && $type == 'defaults') {
			$where['accountID'] = -1;
		}
		$brand_list = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// get tabs
		$tabs = [];
		if (array_key_exists($current_page, $this->tabs)) {
			$tabs = $this->tabs[$current_page];
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'type' => $type,
			'buttons' => $buttons,
			'settings' => $settings,
			'staff_list' => $staff_list,
			'brand_list' => $brand_list,
			'titles' => $titles,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'sections' => [],
			'settings_array' => $settings_array,
			'default_section' => $default_section,
			'subsections' => $subsections,
			'tabs' => $tabs
		);

		// load view
		$this->crm_view('settings/main', $data);
	}

	public function listing($section = NULL, $subsection = NULL, $type = NULL) {
		// set defaults
		$icon = 'cog';
		$current_page = $section;
		$title = NULL;
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$table = 'accounts_settings';
		$menu_section ='settings';

		if ($type == 'defaults') {
			$menu_section = 'accounts';
			$current_page = 'defaults_' . $section;
			$section = str_replace('defaults_', '', $section);
		}

		$breadcrumb_levels = array(
			'settings/listing/general' => 'Settings'
		);

		if ($type == 'defaults') {
			$breadcrumb_levels = array(
				'accounts' => 'Accounts'
			);
		}

		$pages_with_subsections = [
			'emailsms',
			'general'
		];
		
		$mileage_section = 0;
		$mileage_account = $this->db->select("*")->from("accounts")->where("accountID", $this->auth->user->accountID)->get();
		foreach($mileage_account->result() as $result){
			$mileage_section = $result->addon_mileage;
		}
		$flag = 1;
		if($subsection == 'timesheets_general' && $mileage_section == 1)
			$flag = 0;

		$nested_subsection_pages = [
			'departments_emailsms'
		];
		if ($subsection == 'defaults') {
			$subsection = null;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$titles = [
			'emailsms' => 'Email & SMS',
			'general' => 'General',
			'dashboard' => 'Dashboard'
		];

		$title = $titles[$section];

		// set where
		$where = array(
			'section' => $section,
			'section !=' => 'global'
		);
		
		$keyArray = array(
			'excluded_mileage',
			'excluded_mileage_without_fuel_card',
			'mileage_default_start_location',
			'mileage_default_mode_of_transport',
			'mileage_activate_fuel_cards',
			'mileage_default_address1',
			'mileage_default_address2',
			'mileage_default_county',
			'mileage_default_town',
			'automatically_approve_fuel_card',
			'mileage_default_postcode',
		);
		
		$settings = $this->db->from('settings') ->where($where)->order_by('section asc, order asc, title asc')->get();

		$settings_array = [];
		foreach ($settings->result() as $row) {
			$settings_array[$row->key] = $row;
		}

		$sections = $this->settings_library->get_main_sections($section);

		$subsections_data = [];
		if (!empty($subsection)) {
			$subsection_info = $this->settings_library->get_field_info($subsection);
			if ($subsection_info) {
				$title = $subsection_info->title;
				if ($type == 'defaults') {
					$breadcrumb_levels['accounts/defaults/listing/' . $section] = $titles[$section];
				} else {
					$breadcrumb_levels['settings/listing/' . $section] = $titles[$section];
				}
			}
			$subsections_data = $this->settings_library->get_subsection_data($section, $subsection);

			$this->load->library('qualifications_library');
			//dynamically add additional info
			foreach ($subsections_data as $key => $field) {
				if ($field->key == 'email_new_booking') {
					$tags = array_filter($this->qualifications_library->getAllTags($this->auth->user->accountID));
					if (!empty($tags)) {
						$subsections_data[$key]->instruction = $subsections_data[$key]->instruction . ', {' . implode('}, {', $tags) . '}';
					}
				}
			}
		}
		
		// if default settings, check
		if ($this->auth->has_features('accounts') && $type == 'defaults') {
			$table = 'settings';
			// else check access to settings or trying to access white label and doesn't have permission
		}

		$errors = array();
		if ($this->input->post()) {
			
			if (empty($subsection)) {
				//get all checkboxes that can be updated
				$checkboxes = [];
				foreach ($sections as $item) {
					if (!empty($item->toggle_fields)) {
						$toggle_fields = array_filter(explode(',', $item->toggle_fields));
						if (count($toggle_fields) > 0) {
							foreach ($toggle_fields as $toggle_field) {
								$checkboxes[] = $toggle_field;
							}
						}
					}
				}

				foreach ($checkboxes as $checkbox) {
					$where = array(
						'key' => $checkbox,
						'accountID' => $this->auth->user->accountID
					);
					if ($type == 'defaults') {
						unset($where['accountID']);
					}

					// check if exists
					$res = $this->db->from($table)->where($where)->get();

					// prepare data
					$data = array(
						'value' => $this->input->post($checkbox, FALSE),
						'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
					);

					// if within a multiple toggle field, look for key within each checkbox posted
					foreach($this->input->post() as $key => $val) {
						if (strpos($key, $checkbox) !== FALSE) {
							// if found, update
							$data['value'] = $this->input->post($key, FALSE);
							break;
						}
					}

					// add blank value if null
					if (is_null($data['value'])) {
						$data['value'] = '';
					}

					if ($res->num_rows() > 0) {
						// update
						$query = $this->db->update($table, $data, $where);
						
					} else {
						// insert
						$data['accountID'] = $this->auth->user->accountID;
						$data['key'] = $checkbox;
						$data['created_at'] = mdate('%Y-%m-%d %H:%i:%s');
						$query = $this->db->insert($table, $data);
					}
				}
			} else {
				
				if($this->auth->has_features("mileage") == 1){
					$this->load->library('form_validation');
					$this->form_validation->set_rules('mileage_default_mode_of_transport', 'Default Mode of Transport', 'trim|xss_clean|required');
					$this->form_validation->set_rules('mileage_default_start_location', 'Default Start Location', 'trim|xss_clean|required');
					if ($this->form_validation->run() === FALSE) {
						$errors = $this->form_validation->error_array();
					}
				}
				if(count($errors) == 0){
					foreach ($subsections_data as $item) {
						$where = array(
							'key' => $item->key,
							'accountID' => $this->auth->user->accountID
						);
						if ($type == 'defaults') {
							unset($where['accountID']);
						}
						// check if exists
						$res = $this->db->from($table)->where($where)->get();

						// get value
						$val = $this->input->post($item->key, FALSE);
						switch($item->type) {
							case 'permission-levels':
								if (is_array($val)) {
									$val = implode(',', $val);
								}
								break;
						}

						// prepare data
						$data = array(
							'value' => $val,
							'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
						);

						// add blank value if null
						if (is_null($data['value'])) {
							$data['value'] = '';
						}
						
						if ($res->num_rows() > 0) {
							// update
							$query = $this->db->update($table, $data, $where);
						} else {
							// insert
							$data['accountID'] = $this->auth->user->accountID;
							$data['key'] = $item->key;
							$data['created_at'] = mdate('%Y-%m-%d %H:%i:%s');
							$query = $this->db->insert($table, $data);
						}
					}
				}
			}
			
			if(count($errors) == 0){
				$this->session->set_flashdata('success', $title . ' settings have been updated successfully.');

				if ($type == 'defaults') {
					redirect('accounts/defaults/listing/' . $section);
				} else {
					redirect('settings/listing/' . $section);
				}
				return TRUE;
			}

		}

		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$brand_list = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// get tabs
		$tabs = [];
		if (array_key_exists($current_page, $this->tabs)) {
			$tabs = $this->tabs[$current_page];
		}

		$mileage_list = $this->db->from('mileage')->where($where)->get();

		//Customers org type
		$customers_org_type = array();
		if($subsection === "customers_general"){
			$where = array(
				'accountID' => $this->auth->user->accountID
			);
			$customers_org_type = $this->db->select('settings_customer_types.*')
				->from('settings_customer_types')
				->where($where)
				->get();
		}

		//Subscection Pages
		$department_data = NULL;
		if (!empty($subsection) && in_array($subsection, $nested_subsection_pages) && count($subsections_data) > 0) {
			$where = array(
				$this->db->dbprefix('settings_departments_email').'.accountID' => $this->auth->user->accountID
			);
			$department_data = $this->db->select('settings_departments_email.*, GROUP_CONCAT('.$this->db->dbprefix('brands').'.name) as brand_name, GROUP_CONCAT('.$this->db->dbprefix('brands').'.colour) as brand_colors')->from('settings_departments_email')
				->join('settings_departments_relation', 'settings_departments_email.ID = settings_departments_relation.department_email_id', 'Left')
				->join('brands', 'settings_departments_relation.departmentID = brands.brandID', 'left')
				->where($where)
				->order_by('settings_departments_email.added asc')
				->group_by('settings_departments_email.ID')
				->get();
			$buttons = '<a class="btn btn-success" href="' . site_url('settings/subsection/departments_emailsms/create') . '"><i class="far fa-plus"></i> Create New</a>';
		}

		if ($type == 'defaults') {
			$form_action = 'accounts/defaults/listing/' . $current_page . '/' . $subsection;
		} else {
			$form_action = 'settings/listing/' . $current_page . '/' . $subsection;
		}

		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $menu_section,
			'type' => $type,
			'subsection' => $subsection,
			'buttons' => $buttons,
			'settings' => $settings,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'settings_array' => $settings_array,
			'customers_org_type' => $customers_org_type,
			'sections' => $sections,
			'subsection_data' => $subsections_data,
			'brand_list' => $brand_list,
			'tabs' => $tabs,
			'flag' => $flag,
			'keyArray' => $keyArray,
			'mileage_list' => $mileage_list,
			'department_data' => $department_data,
			'form_action' => $form_action
		);

		if (!empty($subsection) && in_array($subsection, $nested_subsection_pages)) {
			$this->crm_view('settings/department_subsection_pages', $data);
		}else if (empty($subsection) && in_array($section, $pages_with_subsections)) {
			$this->crm_view('settings/sections_main_table', $data);
		} else {
			$this->crm_view('settings/subsection', $data);
		}
	}

	/**
	 * show list of subsections
	 * @return void
	 */
	public function subsection($subsection = NULL, $actions = NULL, $entry_id = NULL)
	{
		if($actions == "active"){
			$IDs = $this->input->post('status');

			$where = array(
				'accountID' => $this->auth->user->accountID
			);
			$department_status_data = $this->db->select('settings_departments_email.*')
				->from('settings_departments_email')
				->where($where)
				->get();
			if($department_status_data->num_rows() > 0){
				foreach ($department_status_data->result() as $item){
					$data = array();
					if(is_array($IDs) && in_array($item->ID, $IDs)){
						$data['active'] = '1';
					}else{
						$data['active'] = '0';
					}
					$where = array(
						'accountID' => $this->auth->user->accountID,
						'ID' => $item->ID
					);
					// update
					$table = 'settings_departments_email';
					$query = $this->db->update($table, $data, $where);
				}
			}
			$this->session->set_flashdata('success','Department settings have been updated successfully.');
			redirect('settings/listing/emailsms/departments_emailsms');
		}
		$section = 'emailsms';
		$type = NULL;
		// set defaults
		$icon = 'cog';
		$current_page = $section;
		$title = NULL;
		$buttons = NULL;
		$success = NULL;
		$errors = array();
		$info = NULL;
		$table = 'accounts_settings';
		$menu_section ='settings';
		if ($type == 'defaults') {
			$menu_section = 'accounts';
			$current_page = 'defaults_' . $section;
			$section = str_replace('defaults_', '', $section);
		}
		if($actions == "create"){
			$form_action = '/settings/subsection/departments_emailsms/create';
		}else{
			$form_action = '/settings/subsection/departments_emailsms/edit/'.$entry_id;
		}

		if($actions == "edit" && empty($entry_id)){
			show_404();
		}

		$breadcrumb_levels = array(
			'settings/listing/general' => 'Settings'
		);

		if ($type == 'defaults') {
			$breadcrumb_levels = array(
				'accounts' => 'Accounts'
			);
		}

		$pages_with_subsections = [
			'emailsms',
			'general'
		];

		if ($subsection == 'defaults') {
			$subsection = null;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$errors[] = $this->session->flashdata('error');
		}

		$titles = [
			'emailsms' => 'Email & SMS',
			'general' => 'General',
			'dashboard' => 'Dashboard'
		];

		$title = $titles[$section];

		// set where
		$where = array(
			'section' => $section,
			'section !=' => 'global',
		);

		$settings = $this->db->from('settings') ->where($where)->order_by('section asc, order asc, title asc')->get();

		$settings_array = [];
		foreach ($settings->result() as $row) {
			$settings_array[$row->key] = $row;
		}

		$sections = $this->settings_library->get_main_sections($section);

		$subsections_data = [];
		if (!empty($subsection)) {
			$subsection_info = $this->settings_library->get_field_info($subsection);
			if ($subsection_info) {
				$title = $subsection_info->title;
				if ($type == 'defaults') {
					$breadcrumb_levels['accounts/defaults/listing/' . $section] = $titles[$section];
				} else {
					$breadcrumb_levels['settings/listing/' . $section] = $titles[$section];
					$breadcrumb_levels['settings/listing/emailsms/departments_emailsms'] = 'Departments';
				}
			}
			$subsections_data = $this->settings_library->get_subsection_data($section, $subsection);

			$this->load->library('qualifications_library');
			//dynamically add additional info
			foreach ($subsections_data as $key => $field) {
				if ($field->key == 'email_new_booking') {
					$tags = array_filter($this->qualifications_library->getAllTags($this->auth->user->accountID));
					if (!empty($tags)) {
						$subsections_data[$key]->instruction = $subsections_data[$key]->instruction . ', {' . implode('}, {', $tags) . '}';
					}
				}
			}
		}

		$selected_department = array();
		if($actions == "edit"){
			// set where
			$where = array(
				$this->db->dbprefix('settings_departments_email').'.accountID' => $this->auth->user->accountID,
				$this->db->dbprefix('settings_departments_email').'.ID' => $entry_id
			);
			$department_entry_data = $this->db->select('settings_departments_email.*,
				GROUP_CONCAT('.$this->db->dbprefix('brands').'.name) as brand_name')
				->from('settings_departments_email')
				->join('settings_departments_relation', 'settings_departments_email.ID = settings_departments_relation.department_email_id', 'Left')
				->join('brands', 'settings_departments_relation.departmentID = brands.brandID', 'left')
				->where($where)
				->order_by('settings_departments_email.added asc')
				->group_by('settings_departments_email.ID')
				->get();
			if($department_entry_data->num_rows() > 0){
				foreach($department_entry_data->result() as $item) break;
				foreach ($subsections_data as $key => $field) {
					if($field->key == 'department_email_name') {
						$subsections_data[$key]->value = $item->name;
					}
					if($field->key == 'department_email_from_name') {
						$subsections_data[$key]->value = $item->from_name;
					}
					if($field->key == 'department_email_from') {
						$subsections_data[$key]->value = $item->reply_email;
					}
					if($field->key == 'department_sms_from') {
						$subsections_data[$key]->value = $item->sms_name;
					}
					if($field->key == 'department_list') {
						$subsections_data[$key]->value = $item->brand_name;
					}
					if($field->key == 'department_email_footer') {
						$subsections_data[$key]->value = $item->email_footer;
					}
					if($field->key == 'department_email_active') {
						$subsections_data[$key]->value = $item->active;
					}
				}
			}else{
				show_404();
			}

			// set where
			$where = array(
				$this->db->dbprefix('settings_departments_relation').'.accountID' => $this->auth->user->accountID,
				$this->db->dbprefix('settings_departments_relation').'.ID <>' => $entry_id
			);
			$selected_department = $this->db->select('GROUP_CONCAT('.$this->db->dbprefix('brands').'.name) as brand_name')
				->from('settings_departments_relation')
				->join('brands', 'settings_departments_relation.departmentID = brands.brandID', 'left')
				->where($where)
				->get();
		}else{
			// set where
			$where = array(
				$this->db->dbprefix('settings_departments_relation').'.accountID' => $this->auth->user->accountID,
			);
			$selected_department = $this->db->select('GROUP_CONCAT('.$this->db->dbprefix('brands').'.name) as brand_name')
				->from('settings_departments_relation')
				->join('brands', 'settings_departments_relation.departmentID = brands.brandID', 'left')
				->where($where)
				->get();
		}

		// if default settings, check
		if ($this->auth->has_features('accounts') && $type == 'defaults') {
			$table = 'settings';
			// else check access to settings or trying to access white label and doesn't have permission
		}

		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('department_email_name ', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('department_email_from_name', 'Send Emails From Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('department_email_from', 'Reply Email Address', 'trim|xss_clean|required');
			$this->form_validation->set_rules('department_sms_from', 'Send SMS From Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('department_email_footer', 'Email Footer', 'trim|xss_clean|required');
			$departments = $this->input->post('department_list');

			if ($this->form_validation->run() === FALSE) {
				$errors = $this->form_validation->error_array();
			}
			if(!isset($departments) || count($departments) === 0){
				$errors[] =  'Department field is empty.';
			}
			if(empty($this->input->post('department_email_name'))){
				$errors[] =  'Name field is empty.';
			}

			if(count($errors) == 0)
			{
				$data = array();
				$data['accountID'] = $this->auth->user->accountID;
				$data['name'] = set_value('department_email_name');
				$data['from_name'] = set_value('department_email_from_name');
				$data['reply_email'] = set_value('department_email_from');
				$data['sms_name'] = set_value('department_sms_from');
				$data['email_footer'] = $this->input->post('department_email_footer');
				$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				$relation_id = NULL;
				if ($actions === "edit") {
					$where = array(
						'accountID' => $this->auth->user->accountID,
						'ID' => $entry_id
					);
					// update
					$table = 'settings_departments_email';
					$query = $this->db->update($table, $data, $where);
					$relation_id = $entry_id;
				} else {
					// insert
					$table = 'settings_departments_email';
					$query = $this->db->insert($table, $data);
					$relation_id = $this->db->insert_id();
				}

				$departments = $this->input->post('department_list');
				if(!empty($relation_id)){
					if ($actions === "edit") {
						$table = 'settings_departments_relation';
						$where = array(
							'accountID' => $this->auth->user->accountID,
							'department_email_id' => $entry_id
						);
						//remove
						$this->db->delete($table, $where);
					}
					foreach($departments as $department){
						$relation_data = array();
						$relation_data['departmentID'] = $department;
						$relation_data['department_email_id'] = $relation_id;
						$relation_data['modified'] = mdate('%Y-%m-%d %H:%i:%s');
						$relation_data['accountID'] = $this->auth->user->accountID;
						$table = 'settings_departments_relation';
						$relation_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						// insert
						$query = $this->db->insert($table, $relation_data);
					}
				}else{
					$this->session->set_flashdata('Error', $title . ' settings are not updated. Please contact support team.');
					redirect('settings/listing/emailsms/departments_emailsms');
					return TRUE;
				}

				$this->session->set_flashdata('success', $title . ' settings have been updated successfully.');
				redirect('settings/listing/emailsms/departments_emailsms');

				return TRUE;
			}
		}

		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$brand_list = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// get tabs
		$tabs = [];
		if (array_key_exists($current_page, $this->tabs)) {
			$tabs = $this->tabs[$current_page];
		}

		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $menu_section,
			'type' => $type,
			'subsection' => $subsection,
			'buttons' => $buttons,
			'settings' => $settings,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'settings_array' => $settings_array,
			'sections' => $sections,
			'subsection_data' => $subsections_data,
			'brand_list' => $brand_list,
			'tabs' => $tabs,
			'form_action' => $form_action,
			'selected_department' => $selected_department,
		);

		if (empty($subsection) && in_array($section, $pages_with_subsections)) {
			$this->crm_view('settings/sections_main_table', $data);
		} else {
			$this->crm_view('settings/subsection', $data);
		}
	}
	
	/* Add/Edit New Mileage */
	
	public function listing_new($section = NULL, $subsection = NULL, $id = NULL){
		
		$icon = 'cog';
		$current_page = $section;
		$title = NULL;
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$menu_section ='settings';
		$return_to = 'settings/listing/'.$section."/".$subsection;
		
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		
		$breadcrumb_levels = array(
			'settings/listing/general' => 'Settings'
		);
		
		if($section != "general" || $subsection != "timesheets_general"){
			show_404();
		}
		$errors = '';
		if ($this->input->post()) {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('rate', 'Rate', 'trim|xss_clean|required|is_numeric');
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$id = set_value("id");
			
			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			}else{
				if($id == 'new'){
					$data = array("name" => set_value("name"),
					"rate" => set_value("rate"),
					"created" => mdate('%Y-%m-%d %H:%i:%s'),
					"modified" => mdate('%Y-%m-%d %H:%i:%s'),
					"accountID" => $this->auth->user->accountID
					);
					
					$this->db->insert("mileage", $data);
					
				}else{
					$data = array("rate" => set_value("rate"),
					"name" => set_value("name"),
					"modified" => mdate('%Y-%m-%d %H:%i:%s')
					);
					
					$where = array("mileageID" => $id,
					"accountID" => $this->auth->user->accountID);
					
					$this->db->update("mileage", $data, $where);
				}
				redirect("settings/listing/".$section."/".$subsection);
			}
			
			
		}
		
		$rate = $name = '';
		if($id == 'new'){
			$mileage_data = array();
		}else{
			$where = array(
			'accountID' => $this->auth->user->accountID,
			'mileageID' => $id
			);
			$mileage_data = $this->db->select("*")->from("mileage")->where($where)->get();
			foreach($mileage_data->result() as $row){
				$rate = $row->rate;
				$name = $row->name;
			}
		}
		
		// get tabs
		$tabs = [];
		if (array_key_exists($current_page, $this->tabs)) {
			$tabs = $this->tabs[$current_page];
		}
		
		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}
		
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $menu_section,
			'sections' => $section,
			'subsection' => $subsection,
			'buttons' => $buttons,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'tabs' => $tabs,
			'id' => $id,
			'mileage_data' => $mileage_data,
			'rate' => $rate,
			'return_to' => $return_to,
			'name' => $name
		);
		
		$this->crm_view('settings/mileage', $data);
		
	}
	
	// Remove Mileage IDs
	public function listing_remove($section = NULL, $subsection = NULL, $id = NULL){
		if($section != "general" || $subsection != "timesheets_general"){
			show_404();
		}
		
		$where = array(
			'mileageID' => $id,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('mileage')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}
		
		// match
		foreach ($query->result() as $row) {
			$mileage_info = $row;

			// all ok, delete
			$query = $this->db->delete('mileage', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $mileage_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $mileage_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			redirect("settings/listing/".$section."/".$subsection);
		}
		
		
	}
	
	
	/**
	 * show list of regions
	 * @return void
	 */
	public function regions() {

		// set defaults
		$icon = 'cog';
		$current_page = 'regions';
		$section = 'settings';
		$page_base = 'settings/regions';
		$title = 'Regions';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/regions/new') . '"><i class="far fa-plus"></i> Create New</a>';
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-regions'))) {

			foreach ($this->session->userdata('search-regions') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-regions', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
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
		$res = $this->db->from('settings_regions')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('settings_regions')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'regions' => $res,
			'page_base' => $page_base,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/regions', $data);
	}

	/**
	 * edit region
	 * @param  int $regionID
	 * @return void
	 */
	public function edit_region($regionID = NULL)
	{

		$region_info = new stdClass;

		// check if editing
		if ($regionID != NULL) {

			// check if numeric
			if (!ctype_digit($regionID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'regionID' => $regionID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('settings_regions')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$region_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Region';
		$submit_to = 'settings/regions/new/';
		$return_to = 'settings/regions';
		if ($regionID != NULL) {
			$title = $region_info->name;
			$submit_to = 'settings/regions//edit/' . $regionID;
		}
		$icon = 'cog';
		$tab = 'details';
		$current_page = 'regions';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/regions' => 'Regions'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				// if new
				if ($regionID === NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($regionID == NULL) {
						// insert id
						$query = $this->db->insert('settings_regions', $data);
					} else {
						$where = array(
							'regionID' => $regionID
						);

						// update
						$query = $this->db->update('settings_regions', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($regionID == NULL) {

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
			'region_info' => $region_info,
			'regionID' => $regionID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/region', $data);
	}

	/**
	 * delete region
	 * @param  int $regionID
	 * @return mixed
	 */
	public function remove_region($regionID = NULL) {

		// check params
		if (empty($regionID)) {
			show_404();
		}

		$where = array(
			'regionID' => $regionID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_regions')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$region_info = $row;

			// all ok, delete
			$query = $this->db->delete('settings_regions', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $region_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $region_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/regions';

			redirect($redirect_to);
		}
	}

	/**
	 * show list of areas
	 * @return void
	 */
	public function areas() {

		// set defaults
		$icon = 'cog';
		$current_page = 'areas';
		$section = 'settings';
		$page_base = 'settings/areas';
		$title = 'Areas';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/areas/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		// set where
		$where = array(
			'settings_areas.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'region_id' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_region_id', 'Region', 'trim|xss_clean');
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['region_id'] = set_value('search_region_id');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-areas'))) {

			foreach ($this->session->userdata('search-areas') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-areas', $search_fields);

			if ($search_fields['region_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix('settings_areas') . "`.`regionID` LIKE '%" . $this->db->escape_like_str($search_fields['region_id']) . "%'";
			}

			if ($search_fields['name'] != '') {
				$search_where[] = "`" . $this->db->dbprefix('settings_areas') . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
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
		$res = $this->db->select('settings_areas.*, settings_regions.name as region')->from('settings_areas')->join('settings_regions', 'settings_areas.regionID = settings_regions.regionID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('settings_areas.*, settings_regions.name as region')->from('settings_areas')->join('settings_regions', 'settings_areas.regionID = settings_regions.regionID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get regions
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$regions = $this->db->from('settings_regions')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'areas' => $res,
			'page_base' => $page_base,
			'regions' => $regions,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/areas', $data);
	}

	/**
	 * edit area
	 * @param  int $areaID
	 * @return void
	 */
	public function edit_area($areaID = NULL)
	{

		$area_info = new stdClass;

		// check if editing
		if ($areaID != NULL) {

			// check if numeric
			if (!ctype_digit($areaID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'areaID' => $areaID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('settings_areas')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$area_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Area';
		$submit_to = 'settings/areas/new/';
		$return_to = 'settings/areas';
		if ($areaID != NULL) {
			$title = $area_info->name;
			$submit_to = 'settings/areas//edit/' . $areaID;
		}
		$icon = 'cog';
		$tab = 'details';
		$current_page = 'areas';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/areas' => 'Areas'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('regionID', 'Region', 'trim|xss_clean|required');
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'regionID' => set_value('regionID'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				// if new
				if ($areaID === NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($areaID == NULL) {
						// insert id
						$query = $this->db->insert('settings_areas', $data);
					} else {
						$where = array(
							'areaID' => $areaID
						);

						// update
						$query = $this->db->update('settings_areas', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($areaID == NULL) {

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

		// get regions
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$regions = $this->db->from('settings_regions')->where($where)->order_by('name asc')->get();

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
			'area_info' => $area_info,
			'regions' => $regions,
			'areaID' => $areaID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/area', $data);
	}

	/**
	 * delete area
	 * @param  int $areaID
	 * @return mixed
	 */
	public function remove_area($areaID = NULL) {

		// check params
		if (empty($areaID)) {
			show_404();
		}

		$where = array(
			'areaID' => $areaID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_areas')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$area_info = $row;

			// all ok, delete
			$query = $this->db->delete('settings_areas', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $area_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $area_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/areas';

			redirect($redirect_to);
		}
	}

	/**
	 * show list of dashboard triggers
	 * @return void
	 */
	public function dashboard_triggers($type = NULL) {

		// set defaults
		$icon = 'cog';
		$section = 'settings';
		$current_page = 'settings_dashboard';
		$title = 'Dashboard Triggers';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$table = 'accounts_settings_dashboard';
		$redirect_to = 'settings/dashboardtriggers';
		$breadcrumb_levels = array(
			'settings/general' => 'Settings'
		);

		// if default settings, check
		if ($this->auth->has_features('accounts') && $type == 'defaults') {
			$title = 'Default Dashboard Triggers';
			$table = 'settings_dashboard';
			$redirect_to = 'accounts/dashboardtriggers';
			$section = 'accounts';
			$current_page = 'defaults_dashboardtriggers';
			$breadcrumb_levels = array(
				'accounts' => 'Accounts'
			);
		}

		// run query
		$settings = $this->db->from('settings_dashboard')->order_by('section asc, order asc, title asc')->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// if posted
		if ($this->input->post()) {

			if ($settings->num_rows() > 0) {
				foreach ($settings->result_array() as $setting) {
					$value_amber = NULL;
					$value_red = NULL;
					$values = $this->input->post($setting['key']);
					if (isset($values['amber']['num'], $values['amber']['interval']) && is_numeric($values['amber']['num']) && (($setting['positive_only'] == 1 && $values['amber']['num'] >= 0) || $setting['positive_only'] == 0) && in_array($values['amber']['interval'], array('day', 'week', 'month', 'year'))) {
						$value_amber = intval($values['amber']['num']) . ' ' . $values['amber']['interval'];
					}
					if (isset($values['red']['num'], $values['red']['interval']) && is_numeric($values['red']['num']) && (($setting['positive_only'] == 1 && $values['red']['num'] >= 0) || $setting['positive_only'] == 0) && in_array($values['red']['interval'], array('day', 'week', 'month', 'year'))) {
						$value_red = intval($values['red']['num']) . ' ' . $values['red']['interval'];
					}

					// all ok, prepare data
					$data = array(
						'value_amber' => $value_amber,
						'value_red' => $value_red,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					$where = array(
						'key' => $setting['key'],
						'accountID' => $this->auth->user->accountID
					);

					if ($type == 'defaults') {
						unset($where['accountID']);
					}

					// check if exists
					$res = $this->db->from($table)->where($where)->get();

					if ($res->num_rows() > 0) {
						// update
						$query = $this->db->update($table, $data, $where);
					} else {
						// insert
						$data['accountID'] = $this->auth->user->accountID;
						$data['key'] = $setting['key'];
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$query = $this->db->insert($table, $data);
					}
				}
			}

			$this->session->set_flashdata('success', 'Dashboard triggers have been updated successfully.');

			redirect($redirect_to);

			return TRUE;

		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'type' => $type,
			'buttons' => $buttons,
			'settings' => $settings,
			'submit_to' => $redirect_to,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/dashboard', $data);
	}

}

/* End of file Main.php */
/* Location: ./application/controllers/settings/Main.php */
