<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Recruitment extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach + office
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach', 'office'), array(), array('staff_management'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * edit recruitment
	 * @param  int $staffID
	 * @return void
	 */
	public function index($staffID = NULL)
	{

		$staff_info = new stdClass;

		// check
		if ($staffID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($staffID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		$fields = get_fields('staff_recruitment');

		$roles = $this->settings_library->get_staff_for_payroll();
		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Recruitment';
		$submit_to = 'staff/recruitment/' . $staffID;
		$return_to = $submit_to;
		$buttons = NULL;
		$icon = 'check-square';
		$tab = 'recruitment';
		$current_page = 'staff';
		$section = 'staff';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname
		);
		
		$mileage_section = 0;
		$mileage_account = $this->db->select("*")->from("accounts")->where("accountID", $this->auth->user->accountID)->get();
		foreach($mileage_account->result() as $result){
			$mileage_section = $result->addon_mileage;
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			// proof of id
			$this->form_validation->set_rules('proofid_passport', field_label('passport', $fields, true), 'trim|xss_clean' . required_field('passport', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_passport_date', 'Passport - Date', 'trim|xss_clean|callback_check_date' . required_field('passport', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_passport_ref', 'Passport - Reference', 'trim|xss_clean' . required_field('passport', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_nicard', field_label('ni_card', $fields, true), 'trim|xss_clean' . required_field('ni_card', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_nicard_ref', 'NI Card - Reference', 'trim|xss_clean' . required_field('ni_card', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_driving', field_label('drivers_licence', $fields, true), 'trim|xss_clean' . required_field('drivers_licence', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_driving_date', 'Driver\'s Licence - Date', 'trim|xss_clean|callback_check_date' . required_field('drivers_licence', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_driving_ref', 'Driver\'s Licence - Reference', 'trim|xss_clean' . required_field('drivers_licence', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_birth', field_label('birth_certificate', $fields, true), 'trim|xss_clean' . required_field('birth_certificate', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_birth_date', 'Birth Certificate - Date', 'trim|xss_clean|callback_check_date' . required_field('birth_certificate', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_birth_ref', 'Birth Certificate - Reference', 'trim|xss_clean' . required_field('birth_certificate', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_utility', field_label('utility_bill', $fields, true), 'trim|xss_clean' . required_field('utility_bill', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_other', field_label('other', $fields, true), 'trim|xss_clean' . required_field('other', $fields, 'validation'));
			$this->form_validation->set_rules('proofid_other_specify', 'Other - Please Specify', 'trim|xss_clean' . required_field('other', $fields, 'validation'));
			
			//mileage
			if($mileage_section == 1){
				$this->form_validation->set_rules('mileage_default_start_location', field_label('mileage_default_start_location', $fields, true), 'trim|xss_clean' . required_field('mileage_default_start_location', $fields, 'validation'));
				$this->form_validation->set_rules('mileage_activate_fuel_cards', field_label('mileage_activate_fuel_cards', $fields, true), 'trim|xss_clean' . required_field('mileage_activate_fuel_cards', $fields, 'validation'));
				$this->form_validation->set_rules('mileage_default_mode_of_transport', field_label('mileage_default_mode_of_transport', $fields, true), 'trim|xss_clean' . required_field('mileage_default_mode_of_transport', $fields, 'validation'));
				$this->form_validation->set_rules('activate_mileage', field_label('activate_mileage', $fields, true), 'trim|xss_clean' . required_field('activate_mileage', $fields, 'validation'));
			}

			// driving
			$this->form_validation->set_rules('driving_mot', field_label('mot', $fields, true), 'trim|xss_clean' . required_field('mot', $fields, 'validation'));
			$this->form_validation->set_rules('driving_mot_expiry', 'MOT - Expiry', 'trim|xss_clean|callback_check_date' . required_field('mot', $fields, 'validation'));
			$this->form_validation->set_rules('driving_insurance', field_label('insurance', $fields, true), 'trim|xss_clean' . required_field('insurance', $fields, 'validation'));
			$this->form_validation->set_rules('driving_insurance_expiry', 'Insurance - Expiry', 'trim|xss_clean|callback_check_date' . required_field('insurance', $fields, 'validation'));
			$this->form_validation->set_rules('driving_declaration', 'Declaration', 'trim|xss_clean');

			// validation
			$this->form_validation->set_rules('proof_address', field_label('proof_of_address', $fields, true), 'trim|xss_clean' . required_field('proof_of_address', $fields, 'validation'));
			$this->form_validation->set_rules('proof_nationalinsurance', field_label('proof_of_national_insurance', $fields, true), 'trim|xss_clean' . required_field('proof_of_national_insurance', $fields, 'validation'));
			$this->form_validation->set_rules('proof_quals', field_label('proof_of_qualifications', $fields, true), 'trim|xss_clean' . required_field('proof_of_qualifications', $fields, 'validation'));
			$this->form_validation->set_rules('proof_permit', field_label('valid_working_permit', $fields, true), 'trim|xss_clean' . required_field('valid_working_permit', $fields, 'validation'));

			// checklist
			$this->form_validation->set_rules('checklist_idcard', field_label('id_card', $fields, true), 'trim|xss_clean' . required_field('valid_working_permit', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_paydates', field_label('pay_dates', $fields, true), 'trim|xss_clean' . required_field('pay_dates', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_timesheet', field_label('timesheet', $fields, true), 'trim|xss_clean' . required_field('timesheet', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_policy', field_label('policy_agreement', $fields, true), 'trim|xss_clean' . required_field('policy_agreement', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_travel', field_label('travel_expenses', $fields, true), 'trim|xss_clean' . required_field('travel_expenses', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_equal', field_label('equal_opportunities', $fields, true), 'trim|xss_clean' . required_field('equal_opportunities', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_contract', field_label('employment_contract', $fields, true), 'trim|xss_clean' . required_field('employment_contract', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_p45', field_label('p45', $fields, true), 'trim|xss_clean' . required_field('p45', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_crb', field_label('dbs', $fields, true), 'trim|xss_clean' . required_field('dbs', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_policies', field_label('policies', $fields, true), 'trim|xss_clean' . required_field('policies', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_details', field_label('details_updated', $fields, true), 'trim|xss_clean' . required_field('details_updated', $fields, 'validation'));
			$this->form_validation->set_rules('checklist_tshirt', field_label('tshirt', $fields, true), 'trim|xss_clean' . required_field('tshirt', $fields, 'validation'));

			// target and hours
			$this->form_validation->set_rules('target_hours', field_label('salaried_hours', $fields, true), 'trim|xss_clean|numeric' . required_field('salaried_hours', $fields, 'validation'));
			$this->form_validation->set_rules('target_utilisation', field_label('target_utilisation', $fields, true), 'trim|xss_clean|integer|greater_than_equal_to[1]|less_than_equal_to[100]' . required_field('target_utilisation', $fields, 'validation'));
			$this->form_validation->set_rules('target_observation_score', field_label('target_observation_score', $fields, true), 'trim|xss_clean|integer|greater_than_equal_to[1]|less_than_equal_to[100]' . required_field('target_observation_score', $fields, 'validation'));
			$this->form_validation->set_rules('employment_start_date', field_label('start_date', $fields, true), 'trim|xss_clean|callback_check_date' . required_field('start_date', $fields, 'validation'));
			$this->form_validation->set_rules('employment_end_date',field_label('end_date', $fields, true), 'trim|xss_clean|callback_check_date' . required_field('end_date', $fields, 'validation'));
			$this->form_validation->set_rules('employment_probation_date', field_label('probation_date', $fields, true), 'trim|xss_clean|callback_check_date' . required_field('probation_date', $fields, 'validation'));
			$this->form_validation->set_rules('employment_probation_complete', field_label('probation_complete', $fields, true), 'trim|xss_clean' . required_field('probation_complete', $fields, 'validation'));

			// payscales
			$this->form_validation->set_rules('payments_scale_head', $this->settings_library->get_staffing_type_label('head') . ' - Per Hour', 'trim|xss_clean' . required_field('head', $fields, 'validation'));
			$this->form_validation->set_rules('payments_scale_assist', $this->settings_library->get_staffing_type_label('assistant') . ' - Per Hour', 'trim|xss_clean' . required_field('assistant', $fields, 'validation'));
			$this->form_validation->set_rules('payments_scale_lead', $this->settings_library->get_staffing_type_label('lead') . ' - Per Hour', 'trim|xss_clean' . required_field('lead', $fields, 'validation'));
			$this->form_validation->set_rules('payments_scale_participant', $this->settings_library->get_staffing_type_label('participant') . ' - Per Hour', 'trim|xss_clean');
			$this->form_validation->set_rules('payments_scale_observer', $this->settings_library->get_staffing_type_label('observer') . ' - Per Hour', 'trim|xss_clean');
			$this->form_validation->set_rules('payments_scale_salaried', field_label('salaried_staff', $fields, true), 'trim|xss_clean' . required_field('salaried_staff', $fields, 'validation'));
			$this->form_validation->set_rules('payments_scale_salary', 'Salary', 'trim|xss_clean|is_numeric');
			$this->form_validation->set_rules('houry_rate_value', field_label('standard_hourly_rate', $fields, true), 'trim|xss_clean' . required_field('standard_hourly_rate', $fields, 'validation'));

			// payments
			$this->form_validation->set_rules('payments_bankName', field_label('bank_name', $fields, true), 'trim|xss_clean' . required_field('bank_name', $fields, 'validation'));
			$this->form_validation->set_rules('payments_sortCode', field_label('sort_code', $fields, true), 'trim|xss_clean' . required_field('sort_code', $fields, 'validation'));
			$this->form_validation->set_rules('payments_accountNumber', field_label('account_number', $fields, true), 'trim|xss_clean' . required_field('standard_hourly_rate', $fields, 'validation'));
			$this->form_validation->set_rules('payroll_number', field_label('payroll_number', $fields, true), 'trim|xss_clean' . required_field('standard_hourly_rate', $fields, 'validation'));

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// dates
				$proofid_passport_date = NULL;
				if (set_value('proofid_passport_date') != '') {
					$proofid_passport_date = uk_to_mysql_date(set_value('proofid_passport_date'));
				}

				$proofid_driving_date = NULL;
				if (set_value('proofid_driving_date') != '') {
					$proofid_driving_date = uk_to_mysql_date(set_value('proofid_driving_date'));
				}

				$proofid_birth_date = NULL;
				if (set_value('proofid_birth_date') != '') {
					$proofid_birth_date = uk_to_mysql_date(set_value('proofid_birth_date'));
				}

				$driving_mot_expiry = NULL;
				if (set_value('driving_mot_expiry') != '') {
					$driving_mot_expiry = uk_to_mysql_date(set_value('driving_mot_expiry'));
				}

				$driving_insurance_expiry = NULL;
				if (set_value('driving_insurance_expiry') != '') {
					$driving_insurance_expiry = uk_to_mysql_date(set_value('driving_insurance_expiry'));
				}

				$employment_start_date = NULL;
				if (set_value('employment_start_date') != '') {
					$employment_start_date = uk_to_mysql_date(set_value('employment_start_date'));
				}

				$employment_end_date = NULL;
				if (set_value('employment_end_date') != '') {
					$employment_end_date = uk_to_mysql_date(set_value('employment_end_date'));
				}

				$employment_probation_date = NULL;
				if (set_value('employment_probation_date') != '') {
					$employment_probation_date = uk_to_mysql_date(set_value('employment_probation_date'));
				}

				// all ok, prepare data
				$data = array(
					'proofid_passport' => 0,
					'proofid_passport_date' => $proofid_passport_date,
					'proofid_passport_ref' => set_value('proofid_passport_ref'),
					'proofid_nicard' => 0,
					'proofid_nicard_ref' => set_value('proofid_nicard_ref'),
					'proofid_driving' => 0,
					'proofid_driving_date' => $proofid_driving_date,
					'proofid_driving_ref' => set_value('proofid_driving_ref'),
					'proofid_birth' => 0,
					'proofid_birth_date' => $proofid_birth_date,
					'proofid_birth_ref' => set_value('proofid_birth_ref'),
					'proofid_utility' => 0,
					'proofid_other' => 0,
					'proofid_other_specify' => set_value('proofid_other_specify'),
					'proof_address' => 0,
					'proof_nationalinsurance' => 0,
					'proof_quals' => 0,
					'proof_permit' => 0,
					'checklist_idcard' => 0,
					'checklist_paydates' => 0,
					'checklist_timesheet' => 0,
					'checklist_policy' => 0,
					'checklist_travel' => 0,
					'checklist_equal' => 0,
					'checklist_contract' => 0,
					'checklist_p45' => 0,
					'checklist_crb' => 0,
					'checklist_policies' => 0,
					'checklist_details' => 0,
					'checklist_tshirt' => 0,
					'payments_scale_head' => 0,
					'payments_scale_assist' => 0,
					'payments_scale_participant' => 0,
					'payments_scale_lead' => 0,
					'payments_scale_observer' => 0,
					'payments_scale_salaried' => 0,
					'payments_scale_salary' => 0,
					'payments_bankName' => set_value('payments_bankName'),
					'payments_sortCode' => set_value('payments_sortCode'),
					'payments_accountNumber' => set_value('payments_accountNumber'),
					'payroll_number' => set_value('payroll_number'),
					'target_hours' => 0,
					'target_utilisation' => 0,
					'target_observation_score' => NULL,
					'employment_start_date' => $employment_start_date,
					'employment_end_date' => $employment_end_date,
					'employment_probation_date' => $employment_probation_date,
					'employment_probation_complete' => 0,
					'driving_mot' => 0,
					'driving_mot_expiry' => $driving_mot_expiry,
					'driving_insurance' => 0,
					'driving_insurance_expiry' => $driving_insurance_expiry,
					'driving_declaration' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'system_pay_rates' => 0,
					'hourly_rate' => 0
				);
				// Check Mileage Section activation
				if($mileage_section == 1){
					$data["default_start_location"] = set_value('mileage_default_start_location');
					$data["mileage_default_mode_of_transport"] = set_value('mileage_default_mode_of_transport');
					$data["mileage_activate_fuel_cards"] = set_value('mileage_activate_fuel_cards');
					$data["activate_mileage"] = set_value('activate_mileage');
				}
				if (set_value('houry_rate_value') > 0) {
					$data['hourly_rate'] = set_value('houry_rate_value');
				}
				if (set_value('hourly_rate') == 0) {
					$data['hourly_rate'] = 0;
				}
				if (set_value('system_pay_rates') == 1) {
					$data['hourly_rate'] = 0;
					$data['system_pay_rates'] = 1;
				}
				if (set_value('proofid_passport') == 1) {
					$data['proofid_passport'] = 1;
				}
				if (set_value('proofid_nicard') == 1) {
					$data['proofid_nicard'] = 1;
				}
				if (set_value('proofid_driving') == 1) {
					$data['proofid_driving'] = 1;
				}
				if (set_value('proofid_birth') == 1) {
					$data['proofid_birth'] = 1;
				}
				if (set_value('proofid_utility') == 1) {
					$data['proofid_utility'] = 1;
				}
				if (set_value('proofid_other') == 1) {
					$data['proofid_other'] = 1;
				}
				if (set_value('proof_address') == 1) {
					$data['proof_address'] = 1;
				}
				if (set_value('proof_nationalinsurance') == 1) {
					$data['proof_nationalinsurance'] = 1;
				}
				if (set_value('proof_quals') == 1) {
					$data['proof_quals'] = 1;
				}
				if (set_value('proof_permit') == 1) {
					$data['proof_permit'] = 1;
				}
				if (set_value('checklist_idcard') == 1) {
					$data['checklist_idcard'] = 1;
				}
				if (set_value('checklist_paydates') == 1) {
					$data['checklist_paydates'] = 1;
				}
				if (set_value('checklist_timesheet') == 1) {
					$data['checklist_timesheet'] = 1;
				}
				if (set_value('checklist_policy') == 1) {
					$data['checklist_policy'] = 1;
				}
				if (set_value('checklist_travel') == 1) {
					$data['checklist_travel'] = 1;
				}
				if (set_value('checklist_equal') == 1) {
					$data['checklist_equal'] = 1;
				}
				if (set_value('checklist_contract') == 1) {
					$data['checklist_contract'] = 1;
				}
				if (set_value('checklist_p45') == 1) {
					$data['checklist_p45'] = 1;
				}
				if (set_value('checklist_crb') == 1) {
					$data['checklist_crb'] = 1;
				}
				if (set_value('checklist_policies') == 1) {
					$data['checklist_policies'] = 1;
				}
				if (set_value('checklist_details') == 1) {
					$data['checklist_details'] = 1;
				}
				if (set_value('checklist_tshirt') == 1) {
					$data['checklist_tshirt'] = 1;
				}
				if (set_value('payments_scale_head') > 0) {
					$data['payments_scale_head'] = set_value('payments_scale_head');
				}
				if (set_value('payments_scale_assist') > 0) {
					$data['payments_scale_assist'] = set_value('payments_scale_assist');
				}
				if (set_value('payments_scale_lead') > 0) {
					$data['payments_scale_lead'] = set_value('payments_scale_lead');
				}
				if (set_value('payments_scale_participant') > 0) {
					$data['payments_scale_participant'] = set_value('payments_scale_participant');
				}
				if (set_value('payments_scale_observer') > 0) {
					$data['payments_scale_observer'] = set_value('payments_scale_observer');
				}
				if (set_value('payments_scale_salaried') == 1) {
					$data['payments_scale_salaried'] = 1;
				}
				
				if (set_value('payments_scale_salary') > 0) {
					$data['payments_scale_salary'] = set_value('payments_scale_salary');
				}
				if (set_value('target_hours') > 0) {
					$data['target_hours'] = set_value('target_hours');
				}
				if (set_value('target_utilisation') > 0) {
					$data['target_utilisation'] = set_value('target_utilisation');
				}
				if (set_value('target_observation_score') > 0) {
					$data['target_observation_score'] = set_value('target_observation_score');
				}
				if (set_value('employment_probation_complete') == 1) {
					$data['employment_probation_complete'] = 1;
				}
				if (set_value('driving_mot') == 1) {
					$data['driving_mot'] = 1;
				}
				if (set_value('driving_insurance') == 1) {
					$data['driving_insurance'] = 1;
				}
				if (set_value('driving_declaration') == 1) {
					$data['driving_declaration'] = 1;
				}

				// work out from date for address
				$fromM = set_value('fromM');
				$fromY = set_value('fromY');
				if (!empty($fromM) && !empty($fromY)) {
					$from = $fromY . "-" . $fromM . "-1";
				} else {
					$from = NULL;
				}

				// final check for errors
				if (count($errors) == 0) {

					$where = array(
						'staffID' => $staffID,
						'accountID' => $this->auth->user->accountID
					);

					// update
					$query = $this->db->update('staff', $data, $where);

					// if updated
					if ($this->db->affected_rows() == 1) {
						
						$approverID = $this->input->post('approverID');
                        if(!is_array($approverID)) {
                            $approverID = array();
                        }
						// remove existing
					    $where = array(
						    'staffID' => $staffID,
						    'accountID' => $this->auth->user->accountID
                        );
                        $this->db->delete('staff_recruitment_approvers', $where);
						if(count($approverID) > 0) {
							foreach($approverID as $approverIDs) {
								$data = array(
									'staffID' => $staffID,
									'approverID' => $approverIDs,
									'accountID' => $this->auth->user->accountID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);

								$this->db->insert('staff_recruitment_approvers', $data);
								
							}
						}

						$this->session->set_flashdata('success', 'Recruitment data has been updated successfully.');

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
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

		// get team leaders
		$where_in = array(
			'directors',
			'management',
			'headcoach'
		);
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$team_leaders = $this->db->from('staff')->where($where)->where_in('department', $where_in)->order_by('first asc, surname asc')->get();
		
		// Get Activate Fuel Card value from Mileage Section
		$where = array(
			'key' => 'mileage_activate_fuel_cards',
			'accountID' => $this->auth->user->accountID
		);
		$query = $this->db->select("*")->from("accounts_settings")->where($where)->get();
		$mileage_activate_fuel_cards = 0;
		foreach($query->result() as $result){
			$mileage_activate_fuel_cards = $result->value;
		}
		
		$mileage_data = $this->db->select("*")->from("mileage")->where("accountID", $this->auth->user->accountID)->get();
		
		//get selected team leaders from staff_recruitment_approvers
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		
		$selected_team_leaders = array();
		$query = $this->db->from('staff_recruitment_approvers')->where($where)->get();
		foreach($query->result() as $result){
			$selected_team_leaders[] = $result->approverID;
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
			'staff_info' => $staff_info,
			'staffID' => $staffID,
			'team_leaders' => $team_leaders,
			'selected_team_leaders' => $selected_team_leaders,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'fields' => $fields,
			'mileage_activate_fuel_cards' => $mileage_activate_fuel_cards,
			'mileage_section' => $mileage_section,
			'mileage_data' => $mileage_data,
			'roles' => $roles
		);

		// load view
		$this->crm_view('staff/recruitment', $data);
	}

	/**
	 * check date is correct
	 * @param  string $date
	 * @return bool
	 */
	public function check_date($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check
		if (check_uk_date($date)) {
			return TRUE;
		}

		return FALSE;

	}

}

/* End of file recruitment.php */
/* Location: ./application/controllers/staff/recruitment.php */
