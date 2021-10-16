<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Project_types extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'typeID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ),
                'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('typeID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('project_types', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('project_types') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('project_types') . '` ADD FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // add type ID to bookings
            $fields = array(
                'project_typeID' => array(
                    'type' => "INT",
                    'constraint' => 11,
                    'null' => TRUE,
                    'after' => 'project'
                )
            );
            $this->dbforge->add_column('bookings', $fields);

            // add key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings') . '` ADD INDEX (`project_typeID`)');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings') . '` ADD CONSTRAINT `fk_bookings_project_typeID` FOREIGN KEY (`project_typeID`) REFERENCES `' . $this->db->dbprefix('project_types') . '`(`typeID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // default project types
            $project_types = array(
                'Commercial',
                'Funded'
            );

            // get accounts
    		$accounts = $this->db->from('accounts')->get();

            // populate accounts with existing activities and migrate
    		foreach ($accounts->result() as $account) {
                // add default project types
                foreach ($project_types as $type) {
                    $data = array(
                        'accountID' => $account->accountID,
                        'name' => $type,
                        'added' => mdate('%Y-%m-%d %H:%i:%s'),
    					'modified' => mdate('%Y-%m-%d %H:%i:%s')
                    );
                    $this->db->insert('project_types', $data);
                }
            }

        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings') . '` DROP FOREIGN KEY `fk_bookings_project_typeID`');

            // remove fields
            $this->dbforge->drop_column('bookings', 'project_typeID');

            // remove table, if exists
            $this->dbforge->drop_table('project_types', TRUE);
        }
}