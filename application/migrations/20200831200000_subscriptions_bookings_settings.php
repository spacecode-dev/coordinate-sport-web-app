<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Subscriptions_bookings_settings extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        $fields = array(
            'enable_subscriptions' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => FALSE,
                'after' => 'website_description'
            ),
            'subscriptions_only' => array(
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'website_description'
            ),
        );

        $this->dbforge->add_column('bookings', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('bookings', 'enable_subscriptions');
        $this->dbforge->drop_column('bookings', 'subscriptions_only');
    }
}
