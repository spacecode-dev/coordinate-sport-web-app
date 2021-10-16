<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_behavioural_information_field extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// add blacklist field to family contacts
			$fields = array(
				'behavioural_info' => array(
					'type' => 'TEXT',
					'null' => TRUE,
					'after' => 'disability_info'
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
			$this->dbforge->add_column('family_children', $fields);

			//Staff Subscription
			$data = [
				[
					'section' => 'account_holder',
					'field' => 'behavioural_information',
					'label' => 'Behavioural Information',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 1032,
					'added' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'section' => 'participant',
					'field' => 'behavioural_information',
					'label' => 'Behavioural Information',
					'show' => 1,
					'required' => 0,
					'locked' => 0,
					'order' => 1045,
					'added' => mdate('%Y-%m-%d %H:%i:%s')
				]
			];

			foreach ($data as $field) {
				$this->db->insert('settings_fields', $field);
			}
		}

		public function down() {

			$this->dbforge->drop_column('family_contacts', 'behavioural_info');
			$this->dbforge->drop_column('family_children', 'behavioural_info');

			//Delete records
			$this->db->delete('settings_fields', [
				'field' => 'behavioural_information'
			]);

		}
}
