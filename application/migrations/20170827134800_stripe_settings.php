<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stripe_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'cc_processor',
                    'title' => 'Credit/Debit Card Processor',
                    'type' => 'select',
                    'section' => 'integrations',
                    'order' => 120,
                    'options' => "stripe : Stripe
sagepay : Sage Pay",
                    'value' => NULL,
                    'instruction' => 'For taking payments online',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'stripe_pk',
                    'title' => 'Stripe Publishable Key',
                    'type' => 'text',
                    'section' => 'integrations',
                    'order' => 130,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => 'From your <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Stripe Dashboard</a>.',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'stripe_sk',
                    'title' => 'Stripe Secret Key',
                    'type' => 'text',
                    'section' => 'integrations',
                    'order' => 131,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => 'From your <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Stripe Dashboard</a>.',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'cc_processor',
                'stripe_pk',
                'stripe_sk'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}