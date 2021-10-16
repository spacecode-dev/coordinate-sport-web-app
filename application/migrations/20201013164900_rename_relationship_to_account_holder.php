<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rename_relationship_to_account_holder extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update setting
			$data = array(
				'label' => 'Account Type'
			);
			$where = array(
				'field' => 'eRelationship',
				'section' => 'account_holder'
			);
			$this->db->update('settings_fields', $data, $where, 1);
			
			// Update Field
            $fields = array(
                'relationship' => array(
                    'name' => 'relationship',
                    'type' => "ENUM('parent','grandparent','guardian','parent&#039;s friend','other','individual')",
                    'default' => NULL
                )
            );
			
			$this->dbforge->modify_column('family_contacts', $fields);
			
		}

		public function down() {
			// revert setting
			$data = array(
				'label' => 'Relationship to Account Holder'
			);
			$where = array(
				'field' => 'eRelationship',
				'section' => 'account_holder'
			);
			$this->db->update('settings_fields', $data, $where, 1);
			
			$fields = array(
                'relationship' => array(
                    'name' => 'relationship',
                    'type' => "ENUM('parent','grandparent','guardian','parents friend','other','individual')",
                    'default' => NULL
                )
            );
			
			$this->dbforge->modify_column('family_contacts', $fields);
		}
}
