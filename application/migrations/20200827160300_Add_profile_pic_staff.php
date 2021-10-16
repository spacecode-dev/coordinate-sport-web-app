<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_profile_pic_staff extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // add 'Demo' Values from status field
            $fields = array(
                'profile_pic' => array(
                    'type' => "text",
                    'after' => 'nationalInsurance',
					'default' => NULL,
                )
            );

            $this->dbforge->add_column('staff', $fields);
        }

        public function down() {
            $this->dbforge->remove_column('staff', $fields);
        }
}