<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_fields extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // settings fields table
            $fields = array(
                'section' => array(
                    'type' => "ENUM('staff')",
                    'null' => FALSE
                ),
                'field' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ),
                'label' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ),
                'show' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE
                ),
                'required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE
                ),
                'order' => array(
                    'type' => 'INT',
                    'constraint' => 3,
                    'default' => 0,
                    'null' => FALSE
                ),
                'locked' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
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
            $this->dbforge->add_key('section', TRUE);
            $this->dbforge->add_key('field', TRUE);

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('settings_fields', FALSE, $attributes);

            // accounts fields table
            $fields = array(
                'fieldID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'section' => array(
                    'type' => "ENUM('staff')",
                    'null' => FALSE
                ),
                'field' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ),
                'show' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE
                ),
                'required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE
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
            $this->dbforge->add_key('fieldID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key(array('section', 'field'));

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('accounts_fields', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_fields') . '` ADD CONSTRAINT `fk_accounts_fields_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_fields') . '` ADD CONSTRAINT `fk_accounts_fields_rel` FOREIGN KEY (`section`, `field`) REFERENCES `' . $this->db->dbprefix('settings_fields') . '`(`section`, `field`) ON DELETE CASCADE ON UPDATE CASCADE');

            // insert
            $data = array(
                // personal info
                array(
                    'section' => 'staff',
                    'field' => 'title',
                    'label' => 'Title',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 100,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'first',
                    'label' => 'First Name',
                    'show' => 1,
                    'required' => 1,
                    'locked' => 1,
                    'order' => 101,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'middle',
                    'label' => 'Middle Name',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 102,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'surname',
                    'label' => 'Last Name',
                    'show' => 1,
                    'required' => 1,
                    'locked' => 1,
                    'order' => 103,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'jobTitle',
                    'label' => 'Job Title',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 104,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'department',
                    'label' => 'Permission Level',
                    'show' => 1,
                    'required' => 1,
                    'locked' => 1,
                    'order' => 105,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'non_delivery',
                    'label' => 'Non-delivery Staff',
                    'show' => 1,
                    'required' => 2,
                    'locked' => 0,
                    'order' => 106,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'brandID',
                    'label' => 'Primary Department',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 107,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'nationalInsurance',
                    'label' => 'NI Number',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 108,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'dob',
                    'label' => 'Date of Birth',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 109,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                // login info
                array(
                    'section' => 'staff',
                    'field' => 'email',
                    'label' => 'Email Address',
                    'show' => 1,
                    'required' => 1,
                    'locked' => 1,
                    'order' => 200,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'password',
                    'label' => 'Password',
                    'show' => 1,
                    'required' => 1,
                    'locked' => 1,
                    'order' => 201,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'password_confirm',
                    'label' => 'Confirm Password',
                    'show' => 1,
                    'required' => 1,
                    'locked' => 1,
                    'order' => 202,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'notify',
                    'label' => 'Send login details by email',
                    'show' => 1,
                    'required' => 2,
                    'locked' => 0,
                    'order' => 203,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                // contact info
                array(
                    'section' => 'staff',
                    'field' => 'address1',
                    'label' => 'Address 1',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 300,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'address2',
                    'label' => 'Address 2',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 301,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'town',
                    'label' => 'Town',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 302,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'county',
                    'label' => 'County',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 303,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'postcode',
                    'label' => 'Post Code',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 304,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'fromM',
                    'label' => 'At Address From',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 305,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'phone',
                    'label' => 'Phone',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 306,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'mobile',
                    'label' => 'Mobile',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 307,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'mobile_work',
                    'label' => 'Work Mobile',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 308,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                // emergency contact
                array(
                    'section' => 'staff',
                    'field' => 'eName',
                    'label' => 'Emergency Contact',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 348,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'eRelationship',
                    'label' => 'Relationship to Staff',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 349,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'eAddress1',
                    'label' => 'Address 1',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 350,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'eAddress2',
                    'label' => 'Address 2',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 351,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'eTown',
                    'label' => 'Town',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 352,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'eCounty',
                    'label' => 'County',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 353,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'ePostcode',
                    'label' => 'Post Code',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 354,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'ePhone',
                    'label' => 'Phone',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 355,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'eMobile',
                    'label' => 'Mobile',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 356,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                // equal ops
                array(
                    'section' => 'staff',
                    'field' => 'equal_ethnic',
                    'label' => 'Ethnic Origin',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 400,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'equal_disability',
                    'label' => 'Disability Information',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 401,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'equal_source',
                    'label' => 'Where did you find us?',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 402,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                // misc
                array(
                    'section' => 'staff',
                    'field' => 'medical',
                    'label' => 'Medical Information',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 500,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'tshirtSize',
                    'label' => 'T-Shirt Size',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 501,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff',
                    'field' => 'onsite',
                    'label' => 'Show profile on web site',
                    'show' => 1,
                    'required' => 2,
                    'locked' => 0,
                    'order' => 502,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );
            $this->db->insert_batch('settings_fields', $data);
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_fields') . '` DROP FOREIGN KEY `fk_accounts_fields_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_fields') . '` DROP FOREIGN KEY `fk_accounts_fields_rel`');

            // remove tables, if exist
            $this->dbforge->drop_table('accounts_fields', TRUE);
            $this->dbforge->drop_table('settings_fields', TRUE);
        }
}