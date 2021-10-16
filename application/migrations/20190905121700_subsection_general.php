<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Subsection_general extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'general-main', 'styling', 'global', 'emailsms', 'emailsms-main', 'dashboard', 'integrations', 'privacy', 'safety')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

			// add main section fields with empty subsection and toggle other checkbox
			$data = [
				[
					'key' => 'general_general',
					'title' => 'General',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 1,
					'value' => 0,
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				], [
					'key' => 'security_general',
					'title' => 'Security',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 2,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'terms_and_conditions_general',
					'title' => 'Terms & Conditions',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 3,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'timesheets_general',
					'title' => 'Timesheets',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 4,
					'value' => 0,
					'toggle_fields' => 'provisional_own_timetable',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'registration_general',
					'title' => 'Registration',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 5,
					'value' => 0,
					'toggle_fields' => 'require_mobile,require_dob',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'participants_general',
					'title' => 'Participants',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 6,
					'value' => 0,
					'toggle_fields' => 'require_participant_email',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'vouchers_general',
					'title' => 'Vouchers',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 7,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'bookings_general',
					'title' => 'Bookings',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 8,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'timetable_general',
					'title' => 'Timetable',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 9,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'credit_limits_general',
					'title' => 'Credit Limit',
					'type' => 'checkbox',
					'section' => 'general-main',
					'order' => 10,
					'value' => 0,
					'toggle_fields' => 'enable_credit_limits',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				]
			];

			foreach ($data as $field) {
                $this->db->insert('settings', $field);
            }

			$data = array(
				'subsection' => 'general_general',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'items_per_page',
				'website',
				'email',
				'phone',
                'address'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'security_general',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'force_password_change_every_x_months'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'terms_and_conditions_general',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'terms_individual',
				'terms_customer'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'timesheets_general',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'provisional_own_timetable',
				'timesheets_create_day',
				'timesheets_submit_day',
				'staff_invoice_address',
				'staff_invoice_prefix',
				'staff_invoice_default_buyer',
				'staff_invoice_default_subject'
			]);

            $this->db->update('settings', $data);

            $data = array(
                'subsection' => 'registration_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'require_mobile',
                'require_dob'
            ]);

            $this->db->update('settings', $data);

            $data = array(
                'subsection' => 'participants_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'require_participant_email'
            ]);

            $this->db->update('settings', $data);

            $data = array(
                'subsection' => 'vouchers_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'childcare_voucher_instruction'
            ]);

            $this->db->update('settings', $data);

            $data = array(
                'subsection' => 'bookings_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'min_age',
                'max_age',
                'booking_cutoff',
                'register_intro'
            ]);

            $this->db->update('settings', $data);


            $data = array(
                'subsection' => 'bookings_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'min_age',
                'max_age',
                'booking_cutoff',
                'register_intro'
            ]);

            $this->db->update('settings', $data);

            $data = array(
                'subsection' => 'timetable_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'timetable_confirm_weeks',
                'shift_pattern_weeks',
                'shift_pattern_start'
            ]);

            $this->db->update('settings', $data);

            $data = array(
                'subsection' => 'credit_limits_general',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where_in('key', [
                'enable_credit_limits',
                'default_credit_limit'
            ]);

            $this->db->update('settings', $data);
		}

		public function down() {

            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations', 'privacy', 'safety')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            $keys = [

            ];

            foreach ($keys as $key) {
                $this->db->delete('settings', [
                    'key' => $key
                ], 1);
            }

            //Todo remove keys

			$this->db->where_in('key', [
				'general_general',
				'security_general',
				'terms_and_conditions_general',
				'timesheets_general',
				'registration_general',
				'participants_general',
				'vouchers_general',
				'bookings_general',
				'timetable_general',
				'credit_limits_general'
			]);

			$data = array(
				'subsection' => NULL,
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->update('settings', $data);
		}
}
