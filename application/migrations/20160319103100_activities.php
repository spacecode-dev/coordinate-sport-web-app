<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Activities extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'activityID' => array(
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
            $this->dbforge->add_key('activityID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('activities', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('activities') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('activities') . '` ADD FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // add activity ID to lessons
            $fields = array(
                'activityID' => array(
                    'type' => "INT",
                    'constraint' => 11,
                    'null' => TRUE,
                    'after' => 'group_other'
                )
            );
            $this->dbforge->add_column('bookings_lessons', $fields);

            // add key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` ADD INDEX (`activityID`)');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` ADD CONSTRAINT `fk_bookings_lessons_activityID` FOREIGN KEY (`activityID`) REFERENCES `' . $this->db->dbprefix('activities') . '`(`activityID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // create staff activities table
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
                'activityID' => array(
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
            $this->dbforge->add_key('activityID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('staff_activities', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_activities') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_activities') . '` ADD FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_activities') . '` ADD CONSTRAINT `fk_staff_activities_activityID` FOREIGN KEY (`activityID`) REFERENCES `' . $this->db->dbprefix('activities') . '`(`activityID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // set existing activities
            $activities = array(
                'games' => 'Games',
    			'dance' => 'Dance',
    			'gymnastics' => 'Gymnastics',
    			'athletics' => 'Athletics',
    			'oaa' => 'OAA',
    			'bikeability' => 'Bikeability',
    			'holiday camps' => 'Holiday Camps'
            );

            // get accounts
    		$accounts = $this->db->from('accounts')->get();

            // populate accounts with existing activities and migrate
    		foreach ($accounts->result() as $account) {
                // track IDs
                $activities_map = array();
                // loop through activities and insert
                foreach ($activities as $activity_alias => $activity_name) {
                    $data = array(
                        'accountID' => $account->accountID,
    					'name' => $activity_name,
                        'added' => mdate('%Y-%m-%d %H:%i:%s'),
    					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
    				);
                    $res = $this->db->insert('activities', $data);
                    // track ID
                    $activities_map[$activity_alias] = $this->db->insert_id();
                }

                // migrate existing lessons to new activities IDs
                foreach ($activities_map as $activity_alias => $activityID) {
                    $where = array(
                        'accountID' => $account->accountID,
                        'activity' => $activity_alias
                    );
                    $data = array(
                        'activityID' => $activityID
                    );
                    // save
                    $res = $this->db->update('bookings_lessons', $data, $where);
                }

                // migrate staff activities
                $where = array(
                    'accountID' => $account->accountID
                );
        		$staff = $this->db->from('staff')->where($where)->get();
                if ($staff->num_rows() > 0) {
                    foreach ($staff->result() as $staff_member) {
                        foreach ($activities_map as $activity_alias => $activityID) {
                            $key = 'activity_' . str_replace(" ", "", $activity_alias);
                            if (isset($staff_member->$key) && $staff_member->$key == 1) {
                                $data = array(
                                    'accountID' => $account->accountID,
                					'staffID' => $staff_member->staffID,
                                    'activityID' => $activityID,
                                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                				);
                                $res = $this->db->insert('staff_activities', $data);
                            }
                        }
                    }
                }
            }

            // rename old activity field
            $fields = array(
                'activity' => array(
                    'name' => 'activity_old',
                    'type' => "ENUM('games','dance','gymnastics','sport','cheerleading','other','athletics','oaa','bikeability','holiday camps')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings_lessons', $fields);

            // rename staff activity fields
            $fields = array(
                'activity_games' => array(
                    'name' => 'activity_games_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_sport' => array(
                    'name' => 'activity_sport_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_dance' => array(
                    'name' => 'activity_dance_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_gymnastics' => array(
                    'name' => 'activity_gymnastics_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_cheer' => array(
                    'name' => 'activity_cheer_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_oaa' => array(
                    'name' => 'activity_oaa_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_athletics' => array(
                    'name' => 'activity_athletics_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_bikeability' => array(
                    'name' => 'activity_bikeability_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_holidaycamps' => array(
                    'name' => 'activity_holidaycamps_old',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('staff', $fields);
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` DROP FOREIGN KEY `fk_bookings_lessons_activityID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_activities') . '` DROP FOREIGN KEY `fk_staff_activities_activityID`');

            // remove fields
            $this->dbforge->drop_column('bookings_lessons', 'activityID');

            // rename old activity field
            $fields = array(
                'activity_old' => array(
                    'name' => 'activity',
                    'type' => "ENUM('games','dance','gymnastics','sport','cheerleading','other','athletics','oaa','bikeability','holiday camps')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings_lessons', $fields);

            // rename staff activity fields
            $fields = array(
                'activity_games_old' => array(
                    'name' => 'activity_games',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_sport_old' => array(
                    'name' => 'activity_sport',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_dance_old' => array(
                    'name' => 'activity_dance',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_gymnastics_old' => array(
                    'name' => 'activity_gymnastics',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_cheer_old' => array(
                    'name' => 'activity_cheer',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_oaa_old' => array(
                    'name' => 'activity_oaa',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_athletics_old' => array(
                    'name' => 'activity_athletics',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_bikeability_old' => array(
                    'name' => 'activity_bikeability',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                ),
                'activity_holidaycamps_old' => array(
                    'name' => 'activity_holidaycamps',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('staff', $fields);

            // remove table, if exists
            $this->dbforge->drop_table('activities', TRUE);

            // remove table, if exists
            $this->dbforge->drop_table('staff_activities', TRUE);
        }
}