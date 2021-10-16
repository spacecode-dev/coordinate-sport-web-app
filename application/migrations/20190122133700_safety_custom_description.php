<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Safety_custom_description extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify settings fields
            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations', 'privacy', 'safety')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            // define new settings
            $data = array(
                array(
                    'key' => 'safety_risk_desc',
                    'title' => 'Risk Assessment Description of Task/Process',
                    'type' => 'wysiwyg',
                    'section' => 'safety',
                    'order' => 10,
                    'value' => '<p>This Risk Assessment was carried out on the behalf of {company} to ensure the safety of the coach and participants during PE and after school sessions at this location.</p>
					<ul>
						<li>{company} coaches are fully trained and specialists in the sports they teach.</li>
						<li>It is a requirement that all {company} Coaches conduct their own safety check of the area before every session to ensure the area is safe for use.</li>
						<li>All {company} Coaches are First Aid trained and Fully DBS checked.</li>
					</ul>',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
				array(
                    'key' => 'safety_camp_desc',
                    'title' => 'Event/Project Induction Description of Task/Process',
                    'type' => 'wysiwyg',
                    'section' => 'safety',
                    'order' => 20,
                    'value' => '<p>This checklist should be completed in conjunction with the Risk Assessment of the School Areas being used during lessons.</p>
					<p>The Information obtained from the school ensure new personnel are made aware of, are issued with, and understand the following:</p>',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
				array(
                    'key' => 'safety_school_desc',
                    'title' => 'School Induction Description of Task/Process',
                    'type' => 'wysiwyg',
                    'section' => 'safety',
                    'order' => 30,
                    'value' => '<p>This checklist should be completed in conjunction with the Risk Assessment of the School Areas being used during lessons.</p>
					<p>The Information obtained from the school ensure new personnel are made aware of, are issued with, and understand the following:</p>',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'safety_camp_desc',
                'safety_risk_desc',
                'safety_school_desc'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // modify fields
            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations', 'privacy')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);
        }
}
