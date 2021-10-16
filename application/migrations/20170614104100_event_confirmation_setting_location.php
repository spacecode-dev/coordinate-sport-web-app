<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Event_confirmation_setting_location extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update instruction and value
            $data = array(
                'value' => '<p>Hi {contact_first},</p>
<p>Thank you for your booking on {event_name}.</p>
<p>Location: {location}</p>
<p>{details}</p>
<p>Please check everything and if anything is incorrect, please contact us.</p>',
                'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {date_description}, {details}, {website}, {location}'
            );
            $where = array(
                'key' => 'email_event_confirmation'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // update instruction and value
            $data = array(
                'value' => '<p>Hi {contact_first},</p>
<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>
<p>{details}</p>
<p>Please check everything and if anything is incorrect, please contact us.</p>',
                'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {date_description}, {details}, {website}'
            );
            $where = array(
                'key' => 'email_event_confirmation'
            );
            $this->db->update('settings', $data, $where, 1);
        }
}