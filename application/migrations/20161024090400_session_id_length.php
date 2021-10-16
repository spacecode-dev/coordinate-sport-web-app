<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Session_id_length extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // modify fields - set to generic while we map values
        $fields = array(
            'id' => array(
                'name' => 'id',
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => FALSE,

            )
        );
        $this->dbforge->modify_column('sessions', $fields);
    }

    public function down() {
        // no downgrade as doing so may trim data
    }
}