<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contacts extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}
	}

	/**
	 * show list of contacts
	 * @return void
	 */
	public function index($org_id = NULL) {

		if ($org_id == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $org_id,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'users';
		$tab = 'contacts';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $org_id] = $org_info->name;
		$page_base = 'customers/contacts/' . $org_id;
		$section = 'customers';
		$title = 'Contacts';
		$buttons = '<a class="btn btn-success" href="' . site_url('customers/contacts/' . $org_id . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'orgs_contacts.orgID' => $org_id,
			'orgs_contacts.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where['is_active'] = '`' . $this->db->dbprefix("orgs_contacts") . "`.`active` = 1";
		$search_fields = array(
			'name' => NULL,
			'position' => NULL,
			'tel' => NULL,
			'mobile' => NULL,
			'email' => NULL,
			'search' => NULL,
			'is_active' => 'yes'
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_position', 'Position', 'trim|xss_clean');
			$this->form_validation->set_rules('search_tel', 'Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('search_mobile', 'Mobile', 'trim|xss_clean');
			$this->form_validation->set_rules('search_email', 'Email', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['position'] = set_value('search_position');
			$search_fields['tel'] = set_value('search_tel');
			$search_fields['mobile'] = set_value('search_mobile');
			$search_fields['email'] = set_value('search_email');
			$search_fields['search'] = set_value('search');
			$search_fields['is_active'] = set_value('search_is_active');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-customer-contacts'))) {

			foreach ($this->session->userdata('search-customer-contacts') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-customer-contacts', $search_fields);

			if ($search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`active` = 1';
				} else {
					$search_where['is_active'] = '`active` != 1';
				}
			}

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['position'] != '') {
				$search_where[] = "`position` LIKE '%" . $this->db->escape_like_str($search_fields['position']) . "%'";
			}

			if ($search_fields['tel'] != '') {
				$search_where[] = "'tel` LIKE '%" . $this->db->escape_like_str($search_fields['tel']) . "%'";
			}

			if ($search_fields['mobile'] != '') {
				$search_where[] = "`mobile` LIKE '%" . $this->db->escape_like_str($search_fields['mobile']) . "%'";
			}

			if ($search_fields['email'] != '') {
				$search_where[] = "`email` LIKE '%" . $this->db->escape_like_str($search_fields['email']) . "%'";
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
		$res = $this->db->from('orgs_contacts')->where($where)->where($search_where, NULL, FALSE)->order_by('isMain desc, name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('orgs_contacts')->where($where)->where($search_where, NULL, FALSE)->order_by('isMain desc, name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'contacts' => $res,
			'org_id' => $org_id,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/contacts', $data);
	}

	public function active($contactID = NULL, $value = NULL) {
		// check params
		if (empty($contactID) || !in_array($value, [1, 0])) {
			show_404();
		}

		$where = [
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		];

		$query = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $row) {
			$data = array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value) {
				$data['active'] = 1;
			}

			// run query
			$query = $this->db->update('orgs_contacts', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}
	}

	/**
	 * edit a contact
	 * @param  int $contactID
	 * @param int $orgID
	 * @return void
	 */
	public function edit($contactID = NULL, $orgID = NULL)
	{

		$contact_info = new stdClass();

		// check if editing
		if ($contactID != NULL) {

			// check if numeric
			if (!ctype_digit($contactID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'orgs_contacts.contactID' => $contactID,
				'orgs_contacts.accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->select('orgs_contacts.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('orgs_contacts_newsletters') . '.brandID SEPARATOR \',\') AS newsletters')->join('orgs_contacts_newsletters', 'orgs_contacts.contactID = orgs_contacts_newsletters.contactID', 'left')->from('orgs_contacts')->where($where)->group_by('orgs_contacts.contactID')->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$contact_info = $row;
				$orgID = $contact_info->orgID;
			}

		}

		// required
		if ($orgID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$org_info = $row;
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Contact';
		if ($contactID != NULL) {
			$submit_to = 'customers/contacts/edit/' . $contactID;
			$title = $contact_info->name;
		} else {
			$submit_to = 'customers/contacts/' . $orgID . '/new/';
		}
		$return_to = 'customers/contacts/' . $orgID;
		$icon = 'user';
		$tab = 'contacts';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/contacts/' . $orgID] = 'Contacts';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('position', 'Position', 'trim|xss_clean|required');
			$this->form_validation->set_rules('tel', 'Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email|callback_check_email[' . $contactID . ']');
			$this->form_validation->set_rules('password', 'Password', 'trim|xss_clean|min_length[8]|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean');
			$this->form_validation->set_rules('notify', 'Notify', 'trim|xss_clean|callback_notify_need_password');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'position' => set_value('position'),
					'tel' => set_value('tel'),
					'mobile' => set_value('mobile'),
					'email' => set_value('email'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($contactID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['orgID'] = $orgID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// check if password entered
				if (set_value('password') != '') {
					// generate hash
					$password_hash = password_hash(set_value('password'), PASSWORD_BCRYPT);

					// check hash
					if (password_verify(set_value('password'), $password_hash)) {

						// save
						$data['password'] = $password_hash;

					}
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($contactID == NULL) {
						// insert
						$query = $this->db->insert('orgs_contacts', $data);
						$contactID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'contactID' => $contactID
						);

						// update
						$query = $this->db->update('orgs_contacts', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// update newsletter
						if ($brands->num_rows() > 0) {
							$newsletters = $this->input->post('newsletters');
							if (!is_array($newsletters)) {
								$newsletters = array();
							}
							foreach ($brands->result() as $brand) {
								// set where
								$where = array(
									'brandID' => $brand->brandID,
									'contactID' => $contactID,
									'accountID' => $this->auth->user->accountID
								);

								// process
								if (in_array($brand->brandID, $newsletters)) {
									// check if exists
									$res = $this->db->from('orgs_contacts_newsletters')->where($where)->limit(1)->get();

									// if not, insert
									if ($res->num_rows() == 0) {
										$data = $where;
										$this->db->insert('orgs_contacts_newsletters', $data);
									}
								} else {
									// remove
									$this->db->delete('orgs_contacts_newsletters', $where, 1);
								}
							}
						}

						// tell user
						$success = set_value('name') . ' has been ';
						if (isset($just_added)) {
							$success .= 'created';
						} else {
							$success .= 'updated';
						}

						if (set_value('notify') == 1 && $this->crm_library->send_customer_welcome_email($contactID, $this->input->post('password'))) {
							$success .= ' and contact notified';
						}
						$success .= ' successfully.';

						$this->session->set_flashdata('success', $success);

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
			'contact_info' => $contact_info,
			'org_id' => $orgID,
			'contactID' => $contactID,
			'brands' => $brands,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/contact', $data);
	}

	/**
	 * delete a contact
	 * @param  int $orgID
	 * @return mixed
	 */
	public function remove($contactID = NULL) {

		// check params
		if (empty($contactID)) {
			show_404();
		}

		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$contact_info = $row;

			if ($contact_info->isMain == 1) {
				show_404();
			}

			// all ok, delete
			$query = $this->db->delete('orgs_contacts', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $contact_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $contact_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers/contacts/' . $contact_info->orgID;

			redirect($redirect_to);
		}
	}

	/**
	 * set a contact as the main contact
	 * @param  int $orgID
	 * @return mixed
	 */
	public function main($contactID = NULL) {

		// check params
		if (empty($contactID)) {
			show_404();
		}

		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$contact_info = $row;

			// all ok, update
			$data = array(
				'isMain' => 1
			);

			$query = $this->db->update('orgs_contacts', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $contact_info->name . ' is now the main contact.');

				// unset previous main contact
				$data = array(
					'isMain' => NULL
				);

				$where = array(
					'orgID' => $contact_info->orgID,
					'contactID !=' => $contactID,
					'accountID' => $this->auth->user->accountID
				);

				$query = $this->db->update('orgs_contacts', $data, $where);
			} else {
				$this->session->set_flashdata('error', $contact_info->name . ' could not be set as the main contact.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers/contacts/' . $contact_info->orgID;

			redirect($redirect_to);
		}
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
	 * validation function for checking email is unique, except in specified record
	 * @param  string $email
	 * @param  int $contactID
	 * @return bool
	 */
	public function check_email($email = NULL, $contactID = NULL) {
		// if no email specified, skip
		if (empty($email)) {
			return TRUE;
		}

		$has_password = FALSE;

		// check if contact already has password
		if (!empty($contactID)) {
			$where = array(
				'contactID' => $contactID,
				'password IS NOT NULL' => NULL,
				'accountID' => $this->auth->user->accountID
			);
			$query = $this->db->get_where('orgs_contacts', $where, 1);

			// check results
			if ($query->num_rows() == 1) {
				$has_password = TRUE;
			}
		}

		// if no password posted and doesn't already have password, skip
		if (!$this->input->post('password') && $has_password != TRUE) {
			return TRUE;
		}

		// check email not in use with anyone on this account with a password
		$where = array(
			'email' => $email,
			'password IS NOT NULL' => NULL,
			'accountID' => $this->auth->user->accountID
		);

		// exclude current user, if set
		if (!empty($contactID)) {
			$where['contactID !='] = $contactID;
		}

		// check
		$query = $this->db->get_where('orgs_contacts', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * validation function for notify as need password
	 * @param  string $val
	 * @return bool
	 */
	public function notify_need_password($val) {

		// valid if empty
		if (empty($val)) {
			return TRUE;
		}

		// check has email and password
		if (empty($this->input->post('email')) || empty($this->input->post('password'))) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file contacts.php */
/* Location: ./application/controllers/customers/contacts.php */
