<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_resources_options_additional
 extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'booking_attachments' => array(
					'type' => "INT",
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'customer_attachments'
				),
				'session_attachments' => array(
					'type' => "INT",
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'booking_attachments'
				),
				'staff_attachments' => array(
					'type' => "INT",
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'session_attachments'
				)
			);
			$this->dbforge->add_column('settings_resources', $fields);

			// map existing categories named to columns
			$where = [
				'name' => 'Camp Documents'
			];
			$data = [
				'booking_attachments' => 1
			];
			$this->db->update('settings_resources', $data, $where);

			$where = [
				'name' => 'Session Plans'
			];
			$data = [
				'session_attachments' => 1
			];
			$this->db->update('settings_resources', $data, $where);

			$where = [
				'name' => 'Staff Templates'
			];
			$data = [
				'staff_attachments' => 1
			];
			$this->db->update('settings_resources', $data, $where);

			// rename old category field
            $fields = array(
                'category' => array(
                    'name' => 'category_old',
                    'type' => "enum('misc','plans','school','camp','policies','staff','office')",
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('files', $fields);
		}

		public function down() {
			$this->dbforge->drop_column('settings_resources', 'booking_attachments');
			$this->dbforge->drop_column('settings_resources', 'session_attachments');
			$this->dbforge->drop_column('settings_resources', 'staff_attachments');

			// rename old category field
            $fields = array(
                'category_old' => array(
                    'name' => 'category',
                    'type' => "enum('misc','plans','school','camp','policies','staff','office')",
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('files', $fields);
		}
}
