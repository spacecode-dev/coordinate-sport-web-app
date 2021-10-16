<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'core/Base_Controller.php');

class Online_Booking_Controller extends Base_Controller
{

	public function __construct()
	{
		parent::__construct();

		// if not using an online booking domain, stop here
		if (empty(SUB_DOMAIN) && empty(CUSTOM_DOMAIN)) {
			show_404();
			return FALSE;
		}

		// load libraries
		$this->load->library('cart_library', array());
		$this->load->library('online_booking');

		// load settings
		$this->config->load('google', TRUE);

		// init
		if (!$this->online_booking->init()) {
			show_404();
		}

		if ($this->online_booking->user !== FALSE) {
			//Check if privacy policy, data protection notice, or safeguarding policy needs confirming.
			$data_protection_notice_required = ($this->online_booking->user->data_protection_agreed != 1 && !empty($this->settings_library->get('participant_data_protection_notice', $this->online_booking->user->accountID)));
			$safeguarding_required = ($this->online_booking->user->safeguarding_agreed != 1 && !empty($this->settings_library->get('participant_safeguarding', $this->online_booking->user->accountID)));
			if (($this->online_booking->user->privacy_agreed != 1 || $data_protection_notice_required || $safeguarding_required) && strpos(current_url(), 'privacy/confirm') === FALSE) {
				$this->session->set_userdata('redirect_to', current_url());
				redirect('account/privacy/confirm');
			}
		}
	}

	/**
	 * online booking view
	 * @param  string $content_view
	 * @param  array  $data
	 * @return void
	 */
	public function booking_view($content_view, $data, $template = 'templates/online-booking') {
		return $this->crm_view($content_view, $data, $template);
	}

}
