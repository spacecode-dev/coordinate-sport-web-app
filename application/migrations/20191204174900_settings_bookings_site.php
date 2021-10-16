<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_bookings_site extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// add new section for bookings site
			$data = [
				'key' => 'bookings_site_general',
				'title' => 'Online Bookings Site',
				'type' => 'checkbox',
				'section' => 'general-main',
				'order' => 11,
				'value' => 0,
				'toggle_fields' => '',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
            $this->db->insert('settings', $data);

			// move existing bookings settings to new subsection
			$data = [
				'subsection' => 'bookings_site_general',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$where = [
				'subsection' => 'bookings_general'
			];
			$this->db->update('settings', $data, $where);
		}

		public function down() {
			// move back settings to previous subsection
			$data = [
				'subsection' => 'bookings_general',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$where = [
				'subsection' => 'bookings_site_general'
			];
			$this->db->update('settings', $data, $where);

			// remove new section for bookings site
			$where = [
				'key' => 'bookings_site_general'
			];
            $this->db->delete('settings', $where, 1);
		}
}
