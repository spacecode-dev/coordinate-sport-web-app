<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Locked_payments extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'locked' => array(
                'type' => "TINYINT",
                'constraint' => 1,
                'default' => '0',
                'null' => FALSE,
                'after' => 'note'
            ),
        );
        $this->dbforge->add_column('family_payments', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('family_payments', 'locked');
    }
}