<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Message_templates extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define brand quals fields
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'staffID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'name' => array(
                    'type' => 'TEXT',
                    'null' => FALSE
                ),
                'subject' => array(
                    'type' => 'TEXT',
                    'null' => FALSE
                ),
                'message' => array(
                    'type' => 'TEXT',
                    'null' => FALSE
                ),
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('accountID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('message_templates', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('message_templates') . '` ADD FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('message_templates') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // define brand quals fields
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'templateID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'name' => array(
                    'type' => 'TEXT',
                    'null' => FALSE
                ),
                'path' => array(
                    'type' => 'TEXT',
                    'null' => FALSE
                ),
                'type' => array(
                    'type' => 'TEXT',
                    'null' => FALSE
                ),
                'ext' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => FALSE
                ),
                'size' => array(
                    'type' => 'INT',
                    'null' => FALSE
                ),
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('templateID');
            $this->dbforge->add_key('accountID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('message_templates_attachments', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('message_templates_attachments') . '` ADD FOREIGN KEY (`templateID`) REFERENCES `' . $this->db->dbprefix('message_templates') . '`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('message_templates_attachments') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');

        }

        public function down() {
            $this->dbforge->drop_table('message_templates', TRUE);
            $this->dbforge->drop_table('message_templates_attachments', TRUE);
        }
}