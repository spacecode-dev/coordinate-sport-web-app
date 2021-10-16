<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Privacy extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify settings fields
            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations', 'privacy')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            // define new settings
            $data = array(
                array(
                    'key' => 'participant_privacy',
                    'title' => 'Participant Privacy Policy',
                    'type' => 'textarea',
                    'section' => 'privacy',
                    'order' => 0,
                    'value' => '',
                    'instruction' => 'If set, participants will be asked to read and agree to this on their next login',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'participant_marketing_consent_question',
                    'title' => 'Participant Marketing Consent Question',
                    'type' => 'text',
                    'section' => 'privacy',
                    'order' => 10,
                    'value' => 'I give my consent for {company} to send me newsletters or promotional emails to my registered email address',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'participant_privacy_phone_script',
                    'title' => 'Participant Privacy Phone Script',
                    'type' => 'textarea',
                    'section' => 'privacy',
                    'order' => 20,
                    'value' => '',
                    'instruction' => 'If a staff member edits a user\'s privacy preferences, they\'ll be prompted to read out this script to them prior to changing their preferences',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'participant_consent_changed_subject',
                    'title' => 'Participant Consent Changed Email Subject',
                    'type' => 'text',
                    'section' => 'privacy',
                    'order' => 40,
                    'value' => 'Coordinate {company} Data and Privacy Change Notification',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'participant_consent_changed',
                    'title' => 'Participant Consent Changed Email',
                    'type' => 'wysiwyg',
                    'section' => 'privacy',
                    'order' => 41,
                    'value' => '<p>Dear {first_name},</p>
                    <p>This email is to inform you that the following changes were made to your data and privacy settings in your Coordinate user account:</p>
                    <p>Changes made by: {changed_by}<br />
                    Changes were made at: {changed_at}</p>
                    <p>You can change these settings again at anytime by logging into your profile at {link}.</p>',
                    'instruction' => 'Available tags: {first_name}, {changed_by}, {changed_at}, {link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'online_booking_link',
                    'title' => 'Online Booking Link',
                    'type' => 'url',
                    'section' => 'general',
                    'order' => 41,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'staff_privacy',
                    'title' => 'Staff Privacy Policy',
                    'type' => 'textarea',
                    'section' => 'privacy',
                    'order' => 100,
                    'value' => '',
                    'instruction' => 'If set, staff will be asked to read and agree to this on their next login',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'company_privacy',
                    'title' => 'Company Privacy Policy',
                    'type' => 'textarea',
                    'section' => 'global',
                    'order' => 10,
                    'value' => '',
                    'instruction' => 'Participants and staff will be asked to read and agree to this on their next login',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);

            // add fields to family contact
            $fields = array(
                'marketing_consent' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'before' => 'added'
                ),
                'marketing_consent_date' => array(
                    'type' => "DATETIME",
                    'default' => NULL,
                    'null' => TRUE,
                    'after' => 'marketing_consent'
                ),
                'privacy_agreed' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'after' => 'marketing_consent_date'
                ),
                'privacy_agreed_date' => array(
                    'type' => "DATETIME",
                    'default' => NULL,
                    'null' => TRUE,
                    'after' => 'privacy_agreed'
                ),
            );
            $this->dbforge->add_column('family_contacts', $fields);

            // add fields to staff
            $fields = array(
                'privacy_agreed' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'after' => 'non_delivery'
                ),
                'privacy_agreed_date' => array(
                    'type' => "DATETIME",
                    'default' => NULL,
                    'null' => TRUE,
                    'after' => 'privacy_agreed'
                ),
            );
            $this->dbforge->add_column('staff', $fields);

            // modify staff notes fields
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('feedbackpositive', 'feedbacknegative', 'observation', 'induction', 'appraisal', 'disciplinary', 'misc', 'payroll', 'pupilassessment', 'late', 'privacy')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('staff_notes', $fields);

            // add family notes fields
            $fields = array(
                'type' => array(
                    'type' => "ENUM('note', 'privacy')",
                    'null' => FALSE,
                    'default' => 'note',
                    'after' => 'byID'
                ),
            );
            $this->dbforge->add_column('family_notes', $fields);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'participant_privacy',
                'participant_marketing_consent_question',
                'participant_privacy_phone_script',
                'participant_consent_changed_subject',
                'participant_consent_changed',
                'online_booking_link',
                'staff_privacy',
                'company_privacy'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // modify fields
            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('feedbackpositive', 'feedbacknegative', 'observation', 'induction', 'appraisal', 'disciplinary', 'misc', 'payroll', 'pupilassessment', 'late')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('staff_notes', $fields);

            // remove fields
            $this->dbforge->drop_column('family_contacts', 'marketing_consent');
            $this->dbforge->drop_column('family_contacts', 'marketing_consent_date');
            $this->dbforge->drop_column('family_contacts', 'privacy_agreed');
            $this->dbforge->drop_column('family_contacts', 'privacy_agreed_date');
            $this->dbforge->drop_column('staff', 'privacy_agreed');
            $this->dbforge->drop_column('staff', 'privacy_agreed_date');
            $this->dbforge->drop_column('family_notes', 'type');
        }
}