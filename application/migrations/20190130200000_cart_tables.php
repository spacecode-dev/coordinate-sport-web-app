<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cart_tables extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // create cart table
            $fields = array(
                'cartID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'familyID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'childcarevoucher_providerID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'type' => array(
                    'type' => "ENUM('cart', 'booking')",
                    'null' => FALSE
                ),
				'subtotal' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'discount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'total' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'balance' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'source' => array(
                    'type' => "ENUM('Twitter', 'Facebook', 'Website', 'Email', 'SMS', 'Flyer', 'Newspaper', 'Poster', 'Referral', 'Existing Customer', 'Other')",
                    'null' => TRUE
                ),
                'source_other' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE
                ),
                'payment_reminder_before' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
                    'null' => FALSE
                ),
				'payment_reminder_after' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
                    'null' => FALSE
                ),
				'childcarevoucher_provider' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE
				),
				'childcarevoucher_ref' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE
				),
				'added' => array(
                    'type' => 'DATETIME'
                ),
				'booked' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('cartID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('familyID');
			$this->dbforge->add_key('contactID');
			$this->dbforge->add_key('childcarevoucher_providerID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_cart', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` ADD CONSTRAINT `fk_bookings_cart_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` ADD CONSTRAINT `fk_bookings_cart_familyID` FOREIGN KEY (`familyID`) REFERENCES `' . $this->db->dbprefix('family') . '`(`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` ADD CONSTRAINT `fk_bookings_cart_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` ADD CONSTRAINT `fk_bookings_cart_childcarevoucher_providerID` FOREIGN KEY (`childcarevoucher_providerID`) REFERENCES `' . $this->db->dbprefix('settings_childcarevoucherproviders') . '`(`providerID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` ADD CONSTRAINT `fk_bookings_cart_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE SET NULL ON UPDATE CASCADE');

			// create cart sessions table
            $fields = array(
                'sessionID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'cartID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'blockID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'lessonID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'childID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'date' => array(
                    'type' => 'DATE'
                ),
				'price' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'discount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'total' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
				'balance' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
					'default' => 0,
                    'null' => FALSE
                ),
                'attended' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
                    'null' => FALSE
                ),
				'bikeability_level' => array(
					'type' => 'VARCHAR',
					'constraint' => 5,
					'null' => TRUE
				),
				'shapeup_weight' => array(
					'type' => 'DECIMAL',
					'constraint' => '5,2',
					'null' => TRUE
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
            $this->dbforge->add_key('sessionID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('cartID');
			$this->dbforge->add_key('bookingID');
			$this->dbforge->add_key('blockID');
			$this->dbforge->add_key('lessonID');
			$this->dbforge->add_key('contactID');
			$this->dbforge->add_key('childID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_cart_sessions', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_blockID` FOREIGN KEY (`blockID`) REFERENCES `' . $this->db->dbprefix('bookings_blocks') . '`(`blockID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_lessonID` FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_childID` FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` ADD CONSTRAINT `fk_bookings_cart_sessions_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE SET NULL ON UPDATE CASCADE');

			// create cart monitoring table
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
                'cartID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'childID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'monitoring1' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring2' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring3' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring4' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring5' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring6' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring7' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring8' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring9' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'monitoring10' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
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
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('cartID');
			$this->dbforge->add_key('bookingID');
			$this->dbforge->add_key('contactID');
			$this->dbforge->add_key('childID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_cart_monitoring', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` ADD CONSTRAINT `fk_bookings_cart_monitoring_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` ADD CONSTRAINT `fk_bookings_cart_monitoring_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` ADD CONSTRAINT `fk_bookings_cart_monitoring_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` ADD CONSTRAINT `fk_bookings_cart_monitoring_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` ADD CONSTRAINT `fk_bookings_cart_monitoring_childID` FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` ADD CONSTRAINT `fk_bookings_cart_monitoring_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE SET NULL ON UPDATE CASCADE');

			// create cart bikeability table
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
                'cartID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'childID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'bikeability_level' => array(
					'type' => 'INT',
					'constraint' => 1,
					'null' => TRUE
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
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('cartID');
			$this->dbforge->add_key('bookingID');
			$this->dbforge->add_key('contactID');
			$this->dbforge->add_key('childID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_cart_bikeability', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` ADD CONSTRAINT `fk_bookings_cart_bikeability_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` ADD CONSTRAINT `fk_bookings_cart_bikeability_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` ADD CONSTRAINT `fk_bookings_cart_bikeability_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` ADD CONSTRAINT `fk_bookings_cart_bikeability_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` ADD CONSTRAINT `fk_bookings_cart_bikeability_childID` FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` ADD CONSTRAINT `fk_bookings_cart_bikeability_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE SET NULL ON UPDATE CASCADE');

        }

        public function down() {
            // remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_cartID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_bookingID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_contactID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_childID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_byID`');

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` DROP FOREIGN KEY `fk_bookings_cart_monitoring_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` DROP FOREIGN KEY `fk_bookings_cart_monitoring_cartID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` DROP FOREIGN KEY `fk_bookings_cart_monitoring_bookingID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` DROP FOREIGN KEY `fk_bookings_cart_monitoring_contactID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` DROP FOREIGN KEY `fk_bookings_cart_monitoring_childID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_monitoring') . '` DROP FOREIGN KEY `fk_bookings_cart_monitoring_byID`');

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_cartID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_bookingID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_blockID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_lessonID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_contactID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_childID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_sessions') . '` DROP FOREIGN KEY `fk_bookings_cart_sessions_byID`');

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` DROP FOREIGN KEY `fk_bookings_cart_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` DROP FOREIGN KEY `fk_bookings_cart_familyID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` DROP FOREIGN KEY `fk_bookings_cart_contactID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` DROP FOREIGN KEY `fk_bookings_cart_childcarevoucher_providerID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` DROP FOREIGN KEY `fk_bookings_cart_byID`');

            // remove tables, if exist
			$this->dbforge->drop_table('bookings_cart_bikeability', TRUE);
			$this->dbforge->drop_table('bookings_cart_monitoring', TRUE);
			$this->dbforge->drop_table('bookings_cart_sessions', TRUE);
			$this->dbforge->drop_table('bookings_cart', TRUE);
        }
}
