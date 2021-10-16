<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cascade_voucher_sessions_associations extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		// get key name
		$sql = "SELECT
		    CONSTRAINT_NAME
		FROM
		    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
		WHERE
		    TABLE_NAME = '" . $this->db->dbprefix('bookings_lessons_vouchers') . "'
		    AND COLUMN_NAME = 'lessonID'
		    AND CONSTRAINT_SCHEMA = '" . $this->db->database . "'";
		$res = $this->db->query($sql);
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// drop foreign key
				$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_vouchers') . '` DROP FOREIGN KEY `' . $row->CONSTRAINT_NAME . '`');
			}
		}

		// recreate foreign key
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_vouchers') . '` ADD CONSTRAINT `fk_bookings_lessons_vouchers_lessonID` FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down() {
        // no going back
    }
}
