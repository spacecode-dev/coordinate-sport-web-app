<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_offer_accepted_declined extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_offer_accept_notifications_to',
                    'title' => 'Offer/Accept - Send Notifications To',
                    'type' => 'email',
                    'section' => 'emailsms',
                    'order' => 202,
                    'value' => NULL,
                    'instruction' => 'Notifications of accepted/rejected session offers will be sent here',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_offer_accept_accepted_subject',
                    'title' => 'Offer/Accept - Offer Accepted Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 203,
                    'value' => 'Sessions Accepted',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_offer_accept_accepted',
                    'title' => 'Offer/Accept - Offer Accepted',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 204,
                    'value' => '<p>Hello,</p>
<p>One or more session offers have been accepted:</p>
<p>{details}</p>
<p>Please go to the link below to view details:</p>
<p>{link}</p>',
                    'instruction' => 'Available tags: {details}, {link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_offer_accept_declined_subject',
                    'title' => 'Offer/Accept - Offer Declined Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 205,
                    'value' => 'Sessions Declined',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_offer_accept_declined',
                    'title' => 'Offer/Accept - Offer Declined',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 206,
                    'value' => '<p>Hello,</p>
<p>One or more session offers have been declined:</p>
<p>{details}</p>
<p>Please go to the link below to view details:</p>
<p>{link}</p>',
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
                'email_offer_accept_notifications_to',
                'email_offer_accept_accepted_subject',
                'email_offer_accept_accepted',
                'email_offer_accept_declined_subject',
                'email_offer_accept_declined'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}