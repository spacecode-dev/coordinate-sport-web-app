<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_permission_levels extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// modify settings fields
			$fields = array(
				'type' => array(
					'name' => 'type',
					'type' => "enum('text','textarea','number','email','email-multiple','wysiwyg','staff','select','image','checkbox','brand','url','tel','html','css','function','date','date-monday', 'permission-levels')",
					'null' => FALSE,
				)
			);
			$this->dbforge->modify_column('settings', $fields);

			// define new settings
			$data = array(
				array(
					'key' => 'removal_permissions',
					'title' => 'Removal Permissions',
					'type' => 'permission-levels',
					'section' => 'general',
					'subsection' => 'general_general',
					'order' => 75,
					'value' => '',
					'instruction' => 'Any Permission Levels added to this field will be able to remove anything within the app whilst removing any associated data',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				)
			);

			// bulk insert
			$this->db->insert_batch('settings', $data);

		}

		public function down() {

			// remove new settings
			$where_in = array(
				'removal_permissions'
			);
			$this->db->from('settings')->where_in('key', $where_in)->delete();

			// modify fields
			$fields = array(
				'type' => array(
					'name' => 'type',
					'type' => "enum('text','textarea','number','email','email-multiple','wysiwyg','staff','select','image','checkbox','brand','url','tel','html','css','function','date','date-monday')",
					'null' => FALSE,
				)
			);
		}
}
