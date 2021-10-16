<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_require_full_payment extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'require_full_payment',
                    'title' => 'Require full payment in online booking, unless paying with childcare vouchers',
                    'type' => 'checkbox',
                    'section' => 'integrations',
                    'order' => 121,
                    'value' => 0,
                    'instruction' => 'Only applicable to Stripe payments',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'require_full_payment'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}