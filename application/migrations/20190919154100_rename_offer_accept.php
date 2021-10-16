<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_offer_accept extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add main section fields with empty subsection and toggle other checkbox
            $data = array(
                'title' => 'Offer & Accept (Auto) Emails',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'offer_accept_emails_emailsms')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Offer Session Subject',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_offer_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Offer Session',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_offer')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Send Notifications To',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_notifications_to')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Offer Accepted Subject',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_accepted_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Offer Accepted',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_accepted')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Offers Declined By All Subject',
                'value' => 'Offer Declined By All',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_exhausted_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept (Auto) - Offers Declined By All',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_exhausted')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept - Offer Declined Subject',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_declined_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer & Accept - Offer Declined',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_declined')->update('settings', $data);
		}

		public function down() {
            $data = array(
                'title' => 'Offer Accept Emails',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'offer_accept_emails_emailsms')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offer Session Subject',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_offer_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offer Session',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_offer')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Send Notifications To',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_notifications_to')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offer Accepted Subject',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_accepted_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offer Accepted',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_accepted')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offers Exhausted Subject',
                'value' => 'Offer Exhausted',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_exhausted_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offers Exhausted',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_exhausted')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offer Declined Subject',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_declined_subject')->update('settings', $data);

            $data = array(
                'title' => 'Offer/Accept - Offer Declined',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'email_offer_accept_declined')->update('settings', $data);
		}
}
