<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_plans extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants', 'online_booking'));

		// load gocardless library
		$this->load->library('gocardless_library');
	}

	/**
	 * show list of payment plans
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
		$icon = 'calendar-alt';
		$tab = 'payment-plans';
		$current_page = 'participants';
		$page_base = 'participants/payment-plans/' . $familyID;
		$section = 'participants';
		$title = 'Payment Plans';
		$buttons = '<a class="btn btn-success" href="' . site_url('participants/payment-plans/' . $familyID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account'
		);

		// set where
		$where = array(
			'family_payments_plans.familyID' => $familyID,
			'family_payments_plans.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'amount' => NULL,
			'contact_id' => NULL,
			'note' => NULL,
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
			$this->form_validation->set_rules('search_contact_id', 'From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_note', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['amount'] = set_value('search_amount');
			$search_fields['contact_id'] = set_value('search_contact_id');
			$search_fields['note'] = set_value('search_note');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-family-payments-plans'))) {

			foreach ($this->session->userdata('search-family-payments-plans') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-payments-plans', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("family_payments_plans") . "`.`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("family_payments_plans") . "`.`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['amount'] != '') {
				$search_where[] = "`amount` = " . $this->db->escape($search_fields['amount']);
			}

			if ($search_fields['contact_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("family_payments_plans") . "`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if ($search_fields['note'] != '') {
				$search_where[] = "`note` LIKE '%" . $this->db->escape_like_str($search_fields['note']) . "%'";;
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('family_payments_plans.*, family_contacts.first_name, family_contacts.last_name')
			->from('family_payments_plans')
			->join('family_contacts', 'family_payments_plans.contactID = family_contacts.contactID', 'inner')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('added desc')
			->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('family_payments_plans.*, family_contacts.first_name, family_contacts.last_name')
			->from('family_payments_plans')
			->join('family_contacts', 'family_payments_plans.contactID = family_contacts.contactID', 'inner')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('added desc')
			->limit($this->pagination_library->amount, $this->pagination_library->start)
			->get();
		// check for valid config
		if ($this->gocardless_library->valid_config() !== TRUE) {
			$info = 'Please complete your GoCardless API details in ' . anchor('settings/integrations', 'Settings') . ' to create and manage payment plans';
			$buttons = NULL;
		} else if ($this->settings_library->get('send_gocardless_mandate') != 1) {
			$info = 'Please enable sending of GoCardless Mandate Links in ' . anchor('settings/emailsms', 'Settings') . ' to create and manage payment plans';
			$buttons = NULL;
		}

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
			'info' => $info,
			'valid_config' => $this->gocardless_library->valid_config()
		);

		// load view
		$this->crm_view('participants/payment-plans', $data);
	}

	/**
	 * edit a payment plan
	 * @param  int $planID
	 * @param int $familyID
	 * @return void
	 */
	public function edit($planID = NULL, $familyID = NULL)
	{

		$plan_info = new stdClass();

		// check if editing
		if ($planID != NULL) {

			// check if numeric
			if (!ctype_digit($planID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'planID' => $planID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('family_payments_plans')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$plan_info = $row;
				$familyID = $plan_info->familyID;
			}

		} else {
			// check for valid config, if trying to create new
			if ($this->gocardless_library->valid_config() !== TRUE) {
				show_404();
			}
		}

		// required
		if ($familyID == NULL) {
			show_404();
		}

		// look up family
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
		$title = 'New Payment Plan';
		if ($planID != NULL) {
			$submit_to = 'participants/payment-plans/view/' . $planID;
			$title = 'View Payment Plan';
		} else {
			$submit_to = 'participants/payment-plans/' . $familyID . '/new/';
		}
		$return_to = 'participants/payment-plans/' . $familyID;
		$icon = 'calendar-alt';
		$tab = 'payment-plans';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$subscription_link = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account',
			'participants/payment-plans/' . $familyID => 'Payment Plans'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			if ($planID == NULL) {
				$this->form_validation->set_rules('contactID', 'Contact', 'trim|xss_clean|required');
				$this->form_validation->set_rules('amount', 'Total Amount', 'trim|xss_clean|required|is_numeric|greater_than[0]|callback_check_amount_more_than_num_payments');
				$this->form_validation->set_rules('interval_count', 'Number of Payments', 'trim|xss_clean|required|is_numeric|greater_than[1]');
				$this->form_validation->set_rules('interval_length', 'Every', 'trim|xss_clean|required|is_numeric|greater_than[0]');
				$this->form_validation->set_rules('interval_unit', 'Period', 'trim|xss_clean|required');
			}
			$this->form_validation->set_rules('note', 'Note', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'note' => set_value('note'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				);

				if ($planID == NULL) {
					$data['contactID'] = set_value('contactID');
					$data['amount'] = set_value('amount');
					$data['interval_count'] = set_value('interval_count');
					$data['interval_length'] = set_value('interval_length');
					$data['interval_unit'] = set_value('interval_unit');
					$data['accountID'] = $this->auth->user->accountID;
					$data['byID'] = $this->auth->user->staffID;
					$data['familyID'] = $familyID;
					$data['status'] = 'inactive';
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($planID == NULL) {
						// insert
						$query = $this->db->insert('family_payments_plans', $data);

						$planID = $this->db->insert_id();

						$affected_rows = $this->db->affected_rows();
						$plan_added = TRUE;

						$new_subscription = $this->gocardless_library->new_payment_plan($planID);

						//Delete newly created plan since mandate was not found on GC gateway
						if ($new_subscription === 'mandate_not_found') {
							if ($affected_rows>0) {
								$this->db->delete('family_payments_plans', ['planID' => $planID]);
								$affected_rows = 0;
							}
							$planID = null;
						}
					} else {
						$where = array(
							'planID' => $planID
						);

						// update
						$query = $this->db->update('family_payments_plans', $data, $where);

						$affected_rows = $this->db->affected_rows();
					}

					// if inserted/updated
					if ($affected_rows == 1) {

						if (isset($plan_added) && $plan_added == TRUE) {
							$message = 'Payment plan has been created successfully';
							if ($new_subscription === 'mandate_sent') {
								$message .= ' and mandate link sent to contact';
							} else if ($new_subscription === TRUE) {
								$message .= ' and existing direct debit activated';
							}
							$this->session->set_flashdata('success', $message);
						} else {
							$this->session->set_flashdata('success', 'Payment plan has been updated successfully');
						}

						redirect($return_to);

						return TRUE;
					} else {
						if ($new_subscription === 'mandate_not_found') {
							$this->session->set_flashdata('info', "Mandate not found.");
						}
						else {
							$this->session->set_flashdata('info', 'Error saving data, please try again.');
						}
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
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'familyID' => $familyID,
			'contacts' => $contacts,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'planID' => $planID
		);

		//Only pass plan_info if planID is set (e.g. plan isn't null)
		if ($planID!=NULL) {
			$data['plan_info'] = $plan_info;
		}

		// load view
		$this->crm_view('participants/payment-plan', $data);
	}

	/**
	 * delete a payment plan
	 * @param  int $planID
	 * @return mixed
	 */
	public function remove($planID = NULL) {

		// check params
		if (empty($planID)) {
			show_404();
		}

		$where = array(
			'planID' => $planID,
			'accountID' => $this->auth->user->accountID,
		);

		// run query
		$query = $this->db->from('family_payments_plans')->where($where)->where_in('status', array('inactive', 'cancelled'))->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$plan_info = $row;

			// all ok, delete
			$query = $this->db->delete('family_payments_plans', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Payment ' . 'Plan has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'Payment ' . 'Plan could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'participants/payment-plans/' . $plan_info->familyID;

			redirect($redirect_to);
		}
	}

	/**
	 * cancel a payment plan
	 * @param  int $planID
	 * @return mixed
	 */
	public function cancel($planID = NULL) {

		// check params
		if (empty($planID)) {
			show_404();
		}

		$where = array(
			'planID' => $planID,
			'accountID' => $this->auth->user->accountID,
			'status !=' => 'cancelled'
		);

		// run query
		$query = $this->db->from('family_payments_plans')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$plan_info = $row;

			// attempt cancel
			if ($this->gocardless_library->cancel_subscription($plan_info->gc_subscription_id)) {
				$this->session->set_flashdata('success', 'Payment ' . 'Plan has been cancelled successfully and the status will be updated momentarily.');
			} else {
				// already cancelled at gocardless, mark as cancelled
				$data = array(
					'status' => 'cancelled',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				);
				$this->db->update('family_payments_plans', $data, $where, 1);
				$this->session->set_flashdata('info', 'Payment ' . 'Plan was already cancelled at source.');
			}

			// determine which page to send the user back to
			$redirect_to = 'participants/payment-plans/' . $plan_info->familyID;

			redirect($redirect_to);
		}
	}

	public function check_amount_more_than_num_payments($amount) {
		$interval_count = $this->input->post('interval_count');

		// if nothing, all ok
		if (empty($amount) || empty($interval_count)) {
			return TRUE;
		}

		// gocardless has minimum amount of 1 per payment
		if ($amount < $interval_count) {
			return FALSE;
		}

		return TRUE;
	}
}

/* End of file payment-plans.php */
/* Location: ./application/controllers/participants/payment-plans.php */
