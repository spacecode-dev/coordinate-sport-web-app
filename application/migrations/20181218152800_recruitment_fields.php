<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Recruitment_fields extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$fields = array(
				'section' => array(
					'type' => "ENUM('staff', 'staff_recruitment')",
					'null' => FALSE
				),
			);
			$this->dbforge->modify_column('settings_fields', $fields);

			$fields = array(
				'section' => array(
					'type' => "ENUM('staff', 'staff_recruitment')",
					'null' => FALSE
				),
			);
			$this->dbforge->modify_column('accounts_fields', $fields);

			// insert
			$data = array(
				// personal info
				array(
					'section' => 'staff_recruitment',
					'field' => 'passport',
					'label' => 'Passport',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 100,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'ni_card',
					'label' => 'NI Card',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 101,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'drivers_licence',
					'label' => 'Driver\'s Licence',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 102,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'birth_certificate',
					'label' => 'Birth Certificate',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 103,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'utility_bill',
					'label' => 'Utility Bill',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 104,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'other',
					'label' => 'Other',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 105,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'mot',
					'label' => 'MOT',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 200,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'insurance',
					'label' => 'Insurance',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 201,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'proof_of_address',
					'label' => 'Proof of Address',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 300,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'proof_of_national_insurance',
					'label' => 'Proof of National Insurance',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 301,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				// login info
				array(
					'section' => 'staff_recruitment',
					'field' => 'proof_of_qualifications',
					'label' => 'Proof of Qualifications/DBS',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 302,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'valid_working_permit',
					'label' => 'Valid working permit or visa/UK resident',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 303,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'id_card',
					'label' => 'ID Card',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 400,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'pay_dates',
					'label' => 'Pay Dates',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 401,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				// contact info
				array(
					'section' => 'staff_recruitment',
					'field' => 'timesheet',
					'label' => 'Timesheet',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 402,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'policy_agreement',
					'label' => 'Policy Agreement',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 403,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'travel_expenses',
					'label' => 'Travel Expenses',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 404,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'equal_opportunities',
					'label' => 'Equal Opportunities',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 405,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'employment_contract',
					'label' => 'Employment Contract',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 406,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'p45',
					'label' => 'P45/P46/P38',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 407,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'dbs',
					'label' => 'DBS',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 408,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'policies',
					'label' => 'Policies',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 409,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'details_updated',
					'label' => 'Details updated on system',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 410,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				// emergency contact
				array(
					'section' => 'staff_recruitment',
					'field' => 'tshirt',
					'label' => 'T-shirt',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 411,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'start_date',
					'label' => 'Start Date',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 500,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'end_date',
					'label' => 'End Date',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 501,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'probation_date',
					'label' => 'Probation Date',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 502,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'probation_complete',
					'label' => 'Probation Complete',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 503,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'salaried_hours',
					'label' => 'Salaried Hours',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 504,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'target_utilisation',
					'label' => 'Target Utilisation',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 505,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'target_observation_score',
					'label' => 'Target Observation Score',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 506,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'team_leader',
					'label' => 'Team Leader',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 507,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'head',
					'label' => 'Head Coach',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 600,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'assistant',
					'label' => 'Assistant Coach',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 601,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'salaried_staff',
					'label' => 'Salaried Staff',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 602,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				// misc
				array(
					'section' => 'staff_recruitment',
					'field' => 'system_pay_rates',
					'label' => 'System Pay Rates',
					'show' => 1,
					'required' => 2,
					'locked' => 0,
					'order' => 603,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'standard_hourly_rate',
					'label' => 'Standard Hourly Rate',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 604,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'bank_name',
					'label' => 'Bank Name',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 700,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'sort_code',
					'label' => 'Sort Code',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 701,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'account_number',
					'label' => 'Account Number',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 702,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'section' => 'staff_recruitment',
					'field' => 'payroll_number',
					'label' => 'Payroll Number',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 703,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				),
			);
			$this->db->insert_batch('settings_fields', $data);
		}

		public function down() {
			$this->db->from('settings_fields')->where(['section' => 'staff_recruitment'])->delete();
			$this->db->from('accounts_fields')->where(['section' => 'staff_recruitment'])->delete();
				$fields = array(
					'section' => array(
						'type' => "ENUM('staff')",
						'null' => FALSE
					),
				);
			$this->dbforge->modify_column('settings_fields', $fields);

			$fields = array(
				'section' => array(
					'type' => "ENUM('staff')",
					'null' => FALSE
				),
			);
			$this->dbforge->modify_column('accounts_fields', $fields);
		}
}