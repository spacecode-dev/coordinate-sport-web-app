<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mandatory_quals_tag extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'tag' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => true
            )
        );
        $this->dbforge->add_column('mandatory_quals', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('mandatory_quals', 'tag');
    }
}
