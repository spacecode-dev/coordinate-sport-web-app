<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('accounts'));
	}

	/**
	 * show list
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'server';
		$current_page = 'accounts';
		$section = 'accounts';
		$page_base = 'accounts';
		$title = 'Accounts';
		$buttons = '<a class="btn btn-success" href="' . site_url('accounts/new') . '"><i class="far fa-plus"></i> Create New</a> <a class="btn btn-secondary" href="' . site_url('accounts/users') . '"><i class="far fa-search"></i> Search Users</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
		);

		// always searching
		$is_search = TRUE;

		// set up search
		$search_where['is_active'] = '`' . $this->db->dbprefix("accounts") . "`.`active` = 1";
		$search_fields = array(
			'company' => NULL,
			'contact' => NULL,
			'plan_id' => NULL,
			'status' => 'paid',
			'is_active' => 'yes',
			'search' => 'true'
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_company', 'Company', 'trim|xss_clean');
			$this->form_validation->set_rules('search_contact', 'Contact', 'trim|xss_clean');
			$this->form_validation->set_rules('search_plan_id', 'Plan', 'trim|xss_clean');
			$this->form_validation->set_rules('search_status', 'Status', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['company'] = set_value('search_company');
			$search_fields['contact'] = set_value('search_contact');
			$search_fields['plan_id'] = set_value('search_plan_id');
			$search_fields['status'] = set_value('search_status');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['search'] = set_value('search');

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-accounts'))) {

			foreach ($this->session->userdata('search-accounts') as $key => $value) {
				$search_fields[$key] = $value;
			}

		}

		// tell pagination to show all one one page
		$this->pagination_library->is_search();

		if (isset($is_search) && $is_search === TRUE) {

			// store search fields
			$this->session->set_userdata('search-accounts', $search_fields);

			if ($search_fields['company'] != '') {
				$search_where[] = "`company` LIKE '%" . $this->db->escape_like_str($search_fields['company']) . "%'";
			}

			if ($search_fields['contact'] != '') {
				$search_where[] = "`contact` LIKE '%" . $this->db->escape_like_str($search_fields['contact']) . "%'";
			}

			if ($search_fields['plan_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix('accounts') . "`.`planID` = " . $this->db->escape($search_fields['plan_id']);
			}

			if ($search_fields['status'] != '') {
				$search_where[] = "`" . $this->db->dbprefix('accounts') . "`.`status` = " . $this->db->escape($search_fields['status']);
			}

			if ($search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = "`" . $this->db->dbprefix('accounts') . '`.`active` = 1';
				} else {
					$search_where['is_active'] = "`" . $this->db->dbprefix('accounts') . '`.`active` != 1';
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
		$res = $this->db->select('accounts.*')->from('accounts')->where($where)->where($search_where, NULL, FALSE)->order_by('accounts.company asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('accounts.*, accounts_plans.name as plan, COUNT(DISTINCT staff_management.staffID) as users_management, COUNT(DISTINCT staff_coaches.staffID) as users_coaches, accounts_plans.addons_all')->from('accounts')->join('accounts_plans', 'accounts.planID = accounts_plans.planID', 'left')->join('staff as staff_management', 'accounts.accountID = staff_management.accountID AND staff_management.active = 1 AND staff_management.department IN (\'office\', \'management\', \'directors\')', 'left')->join('staff as staff_coaches', 'accounts.accountID = staff_coaches.accountID AND staff_coaches.active = 1 AND staff_coaches.department NOT IN (\'office\', \'management\', \'directors\')', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('accounts.company asc')->group_by('accounts.accountID')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get plans
		$plans = $this->db->from('accounts_plans')->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'plans' => $plans,
			'accounts' => $res,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('accounts/main', $data);
	}

	/**
	 * edit account
	 * @param  int $accountID
	 * @return void
	 */
	public function edit($accountID = NULL)
	{

		$account_info = new stdClass;

		// check if editing
		if ($accountID != NULL) {

			// check if numeric
			if (!ctype_digit($accountID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'accountID' => $accountID,
			);

			// run query
			$query = $this->db->from('accounts')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$account_info = $row;
			}

		}

		// check if account has a user yet
		$has_user = FALSE;
		if ($accountID != NULL) {
			$where = array(
				'accountID' => $accountID
			);
			$res = $this->db->from('staff')->where($where)->limit(1)->get();
			if ($res->num_rows() > 0) {
				$has_user = TRUE;
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Account';
		$submit_to = 'accounts/new/';
		$return_to = 'accounts';
		if ($accountID != NULL) {
			$title = $account_info->company;
			$submit_to = 'accounts/edit/' . $accountID;
		}
		$icon = 'server';
		$tab = 'details';
		$current_page = 'accounts';
		$section = 'accounts';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'accounts' => 'Accounts'
		);

		// define addons
		$addons = array(
			'addon_resources' => 'Resources',
			'addon_messages' => 'Messages',
			'addon_equipment' => 'Equipment',
			'addon_sms' => 'SMS',
			'addon_bookings_timetable_confirmation' => 'Bookings - Timetable Confirmation',
			'addon_bookings_bookings' => 'Bookings - Bookings',
			'addon_bookings_projects' => 'Bookings - Projects',
			'addon_safety' => 'Health & Safety',
			'addon_staff_id' => 'Staff ID',
			'addon_export' => 'Export Data',
			'addon_online_booking' => 'Online Booking',
			'addon_online_booking_subscription_module' => 'Online Booking Subscriptions',
			'addon_attachments_editing' => 'Attachments - Online Editing',
			'addon_reports' => 'Reports',
			'addon_timesheets' => 'Finance',
			'addon_expenses' => 'Out of Pocket Expenses',
			'addon_staff_invoices' => 'Invoices',
			'addon_availability_cals' => 'Availability Calendars',
			'addon_offer_accept_manual' => 'Offer & Accept',
			'addon_session_evaluations' => 'Coach Session Evaluations',
			'addon_staff_performance' => 'Staff Performance',
			'addon_staff_lesson_uploads' => 'Coach Session Attachment Uploads',
			'addon_whitelabel' => 'White Label',
			'addon_lesson_checkins' => 'Session Check-ins',
			'addon_shapeup' => 'Shape Up',
			'addon_bikeability' => 'Bikeability',
			'addon_payroll' => 'Payroll',
			'addon_projectcode' => 'Project Codes',
			'addon_contracts' => 'Projects & Contracts Report',
			'addon_session_delivery' => 'Session Delivery Report',
			'addon_participant_billing' => 'Booking Payments & Transactions Reports',
			'addon_marketing_report' => 'Marketing Report',
			'addon_participant_account_module' => 'Participants',
			'addon_mileage' => 'Mileage'
		);

		$addons_descriptions = array(
			'addon_resources' => "Enables the user to upload documents/attachments in specefic areas. It enables the button 'Resources in the menu'",
			'addon_messages' => "Enables the users to send messages between other users within Coordinate",
			'addon_equipment' => 'Enables the ability to register and book equipment',
			'addon_sms' => 'Activates SMS alerts for the account',
			'addon_bookings_timetable_confirmation' => 'Enables the user to confirm is timetable',
			'addon_bookings_bookings' => 'Activates the default Bookings system in Coordinate with Projects/Contracts > Blocks > Sessions',
			'addon_bookings_projects' => 'Allows User to allocate project codes to a booking project so that reports can be run for funding area or similar etc',
			'addon_safety' => "It enables the tab 'Health & Safety' in Schools and Organisations",
			'addon_staff_id' => "It enables the 'Coach ID' tab in the profile settings of each member of staff in Coordinate",
			'addon_export' => "Enables the user to export a list of the main contacts of the customer/participants so it can be used as a newsletter. Gives also the feature the export 'SMS Softwere'",
			'addon_online_booking' => 'Enable participants to book online with the online booking website',
			'addon_online_booking_subscription_module' => 'Enable participants to book subscriptions online with the online booking website',
			'addon_attachments_editing' => 'Enables the user to edit attachments online (word and excel) with a web app called Zoho',
			'addon_reports' => "Enables the user to export reports/data as spreadsheets. It enables the button 'Reports' in the menu ",
			'addon_timesheets' => "It enables the finance functionality and the 'Finance' button in the main menu",
			'addon_expenses' => "It enables the user to claim out of pocket expenses when submitting a timesheet. It enables the section 'Out of Pocket Expenses' when submitting a timesheet",
			'addon_staff_invoices' => "It enables freelance coaches to generate and submit invoices",
			'addon_availability_cals' => "Enables in 'Settings' the 'Availability Calendar' button. It enables the super user to check the availability of coaches or instructor in delivering an activity in a specific time during the date",
			'addon_session_evaluations' => 'Enables the session evaluation function. Coaches are able to enter an evaluation of the session they attended',
			'addon_staff_performance' => "It enables the staff performance button in 'Reports'. This report gives a performance overview of members of staff",
			'addon_staff_lesson_uploads' => 'Enables the coach to upload documents/attachments to a specific session in Coordinate',
			'addon_whitelabel' => "It enables the styling button in underneath settings in the menu. Gives the ability to customize the logo and the colours when creating the account ",
			'addon_lesson_checkins' => "Enables the function check in and the button 'Check-ins' in the menu",
			'addon_shapeup' => "For use on Football Foundation shape up program for admin to collate statistics. New fields are added to the register to enter the statistics",
			'addon_bikeability' => "For those accounts who service bikeability/ cycle instruction customers.
It enable the bikeability registers (names only, children, individual) and the bikeability report",
			'addon_payroll' => 'Allows Super Users to set up pay rates for staff based on a qualification level and time of service. i.e pay increases to xx after 12 months',
			'addon_projectcode' => "Enables the button 'Projects Code' in settings",
			'addon_contracts' => 'Activates The Project & Contracts Report',
			'addon_session_delivery' => 'Activates the Session Delivery Report',
			'addon_marketing_report' => 'Activates the Marketing Data & Privacy Report',
			'addon_offer_accept_manual' => "It enables in settings the button 'Offer & Accept (Manual)'. Gives the ability to the user to manually offer sessions to coaches. If the coaches are able to deliver the sessions they can choose to accept or decline"
		);
		asort($addons);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			// basic info
			$this->form_validation->set_rules('company', 'Company', 'trim|xss_clean|required');
			$this->form_validation->set_rules('planID', 'Plan', 'trim|xss_clean|required');
			$this->form_validation->set_rules('organisation_size', 'Organisation Size', 'trim|xss_clean|integer');
			$this->form_validation->set_rules('crm_customdomain', 'Application Custom Domain', 'trim|xss_clean|callback_check_unique_custom_domain[' . $accountID . ']|callback_check_unique_custom_domain_account[' . $this->input->post('booking_customdomain') . ']');
			$this->form_validation->set_rules('booking_subdomain', 'Booking Site Subdomain', 'trim|xss_clean|callback_check_valid_domain|callback_check_reserved_name|callback_check_unique[' . $accountID . ']');
			$this->form_validation->set_rules('booking_customdomain', 'Booking Site Custom Domain', 'trim|xss_clean|callback_check_valid_domain|callback_check_unique_custom_domain[' . $accountID . ']');
			$this->form_validation->set_rules('status', 'Status', 'trim|xss_clean|required');
			$this->form_validation->set_rules('paid_until', 'Paid Until', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('trial_until', 'Trial Until', 'trim|xss_clean|callback_check_date');

			$this->form_validation->set_rules('email_from_override', 'Send Emails From', 'trim|xss_clean|valid_email');

			$this->form_validation->set_rules('contact', 'Contact', 'trim|xss_clean|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email|required|callback_check_staff_email');
			$this->form_validation->set_rules('phone', 'Phone', 'trim|xss_clean');

			if ($has_user !== TRUE) {
				$this->form_validation->set_rules('account_password', 'Password', 'trim|xss_clean|min_length[8]|matches[account_password_confirm]');
				$this->form_validation->set_rules('account_password_confirm', 'Confirm Password', 'trim|xss_clean');
				$this->form_validation->set_rules('notify', 'Notify', 'trim|xss_clean');
			}

			// customisation
			$this->form_validation->set_rules('body_colour', 'Main Colour', 'trim|xss_clean|required');
			$this->form_validation->set_rules('contrast_colour', 'Contrast Colour', 'trim|xss_clean|required');

			// addons
			foreach ($addons as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'company' => set_value('company'),
					'planID' => set_value('planID'),
					'status' => set_value('status'),
					'contact' => set_value('contact'),
					'email' => set_value('email'),
					'phone' => set_value('phone'),
					'organisation_size' => intval(set_value('organisation_size')),
					'booking_subdomain' => set_value('booking_subdomain'),
					'booking_customdomain' => set_value('booking_customdomain'),
					'crm_customdomain' => set_value('crm_customdomain'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				foreach ($addons as $field => $label) {
					$data[$field] = intval(set_value($field));
				}

				// check if paid
				if (set_value('status') == 'paid' && set_value('paid_until') != '') {
					$data['paid_until'] = uk_to_mysql_date(set_value('paid_until'));
				} else {
					$data['paid_until'] = NULL;
				}

				// check if trial
				if (set_value('status') == 'trial' && set_value('trial_until') != '') {
					$data['trial_until'] = uk_to_mysql_date(set_value('trial_until'));
				} else {
					$data['trial_until'] = NULL;
				}

				// if no booking sub domain, set null
				if (empty(set_value('booking_subdomain'))) {
					$data['booking_subdomain'] = NULL;
				}

				// if new
				if ($accountID === NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
					$data['api_key'] = $this->crm_library->generate_api_key();
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($accountID == NULL) {
						// insert
						$query = $this->db->insert('accounts', $data);
						$accountID = $this->db->insert_id();
						$just_added = TRUE;

						// get plan info
						$where = array(
							'planID' => set_value('planID')
						);
						$plan = $this->db->from('accounts_plans')->where($where)->limit(1)->get();

						// if plan, get default project types
						if ($plan->num_rows() > 0) {
							foreach ($plan->result() as $plan_info) {}
							$project_types = explode(PHP_EOL, $plan_info->default_project_types);
							if (!is_array($project_types)) {
								$project_types = array();
							} else if (count($project_types) > 0) {
								foreach($project_types as $key => $value) {
									$value = trim($value);
									if (empty($value)) {
										unset($project_types[$key]);
									} else {
										$project_types[$key] = $value;
									}
								}
							}
							// if some, add
							if (count($project_types) > 0) {
								foreach ($project_types as $type) {
									$data = array(
										'accountID' => $accountID,
										'name' => $type,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$this->db->insert('project_types', $data);
								}
							}
						}

						// default mandatory quals
						$default_quals = array(
							'Level 1 Coaching',
							'Level 2 Coaching'
						);
						if (count($default_quals) > 0) {
							foreach ($default_quals as $qual) {
								$data = array(
									'accountID' => $accountID,
									'name' => $qual,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								$this->db->insert('mandatory_quals', $data);
							}
						}
						
						// default Mileage Types
						$data = array(
									'accountID' => $accountID,
									'name' => 'Car',
									'rate' => '45',
									'created' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
						$this->db->insert('mileage', $data);
						$data = array(
									'accountID' => $accountID,
									'name' => 'Bicycle',
									'rate' => '25',
									'created' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
						$this->db->insert('mileage', $data);
						

					} else {
						$where = array(
							'accountID' => $accountID
						);

						// update
						$query = $this->db->update('accounts', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// update logo
						$field_info = $this->settings_library->get_field_info('logo');
						$upload_res = $this->crm_library->handle_image_upload('logo', FALSE, $accountID, $field_info->max_width, $field_info->max_height);

						if ($upload_res !== NULL) {
							$image_data = array(
								'name' => $upload_res['client_name'],
								'path' => $upload_res['raw_name'],
								'type' => $upload_res['file_type'],
								'size' => $upload_res['file_size']*1024,
								'ext' => substr($upload_res['file_ext'], 1)
							);
							$this->settings_library->save('logo', serialize($image_data), $accountID);
						}
						
						// update favicon
						$field_info = $this->settings_library->get_field_info('favicon');
						$upload_res = $this->crm_library->handle_image_upload('favicon', FALSE, $accountID, $field_info->max_width, $field_info->max_height);

						if ($upload_res !== NULL) {
							$image_data = array(
								'name' => $upload_res['client_name'],
								'path' => $upload_res['raw_name'],
								'type' => $upload_res['file_type'],
								'size' => $upload_res['file_size']*1024,
								'ext' => substr($upload_res['file_ext'], 1)
							);
							$this->settings_library->save('favicon', serialize($image_data), $accountID);
						}
						

						// update email from override
						$this->settings_library->save('email_from_override', set_value('email_from_override'), $accountID);

						// update styling
						$this->settings_library->save('body_colour', set_value('body_colour'), $accountID);
						$this->settings_library->save('contrast_colour', set_value('contrast_colour'), $accountID);

						// set success message
						if (isset($just_added)) {
							$success = set_value('company') . ' has been created successfully';
						} else {
							$success = set_value('company') . ' has been updated successfully';
						}

						$staff_activity_posted = set_value('staff_activity');
						if (is_array($staff_activity_posted)) {
							foreach ($staff_activity_posted as $item) {
								$this->db->update('staff', [
									'show_user_activity' => 1
								], [
									'staffID' => $item
								]);
							}
						}

						// create new user if password specified
						if ($has_user !== TRUE) {
							$password = set_value('account_password');

							// if no password, generate
							if (empty($password)) {
								$password = random_string();
							}

							// prepare data
							$data = array(
								'first' => trim(substr(set_value('contact'), 0, strpos(set_value('contact'), ' '))),
								'surname' => trim(substr(set_value('contact'), strpos(set_value('contact'), ' '))),
								'department' => 'directors',
								'email' => set_value('email'),
								'active' => 1,
								'password' => $this->auth->encrypt_password($password),
								'last_password_change' => NULL,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $accountID
							);

							// insert
							$query = $this->db->insert('staff', $data);

							if ($this->db->affected_rows() == 1) {
								$staffID = $this->db->insert_id();

								// address data
								$address_data = array(
									'staffID' => $staffID,
									'byID' => $this->auth->user->staffID,
									'type' => 'main',
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);

								// insert address info
								$this->db->insert('staff_addresses', $address_data);
								if ($this->db->affected_rows() == 1) {
									$success .= ' and user created';
									if (set_value('notify') == 1 && $this->crm_library->send_staff_welcome_email($staffID, $password)) {
										$success .= ' and notified';
									}
								}
							}
						}

						$this->session->set_flashdata('success', $success . '.');

						// redirect
						redirect('accounts/edit/' . $accountID);

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
		} else if ($this->session->flashdata('error')) {
			$errors[] = $this->session->flashdata('error');
		}

		// get plans
		$plans = $this->db->from('accounts_plans')->order_by('name asc')->get();

		$res = $this->db->from('staff')->where([
			'accountID' => $accountID
		])->get();

		$staff_listing = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $staff) {
				$staff_listing[$staff->staffID] = $staff->first . ' ' . $staff->surname;
			}
		}

		$res = $this->db->from('staff')->where([
			'accountID' => $accountID,
			'show_user_activity' => 1
		])->get();

		$staff_listing_selected = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $staff) {
				$staff_listing_selected[] = $staff->staffID;
			}
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
			'account_info' => $account_info,
			'accountID' => $accountID,
			'has_user' => $has_user,
			'plans' => $plans,
			'addons' => $addons,
			'addons_descriptions' => $addons_descriptions,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'staff_listing' => $staff_listing,
			'staff_listing_selected' => $staff_listing_selected
		);

		// load view
		$this->crm_view('accounts/account', $data);
	}

	/**
	 * delete account
	 * @param  int $accountID
	 * @return mixed
	 */
	public function remove($accountID = NULL) {

		// check params
		if (empty($accountID)) {
			show_404();
		}

		// can't delete own account
		if ($this->auth->user->accountID == $accountID) {
			show_404();
		}

		$where = array(
			'accountID' => $accountID,
			'admin !=' => 1
		);

		// run query
		$query = $this->db->from('accounts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		$redirect_to = 'accounts';
		$account_info = $query->result()[0];

		if ($account_info->status == 'paid' && $account_info->active == 1) {
			$this->session->set_flashdata('error', $account_info->company . ' could not be removed.');
			redirect($redirect_to);
			return false;
		}

		// loop through all tables and delete based on account ID
		$where = array(
			'accountID' => $accountID
		);

		// disable foreign key check
		$this->db->query('SET FOREIGN_KEY_CHECKS=0;');

		// loop through all tables
		$db_tables = $this->db->list_tables();
		foreach ($db_tables as $db_table) {
			if ($db_table == 'accounts') {
				// leave accounts table for last
				continue;
			}
			$fields = $this->db->list_fields($db_table);
			if (in_array('accountID', $fields)) {
				$res = $this->db->delete($db_table, $where);
			}
		}

		// enable foreign key check
		$this->db->query('SET FOREIGN_KEY_CHECKS=1;');

		// all ok, delete
		$query = $this->db->delete('accounts', $where);

		$this->session->set_flashdata('success', $account_info->company . ' has been removed successfully.');

		redirect($redirect_to);
	}

	/**
	 * toggle active status
	 * @param  int $accountID
	 * @param string $value
	 * @return mixed
	 */
	public function active($accountID = NULL, $value = NULL) {

		// check params
		if (empty($accountID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'accountID' => $accountID,
			'admin !=' => 1
		);

		// run query
		$query = $this->db->from('accounts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$account_info = $row;

			$data = array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['active'] = 1;
			}

			// run query
			$query = $this->db->update('accounts', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

	/**
	 * access account
	 * @param  int $accountID
	 * @return mixed
	 */
	public function access($accountID = NULL) {

		// check params
		if (empty($accountID)) {
			show_404();
		}

		$where = array(
			'accountID' => $accountID,
			'accountID !=' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('accounts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// override account id
		$this->session->set_userdata('account_id_override', $accountID);

		// go to
		$redirect_to = '/';

		redirect($redirect_to);
	}

	/**
	 * init import of demo data
	 * @param  int $accountID
	 * @return bool
	 */
	public function demodata($accountID) {
		if ($this->copy_demo_data($accountID) === TRUE) {
			$this->session->set_flashdata('success', 'Demo data has been imported successfully.');
		} else {
			$this->session->set_flashdata('error', 'There was a problem importing the demo data. Please review the account to check the status.');
		}
		$redirect_to = 'accounts/edit/' . $accountID;
		redirect($redirect_to);
	}

	/**
	 * copy demo account data to specified account
	 * @param  int $accountID
	 * @return bool
	 */
	private function copy_demo_data($accountID) {

		// check params
		if (empty($accountID)) {
			return FALSE;
		}

		// remove time limit and increase memory limit
		set_time_limit(0);
		ini_set('memory_limit', '512M');

		// account to copy from
		$fromID = DEMO_DATA_ACCOUNT;

		// check not copying from same
		if ($accountID == $fromID) {
			return FALSE;
		}

		// check both acccounts exist
		$where = array(
			'accountID' => $accountID,
			'demo_data_imported !=' => 1
		);
		$res = $this->db->from('accounts')->where($where)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		$where = array(
			'accountID' => $fromID
		);
		$res = $this->db->from('accounts')->where($where)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $row) {
			$account_created = $row->added;
		}

		// find first session in source account
		$first_session_date = NULL;
		$first_session_day = NULL;
		$where = [
			'bookings_blocks.accountID' => $fromID
		];
		$res = $this->db->select('bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end, bookings_lessons.day')
			->from('bookings_blocks')
			->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID')
			->where($where)
			->order_by('bookings_blocks.startDate asc')
			->limit(5)
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// get block dates
				$date = $row->block_start;
				$end_date = $row->block_end;

				// check if overridden on block level
				if (!empty($row->lesson_start)) {
					$date = $row->lesson_start;
				}
				if (!empty($row->lesson_end)) {
					$end_date = $row->lesson_end;
				}

				// if we already have a first session date and this block starts after, skip
				if (!empty($first_session_date) && strtotime($date) > strtotime($first_session_date)) {
					continue;
				}

				// loop through dates in lesson
				while (strtotime($date) <= strtotime($end_date)) {
					$day = strtolower(date('l', strtotime($date)));
					// if days is on one of the dates
					if ($day == $row->day) {
						// if no first session date or this date is less than first session date, update
						if (empty($first_session_date) || strtotime($date) < strtotime($first_session_date)) {
							$first_session_date = $date;
							$first_session_day = $day;
						}
						// we're only interested in the first session in the block so skip the rest
						break;
					}
					$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
				}
			}
		}

		// work out how many days to shift dates to
		if (!empty($first_session_date) && !empty($first_session_day)) {
			// get the date the first session should be this week
			$compare_to = strtotime($first_session_day . ' this week');
			// work out difference in seconds
			$shift_dates = $compare_to - strtotime($first_session_date);
		} else {
			// if no sessions, don't shift
			$shift_dates = 0;
		}

		// mark as imported
		$data = array(
			'demo_data_imported' => 1
		);
		$where = array(
			'accountID' => $accountID
		);
		$res = $this->db->update('accounts', $data, $where, 1);

		// map items
		$maps = array();

		// clone tables and map fields
		$clone_tables = array(
			'activities' => array(
				'id' => 'activityID',
				'fields_to_map' => array()
			),
			'lesson_types' => array(
				'id' => 'typeID',
				'fields_to_map' => array()
			),
			'project_types' => array(
				'id' => 'typeID',
				'fields_to_map' => array()
			),
			'brands' => array(
				'id' => 'brandID',
				'fields_to_map' => array()
			),
			'staff' => array(
				'id' => 'staffID',
				'fields_to_map' => array()
			),
			'staff_activities' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'staffID' => 'staff',
					'activityID' => 'activities'
				)
			),
			'staff_addresses' => array(
				'id' => 'addressID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'staff_attachments' => array(
				'id' => 'attachmentID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'staff_availability' => array(
				'id' => 'availabilityID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'staff_availability_exceptions' => array(
				'id' => 'exceptionsID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'staff_notes' => array(
				'id' => 'noteID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'staff_quals' => array(
				'id' => 'qualID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'tasks' => array(
				'id' => 'taskID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'timetable_read' => array(
				'id' => 'recordID',
				'fields_to_map' => array(
					'staffID' => 'staff'
				)
			),
			'vouchers' => array(
				'id' => 'voucherID',
				'fields_to_map' => array()
			),
			'vouchers_lesson_types' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'voucherID' => 'vouchers',
					'typeID' => 'lesson_types'
				)
			),
			'settings_regions' => array(
				'id' => 'regionID',
				'fields_to_map' => array()
			),
			'settings_areas' => array(
				'id' => 'areaID',
				'fields_to_map' => array(
					'regionID' => 'settings_regions',
				)
			),
			'settings_childcarevoucherproviders' => array(
				'id' => 'providerID',
				'fields_to_map' => array()
			),
			'equipment' => array(
				'id' => 'equipmentID',
				'fields_to_map' => array()
			),
			'equipment_bookings' => array(
				'id' => 'bookingID',
				'fields_to_map' => array(
					'staffID' => 'staff',
					'orgID' => 'orgs',
					'contactID' => 'family_contacts',
					'childID' => 'family_children',
					'equipmentID' => 'equipment'
				)
			),
			'files' => array(
				'id' => 'attachmentID',
				'fields_to_map' => array()
			),
			'files_brands' => array(
				'id' => 'id',
				'fields_to_map' => array(
					'brandID' => 'brands',
				)
			),
			'messages' => array(
				'id' => 'messageID',
				'fields_to_map' => array(
					'byID' => 'staff',
					'forID' => 'staff',
				)
			),
			'messages_attachments' => array(
				'id' => 'attachmentID',
				'fields_to_map' => array(
					'messageID' => 'messages',
				)
			),
			'orgs' => array(
				'id' => 'orgID',
				'fields_to_map' => array()
			),
			'orgs_addresses' => array(
				'id' => 'addressID',
				'fields_to_map' => array(
					'orgID' => 'orgs'
				)
			),
			'orgs_attachments' => array(
				'id' => 'attachmentID',
				'fields_to_map' => array(
					'orgID' => 'orgs',
					'addressID' => 'orgs_addresses'
				)
			),
			'orgs_clusters' => array(
				'id' => 'clusterID',
				'fields_to_map' => array(
					'orgID' => 'orgs'
				)
			),
			'orgs_contacts' => array(
				'id' => 'contactID',
				'fields_to_map' => array(
					'orgID' => 'orgs'
				)
			),
			'orgs_contacts_newsletters' => array(
				'id' => 'id',
				'fields_to_map' => array(
					'orgID' => 'orgs',
					'brandID' => 'brands'
				)
			),
			'orgs_notes' => array(
				'id' => 'noteID',
				'fields_to_map' => array(
					'orgID' => 'orgs'
				)
			),
			'orgs_notifications' => array(
				'id' => 'notificationID',
				'fields_to_map' => array(
					'orgID' => 'orgs',
					'contactID' => 'orgs_contacts'
				)
			),
			'orgs_notifications_attachments_customers' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'notificationID' => 'orgs_notifications',
					'attachmentID' => 'orgs_attachments'
				)
			),
			'orgs_notifications_attachments_resources' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'notificationID' => 'orgs_notifications',
					'attachmentID' => 'files'
				)
			),
			'orgs_notifications_attachments_bookings' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'notificationID' => 'orgs_notifications',
					'attachmentID' => 'bookings_attachments'
				)
			),
			'orgs_pricing' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'orgID' => 'orgs',
					'typeID' => 'lesson_types',
					'brandID' => 'brands'
				)
			),
			'orgs_safety' => array(
				'id' => 'docID',
				'fields_to_map' => array(
					'orgID' => 'orgs',
					'addressID' => 'orgs_addresses'
				)
			),
			'orgs_safety_hazards' => array(
				'id' => 'hazardID',
				'fields_to_map' => array(
					'orgID' => 'orgs',
					'docID' => 'orgs_safety'
				)
			),
			'orgs_safety_read' => array(
				'id' => 'readID',
				'fields_to_map' => array(
					'docID' => 'orgs_safety',
					'staffID' => 'staff'
				)
			),
			'family' => array(
				'id' => 'familyID',
				'fields_to_map' => array()
			),
			'family_children' => array(
				'id' => 'childID',
				'fields_to_map' => array(
					'familyID' => 'family',
					'orgID' => 'orgs'
				)
			),
			'family_contacts' => array(
				'id' => 'contactID',
				'fields_to_map' => array(
					'familyID' => 'family'
				)
			),
			'family_contacts_newsletters' => array(
				'id' => 'id',
				'fields_to_map' => array(
					'contactID' => 'family_contacts',
					'brandID' => 'brands'
				)
			),
			'family_notes' => array(
				'id' => 'noteID',
				'fields_to_map' => array(
					'familyID' => 'family'
				)
			),
			'family_notifications' => array(
				'id' => 'notificationID',
				'fields_to_map' => array(
					'familyID' => 'family',
					'contactID' => 'family_contacts'
				)
			),
			'bookings' => array(
				'id' => 'bookingID',
				'fields_to_map' => array(
					'contactID' => 'orgs_contacts',
					'orgID' => 'orgs',
					'addressID' => 'orgs_addresses',
					'project_typeID' => 'project_types',
					'brandID' => 'brands'
				)
			),
			'bookings_blocks' => array(
				'id' => 'blockID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'addressID' => 'orgs_addresses'
				)
			),
			'bookings_lessons' => array(
				'id' => 'lessonID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'blockID' => 'bookings_blocks',
					'addressID' => 'orgs_addresses',
					'activityID' => 'activities',
					'typeID' => 'lesson_types'
				)
			),
			'bookings_attachments' => array(
				'id' => 'attachmentID',
				'fields_to_map' => array(
					'bookingID' => 'bookings'
				)
			),
			'family_notifications_attachments' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'notificationID' => 'family_notifications',
					'attachmentID' => 'bookings_attachments'
				)
			),
			'bookings_attendance_names' => array(
				'id' => 'participantID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'blockID' => 'bookings_blocks'
				)
			),
			'bookings_attendance_names_sessions' => array(
				'id' => 'attendanceID',
				'fields_to_map' => array(
					'participantID' => 'bookings_attendance_names',
					'bookingID' => 'bookings',
					'blockID' => 'bookings_blocks',
					'lessonID' => 'bookings_lessons'
				)
			),
			'bookings_attendance_numbers' => array(
				'id' => 'attendanceID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'blockID' => 'bookings_blocks',
					'lessonID' => 'bookings_lessons'
				)
			),
			'bookings_costs' => array(
				'id' => 'costID',
				'fields_to_map' => array(
					'blockID' => 'bookings_blocks'
				)
			),
			'bookings_invoices' => array(
				'id' => 'invoiceID',
				'fields_to_map' => array(
					'bookingID' => 'bookings'
				)
			),
			'bookings_invoices_blocks' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'invoiceID' => 'bookings_invoices',
					'blockID' => 'bookings_blocks'
				)
			),
			'bookings_lessons_attachments' => array(
				'id' => 'attachmentID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons'
				)
			),
			'bookings_lessons_exceptions' => array(
				'id' => 'exceptionID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons',
					'fromID' => 'staff',
					'staffID' => 'staff',
				)
			),
			'bookings_lessons_notes' => array(
				'id' => 'noteID',
				'fields_to_map' => array(
					'byID' => 'staff',
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons'
				)
			),
			'bookings_lessons_orgs_attachments' => array(
				'id' => 'actualID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons',
					'attachmentID' => 'orgs_attachments'
				)
			),
			'bookings_lessons_resources_attachments' => array(
				'id' => 'actualID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons',
					'attachmentID' => 'files'
				)
			),
			'bookings_lessons_staff' => array(
				'id' => 'recordID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons',
					'staffID' => 'staff'
				)
			),
			'bookings_orgs_attachments' => array(
				'id' => 'actualID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'attachmentID' => 'orgs_attachments'
				)
			),
			'bookings_pricing' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'typeID' => 'lesson_types'
				)
			),
			'bookings_vouchers' => array(
				'id' => 'voucherID',
				'fields_to_map' => array(
					'bookingID' => 'bookings',
					'lessonID' => 'bookings_lessons'
				)
			),
			'bookings_lessons_vouchers' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'lessonID' => 'bookings_lessons',
					'voucherID' => 'bookings_vouchers'
				)
			),
			'bookings_cart' => array(
				'id' => 'cartID',
				'fields_to_map' => array(
					'familyID' => 'family',
					'contactID' => 'family_contacts',
					'childcarevoucher_providerID' => 'settings_childcarevoucherproviders'
				)
			),
			'bookings_cart_vouchers' => array(
				'id' => 'id',
				'fields_to_map' => array(
					'cartID' => 'bookings_cart',
					'voucherID' => 'bookings_vouchers',
					'voucherID_global' => 'vouchers'
				)
			),
			'bookings_cart_monitoring' => array(
				'id' => 'id',
				'fields_to_map' => array(
					'cartID' => 'bookings_cart',
					'bookingID' => 'bookings',
					'contactID' => 'family_contacts',
					'childID' => 'family_children'
				)
			),
			'bookings_cart_sessions' => array(
				'id' => 'sessionID',
				'fields_to_map' => array(
					'cartID' => 'bookings_cart',
					'bookingID' => 'bookings',
					'blockID' => 'bookings_blocks',
					'lessonID' => 'bookings_lessons',
					'contactID' => 'family_contacts',
					'childID' => 'family_children'
				)
			),
			'family_payments' => array(
				'id' => 'paymentID',
				'fields_to_map' => array(
					'familyID' => 'family',
					'contactID' => 'family_contacts',
				)
			),
			/*'family_payments_plans' => array(
				'id' => 'planID',
				'fields_to_map' => array(
					'familyID' => 'family',
					'contactID' => 'family_contacts'
				)
			),*/
			'offer_accept_groups' => array(
				'id' => 'groupID',
				'fields_to_map' => array()
			),
			'offer_accept' => array(
				'id' => 'offerID',
				'fields_to_map' => array(
					'lessonID' => 'bookings_lessons',
					'staffID' => 'staff',
					'groupID' => 'offer_accept_groups',
				)
			),
			'groups' => array(
				'id' => 'groupID',
				'fields_to_map' => array()
			),
			'staff_groups' => array(
				'id' => 'linkID',
				'fields_to_map' => array(
					'staffID' => 'staff',
					'groupID' => 'groups',
				)
			),
			'bookings_lessons_checkins' => array(
				'id' => 'logID',
				'fields_to_map' => array(
					'lessonID' => 'bookings_lessons',
					'staffID' => 'staff',
				)
			),
			'bookings_lessons_checkouts' => array(
				'id' => 'logID',
				'fields_to_map' => array(
					'lessonID' => 'bookings_lessons',
					'staffID' => 'staff',
				)
			),
			'bookings_images' => array(
				'id' => 'imageID',
				'fields_to_map' => array(
					'bookingID' => 'bookings'
				)
			),
			'accounts_settings' => array(
				'id' => 'settingID',
				'fields_to_map' => array()
			),
			'accounts_settings_dashboard' => array(
				'id' => 'settingID',
				'fields_to_map' => array()
			),
			'project_codes' => array(
				'id' => 'codeID',
				'fields_to_map' => array()
			),
			'mandatory_quals' => array(
				'id' => 'qualID',
				'fields_to_map' => array()
			)
		);
		foreach ($clone_tables as $table => $details) {
			$where = array(
				'accountID' => $fromID
			);
			$res = $this->db->from($table)->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $data) {
					$recordID = $data[$details['id']];
					// modify fields
					$data['accountID'] = $accountID;
					if ($table == 'staff') {
						// create new unique email
						$at_pos = strpos($data['email'], '@');
						$data['email'] = substr($data['email'], 0, $at_pos) . '_' . $accountID . substr($data['email'], $at_pos);
					}
					// map fields to new IDs
					if (count($details['fields_to_map']) > 0) {
						foreach ($details['fields_to_map'] as $field => $to_table) {
							if (!empty($data[$field]) && array_key_exists($to_table, $maps) && array_key_exists($data[$field], $maps[$to_table])) {
								$data[$field] = $maps[$to_table][$data[$field]];
							}
						}
					}
					// check for dates to shift
					foreach ($data as $field => $value) {
						if (check_mysql_date(substr($value, 0, 10))) {
							$data[$field] = date('Y-m-d H:i:s', strtotime($value) + $shift_dates);
						}
					}
					// unset fields
					unset($data[$details['id']]);
					if (!array_key_exists('byID', $details['fields_to_map'])) {
						unset($data['byID']);
					}
					// copy
					$this->db->insert($table, $data);
					// save map
					$maps[$table][$recordID] = $this->db->insert_id();
				}
			}
		}

		// update approvers
		$where = array(
			'accountID' => $fromID
		);
		$res = $this->db->from('staff_recruitment_approvers')->where($where)->get();
		if ($res->num_rows() > 0) {
			$overall_data = array();
			foreach ($res->result() as $row) {
				$data = array("accountID" => $accountID,
				"staffID" => $row->staffID,
				"approverID" => $maps['staff'][$row->approverID],
				"added" => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'));
				$overall_data[] = $data;
			}
			$this->db->insert_batch("staff_recruitment_approvers", $overall_data);
		}

		// copy files
		$this->load->helper('directory');
		$dir_from = str_replace($this->auth->user->accountID, $fromID, UPLOADPATH);
		$dir_to = str_replace($this->auth->user->accountID, $accountID, UPLOADPATH);
		directory_copy($dir_from, $dir_to);

		return TRUE;
	}

	/**
	 * init clear of account data
	 * @param  int $accountID
	 * @return bool
	 */
	public function cleardata($accountID) {
		if ($this->clear_account_data($accountID) === TRUE) {
			$this->session->set_flashdata('success', 'Account data has been cleared successfully.');
		} else {
			$this->session->set_flashdata('error', 'There was a problem clearing the accountdata. Please review the account to check the status.');
		}
		$redirect_to = 'accounts/edit/' . $accountID;
		redirect($redirect_to);
	}

	/**
	 * clear account data
	 * @param  int $accountID
	 * @return bool
	 */
	private function clear_account_data($accountID) {

		// check params
		if (empty($accountID)) {
			return FALSE;
		}

		// check exists
		$where = array(
			'accountID' => $accountID,
			'demo_data_imported =' => 1
		);
		$res = $this->db->from('accounts')->where($where)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get first created user
		$staffID = 0;
		$where = array(
			'accountID' => $accountID
		);
		$res = $this->db->from('staff')->where($where)->order_by('staffID ASC')->limit(1)->get();
		if ($res->num_rows() == 1) {
			foreach ($res->result() as $row) {
				$staffID = $row->staffID;
			}
		}

		// start transaction
		$this->db->trans_start();

		// disable foreign key checks
		$this->db->query('SET FOREIGN_KEY_CHECKS=0');

		// get list of tables
		$tables = $this->db->list_tables();

		foreach ($tables as $table)	{
			$table = str_replace($this->db->dbprefix, '', $table);
			// skip accounts tables
			if (substr($table, 0, 8) == 'accounts') {
				continue;
			}
			// check for account id field
			$fields = $this->db->list_fields($table);
			if (!in_array('accountID', $fields)) {
				continue;
			}
			// delete
			$where = array(
				'accountID' => $accountID
			);
			// dont delete first created user in staff tables
			if (substr($table, 0, 5) == 'staff') {
				if (in_array('staffID', $fields)) {
					$where['staffID !='] = $staffID;
				}
			}
			$this->db->delete($table, $where);
		}

		// set demo data imported to 0
		$data = array(
			'demo_data_imported' => 0
		);
		$where = array(
			'accountID' => $accountID
		);
		$this->db->update('accounts', $data, $where, 1);

		// enable foreign key checks
		$this->db->query('SET FOREIGN_KEY_CHECKS=1');

		// finish transaction
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * validation function for checking email is unique, except in specified account record
	 * @param  string $email
	 * @param  int $accountID
	 * @return bool
	 */
	public function check_email($email = NULL, $accountID = NULL) {
		// check if parameters
		if (empty($email)) {
			return FALSE;
		}

		// check email not in use with anyone on any account
		$where = array(
			'email' => $email
		);

		// exclude current user, if set
		if (!empty($accountID)) {
			$where['accountID !='] = $accountID;
		}

		// check
		$query = $this->db->get_where('accounts', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * validation function for checking email is unique
	 * @param  string $email
	 * @return bool
	 */
	public function check_staff_email($email = NULLL) {
		// if password posted, don't check
		if (!$this->input->post('account_password')) {
			return TRUE;
		}

		// check if parameters
		if (empty($email)) {
			return FALSE;
		}

		// check email not in use with anyone on any account
		$where = array(
			'email' => $email
		);

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
	 * check if subdomain is a reserved name
	 * @param  string $subdomain
	 * @return bool
	 */
	public function check_reserved_name($subdomain) {

		// check if not a reserved name
		if (!in_array($subdomain, CRM_SUB_DOMAINS)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * validation function for checking subdomain is unique, except in specified account record
	 * @param  string $subdomain
	 * @param  int $accountID
	 * @return bool
	 */
	public function check_unique($subdomain = NULL, $accountID = NULL) {
		// if no subdomain, all ok
		if (empty($subdomain)) {
			return TRUE;
		}

		// check subdomain not in use with anyone on any account
		$where = array(
			'booking_subdomain' => $subdomain
		);

		// exclude current user, if set
		if (!empty($accountID)) {
			$where['accountID !='] = $accountID;
		}

		// check
		$query = $this->db->get_where('accounts', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * validation function for checking custom domain is unique
	 * @param  string $domain
	 * @param  int $accountID
	 * @return bool
	 */
	public function check_unique_custom_domain($domain = NULL, $accountID = NULL) {
		// if no domain, all ok
		if (empty($domain)) {
			return TRUE;
		}

		// 1. check domain not in use with anyone on any account for booking custom domain
		$where = array(
			'booking_customdomain' => $domain
		);

		// exclude current user, if set
		if (!empty($accountID)) {
			$where['accountID !='] = $accountID;
		}

		// check
		$query = $this->db->get_where('accounts', $where, 1);

		// check results
		if ($query->num_rows() > 0) {
			return FALSE;
		}

		// 2. check domain not in use with anyone on any account for crm custom domain
		$where = array(
			'crm_customdomain' => $domain
		);

		// exclude current user, if set
		if (!empty($accountID)) {
			$where['accountID !='] = $accountID;
		}

		// check
		$query = $this->db->get_where('accounts', $where, 1);

		// check results
		if ($query->num_rows() > 0) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function for checking custom domain is unique from this account's custom crm and bookign domains
	 * @param  string $domain
	 * @param  string $compare_domain
	 * @return bool
	 */
	public function check_unique_custom_domain_account($domain = NULL, $compare_domain = NULL) {
		// if no domain, all ok
		if (empty($domain)) {
			return TRUE;
		}

		$domain = trim($domain);
		$compare_domain = trim($compare_domain);

		// check domains are not the same
		if ($domain == $compare_domain) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function for domain is in a valid format
	 * @param  string $domain
	 * @return bool
	 */
	public function check_valid_domain($domain = NULL) {
		// if no domain, all ok
		if (empty($domain)) {
			return TRUE;
		}

		// check format
		return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
	}

}

/* End of file main.php */
/* Location: ./application/controllers/accounts/main.php */
