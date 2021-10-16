<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booking_confirmation_contacts extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update instruction
            $data = array(
                'instruction' => 'Available tags: {org_name}, {date_description}'
            );
            $where = array(
                'key' => 'email_new_booking_subject'
            );
            $this->db->update('settings', $data, $where, 1);

            // update instruction and value
            $data = array(
                'instruction' => 'Available tags: {contact_name}, {org_name}, {brand}, {date_description}, {details}',
                'value' =>'<p>Dear {contact_name},</p>
<p>Thank you for booking with {brand} for {org_name} {date_description}.</p>
<p>Please see below a detailed summary of the lessons you have booked and any information you will need should you have a query or would like to make any amendments to your booking.</p>
<p>{details}</p>
<p>Please check all the above details match your booking requirements and inform us as soon as possible should you notice any discrepancies or are required to make changes.</p>
<p>Please do not hesitate to contact us if you have any queries.</p>
<p>We look forward to continuing working closely with you.</p>'
            );
            $where = array(
                'key' => 'email_new_booking'
            );
            $this->db->update('settings', $data, $where, 1);


        }

        public function down() {
            // update instruction
            $data = array(
                'instruction' => 'Available tags: {main_contact}, {org_name}, {date_description}'
            );
            $where = array(
                'key' => 'email_new_booking_subject'
            );
            $this->db->update('settings', $data, $where, 1);

            // update instruction and value
            $data = array(
                'instruction' => 'Available tags: {main_contact}, {org_name}, {brand}, {date_description}, {details}',
                'value' =>'<p>Dear {main_contact},</p>
<p>Thank you for booking with {brand} for {org_name} {date_description}.</p>
<p>Please see below a detailed summary of the lessons you have booked and any information you will need should you have a query or would like to make any amendments to your booking.</p>
<p>{details}</p>
<p>Please check all the above details match your booking requirements and inform us as soon as possible should you notice any discrepancies or are required to make changes.</p>
<p>Please do not hesitate to contact us if you have any queries.</p>
<p>We look forward to continuing working closely with you.</p>'
            );
            $where = array(
                'key' => 'email_new_booking'
            );
            $this->db->update('settings', $data, $where, 1);
        }
}