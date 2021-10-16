<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_offer_exhausted extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_offer_accept_exhausted_subject',
                    'title' => 'Offer/Accept - Offers Exhausted Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 207,
                    'value' => 'Offer Exhausted',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_offer_accept_exhausted',
                    'title' => 'Offer/Accept - Offers Exhausted',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 208,
                    'value' => '<p>Hello,</p>
<p>The following session has exhausted all available staffing options:</p>
<p>{details}</p>
<p>View Lesson Staff: {link}</p>',
                    'instruction' => 'Available tags: {details}, {link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_offer_accept_exhausted_subject',
                'email_offer_accept_exhausted'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}