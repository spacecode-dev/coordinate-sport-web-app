<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payments extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));
	}

	/**
	 * show list of payments
	 * @return void
	 */
	public function index($familyID = NULL) {

		if ($familyID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$family_info = $row;
		}

		// set defaults
		$icon = 'sack-dollar';
		$tab = 'payments';
		$current_page = 'participants';
		$page_base = 'participants/payments/' . $familyID;
		$section = 'participants';
		$title = 'Payments';
		$buttons = '<a class="btn btn-success" href="' . site_url('participants/payments/' . $familyID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account'
 		);

		// set where
		$where = array(
			'family_payments.familyID' => $familyID,
			'family_payments.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'amount' => NULL,
			'transaction_ref' => NULL,
			'method' => NULL,
			'contact_id' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_amount', 'Amount', 'trim|xss_clean');
			$this->form_validation->set_rules('search_transaction_ref', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search_method', 'Method', 'trim|xss_clean');
			$this->form_validation->set_rules('search_contact_id', 'From', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['amount'] = set_value('search_amount');
			$search_fields['transaction_ref'] = set_value('search_transaction_ref');
			$search_fields['method'] = set_value('search_method');
			$search_fields['contact_id'] = set_value('search_contact_id');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-family-payments'))) {

			foreach ($this->session->userdata('search-family-payments') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-payments', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['amount'] != '') {
				$search_where[] = "`amount` = " . $this->db->escape($search_fields['amount']);
			}

			if ($search_fields['transaction_ref'] != '') {
				$search_where[] = "`transaction_ref` = " . $this->db->escape($search_fields['transaction_ref']);
			}

			if ($search_fields['method'] != '') {
				$search_where[] = "`method` = " . $this->db->escape($search_fields['method']);
			}

			if ($search_fields['contact_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('family_payments.*, family_contacts.first_name, family_contacts.last_name, staff.first as staff_first, staff.surname as staff_last, CONCAT('.$this->db->dbprefix("settings_childcarevoucherproviders").'.name, " (Reference:",'.$this->db->dbprefix("settings_childcarevoucherproviders").'.reference, ")") as reference')
		->from('family_payments')
		->join('family_contacts', 'family_payments.contactID = family_contacts.contactID', 'left')
		->join('staff', 'family_payments.byID = staff.staffID', 'left')
		->join('settings_childcarevoucherproviders', 'family_payments.voucher_id = settings_childcarevoucherproviders.providerID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('family_payments.*, family_contacts.first_name, family_contacts.last_name, staff.first as staff_first, staff.surname as staff_last, CONCAT('.$this->db->dbprefix("settings_childcarevoucherproviders").'.name, " (Reference:",'.$this->db->dbprefix("settings_childcarevoucherproviders").'.reference, ")") as reference')
		->from('family_payments')
		->join('family_contacts', 'family_payments.contactID = family_contacts.contactID', 'left')
		->join('staff', 'family_payments.byID = staff.staffID', 'left')
		->join('settings_childcarevoucherproviders', 'settings_childcarevoucherproviders.providerID = family_payments.voucher_id', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->limit($this->pagination_library->amount, $this->pagination_library->start)
		->order_by('added desc')->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('first_name ASC')->get();

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
			'payments' => $res,
			'contacts' => $contacts,
			'familyID' => $familyID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/payments', $data);
	}

	/**
	 * edit a payment
	 * @param  int $paymentID
	 * @param int $familyID
	 * @return void
	 */
	public function edit($paymentID = NULL, $familyID = NULL)
	{

		$payment_info = new stdClass();

		// check if editing
		if ($paymentID != NULL) {

			// check if numeric
			if (!ctype_digit($paymentID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'paymentID' => $paymentID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('family_payments')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$payment_info = $row;
				$familyID = $payment_info->familyID;
				if(($row->method == "online" || $row->method == "direct debit") && empty($row->byID)){
					$this->session->set_flashdata('error', "The payment cannot be changed, as it was made through the online bookings site.");
					redirect('participants/payments/' . $familyID);
				}
			}
		}

		// required
		if ($familyID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$family_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Payment';
		$payment_action = 'created';
		if ($paymentID != NULL) {
			$submit_to = 'participants/payments/edit/' . $paymentID;
			$title = mysql_to_uk_datetime($payment_info->added);
			$payment_action = 'updated';
		} else {
			$submit_to = 'participants/payments/' . $familyID . '/new/';
		}
		$return_to = 'participants/payments/' . $familyID;
		$icon = 'sack-dollar';
		$tab = 'payments';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$childcarevoucher_providers = FALSE;
		$childcarevoucher_provider_notices = [];
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account',
			'participants/payments/' . $familyID => 'Payments'
 		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			if ($childcarevoucher_providers !== FALSE) {
				if ($this->input->post('method') == "childcare voucher") {
					$this->form_validation->set_rules('childcarevoucher_providerID', 'Childcare Voucher Provider', 'trim|xss_clean|required|callback_check_childcarevoucher_provider');
				}else{
					$this->form_validation->set_rules('childcarevoucher_providerID', 'Childcare Voucher Provider', 'trim|xss_clean|callback_check_childcarevoucher_provider');
				}
			}
			$this->form_validation->set_rules('contactID', 'From', 'trim|xss_clean|required');
			$this->form_validation->set_rules('amount', 'Amount', 'trim|xss_clean|required|is_numeric');
			if ($this->input->post('method') == "credit note") {
				$this->form_validation->set_rules('amount', 'Amount', 'trim|xss_clean|required|is_numeric|greater_than[0]');
			} else if ($this->input->post('method') == "refund") {
				$this->form_validation->set_rules('amount', 'Amount', 'trim|xss_clean|required|is_numeric|less_than[0]');
			}
			$this->form_validation->set_rules('method', 'Payment Method', 'trim|xss_clean|required');
			$this->form_validation->set_rules('transaction_ref', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('note', 'Note', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'contactID' => set_value('contactID'),
					'amount' => set_value('amount'),
					'method' => set_value('method'),
					'transaction_ref' => set_value('transaction_ref'),
					'note' => set_value('note'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID,
					'internal' => 0,
					'voucher_id' => set_value('childcarevoucher_providerID')
				);
				if (set_value('contactID') == 'internal') {
					$data['contactID'] = NULL;
					$data['internal'] = 1;
				}
				if ((set_value('contactID') !== 'internal' && set_value('contactID') != 'other' && set_value('contactID') != 'bacs' && set_value('contactID') != 'credit memo') || set_value('method') == 'refund') {
					$data['byID'] = $this->auth->user->staffID;
				}

				if ($paymentID == NULL) {
					$data['familyID'] = $familyID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				} else if ($payment_info->locked == 1) {
					// if locked, only save note
					unset($data['contactID']);
					unset($data['amount']);
					unset($data['method']);
					unset($data['transaction_ref']);
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($paymentID == NULL) {
						// insert
						$query = $this->db->insert('family_payments', $data);
						$paymentID = $this->db->insert_id();
						$just_added = TRUE;
					} else {
						$where = array(
							'paymentID' => $paymentID
						);

						// update
						$query = $this->db->update('family_payments', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						$this->crm_library->recalc_family_balance($familyID);

						$success = 'Payment has been ' . $payment_action . ' successfully';

						if (isset($just_added) && $data['internal'] != 1) {
							// send confirmation
							if ($this->crm_library->send_payment_confirmation($paymentID)) {
								$success .= ' and confirmation sent';
							}
						}

						$this->session->set_flashdata('success', $success);

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// get list of childcare voucher providers
		// load libraries
		$args = array(
			'accountID' => $this->auth->user->accountID,
			'in_crm' => TRUE
		);
		$this->load->library('cart_library', $args);
		$childcarevoucher_providers = $this->cart_library->get_childcarevoucher_providers(FALSE, TRUE);

		// get list of childcare voucher provider notices
		$childcarevoucher_provider_notices = $this->cart_library->get_childcarevoucher_providers(TRUE, TRUE);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('first_name ASC')->get();

		// check for locked payment
		if (isset($payment_info->locked) && $payment_info->locked == 1) {
			$info = 'You may only edit the note as this payment is locked';
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
			'payment_info' => $payment_info,
			'familyID' => $familyID,
			'contacts' => $contacts,
			'breadcrumb_levels' => $breadcrumb_levels,
			'childcarevoucher_providers' => $childcarevoucher_providers,
			'childcarevoucher_provider_notices' => $childcarevoucher_provider_notices,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/payment', $data);
	}

	/**
	 * delete a payment
	 * @param  int $familyID
	 * @return mixed
	 */
	public function remove($paymentID = NULL) {

		// check params
		if (empty($paymentID)) {
			show_404();
		}

		$where = array(
			'paymentID' => $paymentID,
			'accountID' => $this->auth->user->accountID,
			'locked !=' => 1
		);

		// run query
		$query = $this->db->from('family_payments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$payment_info = $row;

			// all ok, delete
			$query = $this->db->delete('family_payments', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Payment ' . mysql_to_uk_date($payment_info->added) . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'Payment ' . mysql_to_uk_date($payment_info->added) . ' could not be removed.');
			}

			// recalc family balance
			$this->crm_library->recalc_family_balance($payment_info->familyID);

			// determine which page to send the user back to
			$redirect_to = 'participants/payments/' . $payment_info->familyID;

			redirect($redirect_to);
		}
	}

	/**
	 * show info window for manual payment
	 * @param  int $contactID
	 * @param  int $amount
	 * @return mixed
	 */
	public function info($contactID = NULL, $amount = 0, $vendortxcode = NULL) {

		// check params
		if (empty($contactID) || !is_numeric($amount)) {
			show_404();
		}

		// look up contact
		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $contact_info) {
			$data = array(
				'amount' => $amount,
				'vendortxcode' => $vendortxcode,
				'contact_info' => $contact_info
			);

			$this->load->view('participants/payment-info', $data);
		}

	}

}

/* End of file payments.php */
/* Location: ./application/controllers/participants/payments.php */
