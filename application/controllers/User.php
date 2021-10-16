<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller {

	public function __construct() {
		// public, don't require login
		parent::__construct(TRUE);
	}

	public function login()
	{
		// check if already logged in
		if ($this->auth->user !== FALSE) {
			redirect('/');
			return TRUE;
		}

		// set defaults
		$title = 'Sign In';
		$instruction = 'Enter your details to login to your account:';
		$email = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$redirect_to = '/';

		// check for redirect to, if not just logged out
		if ($this->session->flashdata('logged_out') != TRUE) {
			if (!empty($_SERVER['HTTP_REFERER'])) {
				$redirect_to = $_SERVER['HTTP_REFERER'];
			}

			if ($this->session->flashdata('redirect_to')) {
				$redirect_to = $this->session->flashdata('redirect_to');
			}

			if ($this->input->post('redirect_to') != '') {
				$redirect_to = $this->input->post('redirect_to');
			}
		}

		// cleanse redirect to
		$redirect_to = parse_url($redirect_to, PHP_URL_PATH);

		// set login attempts to 0 for any user with an expired locked until date
		$where = array(
			'locked_until <' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$data = array(
			'locked_until' => NULL,
			'invalid_logins' => 0
		);
		$res = $this->db->update('staff', $data, $where);

		// if posted
		if ($this->input->post()) {
			// get email and password
			$email = $this->input->post('email');
			$password = $this->input->post('password');

			// check auth
			if (!$this->auth->check_auth($email, $password)) {

				// set error
				if (empty($email) && empty($password)) {
					$error = 'Please enter your email address and password';
				} else if (!empty($email) && empty($password)) {
					$error = 'Please enter your password';
				} else if (empty($email) && !empty($password)) {
					$error = 'Please enter your email address';
				} else if (!empty($this->session->flashdata('login_reason'))) {
					$error = $this->session->flashdata('login_reason');
				} else {
					$error = 'You have entered an incorrect email address or password. Please check your login details and try again.';
				}

			} else {
				// if logging in to admin account, redirect to accounts screen
				if ($this->auth->account->admin == 1) {
					$redirect_to = 'accounts';
				}
				redirect($redirect_to);
				return TRUE;
			}
		} else if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'instruction' => $instruction,
			'email' => $email,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'redirect_to' => $redirect_to,
			'body_class' => 'login'
		);

		// load view
		$this->crm_view('user/login', $data, 'templates/public.php');
	}

	public function logout()
	{
		// handle logout
		$this->auth->logout();

		redirect('/login');
		return TRUE;
	}

	public function reset($reset_hash = NULL)
	{
		// check if already logged in
		if ($this->auth->user !== FALSE) {
			redirect('/');
			return TRUE;
		}

		// load libraries
		$this->load->library('form_validation');

		// if setting new password
		if (!empty($reset_hash)) {
			// if hash invalid
			if (!$user_info = $this->auth->check_hash($reset_hash)) {

				// set data
				$data = array(
					'title' => 'Reset Password',
					'email' => NULL,
					'success' => NULL,
					'error' => 'Invalid or expired confirmation link. Please try again.',
					'body_class' => 'login'
				);

				// load view
				$this->crm_view('user/reset', $data, 'templates/public.php');

				return TRUE;

			} else {
				return $this->set_new_password('reset', 'reset/' . $reset_hash, $user_info->staffID);
			}
		}

		// set defaults
		$title = 'Forgot Password';
		$instruction = 'Enter your email and we\'ll send you instructions to reset your password:';
		$email = NULL;
		$success = NULL;
		$error = NULL;
		$errors = array();
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				// get email
				$email = $this->input->post('email');

				// check account and generate reset hash
				$reset_hash = $this->auth->reset_password($email);

				// tell user
				$success = 'If you\'ve entered a valid email, we\'ve sent you an email with further instructions.';

				$this->session->set_flashdata('success', $success);

				redirect('reset');
			}
		}

		$this->user = FALSE;

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
			'instruction' => $instruction,
			'email' => $email,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'body_class' => 'login'
		);

		$this->crm_view('user/reset', $data, 'templates/public.php');
	}

	public function password_change() {
		// check if password change needed
		if ($this->settings_library->get('force_password_change_every_x_months') == 0 || (!empty($this->auth->user->last_password_change) && strtotime($this->auth->user->last_password_change) > strtotime('-' . $this->settings_library->get('force_password_change_every_x_months') . ' months'))) {
			redirect('/');
			exit();
		}
		return $this->set_new_password('force');
	}

	private function set_new_password($type = NULL, $submit_to = 'password-change', $staffID = NULL) {

		// if force password change, must be logged in
		if ($type == 'force' ) {
			if ($this->auth->user === FALSE) {
				redirect('login');
				return TRUE;
			}
		} else {
			// if reset, must NOT be logged in
			if ($this->auth->user !== FALSE) {
				redirect('/');
				return TRUE;
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Set Password';
		$success_message = 'Your password has been reset. Please login to access your account.';
		$instruction = 'Enter a new password below.';
		$show_login_link = TRUE;
		$redirect_to = 'login';
		$password = NULL;
		$password_confirm = NULL;
		$success = NULL;
		$error = NULL;
		$errors = array();

		if ($type == 'force') {
			$title = 'Password Expired';
			$success_message = 'Your password has been updated.';
			$instruction = 'Enter a new password below as your current password has now expired.';
			$show_login_link = FALSE;
			$redirect_to = '/';
			$staffID = $this->auth->user->staffID;
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('password', 'New Password', 'trim|xss_clean|required|min_length[8]|matches[password_confirm]|callback_check_password_reuse[' . $type . ']');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// get passwords
				$password = $this->input->post('password');
				$password_confirm = $this->input->post('password_confirm');

				// encrypt password
				$password_hash = $this->auth->encrypt_password($password);

				// verify ok
				if (!$password_hash) {
					$error = 'Password could not be encryped';
				} else {
					// all ok

					// set where
					$where = array(
						'staffID' => $staffID
					);

					// update user
					$data = array(
						'reset_hash' => NULL,
						'reset_at' => NULL,
						'invalid_logins' => 0,
						'locked_until' => NULL,
						'password' => $password_hash,
						'last_password_change' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					$this->db->update('staff', $data, $where);

					if ($this->db->affected_rows() > 0) {
						// redirect
						$this->session->set_flashdata('success', $success_message);
						// if logging in to admin account, redirect to accounts screen
						if ($type == 'force' && $this->auth->account->admin == 1) {
							$redirect_to = 'accounts';
						}
						redirect($redirect_to);
						return TRUE;
					} else {
						$error = 'There was an error, please try again.';
					}
				}
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'submit_to' => $submit_to,
			'password' => $password,
			'password_confirm' => $password_confirm,
			'instruction' => $instruction,
			'show_login_link' => $show_login_link,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'body_class' => 'login'
		);

		// load view
		$this->crm_view('user/set_password', $data, 'templates/public.php');

	}

	public function terms() {

		// check if not logged in
		if ($this->auth->user === FALSE) {
			redirect('login');
			return TRUE;
		}

		// check if not already agreed and something to agree to
		if ($this->auth->account_overridden !== TRUE && $this->auth->user_overridden !== TRUE && $this->auth->user->privacy_agreed != 1 && (!empty($this->settings_library->get('company_privacy', 'default')) || !empty($this->settings_library->get('staff_privacy')))) {
			// not agreed
		} else {
			// already agreed
			redirect('/');
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Privacy Policy';
		$show_login_link = TRUE;
		$redirect_to = '/';
		$success = NULL;
		$error = NULL;
		$errors = array();

		// determine redirect path
		if ($this->session->flashdata('redirect_to')) {
			$redirect_to = $this->session->flashdata('redirect_to');
		}
		if ($this->input->post('redirect_to') != '') {
			$redirect_to = $this->input->post('redirect_to');
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('agree', 'Agree to Privacy Policy', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				if (set_value('agree') != 1) {
					$errors[] = 'You must agree to the privacy policy';
				} else {
					// all ok

					// set where
					$where = array(
						'staffID' => $this->auth->user->staffID
					);

					// update user
					$data = array(
						'privacy_agreed' => 1,
						'privacy_agreed_date' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$this->db->update('staff', $data, $where);

					if ($this->db->affected_rows() > 0) {

						// insert note
						$details = 'IP: ' . get_ip_address() . '
						Hostname: ' . gethostbyaddr(get_ip_address());
						$data = array(
							'date' => mdate('%Y-%m-%d'),
							'type' => 'privacy',
							'observation_score' => NULL,
							'summary' => 'Privacy Agreed',
							'content' => $details,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID,
							'byID' => $this->auth->user->byID,
							'staffID' => $this->auth->user->staffID
						);
						$query = $this->db->insert('staff_notes', $data);

						// redirect
						$this->session->set_flashdata('success', 'Your agreement to the privacy policy has been recorded');
						// if logging in to admin account, redirect to accounts screen
						if ($this->auth->account->admin == 1) {
							$redirect_to = 'accounts';
						}
						redirect($redirect_to);
						return TRUE;
					} else {
						$error = 'There was an error, please try again.';
					}
				}
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'redirect_to' => $redirect_to,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'body_class' => 'login terms'
		);

		// load view
		$this->crm_view('user/terms', $data, 'templates/public.php');

	}

	public function profile()
	{
		// check if not logged in
		if ($this->auth->user === FALSE) {
			redirect('login');
			return TRUE;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Change Password';
		$icon = 'users';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('password_current', 'Current Password', 'trim|xss_clean|required|callback_check_profile_password');
			$this->form_validation->set_rules('password', 'New Password', 'trim|xss_clean|required|min_length[8]|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				$data = array(
					'last_password_change' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				// encrypt password
				$data['password'] = $this->auth->encrypt_password(set_value('password'));

				// final check for errors
				if (count($errors) == 0) {

					// set where
					$where = array(
						'staffID' => $this->auth->user->staffID,
						'accountID' => $this->auth->user->accountID
					);

					// update
					$query = $this->db->update('staff', $data, $where);

					// if updated
					if ($this->db->affected_rows() == 1) {
						$this->session->set_flashdata('success', 'Your password has been updated');
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}

					redirect('profile');
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
			'errors' => $errors,
			'info' => $info,
			'success'=> $success
		);

		// load view
		$this->crm_view('user/profile', $data);
	}

	/**
	 * validation function for checking current password is correct
	 * @param  string $password
	 * @return bool
	 */
	public function check_profile_password($password) {
		// check if parameters
		if (empty($password)) {
			return FALSE;
		}

		$where = array(
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('staff')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $user_info) {}

		// check if correct
		if ($this->auth->verify_password($password, $user_info->password, $user_info->email)) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * validation function for checking not re-using current password
	 * @param  string $password
	 * @return bool
	 */
	public function check_password_reuse($password, $type = NULL) {

		// check if parameters
		if (empty($password)) {
			return FALSE;
		}

		// only check on force password change page
		if ($type !== 'force') {
			return TRUE;
		}

		// reused same
		if ($this->check_profile_password($password)) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file user.php */
/* Location: ./application/controllers/user.php */
