<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_types extends CI_Migration {

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
                'show_dashboard' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'exclude_autodiscount' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'show_label_register' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'birthday_tab' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
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
            $this->dbforge->create_table('lesson_types', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('lesson_types') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('lesson_types') . '` ADD FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // add type ID to lessons
            $fields = array(
                'typeID' => array(
                    'type' => "INT",
                    'constraint' => 11,
                    'null' => TRUE,
                    'after' => 'activity_desc'
                )
            );
            $this->dbforge->add_column('bookings_lessons', $fields);

            // add key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` ADD INDEX (`typeID`)');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` ADD CONSTRAINT `fk_bookings_lessons_typeID` FOREIGN KEY (`typeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // create voucher lesson types table
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
                'voucherID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'typeID' => array(
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
            $this->dbforge->add_key('voucherID');
            $this->dbforge->add_key('typeID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('vouchers_lesson_types', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('vouchers_lesson_types') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('vouchers_lesson_types') . '` ADD FOREIGN KEY (`voucherID`) REFERENCES `' . $this->db->dbprefix('vouchers') . '`(`voucherID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('vouchers_lesson_types') . '` ADD CONSTRAINT `fk_vouchers_lesson_types_typeID` FOREIGN KEY (`typeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // create orgs pricing table
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
                'orgID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'typeID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'amount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2'
                ),
                'contract' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
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
            $this->dbforge->add_key('orgID');
            $this->dbforge->add_key('typeID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('orgs_pricing', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` ADD FOREIGN KEY (`orgID`) REFERENCES `' . $this->db->dbprefix('orgs') . '`(`orgID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` ADD CONSTRAINT `fk_orgs_pricing_typeID` FOREIGN KEY (`typeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // create bookings pricing table
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
                'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'typeID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'amount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2'
                ),
                'contract' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
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
            $this->dbforge->add_key('bookingID');
            $this->dbforge->add_key('typeID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_pricing', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_pricing') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_pricing') . '` ADD FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_pricing') . '` ADD CONSTRAINT `fk_bookings_pricing_typeID` FOREIGN KEY (`typeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // set existing lesson types
            $lesson_types = array(
                'ppa' => 'PPA',
    			'pe' => 'PE Development',
    			'extracurricular' => 'Extra Curricular',
    			'academy' => 'Academy',
    			'holcamp' => 'Holiday Camp',
    			'earlydropoff' => 'EDO',
    			'latepickup' => 'LPU',
    			'staff' => 'Staff Event',
    			'project' => 'Project',
    			'bikeability' => 'Bikeability',
    			'oneoff' => 'One Off',
    			'training' => 'Training',
    			'enrichment' => 'Enrichment',
                'birthday' => 'Birthday'
            );

            // get accounts
    		$accounts = $this->db->from('accounts')->get();

            // populate accounts with existing lesson types and migrate
    		foreach ($accounts->result() as $account) {
                // track IDs
                $lesson_types_map = array();
                // loop through lesson_types and insert
                foreach ($lesson_types as $type_alias => $type_name) {
                    $data = array(
                        'accountID' => $account->accountID,
    					'name' => $type_name,
                        'added' => mdate('%Y-%m-%d %H:%i:%s'),
    					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
    				);
                    switch ($type_alias) {
                        case 'staff':
                            $data['show_dashboard'] = 1;
                            break;
                        case 'birthday':
                            $data['birthday_tab'] = 1;
                            break;
                        case 'earlydropoff':
                        case 'latepickup':
                            $data['exclude_autodiscount'] = 1;
                            $data['show_label_register'] = 1;
                            break;
                    }
                    $res = $this->db->insert('lesson_types', $data);
                    // track ID
                    $lesson_types_map[$type_alias] = $this->db->insert_id();
                }

                // migrate existing lessons to new lesson type IDs
                foreach ($lesson_types_map as $type_alias => $typeID) {
                    $where = array(
                        'accountID' => $account->accountID,
                        'type' => $type_alias
                    );
                    $data = array(
                        'typeID' => $typeID
                    );
                    // save
                    $res = $this->db->update('bookings_lessons', $data, $where);
                }

                // migrate voucher lesson types
                $where = array(
                    'accountID' => $account->accountID
                );
        		$vouchers = $this->db->from('vouchers')->where($where)->get();
                if ($vouchers->num_rows() > 0) {
                    foreach ($vouchers->result() as $voucher) {
                        $applies_to = explode(",", $voucher->applies_to);
                        if (is_array($applies_to)) {
                            foreach ($lesson_types_map as $type_alias => $typeID) {
                                if (in_array($type_alias, $applies_to)) {
                                    $data = array(
                                        'accountID' => $account->accountID,
                    					'voucherID' => $voucher->voucherID,
                                        'typeID' => $typeID,
                                        'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                        'modified' => mdate('%Y-%m-%d %H:%i:%s')
                    				);
                                    $res = $this->db->insert('vouchers_lesson_types', $data);
                                }
                            }
                        }
                    }
                }

                // migrate org pricing
                $where = array(
                    'accountID' => $account->accountID
                );
        		$orgs = $this->db->from('orgs')->where($where)->get();
                if ($orgs->num_rows() > 0) {
                    foreach ($orgs->result() as $org) {
                        foreach ($lesson_types_map as $type_alias => $typeID) {
                            $key = 'price_' . $type_alias;
                            $key_contract = $key . '_contract';
                            if ($org->$key > 0 || $org->$key_contract == 1) {
                                $data = array(
                                    'accountID' => $account->accountID,
                					'orgID' => $org->orgID,
                                    'typeID' => $typeID,
                                    'amount' => floatval($org->$key),
                                    'contract' => $org->$key_contract,
                                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                				);
                                $res = $this->db->insert('orgs_pricing', $data);
                            }
                        }
                    }
                }

                // migrate bookings pricing
                $where = array(
                    'accountID' => $account->accountID
                );
        		$bookings = $this->db->from('bookings')->where($where)->get();
                if ($bookings->num_rows() > 0) {
                    foreach ($bookings->result() as $booking) {
                        foreach ($lesson_types_map as $type_alias => $typeID) {
                            $key = 'price_' . $type_alias;
                            $key_contract = $key . '_contract';
                            if ($booking->$key > 0 || $booking->$key_contract == 1) {
                                $data = array(
                                    'accountID' => $account->accountID,
                					'bookingID' => $booking->bookingID,
                                    'typeID' => $typeID,
                                    'amount' => floatval($booking->$key),
                                    'contract' => $booking->$key_contract,
                                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                				);
                                $res = $this->db->insert('bookings_pricing', $data);
                            }
                        }
                    }
                }
            }

            // rename old type field
            $fields = array(
                'type' => array(
                    'name' => 'type_old',
                    'type' => "ENUM('pe','ppa','ssp','extracurricular','oneoff','other','community','sportunlimited','leadersaward','holcamp','academy','birthday','staff','earlydropoff','latepickup','bikeability','project','training','enrichment')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings_lessons', $fields);

            // rename voucher applies to field
            $fields = array(
                'applies_to' => array(
                    'name' => 'applies_to_old',
                    'type' => "SET('ppa','pe','extracurricular','academy','holcamp','earlydropoff','latepickup','staff','project','bikeability','oneoff','training','enrichment','other')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('vouchers', $fields);

            // rename orgs + bookings pricing fields
            $fields = array(
                'price_pe' => array(
                    'name' => 'price_pe_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_pe_contract' => array(
                    'name' => 'price_pe_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_ppa' => array(
                    'name' => 'price_ppa_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_ppa_contract' => array(
                    'name' => 'price_ppa_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_ssp' => array(
                    'name' => 'price_ssp_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_ssp_contract' => array(
                    'name' => 'price_ssp_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_extracurricular' => array(
                    'name' => 'price_extracurricular_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_extracurricular_contract' => array(
                    'name' => 'price_extracurricular_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_oneoff' => array(
                    'name' => 'price_oneoff_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_oneoff_contract' => array(
                    'name' => 'price_oneoff_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_other' => array(
                    'name' => 'price_other_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_other_contract' => array(
                    'name' => 'price_other_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_other' => array(
                    'name' => 'price_other_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_other_contract' => array(
                    'name' => 'price_other_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_community' => array(
                    'name' => 'price_community_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_community_contract' => array(
                    'name' => 'price_community_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_sportunlimited' => array(
                    'name' => 'price_sportunlimited_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_sportunlimited_contract' => array(
                    'name' => 'price_sportunlimited_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_leadersaward' => array(
                    'name' => 'price_leadersaward_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_leadersaward_contract' => array(
                    'name' => 'price_leadersaward_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_holcamp' => array(
                    'name' => 'price_holcamp_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_holcamp_contract' => array(
                    'name' => 'price_holcamp_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_academy' => array(
                    'name' => 'price_academy_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_academy_contract' => array(
                    'name' => 'price_academy_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_birthday' => array(
                    'name' => 'price_birthday_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_birthday_contract' => array(
                    'name' => 'price_birthday_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_staff' => array(
                    'name' => 'price_staff_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_staff_contract' => array(
                    'name' => 'price_staff_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_earlydropoff' => array(
                    'name' => 'price_earlydropoff_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_earlydropoff_contract' => array(
                    'name' => 'price_earlydropoff_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_latepickup' => array(
                    'name' => 'price_latepickup_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_latepickup_contract' => array(
                    'name' => 'price_latepickup_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_project' => array(
                    'name' => 'price_project_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_project_contract' => array(
                    'name' => 'price_project_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_bikeability' => array(
                    'name' => 'price_bikeability_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_bikeability_contract' => array(
                    'name' => 'price_bikeability_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_training' => array(
                    'name' => 'price_training_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_training_contract' => array(
                    'name' => 'price_training_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_enrichment' => array(
                    'name' => 'price_enrichment_old',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_enrichment_contract' => array(
                    'name' => 'price_enrichment_contract_old',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                )
            );
            $this->dbforge->modify_column('orgs', $fields);
            $this->dbforge->modify_column('bookings', $fields);
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` DROP FOREIGN KEY `fk_bookings_lessons_typeID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('vouchers_lesson_types') . '` DROP FOREIGN KEY `fk_vouchers_lesson_types_typeID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` DROP FOREIGN KEY `fk_orgs_pricing_typeID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_pricing') . '` DROP FOREIGN KEY `fk_bookings_pricing_typeID`');

            // remove fields
            $this->dbforge->drop_column('bookings_lessons', 'typeID');

            // rename old type field
            $fields = array(
                'type_old' => array(
                    'name' => 'type',
                    'type' => "ENUM('pe','ppa','ssp','extracurricular','oneoff','other','community','sportunlimited','leadersaward','holcamp','academy','birthday','staff','earlydropoff','latepickup','bikeability','project','training','enrichment')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings_lessons', $fields);

            // rename voucher applies to field
            $fields = array(
                'applies_to_old' => array(
                    'name' => 'applies_to',
                    'type' => "SET('ppa','pe','extracurricular','academy','holcamp','earlydropoff','latepickup','staff','project','bikeability','oneoff','training','enrichment','other')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('vouchers', $fields);

            // rename orgs + bookings pricing fields
            $fields = array(
                'price_pe_old' => array(
                    'name' => 'price_pe',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_pe_contract_old' => array(
                    'name' => 'price_pe_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_ppa_old' => array(
                    'name' => 'price_ppa',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_ppa_contract_old' => array(
                    'name' => 'price_ppa_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_ssp_old' => array(
                    'name' => 'price_ssp',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_ssp_contract_old' => array(
                    'name' => 'price_ssp_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_extracurricular_old' => array(
                    'name' => 'price_extracurricular',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_extracurricular_contract_old' => array(
                    'name' => 'price_extracurricular_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_oneoff_old' => array(
                    'name' => 'price_oneoff',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_oneoff_contract_old' => array(
                    'name' => 'price_oneoff_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_other_old' => array(
                    'name' => 'price_other',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_other_contract_old' => array(
                    'name' => 'price_other_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_other_old' => array(
                    'name' => 'price_other',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_other_contract_old' => array(
                    'name' => 'price_other_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_community_old' => array(
                    'name' => 'price_community',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_community_contract_old' => array(
                    'name' => 'price_community_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_sportunlimited_old' => array(
                    'name' => 'price_sportunlimited',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_sportunlimited_contract_old' => array(
                    'name' => 'price_sportunlimited_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_leadersaward_old' => array(
                    'name' => 'price_leadersaward',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_leadersaward_contract_old' => array(
                    'name' => 'price_leadersaward_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_holcamp_old' => array(
                    'name' => 'price_holcamp',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_holcamp_contract_old' => array(
                    'name' => 'price_holcamp_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_academy_old' => array(
                    'name' => 'price_academy',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_academy_contract_old' => array(
                    'name' => 'price_academy_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_birthday_old' => array(
                    'name' => 'price_birthday',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_birthday_contract_old' => array(
                    'name' => 'price_birthday_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_staff_old' => array(
                    'name' => 'price_staff',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_staff_contract_old' => array(
                    'name' => 'price_staff_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_earlydropoff_old' => array(
                    'name' => 'price_earlydropoff',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_earlydropoff_contract_old' => array(
                    'name' => 'price_earlydropoff_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_latepickup_old' => array(
                    'name' => 'price_latepickup',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_latepickup_contract_old' => array(
                    'name' => 'price_latepickup_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_project_old' => array(
                    'name' => 'price_project',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_project_contract_old' => array(
                    'name' => 'price_project_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_bikeability_old' => array(
                    'name' => 'price_bikeability',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_bikeability_contract_old' => array(
                    'name' => 'price_bikeability_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_training_old' => array(
                    'name' => 'price_training',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_training_contract_old' => array(
                    'name' => 'price_training_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'price_enrichment_old' => array(
                    'name' => 'price_enrichment',
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => TRUE
                ),
                'price_enrichment_contract_old' => array(
                    'name' => 'price_enrichment_contract',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                )
            );
            $this->dbforge->modify_column('orgs', $fields);
            $this->dbforge->modify_column('bookings', $fields);

            // remove tables, if exist
            $this->dbforge->drop_table('lesson_types', TRUE);
            $this->dbforge->drop_table('vouchers_lesson_types', TRUE);
            $this->dbforge->drop_table('orgs_pricing', TRUE);
            $this->dbforge->drop_table('bookings_pricing', TRUE);
        }
}