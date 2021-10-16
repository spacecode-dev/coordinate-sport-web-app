<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_childcare_voucher_instruction extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'childcare_voucher_instruction',
                    'title' => 'Childcare Voucher Instruction',
                    'type' => 'textarea',
                    'section' => 'general',
                    'order' => 360,
                    'value' => 'Please note, your balance will remain outstanding until the childcare vouchers are received/activated (this can take several working days).',
                    'instruction' => 'Shown on online booking once childcare voucher option selected',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'childcare_voucher_instruction'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}