<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_blacklist_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // add blacklist field to family contacts
            $fields = array(
                'blacklisted' => array(
                    'type' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'after' => 'mc_synced'
                )
            );

            $this->dbforge->add_column('family_contacts', $fields);
        }

        public function down() {
            // remove blacklist field from family contacts
            $this->dbforge->drop_column('family_contacts', 'blacklisted');
        }
}