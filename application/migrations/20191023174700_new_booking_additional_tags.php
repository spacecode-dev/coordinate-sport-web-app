<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_booking_additional_tags extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add main section fields with empty subsection and toggle other checkbox
            $data = array(
                'instruction' => 'Available tags: {contact_name}, {org_name}, {brand}, {date_description}, {details}, {first_aid}, {child_protection}, {company_dbs}, {other_dbs}',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_new_booking')->update('settings', $data);
		}

		public function down() {
			$data = array(
				'instruction' => 'Available tags: {contact_name}, {org_name}, {brand}, {date_description}, {details}',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where('key', 'email_new_booking')->update('settings', $data);
		}
}
