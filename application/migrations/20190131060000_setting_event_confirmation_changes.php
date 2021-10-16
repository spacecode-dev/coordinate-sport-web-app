<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_event_confirmation_changes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // update settings
        $data = array(
            'value' => "Booking Confirmation",
            'instruction' => 'Available tags: {contact_first}, {contact_last}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_event_confirmation_subject'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);

		// update settings
        $data = array(
            'value' => "<p>Hi {contact_first},</p>
 <p>Thank you for your booking. You can find details of the sessions booked below.</p>
 <p>{details}</p>
 <p>Please check everything and if anything is incorrect, please contact us.</p>",
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {details}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_event_confirmation'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);
    }

    public function down() {
		// revert settings
        $data = array(
            'value' => "Booking Confirmation for {event_name}",
            'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {date_description}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_event_confirmation_subject'
		);
        $this->db->update('settings', $data, $where, 1);

		// revert settings
        $data = array(
            'value' => "<p>Hi {contact_first},</p>
 <p>Thank you for your booking on {event_name}.</p>
 <p>Location: {location}</p>
 <p>{details}</p>
 <p>Please check everything and if anything is incorrect, please contact us.</p>",
            'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {date_description}, {details}, {website}, {location}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_event_confirmation'
		);
        $this->db->update('settings', $data, $where, 1);
    }
}
