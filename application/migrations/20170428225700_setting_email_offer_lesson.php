<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_offer_lesson extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_offer_accept_offer_subject',
                    'title' => 'Offer/Accept - Offer Session Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 200,
                    'value' => 'New Sessions Offered',
                    'instruction' => 'Available tags: {first_name}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_offer_accept_offer',
                    'title' => 'Offer/Accept - Offer Session',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 201,
                    'value' => '<p>Hello {first_name},</p>
<p>You have been invited to teach on one or more sessions. Please go to the link below to accept or decline them:</p>
<p>{link}</p>',
                    'instruction' => 'Available tags: {first_name}, {link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_offer_accept_offer_subject',
                'email_offer_accept_offer'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}