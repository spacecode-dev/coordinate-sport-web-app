<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Not_null_staff_address_staff_id extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// delete addresses with no staff
			$where = array(
				'staff.staffID' => NULL
			);
			$res = $this->db->from('staff_addresses')->join('staff', 'staff_addresses.staffID = staff.staffID', 'left')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$where = array(
						'addressID' => $row->addressID
					);
					$this->db->delete('staff_addresses', $where, 1);
				}
			}

			// remove foreign key
	        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_addresses') . '` DROP FOREIGN KEY `app_staff_addresses_ibfk_2`');

			// modify
			$fields = array(
				'staffID' => array(
					'name' => 'staffID',
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE
				),
			);
			$this->dbforge->modify_column('staff_addresses', $fields);

			// set foreign key
	        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_addresses') . '` ADD CONSTRAINT `fk_staff_addresses_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
		}

		public function down() {
			// no going back
		}
}