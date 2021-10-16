<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends MY_Controller {

	private $accountID;

	public function __construct() {
		// allow public access
		parent::__construct(TRUE);
	}

	/**
	 * email a contact
	 * @return void
	 */
	public function email_contact() {

		// check key
		if (!$this->validate_key()) {
			echo 'INVALID_KEY';
			return FALSE;
		}

		$contact_id = $this->input->post('contact_id', FALSE);
		$subject = $this->input->post('subject', FALSE);
		$message = $this->input->post('message', FALSE);

		// check missing fields
		if (empty($contact_id) || empty($subject) || empty($message)) {
			echo 'MISSING_PARAMS';
			return FALSE;
		}

		// look up contact
		$where = array(
			'contactID' => $contact_id,
			'accountID' => $this->accountID
		);

		$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			echo 'NOT_FOUND';
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		// check email
		if (empty($contact_info->email) || !filter_var($contact_info->email, FILTER_VALIDATE_EMAIL)) {
			echo 'NO_EMAIL';
			return FALSE;
		}

		// get html email and convert to plain text
		$this->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($message);
		$email_plain = $html2text->get_text();

		if ($this->crm_library->send_email($contact_info->email, $subject, $message, array(), FALSE, $contact_info->accountID)) {
			echo 'OK';
			return TRUE;
		} else {
			echo 'ERROR';
			return FALSE;
		}

	}

	/**
	 * email an org contact
	 * @return void
	 */
	public function email_org_contact() {

		// check key
		if (!$this->validate_key()) {
			echo 'INVALID_KEY';
			return FALSE;
		}

		$contact_id = $this->input->post('contact_id', FALSE);
		$subject = $this->input->post('subject', FALSE);
		$message = $this->input->post('message', FALSE);

		// check missing fields
		if (empty($contact_id) || empty($subject) || empty($message)) {
			echo 'MISSING_PARAMS';
			return FALSE;
		}

		// look up contact
		$where = array(
			'contactID' => $contact_id,
			'accountID' => $this->accountID,
			'active' => 1
		);

		$res = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			echo 'NOT_FOUND';
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		// check email
		if (empty($contact_info->email) || !filter_var($contact_info->email, FILTER_VALIDATE_EMAIL)) {
			echo 'NO_EMAIL';
			return FALSE;
		}

		// get html email and convert to plain text
		$this->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($message);
		$email_plain = $html2text->get_text();

		if ($this->crm_library->send_email($contact_info->email, $subject, $message, array(), FALSE, $contact_info->accountID)) {
			echo 'OK';
			return TRUE;
		} else {
			echo 'ERROR';
			return FALSE;
		}

	}

	/**
	 * send customer booking notification
	 * @return void
	 */
	public function send_customer_booking_notification() {

		// check key
		if (!$this->validate_key()) {
			echo 'INVALID_KEY';
			return FALSE;
		}

		$block_id = $this->input->post('block_id', FALSE);
		$contact_id = $this->input->post('contact_id', FALSE);

		// check missing fields
		if (empty($block_id) || empty($contact_id)) {
			echo 'MISSING_PARAMS';
			return FALSE;
		}

		// send confirmation
		$this->crm_library->send_customer_booking_notification($block_id, $contact_id);
		$this->crm_library->send_customer_booking_confirmation($block_id, $contact_id);

		echo 'OK';
		return TRUE;

	}

	/**
	 * check api key is valid
	 * @return bool
	 */
	private function validate_key() {
		$key = $this->input->post('key', FALSE);

		if (empty($key)) {
			return FALSE;
		}

		// look up
		$where = array(
			'active' => 1,
			'api_key' => $key
		);

		$res = $this->db->from('accounts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 1) {
			foreach ($res->result() as $row) {
				$this->accountID = $row->accountID;
				// pass account info into auth to allow access to account specific settings
				$this->auth->user = new stdClass;
				$this->auth->user->accountID = $row->accountID;
				$this->auth->user->department = NULL;
				$this->auth->account = $row;
				$this->auth->get_account();
				return TRUE;
			}
		}

		return FALSE;
	}
}

/* End of file api.php */
/* Location: ./application/controllers/api.php */