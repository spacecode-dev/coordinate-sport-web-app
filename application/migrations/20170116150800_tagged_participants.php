<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tagged_participants extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'tagID' => array(
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
            $this->dbforge->add_key('tagID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('settings_tags', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_tags') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_tags') . '` ADD FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // create child/tags link table
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
                'childID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'tagID' => array(
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
            $this->dbforge->add_key('childID');
            $this->dbforge->add_key('tagID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('family_children_tags', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_children_tags') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_children_tags') . '` ADD FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_children_tags') . '` ADD CONSTRAINT `fk_family_children_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `' . $this->db->dbprefix('settings_tags') . '`(`tagID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // create contact/tags link table
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
                'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'tagID' => array(
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
            $this->dbforge->add_key('contactID');
            $this->dbforge->add_key('tagID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('family_contacts_tags', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_contacts_tags') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_contacts_tags') . '` ADD FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_contacts_tags') . '` ADD CONSTRAINT `fk_family_contacts_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `' . $this->db->dbprefix('settings_tags') . '`(`tagID`) ON DELETE CASCADE ON UPDATE CASCADE');


        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_children_tags') . '` DROP FOREIGN KEY `fk_family_children_tags_tagID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_contacts_tags') . '` DROP FOREIGN KEY `fk_family_contacts_tags_tagID`');

            // remove tables, if exist
            $this->dbforge->drop_table('family_children_tags', TRUE);
            $this->dbforge->drop_table('family_contacts_tags', TRUE);
            $this->dbforge->drop_table('settings_tags', TRUE);
        }
}