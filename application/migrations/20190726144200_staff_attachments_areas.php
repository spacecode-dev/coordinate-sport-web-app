<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_attachments_areas extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'area' => array(
                'type' => 'VARCHAR',
                'after' => 'comment',
                'constraint' => 50,
                'default' => null
			),
            'belongs_to' => array(
                'type' => 'VARCHAR',
                'after' => 'area',
                'constraint' => 50,
                'default' => null
			)
		);
		$this->dbforge->add_column('staff_attachments', $fields);

	}

	public function down() {
		$this->dbforge->drop_column('staff_attachments', 'area');
	}
}