<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_resources_options
 extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'policies' => array(
					'type' => "INT",
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'active'
				),
				'customer_attachments' => array(
					'type' => "INT",
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'policies'
				)
			);
			$this->dbforge->add_column('settings_resources', $fields);

			// map existing categories named to columns
			$where = [
				'name' => 'Staff Policies'
			];
			$data = [
				'policies' => 1
			];
			$this->db->update('settings_resources', $data, $where);

			$where = [
				'name' => 'Office Templates'
			];
			$data = [
				'customer_attachments' => 1
			];
			$this->db->update('settings_resources', $data, $where);
		}

		public function down() {
			$this->dbforge->drop_column('settings_resources', 'policies');
			$this->dbforge->drop_column('settings_resources', 'customer_attachments');
		}
}
