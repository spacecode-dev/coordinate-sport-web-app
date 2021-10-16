<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Anonymise extends MY_Controller {

	private $fields = array(
		'accounts' => array(
			'company' => 'company',
			'contact' => 'name',
			'email' => 'email',
			'phone' => 'phoneNumber'
		),
		'bookings_attendance_names' => array(
			'name' => 'name'
		),
		'bookings_attachments' => array(
			'name' => 'filename',
			'comment' => 'sentence'
		),
		'bookings_lessons_attachments' => array(
			'name' => 'filename',
			'comment' => 'sentence'
		),
		'bookings_lessons_notes' => array(
			'summary' => 'sentence',
			'content' => 'text'
		),
		'bookings_lessons_staff' => array(
			'comment' => 'sentence'
		),
		'bookings_vouchers' => array(
			'name' => 'word',
			'code' => 'randomNumber',
			'comment' => 'sentence'
		),
		'equipment' => array(
			'notes' => 'sentence'
		),
		'family_children' => array(
			'name' => NULL,
			'first_name' => 'firstName',
			'last_name' => 'lastName',
			'dob' => 'date',
			'medical' => 'sentence'
		),
		'family_contacts' => array(
			'name' => NULL,
			'title' => NULL,
			'first_name' => 'firstName',
			'last_name' => 'lastName',
			'address1' => 'streetAddress',
			'address2' => NULL,
			'address3' => NULL,
			'town' => 'city',
			'county' => 'county',
			'postcode' => 'postcode',
			'phone' => 'phoneNumber',
			'mobile' => NULL,
			'workPhone' => NULL,
			'email' => 'email',
			'dob' => 'date',
			'medical' => 'sentence',
			'gc_redirect_id' => NULL,
			'gc_customer_id' => NULL,
			'gc_mandate_id' => NULL
		),
		'family_notes' => array(
			'summary' => 'sentence',
			'content' => 'text'
		),
		'family_notifications' => array(
			'destination' => 'email',
			'subject' => 'sentence',
			'contentText' => 'text',
			'contentHTML' => 'textHTML'
		),
		'family_payments' => array(
			'transaction_ref' => 'randomNumber',
			'note' => NULL
		),
		'family_payments_plans' => array(
			'gc_subscription_id' => NULL,
			'note' => NULL
		),
		'files' => array(
			'comment' => 'sentence'
		),
		'messages' => array(
			'subject' => 'sentence',
			'message' => 'text'
		),
		'offer_accept' => array(
			'reason' => 'sentence'
		),
		'orgs' => array(
			'name' => 'company',
			'email' => 'email',
			'website' => 'domainName',
		),
		'orgs_addresses' => array(
			'address1' => 'streetAddress',
			'address2' => NULL,
			'address3' => NULL,
			'town' => 'city',
			'county' => 'county',
			'postcode' => 'postcode',
			'phone' => 'phoneNumber'
		),
		'orgs_contacts' => array(
			'name' => 'name',
			'position' => 'jobTitle',
			'tel' => 'phoneNumber',
			'mobile' => NULL,
			'email' => 'email'
		),
		'orgs_notes' => array(
			'summary' => 'sentence',
			'content' => 'text'
		),
		'orgs_notifications' => array(
			'destination' => 'email',
			'subject' => 'sentence',
			'contentText' => 'text',
			'contentHTML' => 'textHTML'
		),
		'orgs_attachments' => array(
			'name' => 'filename',
			'comment' => 'sentence'
		),
		'orgs_safety' => array(
			'details' => NULL,
		),
		'orgs_safety_hazards' => array(
			'hazard' => 'sentence',
			'potential_effect' => 'sentence',
			'control_measures' => 'sentence'
		),
		'project_codes' => array(
			'code' => 'randomNumber',
			'desc' => 'sentence'
		),
		'settings_childcarevoucherproviders' => array(
			'name' => 'company',
			'reference' => 'randomNumber',
			'comment' => 'sentence'
		),
		'settings_tags' => array(
			'name' => 'word'
		),
		'staff' => array(
			'title' => NULL,
			'first' => 'firstName',
			'middle' => NULL,
			'surname' => 'lastName',
			'jobTitle' => 'jobTitle',
			'medical' => 'sentence',
			'nationalInsurance' => NULL,
			'dob' => 'date',
			'email' => 'email',
			'payments_bankName' => 'company',
			'payments_sortCode' => 'randomNumber',
			'payments_accountNumber' => 'randomNumber',
			'payroll_number' => 'randomNumber',
			'proofid_passport_date' => 'date',
			'proofid_passport_ref' => 'randomNumber',
			'proofid_nicard_ref' => 'randomNumber',
			'proofid_driving_date' => 'date',
			'proofid_driving_ref' => 'randomNumber',
			'proofid_birth_date' => 'date',
			'proofid_birth_ref' => 'randomNumber',
			'proofid_other_specify' => NULL,
			'id_personalStatement' => 'sentence',
			'id_specialism' => 'word',
			'id_favQuote' => 'sentence',
			'id_sportingHero' => 'name',
			'equal_ethnic' => NULL,
			'equal_ethnic_other' => NULL,
			'equal_source' => NULL,
			'equal_comments' => NULL,
			'qual_first_issue_date' => 'date',
			'qual_first_expiry_date' => 'date',
			'qual_child_issue_date' => 'date',
			'qual_child_expiry_date' => 'date',
			'qual_fsscrb_issue_date' => 'date',
			'qual_fsscrb_expiry_date' => 'date',
			'qual_fsscrb_ref' => 'randomNumber',
			'qual_othercrb_issue_date' => 'date',
			'qual_othercrb_expiry_date' => 'date',
			'qual_othercrb_ref' => 'randomNumber',
			'employment_start_date' => 'date',
			'employment_end_date' => NULL,
			'employment_probation_date' => 'date',
			'driving_mot_expiry' => 'date',
			'driving_insurance_expiry' => 'date'
		),
		'staff_addresses' => array(
			'name' => 'name',
			'address1' => 'streetAddress',
			'address2' => NULL,
			'town' => 'city',
			'county' => 'county',
			'postcode' => 'postcode',
			'phone' => 'phoneNumber',
			'mobile' => 'phoneNumber',
			'mobile_work' => 'phoneNumber',
			'from' => 'date',
			'to' => 'date'
		),
		'staff_attachments' => array(
			'name' => 'filename',
			'comment' => 'sentence'
		),
		'staff_availability_exceptions' => array(
			'reason' => 'sentence'
		),
		'staff_invoices' => array(
			'subject' => 'randomNumber',
			'buyer_id' => 'randomNumber',
			'utr' => 'randomNumber',
			'bank_name' => 'company',
			'bank_sort_code' => 'randomNumber',
			'bank_account' => 'randomNumber',
		),
		'staff_notes' => array(
			'summary' => 'sentence',
			'content' => 'text'
		),
		'staff_quals' => array(
			'name' => 'sentence',
			'reference' => 'randomNumber',
			'issue_date' => 'date',
			'expiry_date' => 'date'
		),
		'tasks' => array(
			'task' => 'sentence'
		),
		'timesheets_expenses' => array(
			'item' => 'sentence',
			'reason_desc' => NULL
		),
		'timesheets_items' => array(
			'reason_desc' => NULL
		),
		'vouchers' => array(
			'name' => 'word',
			'code' => 'randomNumber',
			'comment' => 'sentence'
		),
	);

	public function __construct() {
		parent::__construct(FALSE, array(), array('directors'), array('accounts'));
	}

	/**
	 * anonymise data
	 * @param  int $accountID
	 * @return void
	 */
	public function index($accountID)
	{

		$account_info = new stdClass;

		// check if empty
		if (empty($accountID)) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($accountID)) {
			show_404();
		}

		// check exists
		$where = array(
			'accountID' => $accountID,
		);

		// run query
		$query = $this->db->from('accounts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$account_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Anonymise Data';
		$submit_to = 'accounts/anonymise/' . $accountID;
		$return_to = 'accounts';
		$icon = 'eye';
		$current_page = 'accounts';
		$section = 'accounts';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'accounts' => 'Accounts'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('keep_emails', 'Keep Emails', 'trim|xss_clean');
			$this->form_validation->set_rules('confirm', 'Confirm', 'trim|xss_clean|required|regex_match[/CONFIRM/]');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				// delete settings for account
				$where = array(
					'accountID' => $accountID
				);
				$this->db->delete('accounts_settings', $where);

				// get first table
				reset($this->fields);
				$first_table = key($this->fields);

				// redirect
				$query = array(
					'keep_emails' => set_value('keep_emails')
				);
				redirect('accounts/anonymise/process/' . $accountID . '/' . $first_table . '?' . http_build_query($query));
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
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'account_info' => $account_info,
			'accountID' => $accountID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('accounts/anonymise', $data);
	}

	public function process($accountID, $table) {

		$account_info = new stdClass;

		// check if empty
		if (empty($accountID)) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($accountID)) {
			show_404();
		}

		// check exists
		$where = array(
			'accountID' => $accountID,
		);

		// run query
		$query = $this->db->from('accounts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$account_info = $row;
		}

		// check for table
		if (!array_key_exists($table, $this->fields)) {
			show_404();
		}

		// get emails to keep
		$keep_emails = array();
		$emails = explode(',', $this->input->get('keep_emails'));
		if (count($emails) > 0) {
			foreach ($emails as $email) {
				$keep_emails[] = trim($email);
			}
		}

		// create faker instance
		$faker = Faker\Factory::create('en_GB');

		// extend time limit
		set_time_limit(0);

		// increase memory limit
		ini_set('memory_limit', '512M');

		// loop through fields
		$where = array(
			'accountID' => $accountID
		);
		$res = $this->db->from($table)->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// set data
				$data = array(
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				);
				foreach ($this->fields[$table] as $field => $type) {
					$value = '';
					switch ($type) {
						case 'null';
						case NULL:
							$value = NULL;
							break;
						case 'textHTML':
							$value = '<p>' . $faker->text . '</p>';
							break;
						case 'filename':
							$value = $faker->word . '.docx';
							break;
						default:
							$value = $faker->$type;
							break;
					}
					$data[$field] = $value;
				}
				// if staff table keep some data if excluding emails
				if ($table == 'staff' && in_array($row->email, $keep_emails)) {
					unset($data['first']);
					unset($data['surname']);
					unset($data['email']);
				}
				// get first field so know how to update
				$where = array();
				foreach ($row as $field => $value) {
					$where[$field] = $value;
					break;
				}
				// update
				$this->db->update($table, $data, $where, 1);
			}
		}

		// get next table
		foreach ($this->fields as $table_name => $fields) {
			if (isset($use_next)) {
				$query = array(
					'keep_emails' => implode(',', $keep_emails)
				);
				redirect('accounts/anonymise/process/' . $accountID . '/' . $table_name . '?' . http_build_query($query));
				break;
			}
			if ($table_name === $table) {
				$use_next = TRUE;
			}
		}

		// if none, redirect
		$success = 'Account anonymised successfully';
		$this->session->set_flashdata('success', $success . '.');
		redirect('accounts/anonymise/' . $accountID);
	}

}

/* End of file Anonymise.php */
/* Location: ./application/controllers/accounts/Anonymise.php */
