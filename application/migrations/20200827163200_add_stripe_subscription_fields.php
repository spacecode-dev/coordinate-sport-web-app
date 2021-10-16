<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_stripe_subscription_fields extends CI_Migration {
    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        $fields = array(
            'payment_provider' => array(
                'type' => "ENUM('gocardless','stripe')",
                'after' => 'price',
                'default' => 'gocardless',
                'null' => false
            ),
            'stripe_product_id' => array(
                'type' => "VARCHAR (255)",
                'after' => 'payment_provider',
                'null' => true
            ),
            'stripe_price_id' => array(
                'type' => "VARCHAR (255)",
                'after' => 'stripe_product_id',
                'null' => true
            ),
        );

        $this->dbforge->add_column('subscriptions', $fields);

        $fields = array(
            'stripe_subscription_id' => array(
                'type' => "VARCHAR (255)",
                'after' => 'gc_subscription_id',
                'null' => true
            ),
        );

        $this->dbforge->add_column('participant_subscriptions', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('subscriptions', 'payment_provider');
        $this->dbforge->drop_column('subscriptions', 'stripe_product_id');
        $this->dbforge->drop_column('subscriptions', 'stripe_price_id');
        $this->dbforge->drop_column('participant_subscriptions', 'stripe_subscription_id');
    }
}
