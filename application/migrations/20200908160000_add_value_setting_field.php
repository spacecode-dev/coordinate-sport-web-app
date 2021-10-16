<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_value_setting_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {
			
			$fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('staff', 'staff_recruitment', 'account_holder', 'participant')",
                    'null' => FALSE,
                )
            );
			$this->dbforge->modify_column('settings_fields', $fields);	
			
			$fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('staff', 'staff_recruitment', 'account_holder', 'participant')",
                    'null' => FALSE,
                )
            );
			$this->dbforge->modify_column('accounts_fields', $fields);	
			
			/* Account Holder Profile Fields */
			$data = array("section" => "account_holder",
			"field" => "title",
			"label" => "Title",
			"show" => 1,
			"required" => 0,
			"order" => 1001,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "first_name",
			"label" => "First Name",
			"show" => 1,
			"required" => 1,
			"order" => 1002,
			"locked" => 1);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "last_name",
			"label" => "Last Name",
			"show" => 1,
			"required" => 1,
			"order" => 1003,
			"locked" => 1);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "gender",
			"label" => "Gender",
			"show" => 1,
			"required" => 0,
			"order" => 1004,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "dob",
			"label" => "Date of Birth",
			"show" => 1,
			"required" => 0,
			"order" => 1005,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "medical",
			"label" => "Medical Notes",
			"show" => 1,
			"required" => 0,
			"order" => 1006,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "disability_info",
			"label" => "Disability Information",
			"show" => 1,
			"required" => 0,
			"order" => 1007,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "ethnic_origin",
			"label" => "Ethnic Origin",
			"show" => 1,
			"required" => 0,
			"order" => 1008,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "eRelationship",
			"label" => "Relationship to Account Holder",
			"show" => 1,
			"required" => 0,
			"order" => 1009,
			"locked" => 0);
			
			$this->db->insert("settings_fields", $data);
		  
			$data = array("section" => "account_holder",
			"field" => "profile_pic",
			"label" => "Profile Picture",
			"show" => 1,
			"required" => 0,
			"order" => 1010,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "address1",
			"label" => "Address",
			"show" => 1,
			"required" => 0,
			"order" => 1011,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "address2",
			"label" => "Address 2",
			"show" => 1,
			"required" => 0,
			"order" => 1012,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "address3",
			"label" => "Address 3",
			"show" => 1,
			"required" => 0,
			"order" => 1013,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "town",
			"label" => "Town",
			"show" => 1,
			"required" => 0,
			"order" => 1014,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "county",
			"label" => "County",
			"show" => 1,
			"required" => 0,
			"order" => 1015,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "postcode",
			"label" => "Post Code",
			"show" => 1,
			"required" => 1,
			"order" => 1016,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "mobile",
			"label" => "Mobile",
			"show" => 1,
			"required" => 0,
			"order" => 1017,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "phone",
			"label" => "Other Phone",
			"show" => 1,
			"required" => 0,
			"order" => 1018,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "workPhone",
			"label" => "Work Phone",
			"show" => 1,
			"required" => 0,
			"order" => 1019,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "email",
			"label" => "Email",
			"show" => 1,
			"required" => 1,
			"order" => 1020,
			"locked" => 1);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "notify",
			"label" => "Send login details by email",
			"show" => 1,
			"required" => 2,
			"order" => 1021,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			
			$data = array("section" => "account_holder",
			"field" => "emergency_contact_1_name",
			"label" => "Emergency Contact 1",
			"show" => 1,
			"required" => 0,
			"order" => 1022,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "emergency_contact_1_phone",
			"label" => "Contact Number",
			"show" => 1,
			"required" => 0,
			"order" => 1023,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "emergency_contact_2_name",
			"label" => "Emergency Contact 2",
			"show" => 1,
			"required" => 0,
			"order" => 1024,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "emergency_contact_2_phone",
			"label" => "Contact Number",
			"show" => 1,
			"required" => 0,
			"order" => 1025,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "blacklisted",
			"label" => "Block contact from making bookings ",
			"show" => 1,
			"required" => 0,
			"order" => 1026,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "account_holder",
			"field" => "tags",
			"label" => "Tags",
			"show" => 1,
			"required" => 0,
			"order" => 1027,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			/* Participant Profile Fields */
			
			$data = array("section" => "participant",
			"field" => "first_name",
			"label" => "First Name",
			"show" => 1,
			"required" => 1,
			"order" => 1028,
			"locked" => 1);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "last_name",
			"label" => "Last Name",
			"show" => 1,
			"required" => 1,
			"order" => 1029,
			"locked" => 1);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "gender",
			"label" => "Gender",
			"show" => 1,
			"required" => 0,
			"order" => 1030,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "dob",
			"label" => "Date of Birth",
			"show" => 1,
			"required" => 1,
			"order" => 1031,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "orgID",
			"label" => "School",
			"show" => 1,
			"required" => 1,
			"order" => 1032,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "pin",
			"label" => "Pickup PIN",
			"show" => 1,
			"required" => 0,
			"order" => 1033,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "medical",
			"label" => "Medical Notes",
			"show" => 1,
			"required" => 0,
			"order" => 1034,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "disability_info",
			"label" => "Disability Information",
			"show" => 1,
			"required" => 0,
			"order" => 1035,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "ethnic_origin",
			"label" => "Ethnic Origin",
			"show" => 1,
			"required" => 0,
			"order" => 1036,
			"locked" => 0);
			
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "photoConsent",
			"label" => "Photo Consent",
			"show" => 1,
			"required" => 0,
			"order" => 1037,
			"locked" => 0);
			
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "tags",
			"label" => "Tags",
			"show" => 1,
			"required" => 0,
			"order" => 1038,
			"locked" => 0);
			
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "profile_pic",
			"label" => "Profile Picture",
			"show" => 1,
			"required" => 0,
			"order" => 1039,
			"locked" => 0);
			
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "emergency_contact_1_name",
			"label" => "Emergency Contact 1",
			"show" => 1,
			"required" => 0,
			"order" => 1040,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "emergency_contact_1_phone",
			"label" => "Contact Number",
			"show" => 1,
			"required" => 0,
			"order" => 1041,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "emergency_contact_2_name",
			"label" => "Emergency Contact 2",
			"show" => 1,
			"required" => 0,
			"order" => 1042,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			$data = array("section" => "participant",
			"field" => "emergency_contact_2_phone",
			"label" => "Contact Number",
			"show" => 1,
			"required" => 0,
			"order" => 1043,
			"locked" => 0);
		  
			$this->db->insert("settings_fields", $data);
			
			
        }

        public function down() {
            $where_in = array(
            'account_holder'
			);
			$this->db->from('settings_fields')->where_in('section', $where_in)->delete();
			$where_in = array(
            'participant'
			);
			$this->db->from('settings_fields')->where_in('section', $where_in)->delete();
			
			$fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('staff', 'staff_recruitment')",
                    'null' => FALSE,
                )
            );

            $this->dbforge->modify_column('settings_fields', $fields);
			
			$fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('staff', 'staff_recruitment')",
                    'null' => FALSE,
                )
            );
			$this->dbforge->modify_column('accounts_fields', $fields);	
        }
}
