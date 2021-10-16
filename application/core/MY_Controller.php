<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'core/Base_Controller.php');
require_once(APPPATH . 'core/Online_Booking_Controller.php');

class MY_Controller extends Base_Controller
{
	/**
	 * MY_Controller constructor.
	 * @param bool $public
	 * @param array $deny
	 * @param array $allow
	 * @param array $features_required
	 */
	public function __construct($public = FALSE, $deny = array(), $allow = array(), $features_required = array())
	{
		parent::__construct();

		// if using an online booking subdomain, stop here except specified controllers
		if (resolve_online_booking_domain() !== FALSE && !in_array($this->uri->segment(1), array('attachment', 'gc'))) {
			show_404();
			return FALSE;
		}

		// if not public page and not logged in
		if ($public !== TRUE && $this->auth->user === FALSE) {
			// store current page for redirection
			$this->session->set_flashdata('redirect_to', $_SERVER['REQUEST_URI']);
			// go to login
			redirect('login');
			return FALSE;
		}

		// if not public, check permissions
		if ($public !== TRUE) {
			// if denying
			if (count($deny) > 0) {
				if (in_array($this->auth->user->department, $deny)) {
					show_403();
					return FALSE;
				}
			}

			// if allowing
			if (count($allow) > 0) {
				if (!in_array($this->auth->user->department, $allow)) {
					show_403();
					return FALSE;
				}
			}

			// check password last changed if not in using account/user override
			if ($this->auth->account_overridden !== TRUE && $this->auth->user_overridden !== TRUE && $this->settings_library->get('force_password_change_every_x_months') > 0 && $this->uri->segment(0) != 'password-change' && (empty($this->auth->user->last_password_change) || strtotime($this->auth->user->last_password_change) < strtotime('-' . $this->settings_library->get('force_password_change_every_x_months') . ' months'))) {
				redirect('password-change');
				exit();
			}

			// check if agreed to privacy if not using account user_overridden and privacy policy to agree to
			if ($this->auth->account_overridden !== TRUE && $this->auth->user_overridden !== TRUE && $this->auth->user->privacy_agreed != 1 && (!empty($this->settings_library->get('company_privacy', 'default')) || !empty($this->settings_library->get('staff_privacy'))) && time() >= strtotime('2018-05-25')) {
				redirect('terms');
				exit();
			}
		}

		// check for required features
		if ($this->auth->has_features($features_required) !== TRUE) {
			show_403();
			return FALSE;
		}

		// if account has participants feature, enable cart
		if ($this->auth->has_features('participants')) {
			$args = array(
				'accountID' => $this->auth->user->accountID,
				'in_crm' => TRUE
			);
			$this->load->library('cart_library', $args);
		}

		//Need every time - user agent in header
		$this->load->library('user_agent');
	}

	/**
	 * run a view through a template
	 * @param  string $content_view
	 * @param  array  $data
	 * @param  string $template
	 * @return void
	 */
	public function crm_view($content_view, $data = array(), $template = 'templates/master')
	{
		// close session write as not required any more
		session_write_close();

		$data['content'] = $this->load->view($content_view, $data, true);

        //if user log in to another user we should not record activity
        if ($this->session->account_id_override || $this->session->user_id_override) {
            $this->load->view($template, $data);
            return false;
        }

        if (isset($data['current_page']) && getenv('DISABLE_ACTIVITY') != 1) {
            $current_page = ucfirst(str_replace('_', ' ', $data['current_page']));
            //logging get requests while view rendering to avoid ajax requests
            $this->activity_library->createRecord($this->auth->user,
                'Viewed', $current_page, $_SERVER['REQUEST_URI']);
        }

		$this->load->view($template, $data);
	}
}
