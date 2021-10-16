<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mandatory_quals extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'qualID' => array(
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
            $this->dbforge->add_key('qualID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('mandatory_quals', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('mandatory_quals') . '` ADD CONSTRAINT `fk_mandatory_quals_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('mandatory_quals') . '` ADD CONSTRAINT `fk_mandatory_quals_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // create staff mandatory quals table
            $fields = array(
                'linkID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'staffID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'qualID' => array(
                    'type' => 'INT',
                    'constraint' => 11
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
            $this->dbforge->add_key('linkID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('qualID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('staff_quals_mandatory', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_quals_mandatory') . '` ADD CONSTRAINT `fk_staff_quals_mandatory_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_quals_mandatory') . '` ADD CONSTRAINT `fk_staff_quals_mandatory_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_quals_mandatory') . '` ADD CONSTRAINT `fk_staff_quals_mandatory_qualID` FOREIGN KEY (`qualID`) REFERENCES `' . $this->db->dbprefix('mandatory_quals') . '`(`qualID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // migrate existing qual types
            $qual_types = array(
                'qual_level1' => 'Level 1 Coaching',
    			'qual_level2' => 'Level 2 Coaching'
            );

            // get accounts
    		$accounts = $this->db->from('accounts')->get();

            // populate accounts with existing qual types and migrate
    		foreach ($accounts->result() as $account) {
                // track IDs
                $qual_types_map = array();
                // loop through types and insert
                foreach ($qual_types as $type_alias => $type_name) {
                    $data = array(
                        'accountID' => $account->accountID,
    					'name' => $type_name,
                        'added' => mdate('%Y-%m-%d %H:%i:%s'),
    					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
    				);
                    $res = $this->db->insert('mandatory_quals', $data);
                    // track ID
                    $qual_types_map[$type_alias] = $this->db->insert_id();
                }

                // migrate staff mandatory quals
                $where = array(
                    'accountID' => $account->accountID
                );
        		$staff = $this->db->from('staff')->where($where)->get();
                if ($staff->num_rows() > 0) {
                    foreach ($staff->result() as $row) {
                        foreach ($qual_types_map as $type_alias => $qualID) {
                            if ($row->$type_alias == 1) {
                                $data = array(
                                    'accountID' => $account->accountID,
                					'staffID' => $row->staffID,
                                    'qualID' => $qualID,
                                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                				);
                                $res = $this->db->insert('staff_quals_mandatory', $data);
                            }
                        }
                    }
                }
            }

            // rename old fields
            $fields = array(
                'qual_level1' => array(
                    'name' => 'qual_level1_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0
                ),
                'qual_level2' => array(
                    'name' => 'qual_level2_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0
                )
            );
            $this->dbforge->modify_column('staff', $fields);
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('mandatory_quals') . '` DROP FOREIGN KEY `fk_mandatory_quals_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('mandatory_quals') . '` DROP FOREIGN KEY `fk_mandatory_quals_byID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_quals_mandatory') . '` DROP FOREIGN KEY `fk_staff_quals_mandatory_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_quals_mandatory') . '` DROP FOREIGN KEY `fk_staff_quals_mandatory_staffID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_quals_mandatory') . '` DROP FOREIGN KEY `fk_staff_quals_mandatory_qualID`');

            // rename old fields
            $fields = array(
                'qual_level1_old' => array(
                    'name' => 'qual_level1',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0
                ),
                'qual_level2_old' => array(
                    'name' => 'qual_level2',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0
                )
            );
            $this->dbforge->modify_column('staff', $fields);

            // remove tables, if exist
            $this->dbforge->drop_table('mandatory_quals', TRUE);
            $this->dbforge->drop_table('staff_quals_mandatory', TRUE);
        }
}