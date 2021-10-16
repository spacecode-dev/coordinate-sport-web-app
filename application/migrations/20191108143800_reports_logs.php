<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Reports_logs extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

			$attributes = array(
				'ENGINE' => 'InnoDB'
			);

            // define fields
            $fields = array(
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ],
                'staffID' => [
                    'type' => 'INT',
                    'constraint' => 11
				],
                'reportType' => [
					'type' => "ENUM('payroll')"
				],
                'data' => [
                	'type' => 'TEXT'
				],
                'added' => array(
                    'type' => 'INT',
					'null' => false
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('reportType');

			$this->dbforge->create_table('reports_logs', FALSE, $attributes);

            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('reports_logs') . '` ADD CONSTRAINT `fk_reports_logs_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('reports_logs') . '` DROP FOREIGN KEY `fk_reports_logs_staffID`');
            // remove tables, if exist
            $this->dbforge->drop_table('reports_logs', TRUE);
        }
}
