<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Brand_cleanup extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // remove old brand fields
            if ($this->db->field_exists('brand', 'bookings')) {
                $this->dbforge->drop_column('bookings', 'brand');
                $this->dbforge->drop_column('files', 'send_with_bookings');
                $this->dbforge->drop_column('files', 'send_with_bookings_education');
                $this->dbforge->drop_column('files', 'send_with_bookings_training');
                $this->dbforge->drop_column('files', 'send_with_bookings_development');
                $this->dbforge->drop_column('files', 'send_with_bookings_cycle');
                $this->dbforge->drop_column('files', 'send_with_bookings_kids');
                $this->dbforge->drop_column('orgs_contacts', 'newsletter_group');
                $this->dbforge->drop_column('orgs_contacts', 'newsletter_education');
                $this->dbforge->drop_column('orgs_contacts', 'newsletter_training');
                $this->dbforge->drop_column('orgs_contacts', 'newsletter_development');
                $this->dbforge->drop_column('orgs_contacts', 'newsletter_cycle');
                $this->dbforge->drop_column('orgs_contacts', 'newsletter_kids');
                $this->dbforge->drop_column('family_contacts', 'newsletter_group');
                $this->dbforge->drop_column('family_contacts', 'newsletter_education');
                $this->dbforge->drop_column('family_contacts', 'newsletter_training');
                $this->dbforge->drop_column('family_contacts', 'newsletter_development');
                $this->dbforge->drop_column('family_contacts', 'newsletter_cycle');
                $this->dbforge->drop_column('family_contacts', 'newsletter');
            }
        }

        public function down() {
            // nothing to do
        }
}