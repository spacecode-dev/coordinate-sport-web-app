<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth {

	private $CI;
	public $user = FALSE;
	public $account = FALSE;
	public $account_overridden = FALSE;
	public $user_overridden = FALSE;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();

		// check if user is logged in
		$this->has_auth();
	}

	// if the feature has all the compounds are allowed, then the feature is allowed
	// else delete the feature from the list
	private $compoundFeatures = [
		'participants' => ['participants', 'participant_account_module'],
		'export' => ['export']
	];

	/**
	 * check if a user is authorised/logged in
	 * @return mixed
	 */
	public function has_auth() {
		// get user id from session
		$user_id = $this->CI->session->userdata('user_id');

		// check if user id set in session and is digit
		if ($user_id === FALSE || !ctype_digit($user_id)) {
			return FALSE;
		}

		// check user id in database
		$where = array(
			'staff.staffID' => $user_id,
			'staff.active' => 1,
			'accounts.active' => 1
		);

		// run query
		$query = $this->CI->db->select('staff.*, brands.name as brand')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID', 'inner')->join('brands', 'staff.brandID = brands.brandID', 'left')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($query->result() as $row) {
			$this->user = $row;
		}

		// update last activity and reset hashes
		$data = array(
			'last_login' => mdate('%Y-%m-%d %H:%i:%s'),
			'reset_hash' => NULL,
			'reset_at' => NULL,
			'invalid_logins' => 0,
			'locked_until' => NULL
		);

		unset($where['accounts.active']);

		$query = $this->CI->db->update('staff', $data, $where);

		// override user id
		$user_id_override = $this->CI->session->userdata('user_id_override');

		// check not self
		if (!empty($user_id_override) && $user_id_override != $user_id) {
			// look up
			$where = array(
				'staff.staffID' => $user_id_override,
				'staff.active' => 1
			);

			// run query
			$query = $this->CI->db->select('staff.*, brands.name as brand')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID', 'inner')->join('brands', 'staff.brandID = brands.brandID', 'left')->where($where)->limit(1)->get();

			// check for result
			if ($query->num_rows() == 1) {
				foreach ($query->result() as $row) {
					$this->user = $row;
				}
				$this->user_overridden = TRUE;
			}
		}
		
		

		// store account ID
		$this->CI->session->set_userdata('account_id', $this->user->accountID);

		// unset password as don't want to reveal it even though encrypted
		unset($this->user->password);

		// override account id
		$account_id_override = $this->CI->session->userdata('account_id_override');

		if (!empty($account_id_override)) {
			// look up
			$where = array(
				'accountID' => $account_id_override
			);

			// run query
			$query = $this->CI->db->from('accounts')->where($where)->limit(1)->get();

			// check for result
			if ($query->num_rows() == 1) {
				$this->user->accountID = $account_id_override;
				$this->account_overridden = TRUE;
			}
		}
		
		//get account name
		
		$query = $this->CI->db->from("accounts")->where("accountID" , $this->user->accountID)->get();
		$this->user->accountName = '';
		foreach($query->result() as $result){
			$this->user->accountName = $result->company;
		}

		// get account info
		$this->get_account();

		// check if expired if account not overidden
		if ($this->account_overridden !== TRUE && (($this->account->status == 'paid' && !empty($this->account->paid_until) && strtotime($this->account->paid_until) <= strtotime(date('Y-m-d'))) || ($this->account->status == 'trial' && !empty($this->account->trial_until) && strtotime($this->account->trial_until) <= strtotime(date('Y-m-d'))))) {
			$this->logout();
			$this->CI->session->set_flashdata('login_reason', 'Account expired. Please contact us.');
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
	public function check_auth($email, $password) {
		// both params required
		if (empty($email) || empty($password)) {
			return FALSE;
		}

		// check for matching user in database
		$where = array(
			'staff.email' => $email,
			'staff.active' => 1,
			'accounts.active' => 1
		);

		// run query
		$query = $this->CI->db->select('staff.*')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID', 'inner')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($query->result() as $row) {
			$this->user = $row;
		}
		
		

		// check if locked out
		if ($this->CI->settings_library->get('max_invalid_logins', 'default') > 0 && !empty($this->user->locked_until) && strtotime($this->user->locked_until) > strtotime(mdate('%Y-%m-%d %H:%i:%s'))) {
			$minutes = ceil((strtotime($this->user->locked_until) - time())/60);
			$plural = NULL;
			if ($minutes != 1) {
				$plural = 's';
			}
			$this->CI->session->set_flashdata('login_reason', 'This user account has been temporarily locked. Please check your login details and try again in ' . $minutes . ' minute' . $plural . ' or click on ‘Forgot your password?’ below.');
			$this->user = FALSE;
			return FALSE;
		}

		// check matches
		if (!$this->verify_password($password, $this->user->password, $email)) {
			// track invalid logins if bar is set above 0
			if ($this->CI->settings_library->get('max_invalid_logins', 'default') > 0) {
				// increment invalid logins by 1
				$invalid_logins = intval($this->user->invalid_logins);

				// increase
				$invalid_logins++;

				$where = array(
					'email' => $email,
					'active' => 1
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
				$this->CI->db->update('staff', $data, $where);
			}

			$this->user = FALSE;
			return FALSE;
		}
		
		//get account name
		
		$query = $this->CI->db->from("accounts")->where("accountID" , $this->user->accountID)->get();
		$this->user->accountName = '';
		foreach($query->result() as $result){
			$this->user->accountName = $result->company;
		}

		// unset password as don't want to reveal it even though encrypted
		unset($this->user->password);

		// store id in session
		$this->CI->session->set_userdata('user_id', $this->user->staffID);

		// check and store user info
		if (!$this->has_auth()) {
			return FALSE;
		}

		// all ok
		return TRUE;
	}

	/**
	 * get account details
	 * @return bool
	 */
	public function get_account() {
		// check if logged in
		if ($this->user === FALSE) {
			return FALSE;
		}

		// look up account
		$where = array(
			'accountID' => $this->user->accountID,
			'active' => 1
		);

		// run query
		$query = $this->CI->db->from('accounts')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// get account info
		foreach ($query->result() as $row) {
			$this->account = $row;
		}

		// get features
		$this->account->features = array();

		// look up features
		$where = array(
			'planID' => $this->account->planID
		);

		// run query
		$query = $this->CI->db->from('accounts_plans')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $features) {}

			// get active features
			$active_features = array();

			// store name of plan in account
			$this->account->plan = $features['name'];

			// remove unrelated fields
			unset($features['planID'], $features['byID'], $features['name'], $features['added'], $features['modified']);

			// store active features from plan
			foreach ($features as $key => $value) {
				if ($value == 1) {
					$active_features[] = $key;
				}
			}

			// store active addons or all if plan allows
			foreach ($this->account as $key => $value) {
				if (in_array('addons_all', $active_features) || (substr($key, 0, 5) == 'addon' && $value == 1)) {
					$active_features[] = substr($key, 6);
				}
			}

			// if current account is admin enable and user level is directors can manage sites
			if ($this->account->admin == 1 && in_array($this->user->department, array('office', 'management', 'directors'))) {
				$active_features[] = 'accounts';
			}

			// remove duplicates
			$active_features = array_unique($active_features);

			// filter compound permissions
			foreach ($this->compoundFeatures as $feature => $compounds) {
				if (
					count(array_intersect($active_features, $compounds))
					!= count($compounds)
				) {
					$active_features = array_diff($active_features, $compounds);
				}
			}
			// save
			$this->account->features = $active_features;
		}

		// all ok
		return TRUE;
	}

	/**
	 * logs a user out
	 * @return void
	 */
	public function logout() {
		// unset session vars
		$this->CI->session->unset_userdata('user_id');
		$this->CI->session->unset_userdata('user_id_override');
		$this->CI->session->unset_userdata('account_id');
		$this->CI->session->unset_userdata('account_id_override');

		// unset var
		$this->user = FALSE;

		// tell user
		$this->CI->session->set_flashdata('success', 'You are now logged out.');

		// set flag to show just logged out
		$this->CI->session->set_flashdata('logged_out', TRUE);

		return TRUE;
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

	/**
	 * verify s password is correct
	 * @param  string $password
	 * @param  string $password_hash
	 * @return boolean
	 */
	public function verify_password($password, $password_hash, $email = NULL) {

		// verify if correct
		if (password_verify($password, $password_hash)) {
			return TRUE;
		}

		// try legacy method
		if (!empty($email) && crypt(md5($password), md5(strtolower($email))) == $password_hash) {
			return TRUE;
		}

		return FALSE;
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
			'staff.email' => $email,
			'staff.active' => 1,
			'accounts.active' => 1
		);

		// run query
		$query = $this->CI->db->select('staff.*')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($query->result() as $row) {
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
			'staffID' => $user_info->staffID
		);

		// update
		$this->CI->db->update('staff', $data, $where);

		// check if updated
		if ($this->CI->db->affected_rows() > 0) {
			// set message
			$message = $this->CI->settings_library->get('email_reset_password', 'default');

			// set tags
			$smart_tags = array(
				'first_name' => $user_info->first,
				'reset_link' => site_url('reset/' . $reset_hash)
			);

			// replace
			foreach ($smart_tags as $key => $value) {
				$message = str_replace('{' . $key . '}', $value, $message);
			}

			// send
			$this->CI->crm_library->send_email($user_info->email, 'Reset Password', $message);

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
			'staff.active' => 1,
			'accounts.active' => 1
		);

		// run query
		$query = $this->CI->db->select('staff.*')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// get user info
		foreach ($query->result() as $row) {
			return $row;
		}
	}

	/**
	 * check if account has required features
	 * @param  mixed $features_required
	 * @return boolean
	 */
	public function has_features($features_required) {

		if (!is_array($features_required)) {
			$features_required = array($features_required);
		}

		// if nothing required return true
		if (count($features_required) == 0) {
			return TRUE;
		}

		//enable messages for admins
		if ($features_required[0] == 'messages' && $this->user->department == 'directors') {
		    return TRUE;
        }

		// check if features required match
		if ($this->user !== FALSE && count(array_intersect($features_required, $this->account->features)) == count($features_required)) {
			return TRUE;
		}

		return FALSE;
	}

	// get bookings site
	public function get_bookings_site() {
		if (!empty($this->account->booking_customdomain)) {
			return PROTOCOL . '://' . $this->account->booking_customdomain;
		} else if (!empty($this->account->booking_subdomain)) {
			return PROTOCOL . '://' . $this->account->booking_subdomain . '.' . ROOT_DOMAIN;
		}
		return FALSE;
	}
}
