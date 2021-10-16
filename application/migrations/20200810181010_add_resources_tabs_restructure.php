<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Resources_tabs_restructure extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {
            // remove table, if exists
            $this->dbforge->drop_table('settings_resourcefile_map', TRUE);
            $this->dbforge->drop_table('settings_resources', TRUE);

         
            // define fields
            $fields = array(
                'resourceID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'permissionLevel' => array(
                    'type' => "ENUM('coaching','office','management','directors','headcoach','fulltimecoach')",
                    'null' => FALSE
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
                'active' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
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
            $this->dbforge->add_key('resourceID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('settings_resources', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resources') . '` ADD CONSTRAINT `fk_settings_resources_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resources') . '` ADD CONSTRAINT `fk_settings_resources_staffID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // define fields
            $fields = array(
                'attachmentID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'resourceID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
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
            $this->dbforge->add_key('attachmentID');
            $this->dbforge->add_key('resourceID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('settings_resourcefile_map', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resourcefile_map') . '` ADD CONSTRAINT `fk_settings_resourcefile_map_resourceID` FOREIGN KEY (`resourceID`) REFERENCES `' . $this->db->dbprefix('settings_resources') . '`(`resourceID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resourcefile_map') . '` ADD CONSTRAINT `fk_settings_resourcefile_map_attachmentID` FOREIGN KEY (`attachmentID`) REFERENCES `' . $this->db->dbprefix('files') . '`(`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resourcefile_map') . '` ADD CONSTRAINT `fk_settings_resourcefile_map_staffID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            $resourceTypes = array(
                'plans' => 'Session Plans',
                'school' => 'School Documents',
                'camp' => 'Camp Documents',
                'policies' => 'Staff Policies',
                'staff' => 'Staff Templates',
                'office' => 'Office Templates',
                'misc' => 'Misc.'
            );

            $min_permission_level = array(
                'plans' => 'headcoach',
                'school' => 'headcoach',
                'camp' => 'headcoach',
                'policies' => 'office',
                'staff' => 'coaching',
                'office' => 'office',
                'misc' => 'office'
            );

            //get all files
            $files = $this->db->from('files')->get();
            $results = $files->result();
            foreach ($results as $file) {
                $accountID =$file->accountID;
                $attachmentID  =$file->attachmentID;

                    if($file->category !=null & $file->category !=''   ){
                        //get allowed permission levels for the category
                        $allowed_permission_level  =  $min_permission_level[$file->category] ;
                        $resourceName = $resourceTypes[$file->category];

                        $where = array(
                            'accountID' => $accountID	,
                            'permissionLevel'=> $allowed_permission_level ,
                            'name'=> $resourceName
                        );
                        $resourceID =NULL;
                        $query = $this->db->get("settings_resources");
                        $settings_resources_query = $this->db->from('settings_resources')->where($where)->get();
                            //create or update resource
                            if ($settings_resources_query->num_rows() > 0) {
                                foreach($settings_resources_query->result() as $row){
                                    $resourceID = $row->resourceID;
                                }
                            }else{
                                $data = array(
                                    'accountID' => $accountID,
                                    'name' => $resourceName,
                                    'permissionLevel'=> $allowed_permission_level,
                                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                    'modified' => mdate('%Y-%m-%d %H:%i:%s'),
                                );
                                $res = $this->db->insert('settings_resources', $data);
                                $resourceID = $this->db->insert_id();

                        }

                        //map file with resource table
                        if($resourceID !=NULL){
                            $data = array(
                                'attachmentID' => $attachmentID,
                                'resourceID' => $resourceID,
                                'added' => mdate('%Y-%m-%d %H:%i:%s')
                            );
                            $res = $this->db->insert('settings_resourcefile_map', $data);
                        }
                    }else{
                        //category is null
                    }
            }
			 
        }

        public function down() {
            // remove foreign keys

            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resource_types') . '` DROP FOREIGN KEY `fk_settings_resourcefile_map_resourceID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resource_types') . '` DROP FOREIGN KEY `fk_settings_resourcefile_map_attachmentID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resource_types') . '` DROP FOREIGN KEY `fk_settings_resourcefile_map_staffID`');

            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resources') . '` DROP FOREIGN KEY `fk_settings_resources_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('settings_resources') . '` DROP FOREIGN KEY`fk_settings_resources_staffID`');

            // remove table, if exists
            $this->dbforge->drop_table('settings_resourcefile_map', TRUE);
            $this->dbforge->drop_table('settings_resources', TRUE);
        }
}
