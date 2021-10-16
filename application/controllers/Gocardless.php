<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gocardless extends MY_Controller {

	public function __construct() {
		// allow public access
		parent::__construct(TRUE);
	}

	/**
	 * create mandate
	 * @return mixed
	 */
	public function mandate($code) {

		if (empty($code)) {
			return FALSE;
		}

		// look up code
		$where = array(
			'participant_subscriptions.gc_code' => $code,
			'participant_subscriptions.status' => 'inactive'
		);
		$res = $this->db->select('subscriptions.subID, subscriptions.familyID, subscriptions.accountID')
					->from('participant_subscriptions')
					->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
					->where($where)->limit(1)->get();

		//Not in participant subscriptions. Check payment plans.
		$is_plan = false;
		if ($res->num_rows() == 0) {
			// look up code
			$where = array(
				'gc_code' => $code,
				'status' => 'inactive'
			);
			$res = $this->db->from('family_payments_plans')->where($where)->limit(1)->get();

			if ($res->num_rows()==0) {
				return FALSE;
			}
			else {
				$is_plan = true;
			}
		}

		foreach ($res->result() as $sub_info) {}

		// load library
		$params = array(
			'accountID' => $sub_info->accountID
		);
		$this->load->library('gocardless_library', $params);

		if ($is_plan) {
			$mandate_url = $this->gocardless_library->new_mandate($sub_info->contactID, $sub_info->planID);
		}
		else {
			// look up contact
			$where = array(
				'family_contacts.familyID' => $sub_info->familyID,
				'family_contacts.accountID' => $sub_info->accountID,
				'family_contacts.main' => TRUE
			);
			$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				return FALSE;
			}

			foreach ($res->result() as $contact_info) {}

			$mandate_url = $this->gocardless_library->new_mandate($contact_info->contactID, $sub_info->subID);
		}

		if (empty($mandate_url)) {
			return FALSE;
		}

		redirect($mandate_url);
	}

	/**
	 * confirm mandate
	 * @return mixed
	 */
	public function confirm($accountID, $subID) {

		if (empty($accountID)) {
			return FALSE;
		}

		// load library
		$params = array(
			'accountID' => $accountID
		);
		$this->load->library('gocardless_library', $params);

		$success_url = $this->gocardless_library->confirm_mandate($subID);

		// check for success url from settings (if not redirecting to booking success)
		if (strpos($success_url, 'account/booking/') === FALSE && !empty($this->settings_library->get('gocardless_success_redirect', $accountID))) {
			$success_url = $this->settings_library->get('gocardless_success_redirect', $accountID);
		}

		if (empty($success_url)) {
			return FALSE;
		}

		redirect($success_url);
	}
}

/* End of file Gocardless.php */
/* Location: ./application/controllers/Gocardless.php */
