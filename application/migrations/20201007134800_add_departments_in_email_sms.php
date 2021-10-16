<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_departments_in_email_sms extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {

		// define fields
		$fields = array(
			'ID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'accountID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 500
			),
			'from_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 500
			),
			'reply_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 500
			),
			'sms_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 500
			),
			'email_footer' => array(
				'type' => 'TEXT'
			),
			'active' => array(
				'type' => "ENUM('1','0')",
				'default' => '1'
			),
			'modified' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			),
			'added' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			)
		);
		$this->dbforge->add_field($fields);

		// add keys
		$this->dbforge->add_key('ID', TRUE);
		$this->dbforge->add_key('accountID');

		// set table attributes
		$attributes = array(
			'ENGINE' => 'InnoDB'
		);

		// create table
		$this->dbforge->create_table('settings_departments_email', FALSE, $attributes);

		// define fields
		$fields = array(
			'ID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'accountID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'department_email_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'departmentID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'modified' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			),
			'added' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			)
		);
		$this->dbforge->add_field($fields);

		// add keys
		$this->dbforge->add_key('ID', TRUE);
		$this->dbforge->add_key('department_email_id');
		$this->dbforge->add_key('departmentID');

		// set table attributes
		$attributes = array(
			'ENGINE' => 'InnoDB'
		);

		// create table
		$this->dbforge->create_table('settings_departments_relation', FALSE, $attributes);

		// modify settings fields
		$fields = array(
			'type' => array(
				'name' => 'type',
				'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css', 'function', 'date', 'date-monday', 'permission-levels', 'brand_tags')",
				'null' => FALSE,
			)
		);
		$this->dbforge->modify_column('settings', $fields);

		// Add mew section
		$data = array(
			'key' => 'departments_emailsms',
			'tab' => 'general',
			'title' => 'Departments',
			'description' => 'Set your email footer, name, email address and the SMS name you will use to communicate with customers from each department area',
			'type' => 'checkbox',
			'section' => 'emailsms-main',
			'order' => 13,
			'value' => 0,
			'instruction' => '',
			'created_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$this->db->insert('settings', $data);

		// define new settings
		$data = array(
			array(
				'key' => 'department_email_name',
				'title' => 'Name',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'departments_emailsms',
				'order' => 0,
				'value' => "",
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'department_email_from_name',
				'title' => 'Send Emails From Name',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'departments_emailsms',
				'order' => 1,
				'value' => 'Coordinate',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'department_email_from',
				'title' => 'Reply Email Address',
				'type' => 'email',
				'section' => 'emailsms',
				'subsection' => 'departments_emailsms',
				'order' => 2,
				'value' => 'support@coordinate.cloud',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'department_sms_from',
				'title' => 'Send SMS From Name',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'departments_emailsms',
				'order' => 3,
				'value' => '{company}',
				'instruction' => 'Maximum 11 characters. Available tags: {company}',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'department_list',
				'title' => 'Departments',
				'type' => 'brand_tags',
				'section' => 'emailsms',
				'subsection' => 'departments_emailsms',
				'order' => 4,
				'value' => "",
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'department_email_footer',
				'title' => 'Email Footer',
				'type' => 'wysiwyg',
				'section' => 'emailsms',
				'subsection' => 'departments_emailsms',
				'instruction' => 'Available Tags: {company}',
				'order' => 5,
				'value' => '<p>Kind Regards,</p><p>{company}</p><p><strong>Disclaimer:</strong> This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed. Any opinions presented do not necessarily represent those of {company}. If you are not the intended recipient, please notify the author immediately by email and delete all copies of the email on your system. Any unauthorised disclosure of information contained in this communication is strictly prohibited.</p>',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);
		// bulk insert
		foreach ($data as $d) {
			$this->db->insert('settings', $d);
		}
	}

	public function down() {

		// drop table
		$this->dbforge->drop_table('settings_departments_email',TRUE);

		// modify settings fields
		$fields = array(
			'type' => array(
				'name' => 'type',
				'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css', 'function', 'date', 'date-monday', 'permission-levels')",
				'null' => FALSE,
			)
		);
		$this->dbforge->modify_column('settings', $fields);

		// drop field
		$where = array(
			'key' => 'departments_emailsms'
		);
		$this->db->delete('settings', $where);

		$where = array(
			'subsection' => 'departments_emailsms'
		);
		$this->db->delete('settings', $where);
	}
}
