<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Online_booking {

	private $CI;
	public $accountID = FALSE;
	public $account = FALSE;
	public $user = FALSE;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	/**
	 * init
	 * @return bool
	 */
	public function init() {
		// look up online booking site by domain account id
		$this->accountID = resolve_online_booking_domain();
		$where = array(
			'accounts.accountID' => $this->accountID,
			'accounts.active' => 1
		);
		$res = $this->CI->db
			->from('accounts')
			->where($where)
			->limit(1)
			->get();

		// not found
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get account info
		foreach ($res->result() as $account_info) {}

		// check if account expired
		if (in_array($account_info->status, array('paid', 'trial'))) {
			$field_until = $account_info->status . '_until';
			if (!empty($account_info->$field_until) && strtotime($account_info->$field_until) <= strtotime(date('Y-m-d'))) {
				return FALSE;
			}
		}

		// get default view
		switch ($this->CI->settings_library->get('default_view', $this->accountID)) {
			case 2:
				$account_info->default_view = site_url("calendar");
				break;
			case 3:
				$account_info->default_view = site_url("map");
				break;
			default:
				$account_info->default_view = site_url("list");
			break;
		}

		// all ok
		$this->account = $account_info;

		// update cart library
		$args = array(
			'accountID' => $this->accountID
		);
		$this->CI->cart_library->init($args);

		// check if user logged in
		$this->has_auth();

		return TRUE;
	}

	/**
	 * check if a user is logged in and get user info
	 * @return mixed
	 */
	public function has_auth() {
		// get user id from session
		$userID = $this->CI->session->userdata('userID');

		// check if user id set in session and is digit
		if ($userID === FALSE || !ctype_digit($userID)) {
			return FALSE;
		}

		// check user id in database
		$where = array(
			'family_contacts.contactID' => $userID,
			'family_contacts.accountID' => $this->accountID
		);

		// run query
		$res = $this->CI->db->select('family_contacts.*, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('family_contacts_tags') . ' .tagID) as contact_tags')->from('family_contacts')->join('family_contacts_tags', 'family_contacts.contactID = family_contacts_tags.contactID', 'left')->where($where)->group_by('family_contacts.contactID')->get();

		// check for result
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($res->result() as $row) {
			$this->user = $row;
		}

		// get tags
		$tags = array();
		$contact_tags = explode(",", $row->contact_tags);
		if (is_array($contact_tags) && count($contact_tags) > 0) {
			// remove empty
			$contact_tags = array_filter($contact_tags);
			// return links
			foreach ($contact_tags as $tagID) {
				$tags[] = $tagID;
			}
		}
		$this->user->contact_tags = $tags;

		//Get disabilities
		$this->user->disability = array();
		$where = array(
			'accountID' => $this->accountID,
			'contactID' => $userID
		);
		$res = $this->CI->db->select('*')->from('family_disabilities')->where($where)->limit(1)->get();
		if ($res->num_rows() > 0) {
			$this->user->disability = $res->result()[0];
		}

		// update last activity and reset hashes
		$data = array(
			'last_login' => mdate('%Y-%m-%d %H:%i:%s'),
			'reset_hash' => NULL,
			'reset_at' => NULL,
			'invalid_logins' => 0,
			'locked_until' => NULL
		);

		$res = $this->CI->db->update('family_contacts', $data, $where, 1);

		// unset password as don't want to reveal it even though encrypted
		unset($this->user->password);

		// update cart library
		$args = array(
			'accountID' => $this->accountID,
			'contactID' => $this->user->contactID
		);
		$this->CI->cart_library->init($args);

		// all ok
		return TRUE;
	}

	/**
	 * validates a user and logs them in
	 * @param  string $email
	 * @param  string $password
	 * @return mixed
	 */
	public function check_auth($email, $password) {
		// both params required
		if (empty($email) || empty($password)) {
			return FALSE;
		}

		// check for matching user in database
		$where = array(
			'family_contacts.email' => $email,
			'family_contacts.accountID' => $this->accountID
		);

		// run query
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		// check for result
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($res->result() as $row) {
			$this->user = $row;
		}

		// check if locked out
		if ($this->CI->settings_library->get('max_invalid_logins', 'default') > 0 && !empty($this->user->locked_until) && strtotime($this->user->locked_until) > strtotime(mdate('%Y-%m-%d %H:%i:%s'))) {
			$minutes = ceil((strtotime($this->user->locked_until) - time())/60);
			$plural = NULL;
			if ($minutes != 1) {
				$plural = 's';
			}
			$this->CI->session->set_flashdata('login_reason', 'This user account has been temporarily locked. Please check your login details and try again in ' . $minutes . ' minute' . $plural . ' or ' . anchor('account/reset', 'reset your password'));
			$this->user = FALSE;
			return FALSE;
		}

		// check matches
		if (!password_verify($password, $this->user->password)) {
			// track invalid logins if bar is set above 0
			if ($this->CI->settings_library->get('max_invalid_logins', 'default') > 0) {
				// increment invalid logins by 1
				$invalid_logins = intval($this->user->invalid_logins);

				// increase
				$invalid_logins++;

				$where = array(
					'email' => $email,
					'accountID' => $this->accountID
				);

				$data = array(
					'invalid_logins' => $invalid_logins
				);

				if ($invalid_logins >= intval($this->CI->settings_library->get('max_invalid_logins', 'default'))) {
					// set valid time + 15 minutes
					$datestring = "%Y-%m-%d %H:%i:%s";
					$time = now()+(60*15);
					$data['locked_until'] = mdate($datestring, $time);
					$this->CI->session->set_flashdata('login_reason', 'This user account has been temporarily locked. Please check your login details and try again in 15 minutes or click on ‘Forgot your password?’ below.');
				}

				// update
				$this->CI->db->update('family_contacts', $data, $where);
			}

			$this->user = FALSE;
			return FALSE;
		}

		// unset password as don't want to reveal it even though encrypted
		unset($this->user->password);

		// store id in session
		$this->CI->session->set_userdata('userID', $this->user->contactID);

		// check and store user info
		if (!$this->has_auth()) {
			return FALSE;
		}

		// all ok
		return TRUE;
	}

	/**
	 * validates a user and logs them in
	 * @param  string $email
	 * @param  string $password
	 * @return mixed
	 */
	public function check_sso_auth($email, $password) {
		// both params required
		if (empty($email) || empty($password)) {
			return FALSE;
		}

		// check for matching user in database
		$where = array(
			'family_contacts.email' => $email,
			'family_contacts.accountID' => $this->accountID
		);

		// run query
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		// check for result
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($res->result() as $row) {
			$this->user = $row;
		}

		// check if locked out
		if ($this->CI->settings_library->get('max_invalid_logins', 'default') > 0 && !empty($this->user->locked_until) && strtotime($this->user->locked_until) > strtotime(mdate('%Y-%m-%d %H:%i:%s'))) {
			$minutes = ceil((strtotime($this->user->locked_until) - time())/60);
			$plural = NULL;
			if ($minutes != 1) {
				$plural = 's';
			}
			$this->CI->session->set_flashdata('login_reason', 'This user account has been temporarily locked. Please check your login details and try again in ' . $minutes . ' minute' . $plural . ' or ' . anchor('account/reset', 'reset your password'));
			$this->user = FALSE;
			return FALSE;
		}

		// check matches
		if (!password_verify($password, $this->user->password)) {
			// track invalid logins if bar is set above 0
			if ($this->CI->settings_library->get('max_invalid_logins', 'default') > 0) {
				// increment invalid logins by 1
				$invalid_logins = intval($this->user->invalid_logins);

				// increase
				$invalid_logins++;

				$where = array(
					'email' => $email,
					'accountID' => $this->accountID
				);

				$data = array(
					'invalid_logins' => $invalid_logins
				);

				if ($invalid_logins >= intval($this->CI->settings_library->get('max_invalid_logins', 'default'))) {
					// set valid time + 15 minutes
					$datestring = "%Y-%m-%d %H:%i:%s";
					$time = now()+(60*15);
					$data['locked_until'] = mdate($datestring, $time);
					$this->CI->session->set_flashdata('login_reason', 'This user account has been temporarily locked. Please check your login details and try again in 15 minutes or click on ‘Forgot your password?’ below.');
				}

				// update
				$this->CI->db->update('family_contacts', $data, $where);
			}

			$this->user = FALSE;
			return FALSE;
		}

		$this->CI->session->set_userdata('ssoUserID', $this->user->contactID);
		$this->CI->session->set_userdata('first_name', $this->user->first_name);
		$this->CI->session->set_userdata('last_name', $this->user->last_name);

		// unset password as don't want to reveal it even though encrypted
		unset($this->user->password);

		// all ok
		return TRUE;
	}

	public function check_active() {
		if ($this->user && $this->user->active == 0) {
			return false;
		}

		return true;
	}

	public function destroy_session() {
		$this->CI->session->sess_destroy();
		$this->user = FALSE;
		return true;
	}

	public function require_auth($required = TRUE) {
		switch ($required) {
				case TRUE:
					if ($this->has_auth() !== TRUE) {
						$this->CI->session->set_flashdata('info', 'Please log in or ' . anchor('account/register', 'register') . ' to continue');
						$this->CI->session->set_userdata('redirect_to', current_url());
						redirect('account/login');
						return TRUE;
					}
					break;
				case FALSE:
					// if logged in, redirect to account
					if ($this->has_auth() === TRUE) {
						redirect('account');
						return TRUE;
					}
					break;
		}
	}

	/**
	 * Validate online booking profiles
	 * @param string $mode
	 * @param null $details
	 * @return void
	 */
	public function validate_profile_details($mode = "register", $details = null) {
		// load libraries
		$this->CI->load->library('form_validation');

		//If no details provided or in existing mode, pull details to verify from existing source
		if ($mode=="existing" || $details==null) {
			$mode = "existing"; $details = (array)$this->user;
			//CI accepts null for required fields. So convert these to empty
			foreach ($details as $k => $detail) {
				if (is_null($detail)) {
					$details[$k] = "";
				}
			}
			$this->CI->form_validation->set_data($details);
		}

		$fields = get_fields("account_holder");

		$this->CI->form_validation->set_rules('booking_for', 'Booking For', 'trim|xss_clean|required|in_list[child,contact,child_and_contact]');
		$this->CI->form_validation->set_rules('title', 'Title', 'trim|xss_clean' . required_field('title', $fields, 'validation'));
		$this->CI->form_validation->set_rules('first_name', 'First Name', 'trim|xss_clean|required');
		$this->CI->form_validation->set_rules('last_name', 'Last Name', 'trim|xss_clean|required');
		if ($this->CI->settings_library->get('require_dob', $this->accountID) == 1 || required_field('dob', $fields)) {
			$this->CI->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean|callback_check_dob');
		} else {
			$this->CI->form_validation->set_rules('dob', 'Date of Birth', 'trim|xss_clean|callback_check_dob');
		}

		if ($this->CI->settings_library->get('require_mobile', $this->accountID) == 1 || required_field('mobile', $fields)) {
			$this->CI->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean|callback_check_mobile');
		} else {
			$this->CI->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean|callback_check_mobile');
		}
		$this->CI->form_validation->set_rules('phone', 'Other Phone', 'trim|xss_clean' . (show_field('mobile',$fields) ? '|callback_phone_or_mobile[' . $details['mobile'] . ']' : "") . required_field('otherPhone', $fields, 'validation'));
		$this->CI->form_validation->set_rules('workPhone', 'Work Phone', 'trim|xss_clean' . required_field('workPhone', $fields, 'validation'));

		$this->CI->form_validation->set_rules('address1', 'Address 1', 'trim|xss_clean' . required_field('address', $fields, 'validation'));
		$this->CI->form_validation->set_rules('address2', 'Address 2', 'trim|xss_clean' . required_field('address2', $fields, 'validation'));
		$this->CI->form_validation->set_rules('address3', 'Address 3', 'trim|xss_clean' . required_field('address3', $fields, 'validation'));
		$this->CI->form_validation->set_rules('town', 'Town', 'trim|xss_clean' . required_field('town', $fields, 'validation'));
		$this->CI->form_validation->set_rules('county', localise('county', $this->accountID), 'trim|xss_clean' . required_field('county', $fields, 'validation'));
		$this->CI->form_validation->set_rules('postcode', 'Post Code', 'trim|xss_clean' . (show_field('postcode',$fields) ? '|callback_check_postcode' : "") . required_field('postcode', $fields, 'validation'));

		//Validate optional fields
		$disabilityFailed = false;
		$this->CI->form_validation->set_rules('gender', 'Gender', 'trim|xss_clean' . required_field('gender', $fields, 'validation'));
		$this->CI->form_validation->set_rules('gender_specify', 'Specific Gender', 'trim|xss_clean' . (@$details['gender']=="please_specify" ? required_field('gender_specify', $fields, 'validation') : ""));
		//Only validate on main account holder
		if (!isset($this->user->main) || $this->user->main) {
			$this->CI->form_validation->set_rules('gender_since_birth', 'Gender Since Birth', 'trim|xss_clean' . required_field('gender_since_birth', $fields, 'validation'));
			$this->CI->form_validation->set_rules('sexual_orientation', 'Sexual Orientation', 'trim|xss_clean' . required_field('sexual_orientation', $fields, 'validation'));
			$this->CI->form_validation->set_rules('sexual_orientation_specify', 'Specific Sexual Orientation', 'trim|xss_clean' . (@$details['sexual_orientation']=="please_specify" ? required_field('sexual_orientation_specify', $fields, 'validation') : ""));
		}
		$this->CI->form_validation->set_rules('medical', 'Medical Information', 'trim|xss_clean' . required_field('medical', $fields, 'validation'));

		if (empty($details['disability']) && required_field('disability', $fields)) {
			$disabilityFailed = true;
		}
		$this->CI->form_validation->set_rules('disability_info', 'Disability Information', 'trim|xss_clean' . required_field('disability_info', $fields, 'validation'));
		$this->CI->form_validation->set_rules('ethnic_origin', 'Ethnic Origin', 'trim|xss_clean' . required_field('ethnic_origin', $fields, 'validation'));
		$this->CI->form_validation->set_rules('religion', 'Religion', 'trim|xss_clean' . required_field('religion', $fields, 'validation'));
		$this->CI->form_validation->set_rules('religion_specify', 'Specific Religion', 'trim|xss_clean' . (@$details['religion']=="please_specify" ? required_field('religion_specify', $fields, 'validation') : ""));

		$this->CI->form_validation->set_rules('emergency_contact_1_name', 'Emergency Contact 1 Name', 'trim|xss_clean' . required_field('emergency_contact_1_name', $fields, 'validation'));
		$this->CI->form_validation->set_rules('emergency_contact_1_phone', 'Emergency Contact 1 Phone', 'trim|xss_clean' . required_field('emergency_contact_1_phone', $fields, 'validation'));
		$this->CI->form_validation->set_rules('emergency_contact_2_name', 'Emergency Contact 2 Name', 'trim|xss_clean' . required_field('emergency_contact_2_name', $fields, 'validation'));
		$this->CI->form_validation->set_rules('emergency_contact_2_phone', 'Emergency Contact 2 Phone', 'trim|xss_clean' . required_field('emergency_contact_2_phone', $fields, 'validation'));

		if ($mode == 'register') {
			$this->CI->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email|callback_check_email');
			$this->CI->form_validation->set_rules('email_confirm', 'Confirm Email', 'trim|xss_clean|required|matches[email]');
			$this->CI->form_validation->set_rules('password', 'Password', 'trim|xss_clean|required|min_length[8]|matches[password_confirm]');
			$this->CI->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean|required');
		} else {
			$this->CI->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email'.($mode!="existing" ? "|callback_check_email[".$this->user->contactID."]'" : ""));
			//Only validate password confirmations is we're not validating existing details
			if ($mode!="existing") {
				$this->CI->form_validation->set_rules('password', 'Password', 'trim|xss_clean|min_length[8]|matches[password_confirm]');
				$this->CI->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean');
				$this->CI->form_validation->set_rules('password_current', 'Current Password', 'trim|xss_clean|required|callback_check_current_password');
			}
		}

		if ($mode == 'register') {
			$this->CI->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
			$this->CI->form_validation->set_rules('privacy_agreed', 'Agreement to privacy policy', 'trim|xss_clean|required|callback_is_checked');
			if (!empty($this->CI->settings_library->get('participant_safeguarding', $this->accountID))) {
				$this->CI->form_validation->set_rules('safeguarding_agreed', 'Agreement to safeguarding policy', 'trim|xss_clean|required|callback_is_checked');
			}
			if (!empty($this->CI->settings_library->get('participant_data_protection_notice', $this->accountID))) {
				$this->CI->form_validation->set_rules('data_protection_agreed', 'Agreement to data protection notice', 'trim|xss_clean|required|callback_is_checked');
			}
			$this->CI->form_validation->set_rules('source', 'Source', 'trim|xss_clean');
			if ($details['source'] == 'Other') {
				$this->CI->form_validation->set_rules('source_other', 'Other (Please specify)', 'trim|required|xss_clean');
			} else {
				$this->CI->form_validation->set_rules('source_other', 'Other (Please specify)', 'trim|xss_clean');
			}
		}

		if ($this->CI->form_validation->run() == FALSE || $disabilityFailed) {
			$errors = $this->CI->form_validation->error_array();
			if ($disabilityFailed) {
				$errors[] = 'Disability is required';
			}
			return $errors;
		}
		else {
			return true;
		}
	}

	/**
	 * logs a user out
	 * @return void
	 */
	public function logout() {
		// destory session
		$this->CI->session->sess_destroy();

		// unset var
		$this->user = FALSE;

		// tell user
		$this->CI->session->set_flashdata('success', 'You are now logged out.');

		return TRUE;
	}

	/**
	 * set a reset hash and expiry in the database to allow the user to reset their password
	 * @param  string $email
	 * @return mixed
	 */
	public function reset_password($email) {
		// email required
		if (empty($email)) {
			return FALSE;
		}

		// check for matching user in database
		$where = array(
			'email' => $email,
			'accountID' => $this->accountID
		);

		// run query
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		// check for result
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($res->result() as $row) {
			$user_info = $row;
		}

		// generate reset hash
		$reset_hash = random_string('alnum', 32);

		// set valid time + 15 minutes
		$datestring = "%Y-%m-%d %H:%i:%s";
		$time = now()+(60*15);

		// prepare data for update
		$data = array(
			'reset_hash' => $reset_hash,
			'reset_at' => mdate($datestring, $time)
		);

		// set where
		$where = array(
			'contactID' => $user_info->contactID
		);

		// update
		$this->CI->db->update('family_contacts', $data, $where, 1);

		// check if updated
		if ($this->CI->db->affected_rows() > 0) {
			// set message
			$subject = $this->CI->settings_library->get('email_participant_reset_password_subject', $this->accountID);
			$message = $this->CI->settings_library->get('email_participant_reset_password', $this->accountID);

			// set tags
			$smart_tags = array(
				'contact_title' => ucwords($user_info->title),
				'contact_first' => $user_info->first_name,
				'contact_last' => $user_info->last_name,
				'contact_email' => $user_info->email,
				'company' => $this->account->company,
				'reset_link' => site_url('account/reset/' . $reset_hash)
			);

			// replace
			foreach ($smart_tags as $key => $value) {
				$message = str_replace('{' . $key . '}', $value, $message);
				$subject = str_replace('{' . $key . '}', $value, $subject);
			}

			// send
			$this->CI->crm_library->send_email($user_info->email, $subject, $message);

			return TRUE;
		}

		// problem updating DB, return false
		return FALSE;
	}

	/**
	 * check hash is valid
	 * @param  string $reset_hash
	 * @return mixed
	 */
	public function check_hash($reset_hash) {
		// hash required
		if (empty($reset_hash)) {
			return FALSE;
		}

		// check for matching user in database within time limit
		$where = array(
			'reset_hash' => $reset_hash,
			'reset_at >' => mdate('%Y-%m-%d %H:%i:%s'),
			'accountID' => $this->accountID
		);

		// run query
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		// check for result
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($res->result() as $row) {
			return $row;
		}
	}

	/**
	 * encrypt password
	 * @param string $password
	 * @return mixed
	 */
	public function encrypt_password($password = NULL) {
		// password required
		if (empty($password)) {
			return FALSE;
		}

		// encrypt new password
		$password_hash = password_hash($password, PASSWORD_BCRYPT);

		// verify ok
		if (!password_verify($password, $password_hash)) {
			return FALSE;
		}

		return $password_hash;
	}
}
