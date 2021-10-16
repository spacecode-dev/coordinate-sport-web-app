<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Brand_labels extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add labels to plans
            $fields = array(
                'label_brand' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'after' => 'addons_all'
                ),
                'label_brands' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'after' => 'label_brand'
                )
            );
            $this->dbforge->add_column('accounts_plans', $fields);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('accounts_plans', 'label_brand');
            $this->dbforge->drop_column('accounts_plans', 'label_brands');
        }
}