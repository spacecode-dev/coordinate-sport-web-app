<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rename_teamleader_to_approver extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();

			// increase timeout and memory limit
			set_time_limit(0);
			ini_set('memory_limit', '1024M');
		}

		public function up() {
			// Rename Label Team Leader
			$data = array(
				'label' => 'Approver'
			);
			$where = array(
				'field' => 'team_leader',
				'section' => 'staff_recruitment'
			);
			$this->db->update('settings_fields', $data, $where, 1);
			
			// create table
			$fields = array(
				'staffID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE
				),
				'approverID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE
				),
				'accountID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'null' => FALSE
				),
				'added' => array(
					'type' => 'DATETIME'
				),
				'modified' => array(
					'type' => 'DATETIME',
					'null' => TRUE
				)
			);
			$this->dbforge->add_field($fields);
			
			// add keys
			$this->dbforge->add_key('staffID', TRUE);
			$this->dbforge->add_key('approverID', TRUE);
			$this->dbforge->add_key('accountID');
			
			// set table attributes
			$attributes = array(
				'ENGINE' => 'InnoDB'
			);
			
			// create table
			$this->dbforge->create_table('staff_recruitment_approvers', FALSE, $attributes);
			
			// set foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_recruitment_approvers') . '` ADD CONSTRAINT `fk_staff_recruitment_approvers_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_recruitment_approvers') . '` ADD CONSTRAINT `fk_staff_recruitment_approvers_approverID` FOREIGN KEY (`approverID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_recruitment_approvers') . '` ADD CONSTRAINT `fk_staff_recruitment_approvers_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE'); 
			
			// Replicate data from staff to approvers table
			
			if ($this->db->field_exists('teamleaderID', $this->db->dbprefix('staff'))){
				$where = array("teamleaderID != " => NULL);
				$query = $this->db->from("staff")->where($where)->get();
				
				$overall_data = array();
				if($query->num_rows() > 0){
					foreach($query->result() as $result){
						$data = array("staffID" => $result->staffID,
						"approverID" => $result->teamleaderID,
						"accountID" => $result->accountID,
						"added" => mdate('%Y-%m-%d %H:%i:%s'),
						"modified" => mdate('%Y-%m-%d %H:%i:%s'));
						$overall_data[] = $data;
					}
					
					// bulk insert staff_recruitment_approvers
					$this->db->insert_batch('staff_recruitment_approvers', $overall_data);
					
					//Set Null to teamleaderID
					$data = array("teamleaderID" => NULL);
					$this->db->update("staff", $data, $where);
				}
				
				//remove constraint for teamleader id
				$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff') . '` DROP FOREIGN KEY `app_staff_ibfk_2`');
				
				//remove column teamleader id from staff
				$this->dbforge->drop_column('staff', 'teamleaderID');
			}
		}

		public function down() {
			$data = array(
				'label' => 'Team Leader'
			);
			$where = array(
				'field' => 'team_leader',
				'section' => 'staff_recruitment'
			);
			$this->db->update('settings_fields', $data, $where, 1);
			
			
			//Add Field teamleaderID
			$fields = array(
				'teamleaderID' => array(
					'type' => 'int',
					'default' => NULL,
					'after' => 'imported'
				)
			);
			$this->dbforge->add_column('staff', $fields);
			
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff') . '` ADD CONSTRAINT `app_staff_ibfk_2` FOREIGN KEY (`teamleaderID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
			
			// Record replicate from approvers to staff table
			$query = $this->db->from("staff_recruitment_approvers")->get();
			if($query->num_rows() > 0){
				foreach($query->result() as $result){
					$where = array("staffID" => $result->staffID);
					$data = array("teamleaderID" => $result->approverID);
					$this->db->update("staff", $data, $where, 1);
				}
			}
			
			// remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_recruitment_approvers') . '` DROP FOREIGN KEY `fk_staff_recruitment_approvers_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_recruitment_approvers') . '` DROP FOREIGN KEY `fk_staff_recruitment_approvers_staffID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_recruitment_approvers') . '` DROP FOREIGN KEY `fk_staff_recruitment_approvers_approverID``');
			
			// remove tables, if exist
			$this->dbforge->drop_table('staff_recruitment_approvers', TRUE);
			
		}
}
